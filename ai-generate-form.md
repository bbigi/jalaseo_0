# AI Article Generation Form - Development Guidelines

## Overview
Form untuk generate artikel dengan AI yang akan menjadi **default form** di `/generate` dan bisa diakses sebagai **popup modal** dari modul lain (keywords, deep research, insights). Template bersifat **per user** (bukan global).

## Implementation Status: ✅ COMPLETED

**Implemented on:** 2025-12-17
**Routes:**
- `GET /generate` → [GenerateController@index](../app/Http/Controllers/GenerateController.php)
- `POST /generate/article` → [GenerateController@generateArticle](../app/Http/Controllers/GenerateController.php)
- `GET /generate/progress/{jobId}` → [GenerateController@getProgress](../app/Http/Controllers/GenerateController.php)

**Views:**
- [resources/views/generate/index.blade.php](../resources/views/generate/index.blade.php)

**Models:**
- [AiGenerateTemplate](../app/Models/AiGenerateTemplate.php)

**Jobs:**
- [GenerateArticleJob](../app/Jobs/GenerateArticleJob.php)

**Database:**
- Table: `ane_ai_generate_templates`
- Seeder: [AiGenerateTemplateSeeder](../database/seeders/AiGenerateTemplateSeeder.php) - 5 templates (Blog, News, Tutorial, Listicle, Review)

---

## 1. Alur Form (dari anewp)

### Input Sources (3 Opsi)
1. **Generate from Title** (default)
   - User input: Topic/Initial Title
   - AI generates full article dari title saja

2. **Generate from Reference (URL/Description)**
   - User input: Title + Reference Topic + Reference Description + Reference URL (optional)
   - AI reads URL (via Firecrawl API) atau pakai description
   - Cocok untuk insight-based articles

3. **Generate from Text (Manual Input)**
   - User input: Title + Reference Text
   - User input: Reference Source Name (optional, untuk attribution)
   - AI rewrites/expands text tersebut

### Output
- Article content (HTML dari AI)
- Auto-generated SEO meta description
- Auto-suggested title (optimized)
- Featured image (optional, via AI Image Generator)

---

## 2. Perubahan dari anewp → Laravel

### Yang Dihapus
- ❌ **Output Language** dropdown → Bahasa diambil dari template (user memilih bahasa di AI Template)
- ❌ **Article Format** dropdown (Blog/News) → Ganti jadi **Template Selector**

### Yang Ditambah
- ✅ **Website Selector** → Select target website (load categories)
- ✅ **Template Selector** → Dropdown template AI milik user (dari ane_ai_generate_templates)
- ✅ **Keyword Integration** → Auto-fill dari keyword yang sedang di-track

### Yang Tetap
- Generation Source (3 opsi)
- Title input (with auto-fill from context)
- Reference fields (conditional visibility)
- Progress tracking (with steps)

---

## 3. Database Schema

### Tables Required

#### ane_ai_generate_templates
```sql
CREATE TABLE ane_ai_generate_templates (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    article_type ENUM('blog', 'news', 'listicle', 'tutorial', 'review') DEFAULT 'blog',
    prompt_template LONGTEXT NOT NULL,
    language VARCHAR(10) DEFAULT 'id',
    is_active BOOLEAN DEFAULT 1,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES ane_users(id) ON DELETE SET NULL
);
```

**Sample Templates:**
- "Blog Post - Comprehensive Guide" (blog, id)
- "News Article - Breaking News" (news, id)
- "Listicle - Top 10 Format" (listicle, id)
- "Tutorial - Step-by-Step" (tutorial, id)
- "Product Review - Detailed Analysis" (review, id)

---

## 4. Routes

```php
// Generate Form
Route::get('/generate', [GenerateController::class, 'index'])->name('generate.index');
Route::post('/generate/article', [GenerateController::class, 'generateArticle'])->name('generate.article');
Route::get('/generate/progress/{jobId}', [GenerateController::class, 'getProgress'])->name('generate.progress');

// Template Management (Admin)
Route::prefix('admin')->middleware(['admin'])->group(function() {
    Route::resource('ai-templates', AiTemplateController::class);
});
```

---

## 5. Controller Logic

### GenerateController@index

**Responsibilities:**
- Load active websites (for dropdown)
- Load active AI templates milik user (for dropdown)
- Check if context exists (from keyword/deep-research/insight)
- Pre-fill form jika ada context

