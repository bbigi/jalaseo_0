# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Project Overview

**AneWP** is a multi-tenant SaaS platform for AI-powered content creation with WordPress integration. Built on Laravel 12, it supports multiple AI providers (Gemini, ChatGPT, Claude, DeepSeek) and manages website analysis, keyword tracking, and content generation.

**Framework:** Laravel 12 (PHP 8.2+)
**Database:** MySQL (production) / SQLite (testing)
**Frontend:** Tailwind CSS 4 + Vite
**Current Phase:** Phase 2 Complete (Website Management), Phase 3 In Progress

---

## Essential Commands

### Development Environment

```bash
# Start development server with all services
composer dev

# Or start individually:
php artisan serve --host=127.0.0.1 --port=8000
npm run dev                    # Vite asset compilation
php artisan queue:listen       # Queue worker
php artisan pail              # Log monitoring

# Initial setup
composer setup                 # Runs install, env setup, migrations, npm build

# Clear caches (run after config/route changes)
php artisan config:clear && php artisan cache:clear
```

### Database Operations

```bash
# Run migrations
php artisan migrate

# Fresh database (WARNING: deletes all data)
php artisan migrate:fresh

# Access Tinker REPL
php artisan tinker
```

### Testing

```bash
# Run all tests
composer test
# Or: php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# List available tests
php artisan test --list-tests
```

### Code Quality

```bash
# Laravel Pint (code formatting)
./vendor/bin/pint

# Run Pint on specific files
./vendor/bin/pint app/Models
```

---

## Architecture Overview

### Multi-Tenant Model

This is a **shared-resource SaaS** where:
- Multiple users (5-10+) share the same API credentials concurrently
- All queries MUST filter by `created_by = auth()->id()` for data isolation
- API credentials are platform-wide (no user ownership)
- User quotas are tracked per-user, not per-API-key

**Critical Rule:** NEVER expose data across users. Always scope queries to authenticated user.

### Request-Based Quota System

Users have monthly quotas tracked in `ane_users` table:
- `api_quota_limit` - Maximum requests per month (10 for free, 50 for starter, 200 for pro)
- `api_quota_used` - Current usage count
- `api_quota_reset_at` - Next reset date

Before expensive operations (AI generation), check quota:
```php
if (auth()->user()->api_quota_used >= auth()->user()->api_quota_limit) {
    throw new QuotaExceededException();
}
// After success:
auth()->user()->increment('api_quota_used', 1);
```

### Data Encryption

Two models use Laravel's `Crypt` for AES-256-CBC encryption:

1. **ApiCredential** ([app/Models/ApiCredential.php](app/Models/ApiCredential.php)):
   - `api_key` field auto-encrypts on write, decrypts on read
   - Use `getMaskedApiKey()` for display (shows first/last 4 chars)

2. **Website** ([app/Models/Website.php](app/Models/Website.php)):
   - `app_password` field (WordPress app password) encrypted
   - Use `getMaskedAppPassword()` for display

**Important:** Encryption requires `APP_KEY` set in `.env`. Never query encrypted fields directly in WHERE clauses.

### AI Service Architecture

The **AIService** ([app/Services/AIService.php](app/Services/AIService.php)) provides unified interface for multiple AI providers:

```php
$aiService->generate(
    prompt: "Write an article about...",
    category: 'article_generation',
    options: ['provider' => 'gemini', 'max_tokens' => 2000]
);
```

**Provider Priority (auto-fallback):**
1. Gemini (primary) - Model: `gemini-2.5-flash`
2. ChatGPT (secondary) - Model: `gpt-4o-mini`
3. Claude (tertiary) - Model: `claude-3-5-sonnet-20241022`
4. DeepSeek (fallback) - Model: `deepseek-chat`

Each provider method returns:
```php
[
    'content' => string,
    'tokens_used' => int,
    'provider' => string,
    'model' => string,
    'raw_response' => array
]
```

---

## Database Schema (Key Tables)

### ane_users
Multi-tenant users with subscription and quota tracking.

**Key columns:**
- `role` - 'user' or 'super_admin'
- `subscription_plan` - 'free', 'starter', 'pro'
- `api_quota_limit` / `api_quota_used` / `api_quota_reset_at`
- `email_verified_at` - Required for access

**Indices:** email (unique), role

### ane_api_credentials
Platform-wide API keys (shared across users).

**Key columns:**
- `category` - 'article_generation', 'image_generation', 'research'
- `provider` - 'gemini', 'chatgpt', 'claude', 'deepseek'
- `api_key` - ENCRYPTED with AES-256
- `is_active` - Enable/disable key

**Constraint:** UNIQUE(category, provider)

### ane_websites
WordPress sites owned by users.

**Key columns:**
- `created_by` - FK to ane_users.id (CASCADE delete)
- `app_password` - ENCRYPTED WordPress app password
- `category` - 14 options (business_industrial, technology_software, etc.)
- `status` - 'active' or 'inactive'

**Indices:** name (unique), (created_by, status)

### ane_website_analyses
Analysis results (structure, SEO, performance).

**Relations:** `website_id` → ane_websites.id

### ane_keywords
Extracted keywords per website.

**Relations:** `website_id` → ane_websites.id
**Constraint:** UNIQUE(website_id, keyword)

### ane_keyword_traces
Keyword ranking history.

**Relations:** `keyword_id` → ane_keywords.id

---

## Routing Structure

All routes defined in [routes/web.php](routes/web.php).

**Public Routes:**
- `GET /` - Welcome page
- `GET|POST /login` - Authentication
- `GET|POST /register` - User registration
- Email verification endpoints

**Protected Routes** (`auth` + `verified` middleware):
- `GET /dashboard` - User dashboard
- `GET|POST /generate` - AI content generation
- Website CRUD: `/websites`, `/websites/create`, `/websites/{id}`, etc.
- `POST /websites/{id}/test-connection` - Test WordPress connection

**Admin Routes** (`auth` + `verified` + `admin` middleware):
- `GET /admin/dashboard` - Admin stats
- API credentials management: `/admin/api-keys/*`
- `PATCH /admin/api-keys/{id}/toggle-active` - Enable/disable key

### Authorization Policy

**WebsitePolicy** ([app/Policies/WebsitePolicy.php](app/Policies/WebsitePolicy.php)):
- Users can only view/edit/delete websites where `created_by = auth()->id()`
- Enforced via `$this->authorize('view', $website)` in controllers

---

## Test Users & Data Consistency

**CRITICAL: Always use these existing test users. Do NOT create new users without updating README.md.**

### Regular User (for feature testing)
- Email: `test@example.com`
- Password: `Test1234!`
- Role: `user`
- Plan: `free` (10 requests/month)

### Admin User (for admin panel testing)
- Email: `wasugi@example.com`
- Password: `Test1234!`
- Role: `super_admin`
- Plan: `premium` (1000 requests/month)