**Context Sources:**
```php
// From URL query params
$context = [
    'source' => request('source'), // 'keyword', 'deep_research', 'insight'
    'source_id' => request('source_id'),
    'keyword' => request('keyword'),
    'title' => request('title'),
    'description' => request('description'),
    'url' => request('url'),
];

// Load related data
if ($context['source'] === 'keyword') {
    $keyword = Keyword::find($context['source_id']);
    // Pre-fill title, website, etc.
}
```

**Return:**
```php
return view('generate.index', [
    'websites' => $websites,
    'templates' => $templates,
    'context' => $context,
]);
```

---

### GenerateController@generateArticle

**Responsibilities:**
- Validate input
- Dispatch background job (Laravel Queue)
- Return job ID for progress tracking

**Validation:**
```php
$validated = $request->validate([
    'generation_source' => 'required|in:title,reference,text',
    'title' => 'required|string|max:255',
    'template_id' => 'required|exists:ane_ai_generate_templates,id',
    'website_id' => 'required|exists:ane_websites,id',

    // Conditional (if source = reference)
    'ref_topic' => 'nullable|string',
    'ref_description' => 'nullable|string',
    'ref_url' => 'nullable|url',

    // Conditional (if source = text)
    'ref_text' => 'nullable|string',
    'ref_source_name' => 'nullable|string',
]);
```

**Job Dispatch:**
```php
$jobId = Str::uuid()->toString();

GenerateArticleJob::dispatch([
    'job_id' => $jobId,
    'user_id' => auth()->id(),
    'generation_source' => $validated['generation_source'],
    'title' => $validated['title'],
    'template_id' => $validated['template_id'],
    'website_id' => $validated['website_id'],
    'references' => [
        'topic' => $validated['ref_topic'] ?? null,
        'description' => $validated['ref_description'] ?? null,
        'url' => $validated['ref_url'] ?? null,
        'text' => $validated['ref_text'] ?? null,
        'source_name' => $validated['ref_source_name'] ?? null,
    ],
]);

return response()->json([
    'success' => true,
    'job_id' => $jobId,
]);
```

---

### GenerateController@getProgress

**Responsibilities:**
- Poll job progress from cache/database
- Return current status + progress percentage

**Progress Stages:**
```php
[
    'status' => 'processing', // 'queued', 'processing', 'completed', 'failed'
    'progress' => 45, // 0-100
    'step' => 'Generating article content...', // Current step description
    'result' => null, // Filled when status = 'completed'
]
```

**Storage:**
```php
// Store progress in cache with TTL
Cache::put("generate_job:{$jobId}", [
    'status' => 'processing',
    'progress' => 30,
    'step' => 'Analyzing topic...',
], now()->addMinutes(10));
```

---

## 6. Background Job (GenerateArticleJob)

### Job Flow

```php
class GenerateArticleJob implements ShouldQueue
{
    public function handle()
    {
        $this->updateProgress(10, 'Initializing...');

        // 1. Load template
        $template = AiGenerateTemplate::find($this->data['template_id']);
        $this->updateProgress(20, 'Loading template...');

        // 2. Prepare context for AI
        $context = $this->prepareContext();
        $this->updateProgress(30, 'Preparing content strategy...');

        // 3. Build AI prompt from template
        $prompt = $this->buildPrompt($template, $context);
        $this->updateProgress(40, 'Contacting AI service...');

        // 4. Generate article with AIService
        $aiService = app(AIService::class);
        $result = $aiService->generate($prompt, 'article_generation', [
            'max_tokens' => 4096,
            'temperature' => 0.7,
        ]);
        $this->updateProgress(70, 'Processing AI response...');

        // 5. Parse result + generate SEO
        $article = $this->parseArticle($result['content']);
        $this->updateProgress(85, 'Generating SEO metadata...');

        // 6. (Optional) Generate featured image
        if ($this->data['generate_image'] ?? false) {
            $image = $this->generateFeaturedImage($article['title']);
            $article['featured_image'] = $image;
        }
        $this->updateProgress(95, 'Finalizing...');

        // 7. Store result
        $this->updateProgress(100, 'Complete!', [
            'article' => $article,
        ]);
    }

    protected function prepareContext(): array
    {
        $source = $this->data['generation_source'];

        if ($source === 'title') {
            return [
                'title' => $this->data['title'],
            ];
        }

        if ($source === 'reference') {
            $context = [
                'title' => $this->data['title'],
                'topic' => $this->data['references']['topic'] ?? null,
                'description' => $this->data['references']['description'] ?? null,
            ];

            // Try to fetch URL content if provided
            if ($url = $this->data['references']['url'] ?? null) {
                $context['url_content'] = $this->fetchUrlContent($url);
            }

            return $context;
        }

        if ($source === 'text') {
            return [
                'title' => $this->data['title'],
                'reference_text' => $this->data['references']['text'],
                'source_name' => $this->data['references']['source_name'] ?? 'reference source',
            ];
        }
    }

    protected function buildPrompt($template, $context): string
    {
        // Replace placeholders in template
        $prompt = $template->prompt_template;

        foreach ($context as $key => $value) {
            $prompt = str_replace("{{" . $key . "}}", $value, $prompt);
        }

        return $prompt;
    }

    protected function updateProgress($percentage, $step, $result = null)
    {
        Cache::put("generate_job:{$this->data['job_id']}", [
            'status' => $percentage === 100 ? 'completed' : 'processing',
            'progress' => $percentage,
            'step' => $step,
            'result' => $result,
        ], now()->addMinutes(10));
    }
}
```

---

## 7. View Structure

### File: resources/views/generate/index.blade.php

**Layout:**
```blade
@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">AI Article Generator</h1>
        <p class="text-gray-500 mt-1">Generate high-quality articles with AI assistance</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form id="generateForm" @submit.prevent="handleSubmit">
            <!-- Website Selector -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Target Website <span class="text-red-500">*</span>
                </label>
                <select name="website_id" required>
                    <option value="">Select website...</option>
                    @foreach($websites as $site)
                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Template Selector -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Template <span class="text-red-500">*</span>
                </label>
                <select name="template_id" required>
                    @foreach($templates as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->name }} - {{ $tpl->description }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Generation Source -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Generation Source <span class="text-red-500">*</span>
                </label>
                <select name="generation_source" id="generationSource" required>
                    <option value="title">Generate from Title</option>
                    <option value="reference">Generate from Reference (URL/Description)</option>
                    <option value="text">Generate from Text (Manual Input)</option>
                </select>
            </div>

            <!-- Title Input -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Topic/Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" required
                       value="{{ $context['title'] ?? '' }}"
                       placeholder="Enter article topic or title">
            </div>

            <!-- Reference Fields (Conditional) -->
            <div id="referenceFields" class="hidden mb-4 p-4 bg-gray-50 rounded-lg border">
                <!-- Reference Topic, Description, URL -->
            </div>

            <!-- Text Field (Conditional) -->
            <div id="textField" class="hidden mb-4">
                <!-- Reference Text, Source Name -->
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">
                Generate Article
            </button>
        </form>
    </div>
</div>

<!-- Progress Modal -->
<div id="progressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <h3 class="text-lg font-semibold mb-4">Generating Article...</h3>

            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all" style="width: 0%"></div>
            </div>
            <p id="progressText" class="text-sm text-gray-600">Initializing...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Handle form submission
// Poll progress endpoint
// Show result modal
</script>
@endpush
```

---

## 8. Template Prompt Format

### Example Template: "Blog Post - Comprehensive Guide"

```
You are an expert content writer creating a comprehensive blog post in {{language}}.

**Topic:** {{title}}

{{#if reference_description}}
**Reference Information:**
{{reference_description}}
{{/if}}

{{#if url_content}}
**Source Content:**
{{url_content}}
{{/if}}

{{#if reference_text}}
**Reference Text to Expand:**
{{reference_text}}

Attribution: "dilansir dari {{source_name}}"
{{/if}}

**Instructions:**
1. Write a comprehensive, SEO-optimized blog post (1500-2000 words)
2. Use clear headings (H2, H3) for structure
3. Include actionable tips and examples
4. Write in a conversational, engaging tone
5. Optimize for keyword: {{title}}
6. Include a conclusion with call-to-action

**Output Format:**
{
  "title": "SEO-optimized article title",
  "content": "Full HTML content with proper formatting",
  "meta_description": "SEO meta description (max 160 chars)",
  "focus_keyword": "primary keyword",
  "featured_image_prompt": "AI image generation prompt for featured image"
}

Return ONLY valid JSON.
```

---

## 9. Integration Points