**If database is reset, recreate users via:**
```bash
php artisan tinker
```
See [README.md](README.md#recreate-users-if-database-reset) for exact commands.

---

## Development Guidelines

### Data Isolation (Multi-Tenant Security)

**ALWAYS filter by user:**
```php
// ✅ CORRECT
Website::where('created_by', auth()->id())->get();

// ❌ WRONG (exposes all users' data)
Website::all();
```

### Performance Best Practices

1. **Avoid N+1 queries** - Use eager loading:
   ```php
   Website::with('owner', 'keywords')->where('created_by', auth()->id())->get();
   ```

2. **Use pagination** for large datasets:
   ```php
   Website::where('created_by', auth()->id())->paginate(20);
   ```

3. **Heavy operations use queues** - AI generation, website analysis, keyword tracking should dispatch jobs, not block HTTP responses.

### Adding New Features

When creating new functionality:

1. **Create migration** with proper indices:
   ```bash
   php artisan make:migration create_table_name
   ```
   - Index foreign keys (`created_by`)
   - Index frequently queried columns (`status`, `category`)
   - Add composite indices for common WHERE combinations

2. **Create model** with relationships and encryption (if needed)

3. **Create policy** if authorization required:
   ```bash
   php artisan make:policy ModelNamePolicy
   ```

4. **Create controller** - Follow RESTful conventions

5. **Add routes** in [routes/web.php](routes/web.php) with proper middleware

6. **Create views** in `resources/views/`

7. **Update documentation**:
   - Update [README.md](README.md) with new routes/features
   - Add user data changes to User System section
   - Document database schema changes

### Code Quality Rules

**ALWAYS:**
- Filter queries by `created_by` for user-owned resources
- Use Eloquent ORM, avoid raw queries for sensitive data
- Use model accessors/mutators for encryption, never raw `Crypt::` in queries
- Validate ownership with policies before update/delete
- Check quota before expensive operations
- Test with `test@example.com` user
- Clear config cache after changes

**NEVER:**
- Create users without documenting in README
- Store sensitive data unencrypted
- Skip authorization checks
- Use deprecated Laravel helpers (e.g., `str_limit()` → `Str::limit()`)
- Expose data across users

### Laravel 12 Compatibility Notes

- Use `Illuminate\Support\Str::limit()` instead of `str_limit()`
- Use `Illuminate\Support\Arr::*` for array helpers
- Check [Laravel 12 upgrade guide](https://laravel.com/docs/12.x/upgrade) for changes

### Heroicons Usage (Blade Components)

**Package:** `blade-ui-kit/blade-heroicons` (installed)

**Syntax:**
```blade
{{-- Outline icons (24x24) - prefix: o- --}}
<x-heroicon-o-home class="w-6 h-6" />
<x-heroicon-o-user class="w-5 h-5" />
<x-heroicon-o-photo class="w-6 h-6" />
<x-heroicon-o-document-text class="w-5 h-5" />
<x-heroicon-o-trash class="w-5 h-5 text-red-500" />
<x-heroicon-o-pencil class="w-4 h-4" />
<x-heroicon-o-x-mark class="w-6 h-6" />

{{-- Solid icons (24x24) - prefix: s- --}}
<x-heroicon-s-home class="w-6 h-6" />
<x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />
<x-heroicon-s-x-circle class="w-5 h-5 text-red-500" />

{{-- Mini icons (20x20) - prefix: m- --}}
<x-heroicon-m-home class="w-5 h-5" />
<x-heroicon-m-check class="w-4 h-4" />
```

**Common Icons:**
- Navigation: `home`, `cog-6-tooth`, `user-circle`, `bars-3`, `x-mark`
- Actions: `pencil`, `trash`, `plus`, `minus`, `check`, `x-mark`
- Media: `photo`, `camera`, `film`, `musical-note`
- Documents: `document-text`, `folder`, `archive-box`, `clipboard-document`
- UI: `chevron-down`, `chevron-up`, `chevron-left`, `chevron-right`, `arrow-up-tray`, `arrow-down-tray`
- Status: `check-circle`, `x-circle`, `exclamation-triangle`, `information-circle`

**Full list:** https://heroicons.com/

**Important:**
- ALWAYS use Blade components (`<x-heroicon-*>`) instead of hardcoded SVG
- Icon names use kebab-case (e.g., `user-circle`, not `userCircle`)
- Default size is 24x24 (outline/solid) or 20x20 (mini), override with Tailwind classes

---

## Post Management Pattern (WordPress-style CRUD)

This section documents the **standard pattern** for WordPress-integrated post management, including featured image handling, category integration, and multi-relationship data persistence.

### Database Schema

**Core Tables:**
- `ane_posts` - Main post content with SEO fields
- `ane_media` - Media library (images, files)
- `ane_post_targets` - Publishing targets per website
- `ane_insights` - Content ideas/topics hub (optional)

**Key Columns in ane_posts:**
```sql
featured_media_id    BIGINT UNSIGNED NULL  -- FK to ane_media.id
insight_id           BIGINT UNSIGNED NULL  -- FK to ane_insights.id (optional)
title                VARCHAR(255)
content              LONGTEXT
article_type         ENUM('blog', 'news')
seo_focus_keyword    VARCHAR(150) NULL
seo_meta_description VARCHAR(255) NULL
source_type          VARCHAR(50) DEFAULT 'manual'
created_by           BIGINT UNSIGNED       -- FK to ane_users.id
```

**Key Columns in ane_post_targets:**
```sql
post_id              BIGINT UNSIGNED       -- FK to ane_posts.id
website_id           BIGINT UNSIGNED       -- FK to ane_websites.id
status               VARCHAR(20) DEFAULT 'draft'
categories           JSON NULL             -- WordPress category IDs array
tags                 JSON NULL             -- WordPress tag IDs array
scheduled_at         TIMESTAMP NULL
published_at         TIMESTAMP NULL
wp_post_id           INT NULL              -- WordPress post ID after publishing
```

**Critical Migration:**
```php
// Add featured_media_id to existing ane_posts table
Schema::table('ane_posts', function (Blueprint $table) {
    $table->unsignedBigInteger('featured_media_id')->nullable()->after('insight_id');
    $table->foreign('featured_media_id')->references('id')->on('ane_media')->onDelete('set null');
});
```

### Model Relationships

**Post.php ([app/Models/Post.php](app/Models/Post.php)):**
```php
protected $fillable = [
    'insight_id',
    'title',
    'content',
    'article_type',
    'seo_focus_keyword',
    'seo_meta_description',
    'featured_media_id',  // ✅ REQUIRED for featured image
    'source_type',
    'created_by',
];

// Featured image relationship
public function featuredMedia()
{
    return $this->belongsTo(Media::class, 'featured_media_id');
}

// Publishing targets (multiple websites)
public function targets()
{
    return $this->hasMany(PostTarget::class);
}

// Optional: Link to content idea
public function insight()
{
    return $this->belongsTo(Insight::class);
}
```

**PostTarget.php ([app/Models/PostTarget.php](app/Models/PostTarget.php)):**
```php
protected $fillable = [
    'post_id',
    'website_id',
    'status',
    'categories',  // JSON array
    'tags',        // JSON array
    'scheduled_at',
    'published_at',
    'wp_post_id',
];

protected $casts = [
    'categories' => 'array',  // ✅ Auto-cast JSON to array
    'tags' => 'array',
    'scheduled_at' => 'datetime',
    'published_at' => 'datetime',
];

public function post()
{
    return $this->belongsTo(Post::class);
}

public function website()
{
    return $this->belongsTo(Website::class);
}
```

### Controller Pattern

**PostController.php - store() method:**
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'insight_id' => 'nullable|exists:ane_insights,id',
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'article_type' => 'required|in:blog,news',
        'seo_focus_keyword' => 'nullable|string|max:150',
        'seo_meta_description' => 'nullable|string|max:255',
        'featured_media_id' => 'nullable|exists:ane_media,id',
        'website_id' => 'nullable|exists:ane_websites,id',
        'wp_category_id' => 'nullable|integer',
    ]);

    // Create main post
    $post = Post::create([
        'insight_id' => $validated['insight_id'] ?? null,
        'title' => $validated['title'],
        'content' => $validated['content'],
        'article_type' => $validated['article_type'],
        'seo_focus_keyword' => $validated['seo_focus_keyword'] ?? null,
        'seo_meta_description' => $validated['seo_meta_description'] ?? null,
        'featured_media_id' => $validated['featured_media_id'] ?? null,
        'source_type' => 'manual',
        'created_by' => auth()->id(),
    ]);

    // Create publishing target if website selected
    if ($request->website_id) {
        $post->targets()->create([
            'website_id' => $request->website_id,
            'categories' => $request->wp_category_id ? [$request->wp_category_id] : null,
            'status' => 'draft',
        ]);
    }

    return redirect()->route('posts.edit', $post->id)
        ->with('success', 'Post created successfully');
}
```

**PostController.php - edit() method:**
```php
public function edit(Post $post)
{
    $this->authorize('update', $post);

    // ✅ CRITICAL: Eager load relationships to avoid N+1 queries
    $post->load('featuredMedia', 'targets.website', 'insight');

    // Load user's websites for target selection
    $websites = Website::where('created_by', auth()->id())->get();

    // Optional: Load insights for autocomplete
    $insights = Insight::where('created_by', auth()->id())->get();

    return view('posts.edit', compact('post', 'websites', 'insights'));
}
```

**PostController.php - update() method:**
```php
public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);

    $validated = $request->validate([
        'insight_id' => 'nullable|exists:ane_insights,id',
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'article_type' => 'required|in:blog,news',
        'seo_focus_keyword' => 'nullable|string|max:150',
        'seo_meta_description' => 'nullable|string|max:255',
        'featured_media_id' => 'nullable|exists:ane_media,id',
        'website_id' => 'nullable|exists:ane_websites,id',
        'wp_category_id' => 'nullable|integer',
    ]);

    // Update main post
    $post->update([
        'insight_id' => $validated['insight_id'] ?? null,
        'title' => $validated['title'],
        'content' => $validated['content'],
        'article_type' => $validated['article_type'],
        'seo_focus_keyword' => $validated['seo_focus_keyword'] ?? null,
        'seo_meta_description' => $validated['seo_meta_description'] ?? null,
        'featured_media_id' => $validated['featured_media_id'] ?? null,
    ]);

    // Update publishing target (delete old, create new)
    if ($request->website_id) {
        $post->targets()->delete();
        $post->targets()->create([
            'website_id' => $request->website_id,
            'categories' => $request->wp_category_id ? [$request->wp_category_id] : null,
            'status' => 'draft',
        ]);
    }

    return redirect()->route('posts.edit', $post->id)
        ->with('success', 'Post updated successfully');
}
```

### WordPress Category Integration

**WebsiteController.php - getCategories() method:**
```php
public function getCategories(Website $website)
{
    $this->authorize('view', $website);

    $url = rtrim($website->url, '/') . '/wp-json/wp/v2/categories?per_page=100';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return response()->json(['error' => 'Failed to fetch categories'], 500);
    }

    return response()->json(json_decode($response, true));
}
```

**Route:**
```php
Route::get('/websites/{website}/categories', [WebsiteController::class, 'getCategories'])
    ->name('websites.categories');
```

### View Pattern (Blade Templates)

**Critical Layout Structure:**
```blade
@extends('layouts.dashboard')  {{-- NOT layouts.app --}}

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (Left: 2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Title, Content Editor -->
        </div>

        <!-- Sidebar (Right: 1/3) -->
        <div class="space-y-6">
            <!-- Featured Image, Category, SEO, Publish -->
        </div>
    </div>
</div>
@endsection
```

**Featured Image Selector:**
```blade
<!-- Hidden field for featured_media_id -->
<input type="hidden" name="featured_media_id" id="featuredMediaId"
       value="{{ old('featured_media_id', $post->featured_media_id ?? '') }}">

<!-- Preview -->
<div id="featuredImagePreview" class="{{ $post->featured_media_id ? '' : 'hidden' }}">
    <img id="featuredImageThumb"
         src="{{ $post->featuredMedia ? asset('storage/' . $post->featuredMedia->file_path) : '' }}"
         class="w-full h-auto rounded border">
    <button type="button" onclick="removeFeaturedImage()"
            class="text-red-600 text-sm mt-2">Remove</button>
</div>

<!-- Button to open modal -->
<button type="button" onclick="openMediaModal()"
        class="text-blue-600 text-sm">
    {{ $post->featured_media_id ? 'Change Image' : 'Set Featured Image' }}
</button>
```

**Media Modal (WordPress-style with Tabs):**
```blade
<!-- Modal Backdrop with BLUR -->
<div id="mediaModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white">Featured Image</h3>
                <button onclick="closeMediaModal()" class="text-gray-400 hover:text-white">✕</button>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-700">
                <button class="px-4 py-2 text-blue-500 border-b-2 border-blue-500">Media Library</button>
                <button class="px-4 py-2 text-gray-400">Upload Files</button>
            </div>

            <!-- Tab Content: Media Library (4-column grid) -->
            <div id="mediaLibrary" class="p-4 overflow-y-auto max-h-[60vh]">
                <div class="grid grid-cols-4 gap-4"></div>
            </div>

            <!-- Tab Content: Upload Files -->
            <div id="uploadFiles" class="hidden p-4">
                <div id="dropZone" class="border-2 border-dashed border-gray-600 rounded-lg p-8 text-center">
                    <p class="text-gray-400">Drag & drop or click to upload</p>
                    <input type="file" id="fileInput" class="hidden" accept="image/*">
                </div>
                <div id="uploadProgress" class="hidden mt-4">
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div id="progressBar" class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Media Grid Item (Square Aspect Ratio):**
```javascript
// ✅ CORRECT: Simple structure with inline aspect-ratio
<div class="rounded overflow-hidden border-2 border-gray-700 hover:border-blue-500 cursor-pointer transition-all"
     onclick="selectMedia(${item.id}, '${item.full_url}', '${item.title || item.file_name}')">
    <div class="relative bg-gray-900" style="aspect-ratio: 1/1;">
        <img src="${item.full_url}" alt="${item.alt_text || item.file_name}"
             class="absolute inset-0 w-full h-full object-cover">
    </div>
</div>
```

**WordPress Category Dropdown (Dynamic Loading):**
```blade
<select name="wp_category_id" id="wpCategory" class="w-full bg-gray-700 text-white">
    <option value="">Select Category</option>
</select>

<script>
document.getElementById('website').addEventListener('change', function() {
    const websiteId = this.value;
    const categoriesSelect = document.getElementById('wpCategory');

    if (!websiteId) {
        categoriesSelect.innerHTML = '<option value="">Select Category</option>';
        return;
    }

    fetch(`/websites/${websiteId}/categories`)
        .then(res => res.json())
        .then(categories => {
            categoriesSelect.innerHTML = '<option value="">Select Category</option>';

            // For EDIT mode: get saved category
            const savedCategoryId = {{ optional($post->targets->first())->categories[0] ?? 'null' }};

            categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = cat.name;

                // Auto-select saved category
                if (savedCategoryId && cat.id == savedCategoryId) {
                    option.selected = true;
                }

                categoriesSelect.appendChild(option);
            });
        });
});

// For EDIT mode: trigger on page load if website already selected
@if(optional($post->targets->first())->website_id)
    window.addEventListener('load', function() {
        document.getElementById('website').dispatchEvent(new Event('change'));
    });
@endif
</script>
```

### JavaScript Patterns

**1. Drag & Drop Upload:**
```javascript
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');

// Click to upload
dropZone.addEventListener('click', () => fileInput.click());

// Drag & drop
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-gray-700');
});

dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-gray-700');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-gray-700');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        uploadFile(files[0]);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        uploadFile(e.target.files[0]);
    }
});
```

**2. Upload with Progress Bar (AJAX + CORS fix):**
```javascript
function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);

    document.getElementById('uploadProgress').classList.remove('hidden');

    fetch('/media/upload', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',  // ✅ CRITICAL: Prevents redirect
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectMedia(data.media.id, data.media.full_url, data.media.title);
            loadMediaLibrary();  // Refresh library
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Upload failed');
    });
}
```

**3. Insight Autocomplete (Search-based):**
```blade
<input type="text" id="insightSearch" placeholder="Search insight..." autocomplete="off"
       value="{{ old('insight_title', $post->insight->title ?? '') }}">
<input type="hidden" name="insight_id" id="insightId"
       value="{{ old('insight_id', $post->insight_id ?? '') }}">

<div id="insightDropdown" class="hidden absolute z-10 bg-gray-700 border rounded shadow-lg max-h-60 overflow-y-auto">
    <!-- Populated dynamically -->
</div>

<script>
const insightSearch = document.getElementById('insightSearch');
const insightDropdown = document.getElementById('insightDropdown');

insightSearch.addEventListener('input', function() {
    const query = this.value.toLowerCase();

    if (query.length < 2) {
        insightDropdown.classList.add('hidden');
        return;
    }

    const insights = @json($insights);
    const filtered = insights.filter(i => i.title.toLowerCase().includes(query));

    insightDropdown.innerHTML = filtered.map(i => `
        <div class="px-4 py-2 hover:bg-gray-600 cursor-pointer"
             onclick="selectInsight(${i.id}, '${i.title.replace(/'/g, "\\'")}')">
            ${i.title}
        </div>
    `).join('');

    insightDropdown.classList.remove('hidden');
});

function selectInsight(id, title) {
    document.getElementById('insightId').value = id;
    document.getElementById('insightSearch').value = title;
    insightDropdown.classList.add('hidden');
}
</script>
```

### Common Pitfalls & Solutions

**Problem 1: Images not showing in grid**
- **Cause:** Using Tailwind `aspect-square` class (requires JIT compiler, Tailwind 4 doesn't support it)
- **Fix:** Use inline `style="aspect-ratio: 1/1;"` instead

**Problem 2: Click not working on media grid items**
- **Cause:** Absolute positioned overlay div blocking clicks
- **Fix:** Remove overlay, use border for selection feedback

**Problem 3: Upload returns HTML instead of JSON (302 redirect)**
- **Cause:** Missing AJAX headers, Laravel redirects unauthenticated requests
- **Fix:** Add `X-Requested-With: XMLHttpRequest` and `Accept: application/json` headers

**Problem 4: WordPress category not saving**
- **Cause:** PostTarget uses `categories` JSON array, not `wp_category_id` single field
- **Fix:** Save as `'categories' => [$request->wp_category_id]`

**Problem 5: Edit page layout broken**
- **Cause:** Using wrong layout file (`@extends('layouts.app')`)
- **Fix:** Use `@extends('layouts.dashboard')` for admin pages

**Problem 6: Featured image/category not pre-selected in edit mode**
- **Cause:** Not loading relationships or triggering JavaScript on page load
- **Fix:**
  - Eager load: `$post->load('featuredMedia', 'targets')`
  - Trigger category load: `window.addEventListener('load', ...)`

### Testing Checklist

**Create Post:**
- [ ] Title and content save correctly
- [ ] Featured image persists to `featured_media_id`
- [ ] WordPress category saves as JSON array in `PostTarget.categories`
- [ ] Website target creates `PostTarget` record with `status='draft'`
- [ ] SEO fields (focus keyword, meta description) save

**Edit Post:**
- [ ] All fields pre-filled (title, content, SEO)
- [ ] Featured image preview shows
- [ ] Website dropdown pre-selected
- [ ] WordPress categories auto-load for selected website
- [ ] Saved category auto-selected
- [ ] Insight autocomplete shows saved insight

**Media Modal:**
- [ ] Library tab shows all media in 4-column grid
- [ ] Upload tab accepts drag & drop
- [ ] Upload shows progress bar
- [ ] Selecting media updates preview
- [ ] Modal closes on selection

**WordPress Integration:**
- [ ] Categories load from WordPress REST API
- [ ] Basic Auth works with encrypted `app_password`
- [ ] 401/403 errors handled gracefully

### File References

**Models:**
- [app/Models/Post.php](app/Models/Post.php) - Main post model with relationships
- [app/Models/PostTarget.php](app/Models/PostTarget.php) - Publishing targets with JSON casting
- [app/Models/Media.php](app/Models/Media.php) - Media library
- [app/Models/Website.php](app/Models/Website.php) - WordPress sites

**Controllers:**
- [app/Http/Controllers/PostController.php](app/Http/Controllers/PostController.php) - Post CRUD
- [app/Http/Controllers/WebsiteController.php](app/Http/Controllers/WebsiteController.php) - Category fetching

**Views:**
- [resources/views/posts/create.blade.php](resources/views/posts/create.blade.php) - Create post UI
- [resources/views/posts/edit.blade.php](resources/views/posts/edit.blade.php) - Edit post UI (mirrors create)

**Migrations:**
- [database/migrations/2025_12_13_031253_add_featured_media_id_to_posts_table.php](database/migrations/2025_12_13_031253_add_featured_media_id_to_posts_table.php)

---

## Calendar Management System (Social Media Scheduling)

This section documents the **Calendar Management System** for visualizing and managing social media post schedules across multiple channels (Instagram, Facebook, Twitter, LinkedIn).

### Overview

The Calendar Management System provides a **FullCalendar-based interface** for:
- **Visual timeline** of scheduled social media posts
- **Drag-and-drop rescheduling** with timezone support
- **Multi-channel filtering** (Instagram, Facebook, Twitter, LinkedIn)
- **Status filtering** (pending, success, failed)
- **Bulk operations** (reschedule, delete) via Ctrl+Click multi-select
- **Day detail modal** showing hourly timeline for selected date
- **Event detail popup** with quick edit/delete actions
- **Statistics dashboard** (posts by channel, status, peak hours)

**Key Features:**
- Timezone-aware (converts UTC → user's timezone)
- Color-coded by channel (Instagram pink, Facebook blue, etc.)
- Compact pill-shaped events for high density
- Responsive design with dark mode UI
- Real-time updates after mutations

### Database Schema

**Primary Table:**
```sql
ane_distribution_schedule (distributes ane_social_assets)
├── id                  BIGINT UNSIGNED PRIMARY KEY
├── asset_id            BIGINT UNSIGNED (FK → ane_social_assets.id)
├── created_by          BIGINT UNSIGNED (FK → ane_users.id)
├── scheduled_at        TIMESTAMP (UTC, indexed)
├── publish_status      VARCHAR(20) DEFAULT 'pending' (pending/success/failed)
├── queued_at           TIMESTAMP NULL
├── published_at        TIMESTAMP NULL
├── last_error          TEXT NULL
├── created_at          TIMESTAMP
└── updated_at          TIMESTAMP
```

**Indexes (Performance-Critical):**
```sql
-- Composite index for calendar queries (user + date range)
idx_created_scheduled (created_by, scheduled_at)

-- Index for status filtering
idx_created_status (created_by, publish_status)

-- Index for pending schedules (used by scheduler)
idx_status_scheduled (publish_status, scheduled_at)
```

**Relationships:**
- `DistributionSchedule` → `SocialAsset` (belongsTo)
- `SocialAsset` → `Post` (belongsTo)
- `SocialAsset` → `ContentDNA` (belongsTo)

### Model Structure

**DistributionSchedule.php ([app/Models/DistributionSchedule.php](app/Models/DistributionSchedule.php)):**
```php
protected $table = 'ane_distribution_schedule';

protected $fillable = [
    'asset_id',
    'created_by',
    'scheduled_at',
    'publish_status',
    'queued_at',
    'published_at',
    'last_error',
];

protected $casts = [
    'scheduled_at' => 'datetime',
    'queued_at' => 'datetime',
    'published_at' => 'datetime',
];

// Relationships
public function asset()
{
    return $this->belongsTo(SocialAsset::class, 'asset_id');
}

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}
```

**SocialAsset.php (Related Model):**
```php
protected $table = 'ane_social_assets';

protected $fillable = [
    'post_id',
    'dna_id',
    'channel',         // instagram, facebook, twitter, linkedin
    'asset_type',      // feed, story, reel, thread
    'copy_text',       // Generated social copy
    'image_prompt',    // AI image generation prompt
    'aspect_ratio',    // 1:1, 9:16, 16:9
    'status',          // draft, ready, published
    'created_by',
];

public function post()
{
    return $this->belongsTo(Post::class);
}

public function dna()
{
    return $this->belongsTo(ContentDNA::class, 'dna_id');
}

public function schedules()
{
    return $this->hasMany(DistributionSchedule::class, 'asset_id');
}
```

### Controller Pattern

**CalendarController.php ([app/Http/Controllers/CalendarController.php](app/Http/Controllers/CalendarController.php)):**

**1. Calendar View (index):**
```php
public function index()
{
    return view('calendar.index');
}
```

**2. Get Events API (getEvents) - Core Query:**
```php
public function getEvents(Request $request)
{
    $userTimezone = auth()->user()->timezone ?? 'UTC';

    $query = DistributionSchedule::where('created_by', auth()->id())
        ->with(['asset.post:id,title', 'asset:id,post_id,channel,asset_type,copy_text,status'])
        ->whereNotNull('scheduled_at');

    // Filter by date range (FullCalendar sends start/end params)
    if ($request->filled('start')) {
        $query->where('scheduled_at', '>=', $request->start);
    }

    if ($request->filled('end')) {
        $query->where('scheduled_at', '<=', $request->end);
    }

    // Filter by channel (dropdown)
    if ($request->filled('channel')) {
        $query->whereHas('asset', function ($q) use ($request) {
            $q->where('channel', $request->channel);
        });
    }

    // Filter by status (dropdown)
    if ($request->filled('status')) {
        $query->where('publish_status', $request->status);
    }

    $schedules = $query->get();

    // Transform to FullCalendar event format with timezone conversion
    $events = $schedules->map(function ($schedule) use ($userTimezone) {
        $asset = $schedule->asset;
        $post = $asset->post ?? null;

        // Convert UTC to user's timezone
        $localTime = $schedule->scheduled_at->copy()->setTimezone($userTimezone);

        // Color-code by channel
        $baseColor = $this->channelColors[$asset->channel] ?? '#6366f1';

        return [
            'id' => $schedule->id,
            'title' => $post->title ?? 'Untitled',
            'start' => $localTime->toISOString(),
            'backgroundColor' => $baseColor,
            'borderColor' => $baseColor,
            'classNames' => ['calendar-event-compact'],
            'extendedProps' => [
                'channel' => $asset->channel,
                'asset_type' => $asset->asset_type,
                'status' => $schedule->publish_status,
                'post_id' => $post->id ?? null,
                'copy_preview' => \Illuminate\Support\Str::limit($asset->copy_text, 100),
                'timezone' => $userTimezone,
                'utc_time' => $schedule->scheduled_at->toISOString(),
            ],
        ];
    });

    return response()->json($events);
}
```

**3. Reschedule Single Event (Drag & Drop):**
```php
public function reschedule(Request $request, DistributionSchedule $schedule)
{
    $this->authorize('update', $schedule);

    $validated = $request->validate([
        'scheduled_at' => 'required|date|after:now',
    ]);

    $schedule->update([
        'scheduled_at' => $validated['scheduled_at'], // Already in UTC from frontend
        'queued_at' => null, // Reset queue flag
        'publish_status' => 'pending', // Reset status if was failed
        'last_error' => null,
    ]);

    Log::info('Schedule rescheduled via calendar', [
        'schedule_id' => $schedule->id,
        'user_id' => auth()->id(),
    ]);

    return response()->json(['success' => true]);
}
```

**4. Bulk Reschedule (Multi-Select):**
```php
public function bulkReschedule(Request $request)
{
    $validated = $request->validate([
        'schedule_ids' => 'required|array|min:1',
        'schedule_ids.*' => 'exists:ane_distribution_schedule,id',
        'action' => 'required|in:delay,advance',
        'hours' => 'required|integer|min:1|max:168', // Max 1 week
    ]);

    $schedules = DistributionSchedule::whereIn('id', $validated['schedule_ids'])
        ->where('created_by', auth()->id())
        ->get();

    if ($schedules->isEmpty()) {
        return response()->json([
            'success' => false,
            'error' => 'No schedules found or access denied',
        ], 403);
    }

    $modifier = $validated['action'] === 'delay' ? 1 : -1;
    $hours = $validated['hours'] * $modifier;

    foreach ($schedules as $schedule) {
        $newTime = $schedule->scheduled_at->copy()->addHours($hours);

        $schedule->update([
            'scheduled_at' => $newTime,
            'queued_at' => null,
            'publish_status' => 'pending',
            'last_error' => null,
        ]);
    }

    return response()->json([
        'success' => true,
        'updated_count' => $schedules->count(),
    ]);
}
```

**5. Delete Schedule:**
```php
public function destroy(DistributionSchedule $schedule)
{
    $this->authorize('delete', $schedule);

    Log::info('Schedule deleted via calendar', [
        'schedule_id' => $schedule->id,
        'user_id' => auth()->id(),
    ]);

    $schedule->delete();

    return response()->json(['success' => true]);
}
```

**6. Calendar Statistics:**
```php
public function getStats(Request $request)
{
    $userId = auth()->id();
    $startDate = $request->get('start', now()->startOfMonth()->toDateTimeString());
    $endDate = $request->get('end', now()->endOfMonth()->toDateTimeString());

    $schedules = DistributionSchedule::where('created_by', $userId)
        ->whereBetween('scheduled_at', [$startDate, $endDate])
        ->with('asset:id,channel')
        ->get();

    $byChannel = $schedules->groupBy('asset.channel')->map->count()->toArray();
    $byStatus = $schedules->groupBy('publish_status')->map->count()->toArray();
    $byDayOfWeek = $schedules->groupBy(function ($s) {
        return $s->scheduled_at->dayOfWeek; // 0=Sun, 6=Sat
    })->map->count()->sortKeys()->toArray();

    $hourCounts = $schedules->groupBy(function ($s) {
        return $s->scheduled_at->hour;
    })->map->count()->sortByDesc(function ($count) {
        return $count;
    });

    return response()->json([
        'total' => $schedules->count(),
        'by_channel' => $byChannel,
        'by_status' => $byStatus,
        'by_day_of_week' => $byDayOfWeek,
        'peak_hour' => $hourCounts->keys()->first(),
    ]);
}
```

### Policy (Authorization)

**DistributionSchedulePolicy.php ([app/Policies/DistributionSchedulePolicy.php](app/Policies/DistributionSchedulePolicy.php)):**
```php
public function view(User $user, DistributionSchedule $distributionSchedule): bool
{
    return $distributionSchedule->created_by === $user->id;
}

public function update(User $user, DistributionSchedule $distributionSchedule): bool
{
    return $distributionSchedule->created_by === $user->id;
}

public function delete(User $user, DistributionSchedule $distributionSchedule): bool
{
    return $distributionSchedule->created_by === $user->id;
}
```

**Critical:** ALWAYS check ownership via policy before mutations (reschedule, delete).

### Frontend Implementation

**View Structure (Blade):**
```blade
@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header with Filters -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Content Calendar</h1>

        <!-- Filter Dropdowns -->
        <div class="flex gap-4">
            <select id="filterChannel" class="bg-gray-700 text-white rounded">
                <option value="">All Channels</option>
                <option value="instagram">Instagram</option>
                <option value="facebook">Facebook</option>
                <option value="twitter">Twitter</option>
                <option value="linkedin">LinkedIn</option>
            </select>

            <select id="filterStatus" class="bg-gray-700 text-white rounded">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="success">Published</option>
                <option value="failed">Failed</option>
            </select>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-4 gap-4 mb-6" id="statsContainer">
        <!-- Populated dynamically -->
    </div>

    <!-- Bulk Actions Toolbar (hidden by default) -->
    <div id="bulkActionsToolbar" class="hidden mb-4 bg-blue-900 p-4 rounded">
        <div class="flex items-center justify-between">
            <span id="selectedCount" class="text-white font-medium">0 events selected</span>
            <div class="flex gap-2">
                <button onclick="openBulkRescheduleModal()" class="bg-blue-600 px-4 py-2 rounded">
                    Bulk Reschedule
                </button>
                <button onclick="bulkDelete()" class="bg-red-600 px-4 py-2 rounded">
                    Bulk Delete
                </button>
                <button onclick="clearSelection()" class="bg-gray-600 px-4 py-2 rounded">
                    Clear Selection
                </button>
            </div>
        </div>
    </div>

    <!-- FullCalendar Container -->
    <div id="calendar" class="bg-white rounded-lg shadow"></div>
</div>

<!-- Day Detail Modal -->
<div id="dayDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="bg-gray-800 rounded-lg max-w-4xl mx-auto mt-10 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 id="modalDayTitle" class="text-xl font-bold text-white"></h3>
                <p id="modalDaySubtitle" class="text-sm text-gray-400"></p>
            </div>
            <button onclick="closeDayModal()" class="text-white">✕</button>
        </div>
        <div id="dayDetailContent" class="max-h-[600px] overflow-y-auto">
            <!-- Populated dynamically with hourly timeline -->
        </div>
    </div>
</div>

<!-- Bulk Reschedule Modal -->
<div id="bulkRescheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <!-- Form for delay/advance hours -->
</div>
@endsection
```

**FullCalendar Configuration (JavaScript):**
```javascript
const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    plugins: ['dayGrid', 'timeGrid', 'interaction'],
    initialView: 'timeGridWeek',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    allDaySlot: false,
    nowIndicator: true,
    editable: true,           // Enable drag & drop
    droppable: false,
    selectable: true,
    selectMirror: true,
    height: 'auto',

    // Show past events (90 days backwards, 90 days forward)
    validRange: {
        start: new Date(Date.now() - 90 * 24 * 60 * 60 * 1000),
        end: new Date(Date.now() + 90 * 24 * 60 * 60 * 1000)
    },

    // Event source (API endpoint)
    events: {
        url: '/calendar/events',
        method: 'GET',
        extraParams: function() {
            return {
                channel: document.getElementById('filterChannel').value,
                status: document.getElementById('filterStatus').value,
            };
        },
        success: function(events) {
            console.log('✅ Calendar events loaded:', events.length);
        },
        failure: function(error) {
            console.error('❌ Failed to load calendar events', error);
        }
    },

    // Drag & Drop - Reschedule
    eventDrop: function(info) {
        rescheduleEvent(info);
    },

    // Click event - Show detail popup or toggle selection
    eventClick: function(info) {
        if (info.jsEvent.ctrlKey || info.jsEvent.metaKey) {
            // Ctrl+Click - Toggle selection for bulk actions
            toggleEventSelection(info.event);
        } else {
            // Normal click - Show event detail popup
            openEventDetailPopup(info.event);
        }
    },

    // Click empty slot or day - Open day detail modal
    dateClick: function(info) {
        openDayModal(info.date);
    },
});

calendar.render();

// Reload on filter change
document.getElementById('filterChannel').addEventListener('change', function() {
    calendar.refetchEvents();
    loadStats();
});

document.getElementById('filterStatus').addEventListener('change', function() {
    calendar.refetchEvents();
    loadStats();
});
```

**Event Rescheduling (Drag & Drop Handler):**
```javascript
async function rescheduleEvent(info) {
    const event = info.event;
    const newDate = event.start; // Local timezone date

    // Convert to UTC for backend
    const utcDate = new Date(newDate.getTime() - newDate.getTimezoneOffset() * 60000);

    try {
        const res = await fetch(`/calendar/events/${event.id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                scheduled_at: utcDate.toISOString(),
            }),
        });

        const data = await res.json();

        if (data.success) {
            calendar.refetchEvents();
            loadStats();
        } else {
            info.revert(); // Revert drag if failed
            alert('Reschedule failed: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        info.revert();
        console.error('Reschedule error:', error);
        alert('Network error during reschedule');
    }
}
```

**Event Detail Popup (Normal Click):**
```javascript
function openEventDetailPopup(event) {
    const props = event.extendedProps;
    const time = event.start.toLocaleString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });

    const statusBadge = props.status === 'success' ?
        '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Published</span>' :
        props.status === 'failed' ?
        '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Failed</span>' :
        '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs">Pending</span>';

    const channelBadge = `<span class="px-2 py-1 rounded text-xs text-white" style="background-color: ${event.backgroundColor}">${props.channel.toUpperCase()}</span>`;

    const html = `
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="this.remove()">
            <div class="bg-gray-800 rounded-lg max-w-md w-full p-6 text-white" onclick="event.stopPropagation()">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-lg font-semibold">${event.title}</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-white">✕</button>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-2">
                        ${channelBadge}
                        ${statusBadge}
                    </div>

                    <div class="flex items-center gap-2 text-gray-300">
                        <span>${time}</span>
                    </div>

                    <div class="text-gray-400 text-xs">
                        <p class="line-clamp-3">${props.copy_preview || 'No preview available'}</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <a href="/posts/${props.post_id}/edit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-center">
                        Edit Post
                    </a>
                    <button onclick="deleteEvent(${event.id})" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', html);
}
```