### Akses dari Modul Lain

#### 1. Keywords Page
```blade
<a href="{{ route('generate.index', [
    'source' => 'keyword',
    'source_id' => $keyword->id,
    'keyword' => $keyword->keyword,
    'title' => $keyword->keyword,
]) }}" class="btn btn-primary">
    Generate Article
</a>
```

#### 2. Deep Research (Main Targets)
```blade
<button onclick="openGenerateModal({
    source: 'deep_research',
    title: '{{ $target['keyword'] }}',
    suggested_title: '{{ $target['suggested_titles'][0] }}'
})">
    Generate
</button>
```

#### 3. Insights
```blade
<a href="{{ route('generate.index', [
    'source' => 'insight',
    'source_id' => $insight->id,
    'title' => $insight->topic,
    'description' => $insight->description,
    'url' => $insight->source_url,
]) }}">
    Create Article
</a>
```

---

## 10. Frontend JavaScript Logic

### Key Functions

```javascript
// Toggle conditional fields
function toggleGenerationSource() {
    const source = document.getElementById('generationSource').value;
    const refFields = document.getElementById('referenceFields');
    const textField = document.getElementById('textField');

    refFields.classList.toggle('hidden', source !== 'reference');
    textField.classList.toggle('hidden', source !== 'text');
}

// Handle form submit
async function handleSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    // Show progress modal
    showProgressModal();

    // Submit to backend
    const response = await fetch('/generate/article', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(data),
    });

    const result = await response.json();

    if (result.success) {
        // Start polling progress
        pollProgress(result.job_id);
    }
}

// Poll progress
async function pollProgress(jobId) {
    const interval = setInterval(async () => {
        const response = await fetch(`/generate/progress/${jobId}`);
        const data = await response.json();

        // Update progress bar
        document.getElementById('progressBar').style.width = data.progress + '%';
        document.getElementById('progressText').textContent = data.step;

        if (data.status === 'completed') {
            clearInterval(interval);
            showResult(data.result.article);
        }

        if (data.status === 'failed') {
            clearInterval(interval);
            showError(data.error);
        }
    }, 2000); // Poll every 2 seconds
}
```

---

## 11. Implementation Checklist

### Phase 1: Backend Setup
- [ ] Create migration for `ane_ai_generate_templates`
- [ ] Seed sample templates
- [ ] Create AiGenerateTemplate model
- [ ] Create GenerateController with index/generateArticle/getProgress
- [ ] Create GenerateArticleJob (queue worker)
- [ ] Add routes to web.php

### Phase 2: Template System
- [ ] Create template CRUD (admin panel)
- [ ] Design prompt templates with placeholders
- [ ] Test template rendering with different contexts

### Phase 3: Frontend
- [ ] Create generate/index.blade.php
- [ ] Implement conditional field toggling
- [ ] Add progress modal with polling
- [ ] Style with Tailwind CSS

### Phase 4: Integrations
- [ ] Add "Generate Article" buttons to Keywords page
- [ ] Add "Generate" buttons to Deep Research main targets
- [ ] Add "Create Article" link to Insights

### Phase 5: Testing
- [ ] Test all 3 generation sources
- [ ] Test with different templates
- [ ] Test progress tracking
- [ ] Test error handling

---

## 12. Error Handling

### Common Errors

1. **AI API Failed**
   - Retry with fallback provider
   - Show error: "AI service temporarily unavailable"

2. **URL Fetch Failed** (Firecrawl)
   - Fallback to description only
   - Warning: "Could not fetch URL content, using description"

3. **Queue Timeout**
   - Max execution time: 60 seconds
   - If timeout, mark job as failed

4. **Invalid Template**
   - Validate template before job dispatch
   - Show error: "Selected template is invalid"

---

## 13. Future Enhancements

- [ ] Save generated articles as drafts (ane_posts)
- [ ] Auto-publish to WordPress option
- [ ] Bulk generate from multiple keywords
- [ ] Custom template builder UI
- [ ] A/B testing for title variations
- [ ] Content quality scoring
- [ ] Plagiarism check integration

---

## Notes

- Form ini adalah **default form** untuk semua AI generation
- Bisa standalone (`/generate`) atau modal popup dari modul lain
- Template system membuat AI prompts lebih flexible & customizable
- Background job memastikan no timeout untuk AI generation
- Progress tracking memberi UX yang lebih baik vs blocking request