**Multi-Select for Bulk Actions (Ctrl+Click):**
```javascript
const selectedEvents = new Set();

function toggleEventSelection(event) {
    const eventId = event.id;

    if (selectedEvents.has(eventId)) {
        selectedEvents.delete(eventId);
        event.setProp('classNames', []);
    } else {
        selectedEvents.add(eventId);
        event.setProp('classNames', ['fc-event-selected']);
    }

    updateBulkActionsUI();
}

function updateBulkActionsUI() {
    const toolbar = document.getElementById('bulkActionsToolbar');
    const count = selectedEvents.size;

    if (count > 0) {
        toolbar.classList.remove('hidden');
        document.getElementById('selectedCount').textContent = `${count} event${count > 1 ? 's' : ''} selected`;
    } else {
        toolbar.classList.add('hidden');
    }
}

async function bulkDelete() {
    if (!confirm(`Delete ${selectedEvents.size} events?`)) return;

    const scheduleIds = Array.from(selectedEvents);

    const deletePromises = scheduleIds.map(id => {
        return fetch(`/calendar/events/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        });
    });

    try {
        await Promise.all(deletePromises);
        calendar.refetchEvents();
        selectedEvents.clear();
        updateBulkActionsUI();
        loadStats();
    } catch (error) {
        console.error('Bulk delete error:', error);
        alert('Some events failed to delete');
    }
}
```

**Day Detail Modal (Click Empty Slot/Day):**
```javascript
function openDayModal(date) {
    const modal = document.getElementById('dayDetailModal');
    const titleEl = document.getElementById('modalDayTitle');
    const subtitleEl = document.getElementById('modalDaySubtitle');
    const contentEl = document.getElementById('dayDetailContent');

    const dateStr = date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    titleEl.textContent = dateStr;
    subtitleEl.textContent = `Timezone: ${userTimezone}`;

    // Get all events for this day
    const dayStart = new Date(date);
    dayStart.setHours(0, 0, 0, 0);
    const dayEnd = new Date(date);
    dayEnd.setHours(23, 59, 59, 999);

    const dayEvents = calendar.getEvents().filter(event => {
        const eventDate = event.start;
        return eventDate >= dayStart && eventDate <= dayEnd;
    });

    // Render 24-hour timeline
    let html = '<div class="space-y-0">';

    for (let hour = 0; hour < 24; hour++) {
        const hourStr = String(hour).padStart(2, '0') + ':00';
        const eventsAtHour = dayEvents.filter(e => e.start.getHours() === hour);

        html += `
            <div class="hour-slot flex gap-4 py-3 border-b border-gray-700">
                <div class="text-sm text-gray-400 font-semibold w-20">${hourStr}</div>
                <div class="flex-1">
        `;

        if (eventsAtHour.length === 0) {
            html += `<div class="text-xs text-gray-500 italic">No posts scheduled</div>`;
        } else {
            eventsAtHour.forEach(event => {
                const props = event.extendedProps;
                const time = event.start.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });

                html += `
                    <div class="bg-gray-700 rounded-lg p-3 mb-2 border-l-4 cursor-pointer hover:bg-gray-600"
                         style="border-left-color: ${event.backgroundColor};"
                         onclick="window.location.href='/posts/${props.post_id}/edit'">
                        <div class="text-sm font-bold text-white mb-1">${event.title}</div>
                        <div class="text-xs text-gray-400">
                            ${time} · ${props.channel} · ${props.status}
                        </div>
                        <div class="text-xs text-gray-500 mt-1 line-clamp-2">${props.copy_preview}</div>
                    </div>
                `;
            });
        }

        html += `
                </div>
            </div>
        `;
    }

    html += '</div>';

    contentEl.innerHTML = html;
    modal.classList.remove('hidden');
}
```

**Custom CSS for Compact Pills:**
```css
.fc .fc-timegrid-event {
    border-radius: 9999px !important; /* Full pill shape */
    border: none !important;
    padding: 2px 10px !important;
    cursor: pointer;
    transition: all 0.2s;
    height: 20px !important; /* Compact height */
    min-height: 20px !important;
}

.fc .fc-timegrid-event:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 10;
}

.fc .fc-timegrid-event.fc-event-selected {
    box-shadow: 0 0 0 2px #3B82F6; /* Blue outline for selected */
    transform: scale(1.05);
}

.fc .fc-event-title {
    font-size: 0.7rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 18px;
}

.fc .fc-event-time {
    display: none; /* Hide time inside event, shown in tooltip */
}
```

### Routes

```php
// Calendar Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::patch('/calendar/events/{schedule}', [CalendarController::class, 'reschedule'])->name('calendar.reschedule');
    Route::delete('/calendar/events/{schedule}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::post('/calendar/bulk-reschedule', [CalendarController::class, 'bulkReschedule'])->name('calendar.bulk-reschedule');
    Route::get('/calendar/stats', [CalendarController::class, 'getStats'])->name('calendar.stats');
});
```

### Timezone Handling (Critical)

**Database Storage:** ALWAYS store `scheduled_at` in UTC
**Frontend Display:** Convert UTC → user's timezone using `auth()->user()->timezone`
**User Input:** Convert user's timezone → UTC before saving

**Example Flow:**
1. User drags event to "10 Jan 2026, 14:00 WIB (Asia/Jakarta)"
2. JavaScript converts to UTC: "10 Jan 2026, 07:00 UTC"
3. Backend saves UTC: `scheduled_at = '2026-01-10 07:00:00'`
4. Frontend retrieves and converts back to WIB for display

**Code Pattern:**
```php
// Backend: UTC to user's timezone
$userTimezone = auth()->user()->timezone ?? 'UTC';
$localTime = $schedule->scheduled_at->copy()->setTimezone($userTimezone);
```

```javascript
// Frontend: User's timezone to UTC
const localDate = event.start; // Browser's local time
const utcDate = new Date(localDate.getTime() - localDate.getTimezoneOffset() * 60000);
```

### Channel Color Mapping

```php
protected array $channelColors = [
    'instagram' => '#E1306C', // Instagram pink
    'facebook' => '#1877F2',   // Facebook blue
    'twitter' => '#000000',    // X black
    'linkedin' => '#0A66C2',   // LinkedIn blue
];
```

Visual consistency across:
- Calendar events (background color)
- Event detail popup (channel badge)
- Day detail modal (border-left color)
- Statistics cards

### Testing Checklist

**Calendar Display:**
- [ ] Events load correctly with channel colors
- [ ] Timezone conversion accurate (check UTC vs local time)
- [ ] Filter by channel works (Instagram only, Facebook only, etc.)
- [ ] Filter by status works (pending/success/failed)
- [ ] Statistics cards show correct counts
- [ ] Past events visible (90 days back)
- [ ] Future events visible (90 days forward)

**Drag & Drop:**
- [ ] Drag event to new time → PATCH request sent
- [ ] Event updates in database (UTC time)
- [ ] Event reverts if API fails
- [ ] Calendar refreshes after successful reschedule
- [ ] `queued_at` reset to NULL
- [ ] `publish_status` reset to 'pending'

**Event Click:**
- [ ] Normal click → Detail popup opens
- [ ] Popup shows correct title, channel, status, time
- [ ] "Edit Post" button redirects to `/posts/{id}/edit`
- [ ] "Delete" button triggers DELETE request
- [ ] Calendar refreshes after delete

**Multi-Select (Ctrl+Click):**
- [ ] Ctrl+Click event → Blue outline appears
- [ ] Bulk actions toolbar shows
- [ ] Selected count accurate
- [ ] "Bulk Reschedule" modal opens
- [ ] "Bulk Delete" deletes all selected
- [ ] "Clear Selection" removes all selections

**Day Detail Modal:**
- [ ] Click empty slot → Modal opens
- [ ] Shows correct date and timezone
- [ ] 24-hour timeline renders
- [ ] Events grouped by hour correctly
- [ ] "No posts scheduled" for empty hours
- [ ] Click event card → Redirects to edit page

**Authorization:**
- [ ] User A cannot see User B's schedules
- [ ] User A cannot reschedule User B's schedules (403 error)
- [ ] User A cannot delete User B's schedules (403 error)
- [ ] Policy checks enforced on all mutations

### Performance Considerations

**Database Query Optimization:**
1. **Composite Indexes:** Use `idx_created_scheduled` for fast lookups
   ```sql
   WHERE created_by = 1 AND scheduled_at BETWEEN '2026-01-01' AND '2026-01-31'
   ```

2. **Eager Loading:** ALWAYS load relationships to avoid N+1
   ```php
   ->with(['asset.post:id,title', 'asset:id,post_id,channel'])
   ```

3. **Select Specific Columns:** Don't load unnecessary fields
   ```php
   ->with('asset:id,post_id,channel,copy_text')
   ```

4. **Pagination for Large Datasets:** If user has >1000 schedules, use pagination
   ```php
   ->whereBetween('scheduled_at', [$start, $end])
   ```

**Frontend Optimization:**
1. **Debounce Filter Changes:** Wait 300ms before refetching
2. **Cache Statistics:** Don't reload stats on every event mutation
3. **Lazy Load Day Modal:** Only fetch events when modal opens
4. **Virtual Scrolling:** For day modal with 100+ events

### Common Issues & Solutions

**Problem 1: Events not showing in calendar**
- **Cause:** `scheduled_at` is NULL or date is outside validRange
- **Fix:** Check database: `SELECT * FROM ane_distribution_schedule WHERE scheduled_at IS NULL`

**Problem 2: Timezone mismatch (events show at wrong time)**
- **Cause:** User's timezone not set or UTC conversion missing
- **Fix:** Verify `auth()->user()->timezone` and check conversion logic

**Problem 3: Drag & drop not working**
- **Cause:** `editable: false` in FullCalendar config
- **Fix:** Set `editable: true` and ensure `eventDrop` handler exists

**Problem 4: Bulk actions not working**
- **Cause:** CSRF token missing or policy denies access
- **Fix:** Add `<meta name="csrf-token">` and check policy

**Problem 5: Day modal shows "No posts scheduled" for day with events**
- **Cause:** Timezone mismatch in date filtering
- **Fix:** Ensure `dayStart`/`dayEnd` use same timezone as event.start

**Problem 6: Filter not updating events**
- **Cause:** `calendar.refetchEvents()` not called
- **Fix:** Add `calendar.refetchEvents()` to filter change handlers

### Integration with Social Copy Generation

Calendar events are created AFTER social copy generation:

**Flow:**
1. User generates ContentDNA from Post
2. `SocialCopyService` creates SocialAssets (Instagram feed, Facebook post, etc.)
3. `SchedulingService` creates DistributionSchedules with optimal times
4. Schedules appear in Calendar view
5. User can reschedule via drag & drop or bulk actions

**Related Services:**
- [SocialCopyService.php](app/Services/SocialCopyService.php) - Generates social copy
- [SchedulingService.php](app/Services/SchedulingService.php) - Creates schedules with optimal times
- [CalendarController.php](app/Http/Controllers/CalendarController.php) - Manages calendar UI

### File References

**Controllers:**
- [app/Http/Controllers/CalendarController.php](app/Http/Controllers/CalendarController.php) - Calendar CRUD

**Models:**
- [app/Models/DistributionSchedule.php](app/Models/DistributionSchedule.php) - Schedule model
- [app/Models/SocialAsset.php](app/Models/SocialAsset.php) - Social asset model

**Policies:**
- [app/Policies/DistributionSchedulePolicy.php](app/Policies/DistributionSchedulePolicy.php) - Authorization

**Views:**
- [resources/views/calendar/index.blade.php](resources/views/calendar/index.blade.php) - Calendar UI

**Migrations:**
- [database/migrations/2026_01_04_121554_add_indexes_to_distribution_schedule_table.php](database/migrations/2026_01_04_121554_add_indexes_to_distribution_schedule_table.php)

---

## Common Issues & Solutions

### "Call to undefined function str_limit()"
**Fix:** Use `\Illuminate\Support\Str::limit($string, $limit)` instead.

### "403 Forbidden on /websites/{id}"
**Cause:** Website not owned by current user.
**Fix:** Verify `created_by` matches `auth()->id()` in database.

### "SQLSTATE: failed to decrypt"
**Cause:** Wrong `APP_KEY` in `.env`.
**Fix:** Ensure correct key is set. If key was regenerated, re-encrypt data.

### "Session not persisting"
**Cause:** `SESSION_DRIVER` misconfigured.
**Fix:** Check `.env` has `SESSION_DRIVER=file` (dev) or `database` (prod).

### Menu duplicates or broken navigation
**Fix:** Check [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php) and [resources/views/dashboard-new.blade.php](resources/views/dashboard-new.blade.php) for duplicate/empty links.

---

## Important File Locations

**Models:** [app/Models/](app/Models/)
- [User.php](app/Models/User.php) - User with subscription/quota
- [ApiCredential.php](app/Models/ApiCredential.php) - Encrypted API keys
- [Website.php](app/Models/Website.php) - WordPress sites with encrypted passwords
- [WebsiteAnalysis.php](app/Models/WebsiteAnalysis.php)
- [Keyword.php](app/Models/Keyword.php)
- [KeywordTrace.php](app/Models/KeywordTrace.php)

**Controllers:** [app/Http/Controllers/](app/Http/Controllers/)
- [DashboardController.php](app/Http/Controllers/DashboardController.php)
- [WebsiteController.php](app/Http/Controllers/WebsiteController.php)
- [GenerationController.php](app/Http/Controllers/GenerationController.php)
- [Admin/ApiKeyController.php](app/Http/Controllers/Admin/ApiKeyController.php)

**Services:** [app/Services/](app/Services/)
- [AIService.php](app/Services/AIService.php) - Multi-provider AI integration

**Middleware:** [app/Http/Middleware/](app/Http/Middleware/)
- [EnsureUserIsAdmin.php](app/Http/Middleware/EnsureUserIsAdmin.php) - Admin role check

**Policies:** [app/Policies/](app/Policies/)
- [WebsitePolicy.php](app/Policies/WebsitePolicy.php) - Website ownership authorization

**Migrations:** [database/migrations/](database/migrations/) - 11 migration files

**Views:** [resources/views/](resources/views/)
- Layout: [layouts/app.blade.php](resources/views/layouts/app.blade.php)
- Dashboard: [dashboard-new.blade.php](resources/views/dashboard-new.blade.php)
- Websites: [websites/](resources/views/websites/)
- Admin: [admin/](resources/views/admin/)

---

## Supporting Documentation

The repository contains extensive documentation. Key files:

- **[README.md](README.md)** - Primary system documentation with user data, database schema, routes
- **[ARCHITECTURE_GUIDELINES.md](ARCHITECTURE_GUIDELINES.md)** - Multi-tenant architecture, quota system, performance guidelines
- **[MIGRATION_WEBSITE_MANAGEMENT.md](MIGRATION_WEBSITE_MANAGEMENT.md)** - Website feature technical details
- **[WEBSITE_MANAGEMENT_QUICKREF.md](WEBSITE_MANAGEMENT_QUICKREF.md)** - Website CRUD testing examples

**Before making changes, read README.md sections:**
- User System & Test Credentials (lines 57-150)
- Database Schema (lines 175-287)
- Routes & Endpoints (lines 435-474)

---

## Quick Reference URLs

**Development Server:** http://127.0.0.1:8000
**Admin Dashboard:** http://127.0.0.1:8000/admin/dashboard (use wasugi@example.com)
**User Dashboard:** http://127.0.0.1:8000/dashboard (use test@example.com)

**Logs:** `storage/logs/laravel.log`
**Sessions:** `storage/framework/sessions/`

---

## Migration from Native PHP (anewp) to Laravel

### Overview

This Laravel 12 application (`anewplaravel`) is a **progressive migration** from the native PHP MVC application (`anewp`). The goal is to migrate all 8 core features while modernizing the architecture with Laravel's ecosystem.

**Source Application:** `/Users/webane/sites/anewp` (Custom PHP MVC)
**Target Application:** `/Users/webane/sites/anewplaravel` (Laravel 12)
**Migration Status:** Phase 1 Complete (Website Management)

### Complete Feature Roadmap (8 Points)

| No | Feature | anewp Status | Laravel Status | Priority |
|----|---------|-------------|----------------|----------|
| 1 | **Website Management** | ✅ Production | ✅ **DONE** | - |
| 2 | **Website Analysis** (GSC, GA4, Keywords) | ✅ Production | ❌ TODO | **HIGH** |
| 3 | **Deep Research** (AI Keyword Research) | ✅ Production | ❌ TODO | **HIGH** |
| 4 | **Insights Hub** (Content Ideas Management) | ✅ Production | ❌ TODO | **HIGH** |
| 5 | **Article Management** (CRUD, Bulk Editor) | ✅ Production | ❌ TODO | **HIGH** |
| 6 | **AI Article Generation** | ✅ Production | ❌ TODO | **HIGH** |
| 7 | **News & Social Insights** | ✅ Production | ❌ TODO | MEDIUM |
| 8 | **WordPress Publishing & Scheduling** | ✅ Production | ❌ TODO | HIGH |

### Database Schema Gap Analysis

**Current Laravel Tables (11):**
```
ane_users                    ✅ Migrated (with quota system)
ane_api_credentials          ✅ Migrated (platform-wide model)
ane_websites                 ✅ Migrated
ane_website_analyses         ✅ Migrated
ane_keywords                 ✅ Migrated
ane_keyword_traces           ✅ Migrated
migrations                   (Laravel internal)
password_reset_tokens        (Laravel internal)
sessions                     (Laravel internal)
cache                        (Laravel internal)
cache_locks                  (Laravel internal)
```

**Missing Tables from anewp (20+ tables needed):**

#### Core Content Management
```sql
ane_insights              -- Content ideas/topics hub
ane_posts                 -- Articles (title, content, SEO fields)
ane_insight_articles      -- Junction: insights ↔ posts (many-to-many)
ane_post_targets          -- Publishing targets per website
ane_media                 -- Images/media files
ane_post_media            -- Junction: posts ↔ media
ane_activity_logs         -- User action audit trail
```

#### Google Search Console Integration
```sql
ane_gsc_connections       -- OAuth tokens for GSC
ane_gsc_keyword_data      -- Keyword performance (impressions, clicks, CTR)
ane_gsc_page_stats        -- Page-level statistics
```

#### Google Analytics 4 Integration
```sql
ane_ga4_connections       -- OAuth tokens for GA4
ane_ga4_traffic_data      -- Daily traffic metrics
ane_ga4_referral_sources  -- Backlink alternative (referring domains)
ane_ga4_page_stats        -- Page-level performance
```

#### Deep Research Module (SEO Keyword Research)
```sql
ane_deep_research_sessions          -- Research sessions (30-35s processing)
ane_deep_research_keywords          -- DataForSEO keyword data
ane_deep_research_serp_competitors  -- Competitor analysis
ane_deep_research_ai_keywords       -- AI recommendations (main targets + informational)
ane_global_keyword_cache            -- Keyword cache for performance
```

#### News & Social Insights
```sql
ane_news_searches         -- News search history
ane_news_results          -- Serper API search results
ane_social_searches       -- Social trending topic searches
ane_social_results        -- Twitter/TikTok trending data
ane_social_cache          -- API response cache (30min TTL)
```

#### Social Media Publishing
```sql
ane_social_accounts       -- Connected social accounts (webhook/OAuth)
ane_social_logs           -- Publishing attempt logs
```

### Architecture Differences: anewp vs anewplaravel

| Aspect | anewp (Native PHP) | anewplaravel (Laravel 12) |
|--------|-------------------|---------------------------|
| **Framework** | Custom MVC | Laravel 12 |
| **Routing** | Custom Router class (supports `:id` and `{id}`) | Laravel Router (`routes/web.php`) |
| **ORM** | Custom Model base class with PDO | Eloquent ORM |
| **Views** | PHP templates with `view()` helper + `extract()` | Blade templating engine |
| **Auth** | Session-based (`$_SESSION`) | Laravel Auth + Sanctum |
| **Encryption** | Manual (WordPress passwords only) | Laravel `Crypt` facade (AES-256-CBC) |
| **API Keys** | Per-user (column exists but unused) | Platform-wide shared |
| **Quota System** | None | Request-based (10/50/200 per month) |
| **Multi-Tenant** | Partial (`created_by` filter in queries) | Full (policies + `created_by` scoping) |
| **Middleware** | Custom `AuthMiddleware` | Laravel middleware pipeline |
| **Database** | PDO Singleton | Laravel Query Builder + Eloquent |
| **UI Framework** | Bootstrap 5 (in some modules) | Tailwind CSS 4 + Heroicons (SVG) |
| **Background Jobs** | Session-based job queue (custom) | Laravel Queues (Redis/Database) |
| **Testing** | Manual (no framework) | PHPUnit + Laravel Feature Tests |

### Services to Migrate

**anewp Services (8 services):**

1. **AIArticleService** - Multi-provider AI generation
   - Gemini 2.0 Flash (primary)
   - Claude 3.5 Sonnet
   - GPT-4o Mini
   - Groq (fallback)
   - Features: News (500-800 words) vs Blog (1000-1500 words) format

2. **GoogleAnalytics4Service** - GA4 OAuth & data sync
   - OAuth 2.0 flow
   - Traffic metrics sync (30 days)
   - Referral sources (backlink alternative)
   - Auto token refresh

3. **GoogleSearchConsoleService** - GSC data sync (implied from schema)
   - Keyword performance data
   - Page-level statistics

4. **NewsInsightService** - News search & article generation
   - Serper API integration
   - 6-hour cache
   - Max 20 results

5. **SerperSearchService** - Google Search API wrapper
   - Web search
   - News search
   - Google Trends

6. **FirecrawlService** - Web scraping
   - Article content extraction
   - News parsing

7. **AIInsightService** - AI-powered insight generation
   - Topic suggestions
   - Content ideas

8. **GoogleTrendsService** - Google Trends integration
   - Trending topics
   - Country-specific data

**Laravel Services (Current):**
- `AIService` - Partial implementation (multi-provider skeleton exists)

### Key Technical Patterns from anewp

#### 1. Background Processing Pattern (Production-Ready)

**anewp uses session-based job queue** to prevent HTTP 500 timeout errors:

```php
// Pattern from anewp
session_write_close();  // Release session lock IMMEDIATELY

$session_id = session_id();
$job_data = [
    'session_id' => $session_id,
    'status' => 'processing',
    'progress' => 0
];

// Store in database/file
saveJobData($session_id, $job_data);

// Process in background
processInBackground($session_id);

// Client polls for status
pollJobStatus($session_id);
```

**Laravel equivalent:** Use Laravel Queues with Redis/Database driver.

#### 2. ProgressTimeline & TimelineMessages Standard

**anewp has standardized UI component** for all AI operations:

```javascript
// TimelineMessages API (multi-language ready)
const timeline = new ProgressTimeline({
    title: 'AI Article Generator',
    closeButton: false,
    autoClose: false
});

timeline.show();
timeline.addLog(TimelineMessages.get('timeline.start'), 'start');
timeline.updateProgress(0.3, 1, 'Contacting AI...');
timeline.addLog(TimelineMessages.get('article.success', {title: title}), 'success');
timeline.complete(TimelineMessages.get('timeline.complete'));
```

**Benefits:**
- Multi-language support (EN/ID)
- Bootstrap Icons (not emoji)
- Centralized message management via API endpoint
- Used in: AI Insights, Social Insights, News Insights, Article Gen, Image Gen

**Laravel migration:** Build similar component with Livewire or Alpine.js.

#### 3. Multi-Website Publishing Workflow

**anewp workflow:**
```
1. User creates Insight (content idea)
2. Generates Article from Insight (AI or manual)
3. Selects target WordPress sites
4. Publishes via WordPress REST API
5. Links tracked in ane_insight_articles + ane_post_targets
6. Article count auto-incremented via database trigger
```

**Database Triggers (anewp):**
```sql
-- Auto-update article count when article is linked to insight
CREATE TRIGGER update_insight_status_after_insert
AFTER INSERT ON ane_insight_articles
FOR EACH ROW
BEGIN
  UPDATE ane_insights
  SET status = 'in_progress',
      article_count = (SELECT COUNT(*) FROM ane_insight_articles WHERE insight_id = NEW.insight_id)
  WHERE id = NEW.insight_id;
END;
```

**Laravel equivalent:** Use Eloquent events or model observers.

### Migration Priority & Strategy

#### Phase 1: Website Management ✅ COMPLETE
- ✅ Website CRUD (add, edit, delete, list)
- ✅ WordPress connection testing
- ✅ Encryption (`app_password`)
- ✅ Multi-tenant policies
- ✅ Tailwind UI conversion

#### Phase 2: Core Content System (NEXT - HIGH PRIORITY)

**Step 1: Database Schema**
```bash
# Create migrations for:
php artisan make:migration create_insights_table
php artisan make:migration create_posts_table
php artisan make:migration create_insight_articles_table
php artisan make:migration create_post_targets_table
php artisan make:migration create_media_table
```

**Step 2: Models & Relationships**
```php
// Insight.php
public function posts() {
    return $this->belongsToMany(Post::class, 'ane_insight_articles')
                ->withPivot('website_id', 'created_by')
                ->withTimestamps();
}

// Post.php
public function insights() {
    return $this->belongsToMany(Insight::class, 'ane_insight_articles');
}

public function media() {
    return $this->belongsToMany(Media::class, 'ane_post_media')
                ->withPivot('is_featured');
}

// Website relationship via pivot
public function publishTargets() {
    return $this->hasMany(PostTarget::class);
}
```

**Step 3: Controllers**
```bash
php artisan make:controller InsightController --resource
php artisan make:controller PostController --resource
php artisan make:controller MediaController --resource
```

**Step 4: Policies**
```bash
php artisan make:policy InsightPolicy --model=Insight
php artisan make:policy PostPolicy --model=Post
```

**Step 5: Services**
```bash
# Expand existing AIService for article generation
# app/Services/AIService.php → add generateArticle() method
```

#### Phase 3: Google Integrations (HIGH PRIORITY)

**Step 1: GSC & GA4 Migrations**
```bash
php artisan make:migration create_gsc_tables
php artisan make:migration create_ga4_tables
```

**Step 2: OAuth Services**
```php
// app/Services/GoogleSearchConsoleService.php
// app/Services/GoogleAnalytics4Service.php

// Pattern from anewp:
getAuthorizationUrl($websiteId)
handleCallback($code, $websiteId)
syncKeywordData($websiteId, $days=30)
refreshAccessToken($connectionId)
```

**Step 3: Analytics Dashboard**
```
/website-analytics/view/{id} - Combined GSC + GA4 view
```

#### Phase 4: Deep Research Module (HIGH PRIORITY)

**External APIs needed:**
- DataForSEO Related Keywords API
- Serper Google Search API
- Gemini 2.0 Flash AI

**Step 1: Database**
```bash
php artisan make:migration create_deep_research_tables
```

**Step 2: Service**
```php
// app/Services/DeepResearchService.php
// Pattern: Session-based background processing (30-35 seconds)
startResearch($seedKeyword, $websiteId)
fetchRelatedKeywords($keyword)  // DataForSEO
fetchSerperData($keyword)       // Serper API
analyzeWithAI($keywords)        // Gemini AI
saveRecommendations($sessionId)
```

**Step 3: Background Processing**
```bash
# Use Laravel Queue
php artisan make:job ProcessDeepResearch
```

#### Phase 5: News & Social Insights (MEDIUM PRIORITY)

**Step 1: Migrations**
```bash
php artisan make:migration create_news_insights_tables
php artisan make:migration create_social_insights_tables
```

**Step 2: Services**
```php
// app/Services/SerperSearchService.php
// app/Services/FirecrawlService.php
// app/Services/SocialInsightService.php (Twitter/TikTok via RapidAPI)
```

#### Phase 6: WordPress Publishing (HIGH PRIORITY)

**Already exists in anewp:**
- `/core/WordPressAPI.php` - REST API client with Basic Auth

**Laravel implementation:**
```php
// app/Services/WordPressPublishingService.php
publishPost($postId, $websiteId)
uploadMedia($imagePath, $websiteId)
fetchCategories($websiteId)
fetchTags($websiteId)
schedulePost($postId, $websiteId, $scheduledAt)
```

### Data Migration Plan

#### User Mapping
```php
// anewp → anewplaravel user ID mapping
anewp: id=1 (admin)  → Laravel: id=2 (wasugi@example.com, super_admin)
anewp: id=2 (author) → Laravel: id=1 (test@example.com, user)
```

#### Website Data Migration Script
```php
// Export from anewp database
$anewpWebsites = AnewpDatabase::table('ane_websites')->get();

foreach ($anewpWebsites as $website) {
    // Decrypt app_password from anewp (manual decryption)
    $decryptedPassword = anewpDecrypt($website->app_password);

    // Re-encrypt with Laravel
    Website::create([
        'name' => $website->name,
        'url' => $website->url,
        'username' => $website->username,
        'app_password' => $decryptedPassword,  // Auto-encrypts via model
        'category' => mapCategory($website->category),  // Map if different
        'status' => $website->status,
        'created_by' => mapUserId($website->created_by),
        'created_at' => $website->created_at,
    ]);
}
```

#### API Credentials Migration
```php
// anewp: per-user model (user_id column exists but unused)
// Laravel: platform-wide (UNIQUE constraint on category+provider)

// Strategy: Take 1 set of active API keys from anewp
$apiKeys = AnewpDatabase::table('ane_api_credentials')
    ->where('is_active', 1)
    ->groupBy(['category', 'provider'])  // De-duplicate
    ->get();

foreach ($apiKeys as $key) {
    ApiCredential::create([
        'category' => mapCategory($key->category),
        'provider' => $key->provider,
        'api_key' => decryptAnewp($key->api_key),  // Decrypt from anewp, re-encrypt via Laravel
        'is_active' => 1,
    ]);
}
```

### Environment Variables Needed

**Add to `.env` (from anewp config):**
```env
# Google APIs (OAuth)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

# DataForSEO (Deep Research)
DATAFORSEO_LOGIN=
DATAFORSEO_PASSWORD=

# Serper API (Google Search)
SERPER_API_KEY=

# RapidAPI (Social Insights)
RAPIDAPI_KEY=

# Firecrawl (Web Scraping)
FIRECRAWL_API_KEY=

# AI Providers (already exists in Laravel)
GEMINI_API_KEY=
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
DEEPSEEK_API_KEY=

# Image Generation
FAL_API_KEY=
STABILITY_API_KEY=
```

### Testing Strategy

**Before migrating each feature:**
1. Test in anewp to understand expected behavior
2. Export sample data (3-5 records) for testing
3. Migrate schema + models
4. Write Feature tests
5. Migrate controller logic
6. Migrate views (Tailwind conversion)
7. Test with real WordPress site

**Test Coverage Required:**
```bash
# Example for Insights feature
php artisan test --filter=InsightTest
php artisan test --filter=PostTest
php artisan test --filter=WordPressPublishingTest
```

### Critical Learnings from anewp

1. **Session Lock Issue (Fixed in anewp):**
   - Problem: Long-running AI operations lock session file
   - Solution: Call `session_write_close()` before heavy processing
   - Reference: `BUGFIX_SESSION_LOCK_TIMEOUT.md`

2. **Polling Duplicate Trigger (Fixed in anewp):**
   - Problem: Frontend polls too frequently, triggers duplicate jobs
   - Solution: Check job status before creating new one
   - Reference: `BUGFIX_POLLING_DUPLICATE_TRIGGER.md`

3. **AI Fallback Pattern:**
   - Always have 2-layer validation: AI + rule-based fallback
   - Example: Deep Research has AI recommendations + manual keyword rules

4. **OAuth Token Management:**
   - Store `refresh_token` for auto-renewal
   - Check `token_expires_at` before API calls
   - Implement auto-refresh in service layer

5. **WordPress API Rate Limiting:**
   - Batch operations (max 10 concurrent)
   - Implement retry with exponential backoff
   - Log all API responses for debugging

### Documentation References

**anewp Documentation (Read These):**
- `/Users/webane/sites/anewp/CLAUDE.md` - 8000+ words technical guide
- `/Users/webane/sites/anewp/README.md` - Feature overview + commands
- `/Users/webane/sites/anewp/STANDARD_PATTERN_BACKGROUND_PROCESSING.md` - Production pattern
- `/Users/webane/sites/anewp/BUGFIX_ALL_SUMMARY.md` - All bug fixes history

**Database Schema:**
- `/Users/webane/sites/anewp/DB/database_schema_update.sql` - Core schema
- `/Users/webane/sites/anewp/DB/deep_research_migration.sql` - Deep Research tables
- `/Users/webane/sites/anewp/DB/ga4_integration_migration.sql` - GA4 tables
- `/Users/webane/sites/anewp/DB/gsc_integration_migration.sql` - GSC tables

### Next Immediate Steps

1. **Read full anewp database schema:**
   ```bash
   mysql -u root anewp_db -e "SHOW CREATE TABLE ane_insights\G"
   mysql -u root anewp_db -e "SHOW CREATE TABLE ane_posts\G"
   ```

2. **Create migrations for core content tables:**
   - `ane_insights`
   - `ane_posts`
   - `ane_insight_articles`
   - `ane_post_targets`

3. **Build Models with relationships**

4. **Create InsightController + PostController**

5. **Build Tailwind views** (following website management pattern)

**Current Status:** Website Management (Point 1) COMPLETE ✅
**Next Target:** Insights + Posts Management (Point 4 + 5)

---

## WordPress Scheduling System

This section documents the **WordPress Scheduling System** for scheduling, publishing, and managing WordPress post schedules with timezone support and calendar integration.

### Overview

The WordPress Scheduling System provides:
- **Schedule WordPress posts** for future publishing
- **Duplicate detection** to prevent accidental re-publishing
- **Force publish** option to bypass duplicate checks
- **Calendar integration** with drag-and-drop reschedule capability
- **Timezone support** (user's local timezone → UTC storage)

**Key Features:**
- Timezone-aware scheduling (Asia/Jakarta, UTC, etc.)
- Duplicate detection by exact title match
- Force publish checkbox for intentional republishing
- Calendar reschedule modal with datetime picker
- Authorization checks (user can only manage own posts)
- Comprehensive logging for audit trails

### Database Schema

**Primary Table:**
```sql
ane_post_targets (WordPress publishing targets)
├── id                  BIGINT UNSIGNED PRIMARY KEY
├── post_id             BIGINT UNSIGNED (FK → ane_posts.id)
├── website_id          BIGINT UNSIGNED (FK → ane_websites.id)
├── status              VARCHAR(20) DEFAULT 'draft' (draft/scheduled/published)
├── categories          JSON NULL (WordPress category IDs array)
├── tags                JSON NULL (WordPress tag IDs array)
├── scheduled_at        TIMESTAMP NULL (UTC, indexed)
├── published_at        TIMESTAMP NULL
├── wp_post_id          INT NULL (WordPress post ID after publishing)
├── created_at          TIMESTAMP
└── updated_at          TIMESTAMP
```

**Indexes (Performance-Critical):**
```sql
-- Composite index for calendar queries (user + date range)
idx_post_website (post_id, website_id)

-- Index for scheduled posts
idx_status_scheduled (status, scheduled_at)
```

**Relationships:**
- `PostTarget` → `Post` (belongsTo)
- `PostTarget` → `Website` (belongsTo)
- `Post` → `PostTarget[]` (hasMany)

### Model Structure

**PostTarget.php ([app/Models/PostTarget.php](app/Models/PostTarget.php)):**
```php
protected $table = 'ane_post_targets';

protected $fillable = [
    'post_id',
    'website_id',
    'status',
    'categories',
    'tags',
    'scheduled_at',
    'published_at',
    'wp_post_id',
];

protected $casts = [
    'categories' => 'array',
    'tags' => 'array',
    'scheduled_at' => 'datetime',
    'published_at' => 'datetime',
];

public function post()
{
    return $this->belongsTo(Post::class);
}

public function website()
{
    return $this->belongsTo(Website::class);
}
```

### Service Pattern

**WordPressPublishService.php ([app/Services/WordPressPublishService.php](app/Services/WordPressPublishService.php)):**

**1. Duplicate Detection:**
```php
/**
 * Check if post with same title already exists on WordPress
 * Returns duplicate post data if found, null otherwise
 */
public function checkDuplicateByTitle(string $title, Website $website): ?array
{
    $url = rtrim($website->url, '/') . '/wp-json/wp/v2/posts?search=' . urlencode($title) . '&per_page=20';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        \Log::warning('WordPress duplicate check failed', [
            'website' => $website->url,
            'http_code' => $httpCode,
            'title' => $title,
        ]);
        return null;
    }

    $results = json_decode($response, true);

    // Check for exact title match (WordPress search is fuzzy)
    foreach ($results as $post) {
        $wpTitle = $post['title']['rendered'] ?? '';

        if ($wpTitle === $title) {
            \Log::info('WordPress duplicate detected', [
                'title' => $title,
                'wp_post_id' => $post['id'],
                'website' => $website->url,
                'status' => $post['status'],
            ]);

            return [
                'wp_post_id' => $post['id'],
                'status' => $post['status'],
                'link' => $post['link'],
                'date' => $post['date'],
            ];
        }
    }

    return null; // No duplicate found
}
```

**2. Publish with Timezone Conversion:**
```php
public function publish(Post $post, PostTarget $target, string $action)
{
    $website = $target->website;

    $wpData = [
        'title' => $post->title,
        'content' => $post->content,
        'status' => $this->getWpStatus($action),
        'categories' => $target->categories ?? [],
    ];

    // Add scheduled date for future posts (use date_gmt for timezone-safe scheduling)
    if ($action === 'schedule' && $target->scheduled_at) {
        $wpData['date_gmt'] = $target->scheduled_at->toIso8601String();
    }

    // ... cURL publish logic
}
```

### Controller Pattern

**PostController.php - store() method with Force Publish:**
```php
public function store(Request $request)
{
    // ... validation and post creation

    // Handle publish action
    $action = $request->publish_action ?? 'save_only';
    if (in_array($action, ['send_draft', 'publish_now', 'schedule'])) {
        try {
            $wpService = new WordPressPublishService();
            $website = Website::findOrFail($request->website_id);

            // Check for duplicate before publishing (unless force_publish is checked)
            if (!$request->has('force_publish')) {
                $duplicate = $wpService->checkDuplicateByTitle($post->title, $website);

                if ($duplicate) {
                    // Show warning with link to existing post
                    $warningMessage = sprintf(
                        'A post with the title "%s" already exists on WordPress (ID: %d, Status: %s). <a href="%s" target="_blank" class="underline font-semibold hover:text-yellow-300">View Post →</a>',
                        $post->title,
                        $duplicate['wp_post_id'],
                        ucfirst($duplicate['status']),
                        $duplicate['link']
                    );

                    return redirect()->route('posts.edit', $post)->with('warning', $warningMessage);
                }
            }

            // Proceed with publish if no duplicate or force_publish checked
            $wpService->publish($post, $target, $action);

            return redirect()->route('posts.index')
                ->with('success', 'Post published to WordPress successfully');

        } catch (\Exception $e) {
            \Log::error('WordPress publish error', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
            ]);

            return redirect()->route('posts.edit', $post)
                ->with('error', 'Failed to publish to WordPress: ' . $e->getMessage());
        }
    }
}
```

**CalendarController.php - rescheduleWordPress() method:**
```php
/**
 * Reschedule WordPress post from calendar
 */
public function rescheduleWordPress(Request $request, PostTarget $target)
{
    // Authorization: User must own the post
    $this->authorize('update', $target->post);

    $validated = $request->validate([
        'scheduled_at' => 'required|date|after:now',
    ]);

    // Update scheduled_at (already in UTC from frontend)
    $target->update([
        'scheduled_at' => $validated['scheduled_at'],
    ]);

    Log::info('WordPress post rescheduled via calendar', [
        'target_id' => $target->id,
        'post_id' => $target->post_id,
        'new_scheduled_at' => $validated['scheduled_at'],
        'user_id' => auth()->id(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Schedule updated successfully',
    ]);
}
```

### Frontend Implementation

**View Structure (Blade):**
```blade
<!-- Force Publish Checkbox (posts/edit.blade.php) -->
<div id="forcePublishField" class="mb-4 {{ in_array($currentAction, ['publish_now', 'send_draft']) ? '' : 'hidden' }}">
    <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 p-2 rounded">
        <input type="checkbox" name="force_publish" value="1" {{ old('force_publish') ? 'checked' : '' }}
               class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-600 focus:ring-2">
        <span class="text-gray-700">
            <span class="font-semibold">Force Publish</span>
            <span class="text-xs text-gray-500 block">Ignore duplicate check and publish anyway</span>
        </span>
    </label>
</div>

<!-- Schedule Time Field -->
<div id="scheduleField" class="mb-4 {{ $currentAction === 'schedule' ? '' : 'hidden' }}">
    <label class="block text-sm font-semibold mb-2">Schedule Time</label>
    <input type="datetime-local" name="scheduled_at"
           value="{{ old('scheduled_at', optional($post->targets->first())->scheduled_at ? $post->targets->first()->scheduled_at->format('Y-m-d\TH:i') : '') }}"
           class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
</div>
```

**Calendar Reschedule Modal (calendar/index.blade.php):**
```blade
<!-- WordPress Reschedule Modal -->
<div id="rescheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Reschedule WordPress Post</h3>
            <p id="reschedulePostTitle" class="text-sm text-gray-600 mt-1"></p>
        </div>

        <!-- Modal Content -->
        <div class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Current Schedule</label>
                    <p id="rescheduleCurrentTime" class="text-sm text-gray-600 bg-gray-50 px-3 py-2 rounded"></p>
                </div>

                <div>
                    <label for="rescheduleNewTime" class="block text-sm font-semibold text-gray-700 mb-2">New Schedule Time</label>
                    <input type="datetime-local" id="rescheduleNewTime"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Select the new date and time for publishing</p>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-200 flex gap-2">
            <button onclick="closeRescheduleModal()" class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                Cancel
            </button>
            <button onclick="confirmReschedule()" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition">
                Reschedule
            </button>
        </div>
    </div>
</div>
```

**JavaScript Functions:**
```javascript
// WordPress Reschedule Modal Functions
let currentRescheduleTargetId = null;

function openRescheduleModal(targetId, title, currentTime) {
    currentRescheduleTargetId = targetId;
    document.getElementById('reschedulePostTitle').textContent = title;
    document.getElementById('rescheduleCurrentTime').textContent = currentTime;

    // Set min datetime to now (can't schedule in the past)
    const now = new Date();
    const minDatetime = now.toISOString().slice(0, 16);
    document.getElementById('rescheduleNewTime').setAttribute('min', minDatetime);

    // Clear previous value
    document.getElementById('rescheduleNewTime').value = '';

    document.getElementById('rescheduleModal').classList.remove('hidden');
}

async function confirmReschedule() {
    const newTime = document.getElementById('rescheduleNewTime').value;

    if (!newTime) {
        alert('Please select a new time');
        return;
    }

    // Convert to UTC ISO string
    const localDate = new Date(newTime);
    const utcDate = new Date(localDate.getTime() - localDate.getTimezoneOffset() * 60000);

    try {
        const res = await fetch(`/calendar/wordpress/${currentRescheduleTargetId}/reschedule`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                scheduled_at: utcDate.toISOString()
            })
        });

        const data = await res.json();

        if (data.success) {
            closeRescheduleModal();
            calendar.refetchEvents();
            showNotification('success', 'Schedule updated successfully!');
        } else {
            alert('Reschedule failed: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Reschedule error:', error);
        alert('Network error during reschedule');
    }
}
```

### Routes

```php
// WordPress Scheduling
Route::middleware(['auth', 'verified'])->group(function () {
    // Post management (includes scheduling)
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/wordpress-events', [CalendarController::class, 'getWordPressEvents'])->name('calendar.wordpress-events');
    Route::patch('/calendar/wordpress/{target}/reschedule', [CalendarController::class, 'rescheduleWordPress'])->name('calendar.wordpress-reschedule');
});
```

### Timezone Handling (Critical)

**Database Storage:** ALWAYS store `scheduled_at` in UTC
**Frontend Display:** Convert UTC → user's timezone using `auth()->user()->timezone`
**User Input:** Convert user's timezone → UTC before saving

**Example Flow:**
1. User selects "10 Jan 2026, 14:00 WIB (Asia/Jakarta)"
2. JavaScript converts to UTC: "10 Jan 2026, 07:00 UTC"
3. Backend saves UTC: `scheduled_at = '2026-01-10 07:00:00'`
4. Frontend retrieves and converts back to WIB for display

**Code Pattern:**
```php
// Backend: UTC to user's timezone
$userTimezone = auth()->user()->timezone ?? 'UTC';
$localTime = $target->scheduled_at->copy()->setTimezone($userTimezone);
```

```javascript
// Frontend: User's timezone to UTC
const localDate = new Date(newTime); // Browser's local time
const utcDate = new Date(localDate.getTime() - localDate.getTimezoneOffset() * 60000);
```

### Testing Checklist

**Schedule WordPress Post:**
- [ ] User can schedule post for future date
- [ ] `scheduled_at` saved as UTC in database
- [ ] `status` set to 'scheduled' (not 'draft')
- [ ] Post appears in calendar at correct time

**Force Publish:**
- [ ] Checkbox appears for "Publish Now" and "Send Draft"
- [ ] Checkbox hidden for "Save Only" and "Schedule"
- [ ] When checked, duplicate detection bypassed
- [ ] When unchecked, normal duplicate detection works

**Duplicate Detection:**
- [ ] Exact title match detected
- [ ] Warning message shows with link to existing post
- [ ] User can click link to view existing WordPress post
- [ ] Force publish overrides duplicate warning

**Calendar Reschedule:**
- [ ] "Reschedule" button appears in event popup
- [ ] Modal shows current and new time fields
- [ ] Datetime picker validates future dates only
- [ ] PATCH request updates `scheduled_at`
- [ ] Calendar refreshes with new time

**Authorization:**
- [ ] User can only reschedule own posts (403 for others)
- [ ] Policy check enforced on server-side

### Common Issues & Solutions

**Problem 1: Duplicate not detected**
- **Cause:** Title mismatch (different case or punctuation)
- **Fix:** WordPress API uses fuzzy search, but we filter for exact match

**Problem 2: Timezone mismatch (events show at wrong time)**
- **Cause:** User's timezone not set or UTC conversion missing
- **Fix:** Verify `auth()->user()->timezone` and check conversion logic

**Problem 3: Force publish not working**
- **Cause:** Missing `force_publish` parameter in request
- **Fix:** Ensure checkbox `name="force_publish"` is set correctly

**Problem 4: Reschedule fails with 403**
- **Cause:** User trying to reschedule another user's post
- **Fix:** Verify ownership via `PostPolicy->update()`

### Integration with Calendar System

WordPress events are displayed in calendar as green pills:

**Flow:**
1. User schedules post (status='scheduled', scheduled_at set)
2. `CalendarController->getWordPressEvents()` fetches scheduled targets
3. Events appear in calendar with green background color
4. User can click event → see details → reschedule

**Related Services:**
- [WordPressPublishService.php](app/Services/WordPressPublishService.php) - Publish and duplicate detection
- [CalendarController.php](app/Http/Controllers/CalendarController.php) - Calendar UI and reschedule
- [PostController.php](app/Http/Controllers/PostController.php) - Post CRUD with scheduling

### File References

**Controllers:**
- [app/Http/Controllers/PostController.php](app/Http/Controllers/PostController.php) - Post CRUD with scheduling
- [app/Http/Controllers/CalendarController.php](app/Http/Controllers/CalendarController.php) - Calendar and reschedule

**Services:**
- [app/Services/WordPressPublishService.php](app/Services/WordPressPublishService.php) - WordPress API integration

**Models:**
- [app/Models/Post.php](app/Models/Post.php) - Main post model
- [app/Models/PostTarget.php](app/Models/PostTarget.php) - Publishing targets with scheduling
- [app/Models/Website.php](app/Models/Website.php) - WordPress sites

**Policies:**
- [app/Policies/PostPolicy.php](app/Policies/PostPolicy.php) - Authorization

**Views:**
- [resources/views/posts/edit.blade.php](resources/views/posts/edit.blade.php) - Post editor with scheduling UI
- [resources/views/calendar/index.blade.php](resources/views/calendar/index.blade.php) - Calendar with reschedule modal

**Tests:**
- [tests/Unit/Services/WordPressPublishServiceTest.php](tests/Unit/Services/WordPressPublishServiceTest.php) - Unit tests
- [tests/Feature/Feature/WordPressSchedulingTest.php](tests/Feature/Feature/WordPressSchedulingTest.php) - Feature tests

**Documentation:**
- [07-scheduling-wordpress-improvement.md](07-scheduling-wordpress-improvement.md) - Phase planning document
- [PHASE_3_COMPLETION_REPORT.md](PHASE_3_COMPLETION_REPORT.md) - Phase 3 completion report
- [OPTION_A_COMPLETION_REPORT.md](OPTION_A_COMPLETION_REPORT.md) - Finalization report

---