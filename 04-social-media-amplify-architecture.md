# Social Media Amplify - Architecture & Implementation Guide

## Overview

Fitur Social Media Amplify adalah **Template-Driven Visual Editor** yang memungkinkan user membuat konten visual untuk social media dengan mudah menggunakan template berbasis Konva.js.

**Konsep Utama:**
- **Template-First Editor** - Bukan image editor biasa, tapi editor untuk template reusable
- **Branding = Universal** - Logo, colors, font bisa dipakai di semua template (article & standalone)
- **Template Type** - Article (auto-fill) vs Standalone (manual content)
- **Category System** - News, Event, Promo, Registration, dll untuk organisasi template

**Tujuan:**
- User dapat membuat konten visual dari artikel dengan 1 klik (amplify)
- User dapat membuat poster standalone (event, promo, announcement)
- Support multiple templates dengan kategorisasi
- Branding preset untuk konsistensi visual (logo, colors, font)
- Template reusable (platform templates + user custom templates)
- Export ke berbagai format social media (Instagram, Facebook, Twitter, LinkedIn)

---

## Progress Summary

### ✅ Completed Phases:

**Phase 1: Template System Foundation** (Week 1)
- ✅ Database schema (templates, brandings, social assets)
- ✅ Template CRUD (create, read, update, delete)
- ✅ Branding management with template selector
- ✅ Variable replacement system (article_title, brand_logo, etc.)
- ✅ Amplify backend logic (AmplifyController)

**Phase 2: Image Editor Enhancement** (Week 2)
- ✅ Konva.js canvas editor integration
- ✅ Templates tab in editor (load platform/user templates)
- ✅ Template application to canvas
- ✅ Save template feature
- ✅ Branding integration in templates

**Phase 3: Asset Library Integration** (Week 3)
- ✅ Load asset in Konva editor via URL parameter
- ✅ Save asset visual (canvas JSON + PNG export)
- ✅ Image editor link from asset library
- ✅ Real-time visual update in asset library

**Phase 4: Auto-Generate from Article** (Week 4)
- ✅ Branding selector in amplify modal
- ✅ Dynamic branding loading via API
- ✅ API routes configuration (bootstrap + Sanctum auth)
- ✅ Route naming conventions (no collisions)
- ✅ Authorization with policies
- ✅ Multi-branding support

**Phase 5: Canvas Size Presets** (Week 5)
- ✅ Resize modal with width/height inputs
- ✅ Magic resize toggle (scale all objects)
- ✅ Instagram presets (Post, Story)
- ✅ Facebook presets (Landscape, Square, Cover)
- ✅ YouTube presets (Thumbnail, Channel, Short)

**Phase 6: Template Categories & Management** (Week 6) - ✅ **COMPLETED**
- ✅ Dynamic template categories system (ane_template_categories table)
- ✅ Category CRUD for admin (/admin/template-categories)
- ✅ Quick add category in Image Editor save modal
- ✅ User vs Admin permissions (user: no category, admin: can categorize + publish)
- ✅ Template categorization (category_id foreign key with SET NULL)
- ✅ Platform templates grouped by category
- ✅ API endpoints for category management (GET /api/template-categories, POST /api/template-categories)
- ✅ 6 default categories seeded (Social Media, Marketing, Business, Events, Education, Tech)
- ✅ Deselect on background click feature (image editor UX improvement)
- ✅ Category seeder (TemplateCategorySeeder)

### 🔄 Next Phases:

**Phase 7: Advanced Features** (Week 7+) - Not Started
- [ ] Scheduled publishing integration
- [ ] AI-powered content suggestions
- [ ] Batch processing
- [ ] Analytics integration

---

## User Flow

### Flow 0: Setup Branding Preset (One-Time Setup)
```
1. User buka Branding Management (/brandings)
2. Klik "Create New Branding"
3. Form:
   - Preset name (e.g., "My Business Brand")
   - Select website (from ane_websites)
   - Upload logo (from ane_media)
   - Primary color (color picker)
   - Secondary color (color picker)
   - Default font (dropdown)
   - **Default Template for Social Media** (NEW - dropdown template selector)
4. Klik "Save"
   → Branding preset tersimpan
   → Template default sudah terset untuk website ini
5. Setiap amplify dari artikel website ini, otomatis pakai template yang sudah dipilih
```

**Catatan Penting:**
- Branding sudah punya relasi `website_id` (existing)
- Template dipilih **SEKALI** saat setup branding
- User bisa edit branding kapan saja untuk ganti template default
- **User bisa punya MULTIPLE brandings untuk 1 website** (key feature!)
  - Contoh: Website "News Portal" punya 3 brandings:
    1. "Sports Branding" → Template merah energik
    2. "Tech Branding" → Template biru modern
    3. "Politics Branding" → Template hitam formal
- Saat amplify → User pilih branding mana yang mau dipakai

---

### Flow 1: Auto-Generate dari Article (Amplify - Quick Publish)
```
1. User buka Post List (/posts)
2. Klik icon "Share" (Amplify) pada artikel tertentu
   → Trigger event: open-amplify-modal
3. Modal "Amplify to Social Media" muncul:
   - **Select Branding** (dropdown) - User pilih branding preset
     * Load brandings dari user via /api/brandings
     * Each branding has defaultTemplate
     * User bisa punya multiple brandings (berbagai tema/style)
   - Select channels (Instagram/Facebook/Twitter/LinkedIn) - checkboxes
   - Auto-schedule option (checkbox) - for future implementation
4. Klik "Amplify"
   → Backend logic (AmplifyController@generate):
      a. Validate request (branding_id, channels)
      b. Get branding with relations (defaultTemplate, logo, website)
      c. Validate branding ownership (branding.user_id === auth()->id())
      d. Get template from branding.defaultTemplate OR fallback to default template
      e. Prepare variables for replacement:
         - article_title → post.title
         - article_excerpt → Str::limit(strip_tags(post.content), 150)
         - article_author → post.owner.name
         - featured_image → post.featuredMedia?.full_url
         - brand_logo → branding.logo?.full_url
         - brand_color_primary → branding.primary_color
         - brand_color_secondary → branding.secondary_color
         - brand_font → branding.default_font
         - website_url → branding.website?.url
      f. Replace variables in template canvas_json via replaceVariables() helper
      g. Generate SocialAsset for each channel:
         - Extract DNA (ContentDNAService)
         - Generate copy (SocialCopyService)
         - Attach canvas_json to asset
      h. (Optional) Auto-schedule if requested
   → Return JSON response with assets array
   → Frontend redirects to /assets
5. User di Asset Library bisa:
   - View generated assets with canvas preview
   - Edit di image editor (click Edit)
   - Export/Download
   - Publish/Schedule (future feature)
```

**Use Case:**
- User punya "Branding A" (template merah, untuk olahraga)
- User punya "Branding B" (template biru, untuk teknologi)
- Hari ini publish berita olahraga → pilih Branding A
- Besok publish berita teknologi → pilih Branding B
- **Instagram feed jadi variatif, tidak monoton!**

**Implementation Details:**
- Modal location: `/resources/views/components/amplify-modal.blade.php`
- Controller: `AmplifyController@generate` (line 33-121)
- POST endpoint: `/posts/{post}/amplify`
- API endpoint for brandings: `GET /api/brandings` (BrandingController@index)
- Variable replacement: `replaceVariables()` private method (line 123-158)
```

### Flow 2: Create Standalone Content (Event, Promo, Announcement)
```
1. User buka Image Editor (/image-editor)
2. Klik tab "Templates" → Pilih category: "Event" / "Promo" / "Announcement"
3. Pilih template (e.g., "Event Announcement")
4. Canvas load template (masih pakai placeholder logo & default colors)
5. User klik dropdown "Load Branding" → Pilih branding "My Business Brand"
   → Logo otomatis replace ke branding logo
   → Primary/Secondary colors otomatis replace
   → Font otomatis replace
6. User edit text manual:
   - Title: "Pendaftaran Siswa Baru 2025"
   - Description: "Dibuka mulai 1 Januari 2025"
   - Date: "1-31 Januari 2025"
7. User upload image jika ada placeholder
8. Klik "Save as Asset"
   → Render canvas to PNG
   → Save to ane_social_assets
   → Redirect to Asset Library
```

**Use Case:**
- Event poster, promo diskon, pengumuman penting
- Template sudah siap, tinggal ganti text & load branding
- Konsisten dengan brand identity (logo, colors)

---

### Flow 3: Edit Asset di Asset Library
```
1. User buka Asset Library (/assets)
2. Klik "Edit" pada asset
3. Image Editor muncul:
   - Canvas loaded dengan canvas_json (jika ada) atau visual_path
   - Sidebar: Templates, Shapes, Text, Icons, Upload
   - Toolbar: Load Branding, Save as Template, Export
4. User bisa:
   - Load branding lain (replace logo/colors)
   - Load template lain
   - Edit shapes, text, images
   - Save as new template
5. Klik "Save" → Update visual_path & canvas_json
```

---

### Flow 4: Create Template (Custom Template)
```
1. User buka Image Editor
2. Design layout (add shapes, text, images, icons)
3. Mark variables (optional):
   - Select text → Sidebar property → Set as variable: "Article Title"
   - Select image → Set as variable: "Brand Logo"
   - Select shape → Set color variable: "Brand Primary Color"
4. Klik "Save as Template"
5. Modal muncul:
   - Template Name: "My News Template"
   - Template Type: ● Article Template / ○ Standalone Template
   - Category: [News ▼] (news, event, promo, announcement, registration, quote, custom)
   - ☑ Make this template public (share with all users)
6. Klik "Save"
   → Template tersimpan ke ane_templates
   → Bisa dipakai ulang di branding atau manual load
```

**Variable Detection (jika Article Template):**
- System scan canvas_json untuk object dengan flag `isVariable: true`
- Preview: "Variables Detected: {article_title}, {brand_logo}, {brand_color_primary}"

---

## Database Schema

### New Table: ane_templates

```sql
CREATE TABLE ane_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id BIGINT UNSIGNED NULL COMMENT 'FK to ane_template_categories (NULL for user templates)',
    template_type ENUM('article', 'standalone') DEFAULT 'standalone' COMMENT 'Article = auto-fill variables, Standalone = manual content',
    canvas_json TEXT NOT NULL COMMENT 'Konva Stage JSON with variable placeholders',
    thumbnail_path VARCHAR(255) NULL COMMENT 'Preview image path',
    canvas_width INT DEFAULT 1080,
    canvas_height INT DEFAULT 1080,
    is_public BOOLEAN DEFAULT 0 COMMENT '0=user template, 1=platform template',
    is_default BOOLEAN DEFAULT 0 COMMENT '1=default template for auto-generate',
    created_by BIGINT UNSIGNED NULL COMMENT 'NULL=platform, user_id=user template',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_public_category (is_public, category_id),
    INDEX idx_created_by (created_by),
    INDEX idx_default (is_default, category_id),

    FOREIGN KEY (created_by) REFERENCES ane_users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES ane_template_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### New Table: ane_template_categories

```sql
CREATE TABLE ane_template_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50) NULL COMMENT 'Heroicons icon name',
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Default Categories (seeded):**
- Social Media (icon: share, sort: 1)
- Marketing (icon: megaphone, sort: 2)
- Business (icon: briefcase, sort: 3)
- Events (icon: calendar, sort: 4)
- Education (icon: book, sort: 5)
- Tech (icon: cpu, sort: 6)

### Existing Table: ane_social_assets (No Changes Needed)

Already has:
- `visual_path` - Path to generated image
- `copy_text` - Caption/text content
- `channel` - Social media platform
- `post_id` - Link to article (optional)
- `created_by` - User ownership

### Modified Table: ane_user_brandings (ADD default_template_id)

**Existing Fields:**
- `user_id` - FK to ane_users
- `website_id` - FK to ane_websites (nullable)
- `preset_name` - Branding preset name
- `logo_id` - FK to ane_media (logo image)
- `primary_color` - Brand primary color (#RRGGBB)
- `secondary_color` - Brand secondary color (#RRGGBB)
- `default_font` - Default font name

**NEW Field (Migration Required):**
```sql
ALTER TABLE ane_user_brandings
ADD COLUMN default_template_id BIGINT UNSIGNED NULL AFTER default_font,
ADD CONSTRAINT fk_branding_template
    FOREIGN KEY (default_template_id)
    REFERENCES ane_templates(id)
    ON DELETE SET NULL;
```

**Purpose:**
- Link branding preset to default template for auto-amplify
- When user amplify article → system loads template from branding
- One template per branding preset
- Nullable: If NULL, use platform default news template

**Migration File:**
`database/migrations/YYYY_MM_DD_add_default_template_to_brandings.php`

---

## Variable System & Branding Integration

### **Core Concept: Branding is Universal**

**Branding preset** bisa dipakai untuk **SEMUA template** (article & standalone), bukan hanya article template.

**Branding Variables (ALWAYS Available):**
- `brand_logo` - Logo image URL from branding preset
- `brand_color_primary` - Primary color (#hex) from branding
- `brand_color_secondary` - Secondary color (#hex) from branding
- `brand_font` - Font family from branding
- `website_url` - Website URL from branding

**Article Variables (ONLY for Article Templates):**
- `article_title` - Post title
- `article_excerpt` - Post content excerpt (150 chars)
- `article_author` - Post author name
- `featured_image` - Post featured image URL

---

### **Variable Marking in Canvas JSON**

**Text Object with Variable:**
```json
{
  "className": "Text",
  "attrs": {
    "text": "Sample Title",
    "fontSize": 48,
    "fontFamily": "Inter",
    "fill": "#000000",

    "isVariable": true,
    "variableName": "article_title",
    "fillVariable": "brand_color_primary"
  }
}
```

**Image Object with Variable (Logo):**
```json
{
  "className": "Image",
  "attrs": {
    "x": 100,
    "y": 100,
    "width": 200,
    "height": 200,
    "image": "https://placeholder.com/logo.png",

    "isVariable": true,
    "variableName": "brand_logo"
  }
}
```

**Shape with Color Variable:**
```json
{
  "className": "Rect",
  "attrs": {
    "x": 0,
    "y": 0,
    "width": 1080,
    "height": 100,
    "fill": "#FF5733",

    "fillVariable": "brand_color_primary"
  }
}
```

---

### **Branding Application Flow**

**Backend Helper Function:**
```php
private function applyBrandingToTemplate($canvasJSON, UserBranding $branding, ?Post $post = null)
{
    $variables = [
        // Branding variables (ALWAYS available)
        'brand_logo' => $branding->logo?->full_url ?? '',
        'brand_color_primary' => $branding->primary_color,
        'brand_color_secondary' => $branding->secondary_color,
        'brand_font' => $branding->default_font,
        'website_url' => $branding->website?->url ?? '',
    ];

    // Article variables (ONLY if $post provided)
    if ($post) {
        $variables = array_merge($variables, [
            'article_title' => $post->title,
            'article_excerpt' => \Str::limit($post->content, 150),
            'article_author' => $post->owner->name,
            'featured_image' => $post->featuredMedia?->full_url ?? '',
        ]);
    }

    // Replace variables in canvas JSON
    foreach ($canvasJSON['children'] as &$layer) {
        foreach ($layer['children'] as &$obj) {
            // Text variable
            if ($obj['attrs']['isVariable'] ?? false) {
                $varName = $obj['attrs']['variableName'];
                if (isset($variables[$varName])) {
                    $obj['attrs']['text'] = $variables[$varName];
                }
            }

            // Image variable
            if ($obj['className'] === 'Image' && ($obj['attrs']['isVariable'] ?? false)) {
                $varName = $obj['attrs']['variableName'];
                if (isset($variables[$varName])) {
                    $obj['attrs']['image'] = $variables[$varName];
                }
            }

            // Fill color variable
            if (isset($obj['attrs']['fillVariable'])) {
                $varName = $obj['attrs']['fillVariable'];
                if (isset($variables[$varName])) {
                    $obj['attrs']['fill'] = $variables[$varName];
                }
            }

            // Font variable
            if (isset($obj['attrs']['fontVariable'])) {
                $varName = $obj['attrs']['fontVariable'];
                if (isset($variables[$varName])) {
                    $obj['attrs']['fontFamily'] = $variables[$varName];
                }
            }
        }
    }

    return $canvasJSON;
}
```

**Usage Scenarios:**

**1. Amplify Article (Auto-fill Article + Branding):**
```php
public function amplify(Request $request, Post $post)
{
    $branding = UserBranding::findOrFail($request->branding_id);
    $template = $branding->defaultTemplate;

    // Apply branding + article variables
    $canvasJSON = applyBrandingToTemplate($template->canvas_json, $branding, $post);

    SocialAsset::create([
        'post_id' => $post->id,
        'canvas_json' => $canvasJSON,
    ]);
}
```

**2. Load Template + Branding in Editor (Branding Only):**
```php
Route::post('/api/templates/{template}/apply-branding', function(Template $template, Request $request) {
    $branding = UserBranding::findOrFail($request->branding_id);

    // Apply branding only (no article data)
    $canvasJSON = applyBrandingToTemplate($template->canvas_json, $branding);

    return response()->json(['canvas_json' => $canvasJSON]);
});
```

---

### **Template Categories & Types**

**Category** = Dynamic categorization (admin-managed):
- Categories are stored in `ane_template_categories` table
- Admins can add/edit/delete categories via `/admin/template-categories`
- Default categories: Social Media, Marketing, Business, Events, Education, Tech
- **User templates**: No category (category_id = NULL), shown in "My Templates" section
- **Platform templates**: Must have category (category_id required), shown grouped by category

**Template Type** = Usage (determines variable availability):
- `article` - Auto-linked dengan post/article, punya article variables
- `standalone` - Manual content, hanya punya branding variables

**Permission System:**
- **Regular users**: Can only save templates WITHOUT category (always private)
- **Super admin**: Can assign category + make templates public (platform templates)

**Example Data:**

| Name | Category | Template Type | is_public | Use Case |
|------|----------|---------------|-----------|----------|
| News Article Default | Social Media | article | 1 (platform) | Amplify news to social media |
| Quote Post | Social Media | article | 1 (platform) | Amplify quote from article |
| Event Announcement | Events | standalone | 1 (platform) | Create event poster manually |
| School Registration | Education | standalone | 1 (platform) | Create registration poster |
| My Custom Template | NULL | article | 0 (user) | User's personal template |

**Branding Selector:**
- Di branding form (`/brandings/create`): Hanya tampilkan `template_type = 'article'`
- Di image editor template tab: Tampilkan SEMUA templates, grouping by category (platform) + "My Templates" (user)

---

## Implementation Roadmap

### Phase 1: Template System Foundation ✅ **ALL STEPS COMPLETED** (Week 1)

#### Step 1.1: Database Migrations ✅ **COMPLETED**

**Task 1.1.1: Create ane_templates table**
- ✅ Create migration for `ane_templates` table
- ✅ Fields: id, name, category, canvas_json, thumbnail_path, canvas_width, canvas_height, is_public, is_default, created_by, timestamps
- ✅ Indices: (is_public, category), created_by, (is_default, category)
- ✅ Foreign key: created_by → ane_users.id

**Task 1.1.2: Add default_template_id to ane_user_brandings**
- ✅ Create migration: `add_default_template_to_brandings`
- ✅ Add column: `default_template_id BIGINT UNSIGNED NULL`
- ✅ Add foreign key: default_template_id → ane_templates.id ON DELETE SET NULL

**Task 1.1.3: Add canvas_json to ane_social_assets**
- ✅ Create migration: `add_canvas_json_to_social_assets`
- ✅ Add column: `canvas_json TEXT NULL`
- ✅ Run migration: `php artisan migrate`

**Files Created:**
- ✅ `database/migrations/2025_12_25_231829_create_templates_table.php`
- ✅ `database/migrations/2025_12_25_231830_add_default_template_to_brandings.php`
- ✅ `database/migrations/2025_12_25_231830_add_canvas_json_to_social_assets.php`

---

#### Step 1.2: Models & Relationships ✅ **COMPLETED**

**Task 1.2.1: Template Model**
- ✅ Create `Template` model
- ✅ Fillable: name, category, canvas_json, thumbnail_path, canvas_width, canvas_height, is_public, is_default, created_by
- ✅ Casts: canvas_json → array, is_public → boolean, is_default → boolean
- ✅ Relationships:
  - ✅ `creator()` → belongsTo(User::class, 'created_by')
  - ✅ `brandings()` → hasMany(UserBranding::class, 'default_template_id')
- ✅ Accessors:
  - ✅ `getThumbnailUrlAttribute()` → asset('storage/' . $this->thumbnail_path)
- ✅ Scopes:
  - ✅ `scopePublic($query)` → where('is_public', 1)
  - ✅ `scopeUserTemplates($query, $userId)` → where('created_by', $userId)
  - ✅ `scopeAccessible($query, $userId)` → public OR user's own
  - ✅ `scopeDefault($query, $category)` → where('is_default', 1)
- ✅ Static methods:
  - ✅ `getDefaultNewsTemplate()` → fallback template
  - ✅ `getDefaultArticleTemplate()` → fallback template

**Task 1.2.2: Update UserBranding Model**
- ✅ Add to fillable: `default_template_id`
- ✅ Add relationship: `defaultTemplate()` → belongsTo(Template::class, 'default_template_id')

**Task 1.2.3: Update SocialAsset Model**
- ✅ Add to fillable: `canvas_json`
- ✅ Add to casts: `canvas_json` → array

**Files Modified:**
- ✅ `app/Models/Template.php` (NEW)
- ✅ `app/Models/UserBranding.php` (MODIFY)
- ✅ `app/Models/SocialAsset.php` (MODIFY)

---

#### Step 1.3: Authorization Policy ✅ **COMPLETED**

**Task 1.3.1: TemplatePolicy**
- ✅ Create `TemplatePolicy`
- ✅ `viewAny(User $user)` → true (all users can browse)
- ✅ `view(User $user, Template $template)` → public OR owned by user
- ✅ `create(User $user)` → all authenticated users
- ✅ `update(User $user, Template $template)` → only owner
- ✅ `delete(User $user, Template $template)` → only owner, NOT public templates
- ✅ `restore(User $user, Template $template)` → only owner
- ✅ `forceDelete(User $user, Template $template)` → only owner

**Files Created:**
- ✅ `app/Policies/TemplatePolicy.php` (NEW)

**Note:** Policy auto-discovery enabled di Laravel 12, tidak perlu register manual di AuthServiceProvider

---

#### Step 1.4: Template Seeder ✅ **COMPLETED**

**Task 1.4.1: Create Platform Templates**
- ✅ Create `TemplateSeeder`
- ✅ Create 3 default templates:
  1. ✅ **News Article Default** (is_default=1, category='news')
     - Primary color background
     - Article title (centered, large text)
     - Brand logo (top center)
     - Website URL (bottom)
     - Konva JSON with placeholders: {brand_logo}, {article_title}, {brand_color_primary}, {brand_color_secondary}, {website_url}
  2. ✅ **Blog Article Default** (is_default=1, category='article')
     - Primary color background with accent bar
     - Brand logo (top left)
     - Article title (large, bold)
     - Article excerpt (smaller text)
     - Author name ("By {article_author}")
     - Website URL (bottom right)
  3. ✅ **Quote Post** (is_default=0, category='quote')
     - Secondary color background
     - Large decorative quote mark
     - Quote text (italic, Georgia font)
     - Author attribution
     - Logo (bottom right corner)
- ✅ All templates: is_public=1, created_by=NULL
- ✅ Run seeder: `php artisan db:seed --class=TemplateSeeder`
- ✅ Verified: 3 templates created successfully

**Template Variables Supported:**
- `{brand_logo}` - Logo image URL
- `{brand_color_primary}` - Primary brand color (#hex)
- `{brand_color_secondary}` - Secondary brand color (#hex)
- `{article_title}` - Post title
- `{article_excerpt}` - Post excerpt (first 150 chars)
- `{article_author}` - Author name
- `{website_url}` - Website URL

**Files Created:**
- ✅ `database/seeders/TemplateSeeder.php` (NEW)

---

#### Step 1.5: Template API Endpoints ✅ **COMPLETED**

**Task 1.5.0: Add template_type field**
- ✅ Create migration `add_template_type_to_templates`
- ✅ Add `template_type ENUM('article', 'standalone')` after category
- ✅ Update Template model fillable
- ✅ Add scopes: `articleTemplates()`, `standaloneTemplates()`
- ✅ Update existing templates to 'article' type
- ✅ Run migration

**Task 1.5.1: TemplateController**
- ✅ Create `Api\TemplateController`
- ✅ `index()` → List templates with filters (category, template_type, search)
- ✅ `show(Template $template)` → Get template detail (authorize view)
- ✅ `store(Request $request)` → Save new template with validation
- ✅ `update(Request $request, Template $template)` → Update template (authorize update)
- ✅ `destroy(Template $template)` → Delete template (authorize delete)
- ✅ `duplicate(Template $template)` → Duplicate template as user's own
- ✅ `applyBranding(Template $template, Request $request)` → Apply branding variables

**Task 1.5.2: API Routes**
- ✅ Added routes in `routes/api.php`:
  - `Route::apiResource('templates', TemplateController::class)`
  - `POST /api/templates/{template}/duplicate`
  - `POST /api/templates/{template}/apply-branding`
- ✅ Auth middleware: `auth:sanctum`

**Task 1.5.3: Validation**
- ✅ Inline validation in controller
- ✅ Category: news,quote,event,promo,announcement,registration,product,custom
- ✅ Template type: article,standalone
- ✅ Canvas JSON: array validation
- ✅ Dimensions: min 100px

**Files Created:**
- ✅ `database/migrations/2025_12_26_000417_add_template_type_to_templates.php`
- ✅ `app/Http/Controllers/Api/TemplateController.php`

**Files Modified:**
- ✅ `app/Models/Template.php` (added template_type, scopes)
- ✅ `routes/api.php` (added template routes)

---

### Phase 1.6: Branding Integration (Template Selector) ✅ **COMPLETED**

**Task 1.6.1: Update Branding Form (Create/Edit)**
- ✅ Locate branding form view (likely `/resources/views/brandings/create.blade.php` & `edit.blade.php`)
- ✅ Add template selector field:
  ```html
  <label>Default Template for Social Media</label>
  <select name="default_template_id" id="default_template_id">
      <option value="">-- Select Template --</option>
      @foreach($templates as $template)
          <option value="{{ $template->id }}"
                  {{ old('default_template_id', $branding->default_template_id) == $template->id ? 'selected' : '' }}>
              {{ $template->name }} ({{ $template->category }})
          </option>
      @endforeach
  </select>
  ```
- [x] Add template preview (thumbnail) when selected

**Task 1.6.2: Update BrandingController**
- ✅ In `create()` method: Load templates
  ```php
  $templates = Template::articleTemplates()->accessible(auth()->id())->orderBy('category')->orderBy('name')->get();
  return view('brandings.create', compact('websites', 'templates'));
  ```
- ✅ In `edit()` method: Load templates
  ```php
  $templates = Template::articleTemplates()->accessible(auth()->id())->orderBy('category')->orderBy('name')->get();
  return view('brandings.edit', compact('branding', 'websites', 'templates'));
  ```
- ✅ In `store()` method: Validate and save `default_template_id`
  ```php
  $validated = $request->validate([
      // ... existing fields
      'default_template_id' => 'nullable|exists:ane_templates,id',
  ]);
  // In UserBranding::create():
  'default_template_id' => $validated['default_template_id'] ?? null,
  ```
- ✅ In `update()` method: Allow updating `default_template_id`
  ```php
  // In $branding->update():
  'default_template_id' => $validated['default_template_id'] ?? null,
  ```

**Task 1.6.3: Template Preview in Branding Form**
- ✅ Add JavaScript to show template thumbnail when selected
- ✅ Fetch template detail via AJAX: `GET /api/templates/{id}`
- ✅ Display thumbnail in preview area
- ✅ Auto-load preview on edit page if template selected

**Files Created:**
- (none)

**Files Modified:**
- ✅ `resources/views/brandings/create.blade.php` (selector + preview + JS)
- ✅ `resources/views/brandings/edit.blade.php` (selector + preview + JS + auto-load)
- ✅ `app/Http/Controllers/BrandingController.php` (load templates, validate, save)

---

### Phase 1.7: Amplify Backend Logic (Connect Template to Amplify) ✅ **ALL TASKS COMPLETED**

**Task 1.7.1: Update AmplifyController@generate Method**

Current location: Endpoint `/posts/{post}/amplify` (AmplifyController exists)

- ✅ Located AmplifyController@generate method
- ✅ Implement template-based generation logic:

```php
public function amplify(Request $request, Post $post)
{
    $validated = $request->validate([
        'branding_id' => 'required|exists:ane_user_brandings,id', // NEW - User pilih branding
        'channels' => 'required|array',
        'channels.*' => 'in:instagram,facebook,twitter,linkedin',
        'auto_schedule' => 'boolean'
    ]);

    // Step 1: Get selected branding & validate ownership
    $branding = UserBranding::with(['defaultTemplate', 'logo', 'website'])
        ->findOrFail($validated['branding_id']);

    // Security check: Ensure user owns this branding
    if ($branding->user_id !== auth()->id()) {
        return response()->json([
            'success' => false,
            'error' => 'Unauthorized: You do not own this branding preset'
        ], 403);
    }

    // Step 2: Get template from branding (or fallback to default)
    $template = $branding->defaultTemplate ?? Template::getDefaultNewsTemplate();

    if (!$template) {
        return response()->json([
            'success' => false,
            'error' => 'No template available. Please set a template in branding settings.'
        ], 400);
    }

    // Step 3: Variable replacement
    $variables = [
        'article_title' => $post->title,
        'article_excerpt' => \Str::limit($post->content, 150),
        'article_author' => $post->owner->name ?? 'Admin',
        'brand_logo' => $branding->logo?->full_url ?? '',
        'brand_color_primary' => $branding->primary_color ?? '#000000',
        'brand_color_secondary' => $branding->secondary_color ?? '#ffffff',
        'website_url' => $branding->website?->url ?? '',
    ];

    $canvasJSON = $this->replaceTemplateVariables($template->canvas_json, $variables);

    // Step 4: Generate assets for each channel
    $createdAssets = [];
    foreach ($validated['channels'] as $channel) {
        // Save canvas_json to be rendered client-side
        // Later: Implement server-side rendering with node-canvas (optional)
        $asset = SocialAsset::create([
            'post_id' => $post->id,
            'channel' => $channel,
            'copy_text' => $post->title, // Default caption
            'canvas_json' => $canvasJSON, // Store for client-side rendering
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        $createdAssets[] = $asset;
    }

    return response()->json([
        'success' => true,
        'assets' => $createdAssets,
        'branding_name' => $branding->preset_name,
        'message' => "Assets created with '{$branding->preset_name}' branding. Complete rendering in Asset Library."
    ]);
}

private function replaceTemplateVariables($canvasJSON, array $variables)
{
    $jsonString = json_encode($canvasJSON);

    foreach ($variables as $key => $value) {
        $placeholder = '{' . $key . '}';
        $jsonString = str_replace($placeholder, $value, $jsonString);
    }

    return json_decode($jsonString, true);
}
```

**Task 1.7.2: Add canvas_json to SocialAsset Model**

- ✅ Check if `ane_social_assets` table has `canvas_json` column (already exists from Phase 1.1)
- ✅ Migration already created in Phase 1.1.3
- ✅ Add to SocialAsset model fillable: `'canvas_json'` (already done)
- ✅ Add cast: `'canvas_json' => 'array'` (already done)

**Task 1.7.3: Template::getDefaultNewsTemplate() Static Method**

- [ ] Add static method in Template model:
```php
public static function getDefaultNewsTemplate()
{
    return self::where('is_public', 1)
        ->where('is_default', 1)
        ->where('category', 'news')
        ->first();
}
```

**Task 1.7.3: Template::getDefaultNewsTemplate() Static Method**

✅ **ALREADY EXISTS** from Phase 1.2 (verified in app/Models/Template.php:110-115)

**Task 1.7.4: Update Asset Library to Render Canvas**

When user opens `/assets?post_id={id}`:
- [x] If `canvas_json` exists but `visual_path` is NULL → show "Render Required" badge
- [x] Add "Complete Rendering" button → open image editor with canvas_json pre-loaded
- [x] User can edit or directly save to generate visual_path

**Files Created:**
- (none)

**Files Modified:**
- ✅ `app/Http/Controllers/AmplifyController.php` (added branding_id validation, template logic, variable replacement)
- ✅ Uses existing `SocialAsset.canvas_json` field (already exists from Phase 1.1)
- ✅ Uses existing `Template.getDefaultNewsTemplate()` helper (already exists from Phase 1.2)
- ✅ `resources/views/assets/index.blade.php` (added render required badge, complete rendering button)

---

### Phase 2: Image Editor Enhancement 🔄 (Week 2)

#### Step 2.1: Template Tab in Sidebar ✅ **COMPLETED**
- [x] Add "Templates" as sub menu of /assets
- [x] Grid layout for template thumbnails (4 columns)
- [x] Separate sections: "Platform Templates" & "My Templates"
- [x] Click thumbnail → load template to canvas
- [x] Button: "Save Current as Template" (modal popup)

**Files Created:**
- `app/Http/Controllers/TemplateController.php`
- `resources/views/templates/index.blade.php`

**Files Modified:**
- `resources/views/layouts/dashboard.blade.php` (added Templates submenu)
- `routes/web.php` (added Route::resource('templates'))

#### Step 2.2: Template Load/Save Functions ✅ **COMPLETED**

**Tasks:**
- [x] Implement `loadTemplate()` function
- [x] Implement `saveAsTemplate()` with modal form
- [x] Add variable replacement logic for `{article_title}`, `{brand_color}`, etc.
- [x] Handle canvas resize when loading template
- [x] Show success/error notifications

**Files Modified:**
- `resources/views/image-editor/index.blade.php` (added Templates/Save Template buttons, modals, JavaScript functions)
- `app/Http/Controllers/Api/TemplateController.php` (updated index/show response format)

#### Step 2.3: Branding Integration ✅ **COMPLETED**
- [x] Add "Branding Presets" dropdown in toolbar
- [x] Load user's brandings from `ane_user_brandings`
- [x] Apply branding:
  - Replace `{brand_logo}` → User's logo from `ane_media`
  - Replace `{brand_color_primary}` → Primary color
  - Replace `{brand_color_secondary}` → Secondary color
  - Replace `{website_url}` in text objects

**Tasks:**
- [x] Fetch `/api/brandings` endpoint
- [x] Branding selector UI in toolbar
- [x] `applyBranding(brandingId)` function
- [x] Variable replacement in template JSON

**Files Created:**
- `app/Http/Controllers/Api/BrandingController.php`

**Files Modified:**
- `routes/api.php` (added GET /api/brandings route)
- `resources/views/image-editor/index.blade.php` (added branding dropdown, loadBrandings(), applySelectedBranding(), applyBrandingVariables())

---

### Phase 3: Asset Library Integration ✅ **COMPLETED**

#### Step 3.1: Replace Visual Editor ✅ **COMPLETED**
- [x] Update `/assets` view (`resources/views/assets/index.blade.php`)
- [x] Change "Edit" button → open Konva Image Editor
- [x] Decision: **Full-page editor** (redirect to `/image-editor?asset_id={id}`)
- [x] Pass `asset_id` to editor via URL parameter
- [x] Remove `<x-visual-editor>` component

**Files Modified:**
- `resources/views/assets/index.blade.php` (changed openVisualEditor → openImageEditor, added message listener)

#### Step 3.2: Load Asset to Editor ✅ **COMPLETED**
```javascript
function loadAsset(assetId) {
    currentAssetId = assetId;

    fetch(`/api/assets/${assetId}`)
        .then(res => res.json())
        .then(data => {
            const asset = data.asset;

            // Show Save Asset button, hide Save Template button
            document.getElementById('saveAssetBtn').classList.remove('hidden');
            document.getElementById('saveTemplateBtn').classList.add('hidden');

            // Load canvas JSON if exists
            if (asset.canvas_json) {
                loadCanvasFromJSON(asset.canvas_json);
            }
            // Otherwise load visual_path as background image
            else if (asset.visual_url) {
                loadImageToCanvas(asset.visual_url);
            }
        });
}
```

**Tasks:**
- [x] Create `GET /api/assets/{id}` endpoint
- [x] Load canvas_json OR visual_path to canvas
- [x] Store asset_id globally for saving
- [x] Toggle Save Asset button visibility
- [x] Auto-load on page load if asset_id parameter exists

#### Step 3.3: Save Edited Asset ✅ **COMPLETED**
```javascript
function saveAsset() {
    const canvasJson = stage.toJSON();
    const imageData = stage.toDataURL({ pixelRatio: 2 });

    fetch(`/api/assets/${currentAssetId}/update-visual`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '...',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            canvas_json: JSON.parse(canvasJson),
            image_data: imageData
        })
    }).then(res => res.json())
      .then(data => {
          showToast('Asset saved successfully');
          // Notify parent window if opened from asset library
          if (window.opener) {
              window.opener.postMessage({
                  type: 'asset-updated',
                  assetId: currentAssetId,
                  visualUrl: data.visual_url
              }, '*');
          }
      });
}
```

**Tasks:**
- [x] Create `POST /api/assets/{id}/update-visual` endpoint
- [x] Convert base64 to file → save to `storage/social-assets/`
- [x] Update both `canvas_json` AND `visual_path`
- [x] Return new image URL
- [x] Send postMessage to parent window for thumbnail refresh
- [x] Asset library listens for message and reloads grid

**Files Created:**
- `app/Http/Controllers/Api/AssetController.php`

**Files Modified:**
- `routes/api.php` (added GET /api/assets/{asset}, POST /api/assets/{asset}/update-visual)
- `resources/views/image-editor/index.blade.php` (added loadAsset(), saveAsset(), currentAssetId global, Save Asset button, auto-load logic)
- `resources/views/assets/index.blade.php` (added message event listener)

---

### Phase 4: Auto-Generate from Article ✅ **COMPLETED**

#### Step 4.1: Branding Selector Integration in Amplify Modal ✅
- [x] Add branding dropdown in amplify modal
- [x] Load brandings dynamically via API `/api/brandings`
- [x] Auto-select branding if only one exists
- [x] Validation: Require branding selection before amplify
- [x] Send `branding_id` to backend amplify endpoint

**Files Modified:**
- ✅ `resources/views/components/amplify-modal.blade.php`
  - Added branding selector dropdown (line 25-35)
  - Added `loadBrandings()` function (line 134-153)
  - Added branding validation in `amplify()` (line 167-170)
  - Send `branding_id` in request body (line 169)
- ✅ `app/Http/Controllers/AmplifyController.php` (already supported `branding_id` parameter)
- ✅ `routes/api.php`
  - Added `/api/brandings` endpoint with proper naming (`api.brandings.index`)
  - Fixed route name collision by prefixing API routes with `api.`

#### Step 4.2: API Routes Configuration ✅
- [x] Register API routes in `bootstrap/app.php`
- [x] Add 'web' middleware to `auth:sanctum` for session-based auth
- [x] Fix route name collisions:
  - `templates.index` → Web route only
  - `api.templates.index` → API route with prefix
  - Same pattern for brandings and assets

**Files Modified:**
- ✅ `bootstrap/app.php`
  - Added `api: __DIR__.'/../routes/api.php'` in routing configuration
- ✅ `routes/api.php`
  - Added `'web'` middleware to authenticated API routes
  - Renamed all API routes with `api.` prefix to avoid collision

#### Step 4.3: Authorization & Security ✅
- [x] Add `AuthorizesRequests` trait to `BrandingController`
- [x] Policy-based authorization for branding access
- [x] Backend validates branding ownership before amplify

**Files Modified:**
- ✅ `app/Http/Controllers/BrandingController.php`
  - Added `use AuthorizesRequests` trait
  - Fixed "Call to undefined method authorize()" error

#### Summary of Implementation:
**What Works Now:**
1. User clicks "Amplify" button on a post
2. Modal shows branding dropdown with user's brandings
3. User selects branding preset + social channels
4. Backend receives `branding_id` and uses it to:
   - Get branding preset (logo, colors, fonts)
   - Get default template from branding
   - Replace template variables with article data
   - Generate canvas JSON for each channel
   - Create social assets with proper branding

**Key Technical Achievements:**
- ✅ Branding integration in amplify flow
- ✅ API authentication with Sanctum + web session
- ✅ Route naming conventions (no collisions)
- ✅ Dynamic branding loading via fetch API
- ✅ Authorization with policies
- ✅ Multi-branding support (user can have multiple brandings per website)

#### Testing Checklist:
- [x] Amplify modal opens correctly
- [x] Brandings load via `/api/brandings` API
- [x] Branding dropdown shows user's brandings
- [x] Auto-select works if only 1 branding exists
- [x] Validation prevents amplify without branding selection
- [x] Backend receives correct `branding_id` parameter
- [x] AmplifyController validates branding ownership
- [x] Template variables replaced with article data
- [x] Social assets created with correct canvas JSON
- [x] No route name collisions (templates.index vs api.templates.index)
- [x] Authorization works (user can only use their own brandings)

#### Next Phase:
Phase 4 is complete. The amplify flow now supports:
- Multiple branding presets per website
- Dynamic branding selection in modal
- Secure API authentication
- Proper route naming conventions
- Full backend integration with template variable replacement

---

### Phase 5: Canvas Size Presets ✅ **COMPLETED**

#### Step 5.1: Canvas Resize Modal with Presets ✅
- [x] Resize button in sidebar
- [x] Modal with width/height inputs
- [x] Magic resize toggle (scale all objects)
- [x] Preset buttons for Instagram (Post, Story, Ad)
- [x] Preset buttons for Facebook (Landscape, Square, Cover)
- [x] Preset buttons for YouTube (Thumbnail, Channel, Short)
- [x] Apply resize function (with/without scaling)

**Files Modified:**
- `resources/views/image-editor/index.blade.php`
  - Added resize modal (line 876-983)
  - Added resize functions: openResizeModal(), closeResizeModal(), setPreset(), applyResize()
  - Magic resize scales objects proportionally

**Presets Implemented:**
- Instagram: 1080×1080 (Post), 1080×1920 (Story)
- Facebook: 1200×630 (Landscape), 1080×1080 (Square), 1200×1500 (Cover)
- YouTube: 1280×720 (Thumbnail), 2560×1440 (Channel), 1080×1920 (Short)

#### Step 5.2: Responsive Canvas - ✅ DONE
- [x] Fit canvas to viewport (max-height)
- [x] Zoom controls (+/-/fit)
- [x] Pan mode for large canvases (native Konva drag)

**Implementation Details:**
- Dynamic zoom calculation via `calculateOptimalZoom()` function
  - Max viewport: 800px height, 650px width
  - Auto-adjusts for any canvas size (Instagram Story, Facebook Cover, etc.)
- Zoom controls UI (bottom-right corner):
  - Zoom In (+10% per click, max 200%)
  - Zoom Out (-10% per click, min 10%)
  - Fit to Screen (reset to optimal)
  - Real-time zoom percentage display
- All hardcoded dimensions replaced with dynamic `stage.width()` / `stage.height()`
- Container auto-resizes on zoom change
- Pan mode: Built-in Konva drag when zoomed (no special mode needed)

**Files Modified:**
- `resources/views/image-editor/index.blade.php`
  - Added `calculateOptimalZoom()` function (line 1105-1119)
  - Added zoom controls UI (line 90-103)
  - Added zoom functions: `zoomIn()`, `zoomOut()`, `fitToScreen()`, `updateZoomDisplay()`
  - Updated `initKonva()` to use dynamic zoom
  - Updated `applyResize()` to recalculate zoom on canvas size change
  - Updated `loadTemplate()` to update zoom display
  - Fixed all hardcoded canvas dimensions:
    - Background rect: `stage.width()`, `stage.height()`
    - Image/text/shape positioning: `stage.width() / 2`, `stage.height() / 2`
    - Fit to page: Uses `stage.width()`, `stage.height()` with `Math.max()` for cover behavior

---

### Phase 6: Advanced Features 🚀 (Week 6+)

#### Step 6.1: Template Variables System - ✅ DONE
**Supported Variables:**
- `{article_title}` - Post title
- `{article_excerpt}` - Post excerpt (first 150 chars, stripped HTML)
- `{article_author}` - Author name
- `{featured_image}` - Post featured image URL
- `{brand_logo}` - User's logo from branding
- `{brand_color_primary}` - Primary brand color
- `{brand_color_secondary}` - Secondary brand color
- `{brand_font}` - Default font family
- `{website_url}` - Brand URL

**Implementation:**
- [x] Define variable syntax in template JSON (`{variable_name}`)
- [x] Parser function to replace variables (`parseTemplateVariables()`)
- [x] Validation: Warn if variable not found (console.warn)
- [x] Default values for missing variables

**Implementation Details:**
- Added `parseTemplateVariables()` function
  - Accepts value and variableData object
  - Merges provided data with default values
  - Replaces `{variable}` syntax with actual values
  - Returns placeholder `[variable]` if not found
  - Logs warning to console for debugging
- Updated `loadCanvasFromJSON()` to accept `variableData` parameter
  - Automatically parses all string attributes
  - Works with text content, colors, image paths
- Default values provided for all supported variables
- Template loading now passes sample/actual data

**Files Modified:**
- `resources/views/image-editor/index.blade.php`
  - Added `parseTemplateVariables()` (line ~2291)
  - Updated `loadCanvasFromJSON()` signature
  - Updated `loadTemplate()` to pass variable data

#### Step 6.2: Multi-Layer Management - ✅ DONE

**Features Implemented:**
- ✅ Layer panel in right sidebar "Layers" tab
- ✅ Layer list showing all canvas objects (images, text, shapes)
- ✅ Object type icons (image, text, shape)
- ✅ Object name display (truncated for long text)
- ✅ Click to select layer
- ✅ Drag & drop to reorder layers (z-index)
- ✅ Move up/down buttons with visual feedback
- ✅ **Visibility toggle** (eye icon) - show/hide layers
- ✅ **Lock toggle** (lock icon) - prevent editing/dragging
- ✅ Selected layer highlighting (purple background)
- ✅ Hidden layer opacity (50% in layer list)
- ✅ Disabled state for locked layers (no selection)

**Technical Implementation:**
- Function: `updateLayersList()` (line ~1971)
  - Filters objects (excludes background & transformer)
  - Generates layer items with all controls
  - Reverse display (top layer shown first)
  - Dynamic icon based on object type
  - State-aware styling (selected/visible/locked)

- Function: `toggleLayerVisibility(konvaIndex)` (line ~2189)
  - Toggle `obj.visible()` property
  - Hide transformer if selected object is hidden
  - Visual feedback with toast notification

- Function: `toggleLayerLock(konvaIndex)` (line ~2211)
  - Toggle `obj.draggable()` property
  - Remove transformer if locked object is selected
  - Prevents selection when locked
  - Visual feedback with toast notification

- Function: `selectObjectByIndex(konvaIndex)` (line ~2180)
  - Select layer from layer panel
  - Highlights in canvas with transformer

- Function: `moveLayerUp(konvaIndex)` (line ~2119)
  - Move object forward in Konva array (higher z-index)
  - Disabled state if already at top

- Function: `moveLayerDown(konvaIndex)` (line ~2137)
  - Move object backward in Konva array (lower z-index)
  - Disabled state if already at bottom

- Drag & Drop Handlers:
  - `handleLayerDragStart()` - Set dragged index, opacity feedback
  - `handleLayerDragOver()` - Visual drop zone with blue border
  - `handleLayerDragLeave()` - Remove drop zone border
  - `handleLayerDrop()` - Reorder objects in Konva
  - `handleLayerDragEnd()` - Cleanup visual feedback

**Layer Panel UI:**
```html
<!-- Layers Tab Header -->
<button onclick="switchTab('layers')" id="layersTab">Layers</button>

<!-- Layers Content -->
<div id="layersContent" class="hidden">
    <div id="layersList" class="space-y-1">
        <!-- Dynamic layer items with: -->
        <!-- - Drag handle (6 dots) -->
        <!-- - Type icon (image/text/shape) -->
        <!-- - Name (truncated) -->
        <!-- - Visibility toggle (eye icon) -->
        <!-- - Lock toggle (lock icon) -->
        <!-- - Move up/down arrows -->
    </div>
</div>
```

**Visual States:**
- Selected: Purple background, white text
- Normal: White background, gray text, gray border
- Hidden: 50% opacity on layer item
- Locked: Red lock icon
- Unlocked: Gray unlock icon
- Visible: Blue eye icon
- Hidden: Blue eye-slash icon

**Auto-Update Triggers:**
The `updateLayersList()` function is automatically called after:
- Adding new object (text, image, shape)
- Deleting object
- Moving layer up/down
- Reordering via drag & drop
- Toggling visibility
- Toggling lock
- Loading template
- Loading asset

**Files Modified:**
- `resources/views/image-editor/index.blade.php`
  - Layer panel UI (line ~345-353)
  - Layer item template with visibility/lock controls (line ~1982-2064)
  - Toggle visibility function (line ~2189-2208)
  - Toggle lock function (line ~2211-2235)

#### Step 6.3: Export Options - ✅ DONE

**Features Implemented:**
- ✅ Export Modal UI with modern design
- ✅ Format selector: PNG, JPG, WebP
- ✅ Quality selector: Low (0.5/1x), Medium (0.8/2x), High (1.0/3x)
- ✅ Download functionality with dynamic filename
- ✅ Save to media library with AJAX upload
- ✅ Batch export placeholder (future feature)

**Technical Implementation:**

**Export Modal UI** (line ~1046-1124):
- Format selection with 3 buttons (PNG default)
- Quality selection with 3 levels (Medium default)
- Export action buttons: Download & Save to Library
- Batch export button (placeholder for future)

**JavaScript Functions:**

1. `openExportModal()` - Shows export modal
2. `closeExportModal()` - Hides export modal
3. `selectExportFormat(format)` - Changes export format
4. `selectExportQuality(quality)` - Changes quality level
5. `performExport(action)` - Exports with selected settings
6. `saveToLibrary(dataURL)` - Uploads to media library
7. `openBatchExport()` - Placeholder for batch export

**Export Settings:**
```javascript
// Format options
png: 'image/png'  // Supports transparency
jpg: 'image/jpeg' // Smaller file size
webp: 'image/webp' // Modern format

// Quality levels
low: { quality: 0.5, pixelRatio: 1 }    // Fast, ~100KB
medium: { quality: 0.8, pixelRatio: 2 } // Balanced, ~300KB
high: { quality: 1.0, pixelRatio: 3 }   // Best quality, ~800KB
```

**User Flow:**
1. Click Export button → Modal opens
2. Select format (PNG/JPG/WebP)
3. Select quality (Low/Medium/High)
4. Choose: Download or Save to Library

**Files Modified:**
- `resources/views/image-editor/index.blade.php`
  - Export modal UI (line ~1046-1124)
  - Export functions (line ~2318-2451)

#### Step 6.4: Image Constraint System - ✅ DONE

**Purpose**: Auto-sizing images (featured image, logo) saat amplify dengan constraint yang bisa diedit manual sesudahnya.

**Constraint Types:**
1. **`fill`** - Cover mode (scale to fill area, allow cropping)
   - Use case: Featured image yang harus mengisi area tertentu (1080x800px)
   - Behavior: Scale dengan `Math.max(scaleX, scaleY)` untuk cover penuh
   - User dapat drag untuk reposition (crop effect)

2. **`fit`** - Contain mode (fit width, maintain aspect ratio)
   - Use case: Logo yang harus fit width tertentu (140px)
   - Behavior: Scale dengan width constraint, height auto-calculate
   - User dapat drag untuk reposition

**Template JSON Structure:**
```json
{
  "className": "Image",
  "attrs": {
    "x": 0,
    "y": 280,
    "width": 1080,
    "height": 800,
    "image": "{featured_image}",
    "isVariable": true,
    "variableName": "featured_image",
    "initialConstraint": {
      "type": "fill",
      "width": 1080,
      "height": 800,
      "allowReposition": true
    }
  }
}
```

**Implementation:**
- **Backend (AmplifyController)**: Tidak perlu modifikasi, constraint hanya metadata
- **Frontend (Image Editor)**:
  - `applyImageConstraint()` - Calculate scale based on constraint type
  - `loadCanvasFromJSON()` - Detect `initialConstraint` and apply auto-sizing
  - After applied, constraint metadata removed (user bebas edit)

**User Flow:**
1. User amplify article → Template dengan constraint di-load
2. Featured image auto-scale to fill 1080x800px (cover mode)
3. Logo auto-scale to fit width 140px (aspect ratio maintained)
4. User buka image editor → Bisa drag, resize, transform bebas (no enforcement)
5. Constraint hanya untuk initial placement, bukan lock permanent

**Key Functions:**
- `applyImageConstraint(imageObj, attrs, constraint)` - Apply constraint logic
  - `type: 'fill'` → Math.max scale (cover)
  - `type: 'fit'` → Width-based scale (contain)
  - Returns Konva.Image with calculated dimensions
  - Removes `initialConstraint` metadata setelah apply

**Example Use Cases:**
- News template: Featured image fill 1080x800px, logo fit 140px width
- Event poster: Hero image fill full canvas, sponsor logos fit 200px width
- Quote template: Background image fill, author avatar fit 100px width

**Files Modified:**
- `resources/views/image-editor/index.blade.php`
  - Added `applyImageConstraint()` function (line ~2690-2742)
  - Updated `loadCanvasFromJSON()` to detect and apply constraints (line ~2770-2780)
  - Example template created: `example-template-with-constraints.json`

**Testing:**
- ✅ Fill constraint for featured_image (1080x800px area)
- ✅ Fit constraint for brand_logo (140px width, auto-height)
- ✅ User dapat drag untuk reposition after load
- ✅ Constraint tidak tersimpan kembali saat save template

---

#### Step 6.5: Template Categories & Search
- [ ] Filter templates by category (quote/article/news/promo)
- [ ] Search templates by name
- [ ] Sort: Popular, Recent, Alphabetical
- [ ] Tag system for templates

#### Step 6.5: Collaboration Features
- [ ] Share template with team (if multi-tenant)
- [ ] Template marketplace (future: sell templates)
- [ ] Import/Export template JSON (backup)

---

## Technical Stack

### Frontend
- **Canvas Library**: Konva.js (already implemented)
- **Color Picker**: Pickr.js (already implemented)
- **UI Framework**: Tailwind CSS 4
- **Icons**: Heroicons (SVG)
- **AJAX**: Fetch API

### Backend
- **Framework**: Laravel 12
- **Database**: MySQL
- **Storage**: Laravel Storage (local/S3)
- **Image Processing**: GD/Imagick (for server-side rendering - optional)

### Optional Enhancements
- **Node Canvas**: Server-side image generation (if needed)
- **Queue Jobs**: Background processing for batch generation
- **Redis**: Cache templates for performance

---

## File Structure

```
app/
├── Http/Controllers/
│   ├── Api/
│   │   ├── TemplateController.php (NEW)
│   │   └── SocialAssetController.php (NEW)
│   └── AssetLibraryController.php (MODIFY)
├── Models/
│   ├── Template.php (NEW)
│   └── SocialAsset.php (EXISTING)
├── Policies/
│   └── TemplatePolicy.php (NEW)

database/
├── migrations/
│   ├── YYYY_MM_DD_create_templates_table.php (NEW)
│   ├── YYYY_MM_DD_add_default_template_to_brandings.php (NEW)
│   └── YYYY_MM_DD_add_canvas_json_to_social_assets.php (NEW)
└── seeders/
    └── TemplateSeeder.php (NEW)

resources/
├── views/
│   ├── image-editor/
│   │   └── index.blade.php (MODIFY - add Templates tab)
│   ├── assets/
│   │   └── index.blade.php (MODIFY - add render button for canvas_json)
│   ├── brandings/
│   │   ├── create.blade.php (MODIFY - add template selector)
│   │   └── edit.blade.php (MODIFY - add template selector)
│   ├── posts/
│   │   └── index.blade.php (NO CHANGES - amplify already exists)
│   └── components/
│       └── amplify-modal.blade.php (NO CHANGES - already perfect)

routes/
├── web.php (MODIFY)
└── api.php (NEW - template & asset endpoints)

storage/
├── app/
│   ├── templates/ (NEW - platform default templates)
│   └── social-assets/ (EXISTING - user-generated images)
└── templates/ (NEW - template thumbnails)
```

---

## Development Checklist

### Phase 1: Template System Foundation
- [ ] Create `ane_templates` migration
- [ ] Create `Template` model with fillable, relationships
- [ ] Create `TemplatePolicy` (view, create, update, delete)
- [ ] Register policy in `AuthServiceProvider`
- [ ] Create `TemplateSeeder` with 3-5 default templates
- [ ] Run migration & seeder
- [ ] Test: Create user template via Tinker
- [ ] Test: Query templates (public + user's own)

### Phase 2: Template API
- [ ] Create `Api/TemplateController`
- [ ] Route: `GET /api/templates` (index with filters)
- [ ] Route: `GET /api/templates/{id}` (show)
- [ ] Route: `POST /api/templates` (store)
- [ ] Route: `DELETE /api/templates/{id}` (destroy)
- [ ] Route: `POST /api/templates/{id}/duplicate` (duplicate platform template)
- [ ] Validation rules for canvas_json (valid JSON)
- [ ] Test all endpoints with Postman/Insomnia

### Phase 3: Image Editor - Templates Tab
- [ ] Add "Templates" button in sidebar
- [ ] Create template grid layout (HTML/CSS)
- [ ] Fetch templates via AJAX on tab open
- [ ] Render thumbnails in grid
- [ ] Implement `loadTemplate(id)` function
- [ ] Clear canvas before loading template
- [ ] Parse Konva JSON and add objects to stage
- [ ] Test: Load platform template
- [ ] Test: Load user template

### Phase 4: Save as Template Feature
- [ ] Add "Save as Template" button in toolbar
- [ ] Create modal form (name, category inputs)
- [ ] Implement `saveAsTemplate()` function
- [ ] Generate thumbnail: `stage.toDataURL({ pixelRatio: 0.2 })`
- [ ] Get canvas JSON: `stage.toJSON()`
- [ ] Send to `POST /api/templates`
- [ ] Backend: Convert base64 thumbnail to file
- [ ] Save to `storage/templates/thumbnails/`
- [ ] Create database record
- [ ] Refresh templates list after save
- [ ] Show success notification

### Phase 5: Branding Integration
- [ ] Fetch user brandings: `GET /api/brandings`
- [ ] Add branding dropdown in toolbar
- [ ] Implement `applyBranding(brandingId)` function
- [ ] Replace `{brand_logo}` with image object
- [ ] Replace `{brand_color_primary}` in shapes
- [ ] Replace `{brand_color_secondary}` in shapes
- [ ] Add `{website_url}` as text object
- [ ] Test with multiple brandings

### Phase 6: Asset Library Integration
- [ ] Modify `/assets` view: Change "Edit" button onclick
- [ ] Create modal for Image Editor (or use full page)
- [ ] Pass `asset_id` to editor
- [ ] Create `GET /api/assets/{id}` endpoint
- [ ] Load asset data to editor
- [ ] If `visual_path` exists, load image to canvas
- [ ] Implement `saveAsset()` function
- [ ] Create `POST /api/assets/{id}/update-visual` endpoint
- [ ] Backend: Save base64 image to storage
- [ ] Update `ane_social_assets.visual_path`
- [ ] Return new image URL
- [ ] Update thumbnail in asset grid (AJAX)

### Phase 7: Auto-Generate from Article
- [ ] Add "Create Social Media Post" button in `/posts`
- [ ] Create modal with template selector
- [ ] Create `GET /api/posts/{id}` endpoint (if not exists)
- [ ] Implement template preview with article data
- [ ] Variable replacement: `{article_title}`, etc.
- [ ] Render preview in modal
- [ ] Create `POST /api/social-assets/generate` endpoint
- [ ] Backend: Replace variables in template JSON
- [ ] Return canvas_json to client
- [ ] Client: Render canvas → `toDataURL()` → upload
- [ ] Create `POST /api/social-assets/upload` endpoint
- [ ] Save image to storage
- [ ] Create `SocialAsset` record
- [ ] Redirect to `/assets`

### Phase 8: Canvas Presets
- [ ] Define `CANVAS_PRESETS` object
- [ ] Add preset selector dropdown in toolbar
- [ ] Implement `setCanvasSize(preset)` function
- [ ] Resize stage and background layer
- [ ] Update dimension display in UI
- [ ] Store canvas size in template
- [ ] Auto-select preset when opening from asset (based on channel)

### Phase 9: Testing & Refinement
- [ ] Test full flow: Article → Generate → Edit → Save
- [ ] Test template creation & reuse
- [ ] Test branding application
- [ ] Test on different screen sizes (responsive)
- [ ] Performance test: Load 50+ templates
- [ ] Edge cases: Empty template, missing variables
- [ ] Error handling: Upload failed, JSON parse error
- [ ] User feedback: Loading states, success/error messages

### Phase 10: Documentation
- [ ] Update README.md with Social Media Amplify feature
- [ ] Document template JSON format
- [ ] Document variable syntax
- [ ] Create user guide (how to create template)
- [ ] API documentation for endpoints

---

## Template JSON Format

### Example: Quote Minimal Template

```json
{
  "attrs": {
    "width": 1080,
    "height": 1080
  },
  "className": "Stage",
  "children": [
    {
      "attrs": {},
      "className": "Layer",
      "children": [
        {
          "attrs": {
            "x": 0,
            "y": 0,
            "width": 1080,
            "height": 1080,
            "fill": "{brand_color_primary}"
          },
          "className": "Rect"
        },
        {
          "attrs": {
            "x": 540,
            "y": 400,
            "text": "{article_title}",
            "fontSize": 48,
            "fontFamily": "Arial",
            "fill": "#FFFFFF",
            "align": "center",
            "width": 900
          },
          "className": "Text"
        },
        {
          "attrs": {
            "x": 540,
            "y": 600,
            "text": "{article_author}",
            "fontSize": 24,
            "fontFamily": "Arial",
            "fill": "#CCCCCC",
            "align": "center"
          },
          "className": "Text"
        },
        {
          "attrs": {
            "x": 90,
            "y": 90,
            "image": "{brand_logo}",
            "width": 100,
            "height": 100
          },
          "className": "Image"
        }
      ]
    }
  ]
}
```

### Variable Syntax

- **Text variables**: `{variable_name}` - Replaced with string value
- **Color variables**: `{brand_color_primary}` - Replaced with hex color
- **Image variables**: `{brand_logo}` - Replaced with image URL, loaded to Image object

### Variable Replacement Logic (JavaScript)

```javascript
function replaceVariables(canvasJSON, data) {
    const jsonString = JSON.stringify(canvasJSON);
    let replaced = jsonString;

    // Replace text variables
    Object.keys(data).forEach(key => {
        const placeholder = `{${key}}`;
        replaced = replaced.replaceAll(placeholder, data[key]);
    });

    return JSON.parse(replaced);
}

// Usage
const data = {
    article_title: "10 Tips for Better Design",
    article_author: "John Doe",
    brand_color_primary: "#667eea",
    brand_logo: "/storage/media/logo.png"
};

const processedJSON = replaceVariables(templateJSON, data);
```

---

## Performance Considerations

### Template Loading
- **Cache templates**: Store in memory after first load
- **Lazy load thumbnails**: Only load visible templates (intersection observer)
- **Paginate templates**: Load 20 templates per page

### Canvas Rendering
- **Debounce events**: Limit redraw frequency on drag/resize
- **Layer caching**: Enable `cache: true` for static objects
- **Limit history**: Keep max 50 undo/redo steps

### Image Upload
- **Compress images**: Client-side compression before upload
- **Max file size**: 5MB per image
- **Optimize thumbnails**: Generate at 300x300px max

---

## Security Considerations

### Template Access Control
- **Policy enforcement**: Use `TemplatePolicy` for all actions
- **SQL injection**: Use Eloquent ORM, avoid raw queries
- **XSS prevention**: Sanitize template names (no HTML tags)

### Image Upload
- **File validation**: Check MIME type (only image/png, image/jpeg)
- **File size limit**: Max 5MB
- **Storage path**: Use Laravel Storage with visibility control
- **Filename sanitization**: Generate unique filenames (UUID)

### API Endpoints
- **Authentication**: Require `auth:sanctum` middleware
- **Rate limiting**: 60 requests per minute per user
- **CSRF protection**: Include CSRF token in POST requests
- **Input validation**: Use Form Requests for complex validation

---

## Future Enhancements

### Advanced Template Features
- [ ] Animation support (GIF export)
- [ ] Video templates (MP4 export for stories)
- [ ] Interactive templates (clickable areas)
- [ ] AI-generated templates based on article content

### Collaboration
- [ ] Team templates (shared within organization)
- [ ] Template versioning (track changes)
- [ ] Comments on templates
- [ ] Approval workflow for templates

### Analytics
- [ ] Track template usage (most popular)
- [ ] A/B testing for templates
- [ ] Performance metrics (engagement rate)

### Integrations
- [ ] Direct publish to social media (Facebook Graph API, Twitter API)
- [ ] Canva integration (import Canva designs)
- [ ] Unsplash/Pexels API (stock photos)
- [ ] Google Fonts API (more font options)

---

## Support & Troubleshooting

### Common Issues

**Issue: Template tidak muncul di list**
- Check: `is_public = 1` OR `created_by = auth()->id()`
- Check: Template seeder sudah run?
- Check: API endpoint return data?

**Issue: Canvas tidak resize saat load template**
- Check: `stage.width()` dan `stage.height()` sudah di-set?
- Check: Container div sudah di-resize?

**Issue: Variable tidak ke-replace**
- Check: Variable syntax benar? `{variable_name}`
- Check: Data object key match dengan placeholder?

**Issue: Thumbnail tidak muncul**
- Check: Thumbnail path exists di storage?
- Check: File readable (permissions)?
- Check: URL di `asset()` helper benar?

**Issue: Save template gagal**
- Check: Validation error di response?
- Check: `canvas_json` valid JSON?
- Check: Storage writable?

---

## Changelog

### Version 0.1.0 (2025-12-26) - Initial Architecture
- ✅ Initial architecture document created
- ✅ Database schema designed (ane_templates + modifications)
- ✅ Implementation roadmap structured (7 phases)
- ✅ Technical stack defined
- ✅ User flow documented (Flow 0, 1, 2, 3)
- ✅ Branding integration strategy defined
- ✅ Amplify backend logic designed

**Key Decisions Made:**
- Template stored in **database** (not JSON files) for flexibility
- Template selector in **Branding** (not in Amplify modal)
- User amplify → Auto-use template from branding preset
- Canvas JSON stored in `ane_social_assets` for client-side rendering
- No changes needed to existing amplify modal (already perfect)

### Version 0.2.0 (2025-12-26) - Phase 1 Complete: Template System Foundation
- ✅ Phase 1.1-1.4: Template System Foundation COMPLETED
- ✅ Phase 1.5: Template API Endpoints COMPLETED
- ✅ Phase 1.6: Branding Integration (template selector) COMPLETED
- ✅ Phase 1.7: Amplify Backend Logic COMPLETED
- ✅ 3 default platform templates created
- ✅ Template CRUD API endpoints functional
- ✅ Amplify now uses branding + template system

**Phase 1.5 Deliverables:**
- ✅ Migration: Add template_type ENUM field to ane_templates
- ✅ Model: Template scopes (articleTemplates, standaloneTemplates)
- ✅ Controller: TemplateController API with filters
- ✅ Routes: API resource routes + duplicate/applyBranding
- ✅ Documentation updated

**Phase 1.6 Deliverables:**
- ✅ BrandingController: Load article templates in create/edit
- ✅ BrandingController: Validate & save default_template_id
- ✅ Views: Template selector added to brandings/create.blade.php
- ✅ Views: Template selector added to brandings/edit.blade.php
- ✅ Template preview (thumbnail) on selection via AJAX
- ✅ Auto-load preview on edit page if template selected

**Phase 1.7 Deliverables:**
- ✅ AmplifyController: Added branding_id validation
- ✅ AmplifyController: Get template from branding or fallback to default
- ✅ AmplifyController: Variable replacement (branding + article variables)
- ✅ AmplifyController: Save canvas_json to SocialAsset
- ✅ Helper method: replaceVariables() for template processing

### Version 0.3.0 (Planned) - Phase 2 Implementation
- [ ] Image Editor Templates tab
- [ ] Load/Save template functions
- [ ] Template variable replacement system
- [ ] Asset Library render workflow

### Version 1.0.0 (Target) - Production Ready
- [ ] All 6 phases completed
- [ ] Full feature set operational
- [ ] User documentation complete
- [ ] Performance optimized

---

## Quick Start Guide (For Developers)

### Step 1: Run Migrations
```bash
php artisan make:migration create_templates_table
php artisan make:migration add_default_template_to_brandings
php artisan make:migration add_canvas_json_to_social_assets
php artisan migrate
```

### Step 2: Create Models & Policies
```bash
php artisan make:model Template
php artisan make:policy TemplatePolicy --model=Template
# Update UserBranding model manually
```

### Step 3: Create Seeder
```bash
php artisan make:seeder TemplateSeeder
# Create 3-5 default templates with Konva JSON
php artisan db:seed --class=TemplateSeeder
```

### Step 4: Create API Controller
```bash
php artisan make:controller Api/TemplateController --api
# Implement index, show, store, update, destroy, duplicate
```

### Step 5: Update Branding Forms
- Add template selector dropdown
- Load templates via controller
- Update validation rules

### Step 6: Update Amplify Logic
- Implement `PostController@amplify`
- Add variable replacement logic
- Store canvas_json in SocialAsset

### Step 7: Test Full Flow
1. Create branding preset → Select template
2. Amplify article → Check canvas_json created
3. Open Asset Library → See "Render Required" badge
4. Complete rendering → Visual generated

---

## Critical Files to Create/Modify

### NEW Files (7 files):
1. ✅ `database/migrations/2025_12_25_231829_create_templates_table.php`
2. ✅ `database/migrations/2025_12_25_231830_add_default_template_to_brandings.php`
3. ✅ `database/migrations/2025_12_25_231830_add_canvas_json_to_social_assets.php`
4. ✅ `database/seeders/TemplateSeeder.php`
5. ✅ `app/Models/Template.php`
6. ✅ `app/Policies/TemplatePolicy.php`
7. ⏳ `app/Http/Controllers/Api/TemplateController.php` - **TODO**

### MODIFY Files (6 files):
1. ✅ `app/Models/UserBranding.php` - Add defaultTemplate relationship
2. ✅ `app/Models/SocialAsset.php` - Add canvas_json field
3. ⏳ `app/Http/Controllers/PostController.php` - Add amplify() method - **TODO**
4. ⏳ `app/Http/Controllers/BrandingController.php` - Load templates in create/edit - **TODO**
5. ⏳ `resources/views/brandings/create.blade.php` - Add template selector - **TODO**
6. ⏳ `resources/views/brandings/edit.blade.php` - Add template selector - **TODO**

### REQUIRES CHANGES (was: NO CHANGES NEEDED):
1. ✅ `resources/views/posts/index.blade.php` - Amplify button already exists
2. ⏳ `resources/views/components/amplify-modal.blade.php` - Add branding selector dropdown - **TODO**

---

## Summary

**Goal**: Enable users to amplify articles to social media with automated visual generation using templates.

**How It Works**:
1. User sets up **Branding Preset** → Links website + template
2. User clicks **Amplify** on article → System auto-generates canvas JSON using template
3. User opens **Asset Library** → Completes rendering or edits in image editor
4. User **publishes** to social media

**Key Benefits**:
- **No template selection** needed during amplify (1-click workflow)
- **Consistent branding** via preset templates
- **Flexible editing** in image editor after generation
- **Reusable templates** (platform + user-created)

---

---

## Changelog

### v0.5.0 - 2025-12-26 16:00 WIB
**Phase 6 COMPLETED** ✅

**Template Categories System:**
- ✅ Created `ane_template_categories` table (id, name, slug, icon, sort_order, is_active)
- ✅ Migration: Changed `ane_templates.category` from ENUM to `category_id` foreign key
- ✅ Foreign key constraint: `category_id` → `ane_template_categories.id` ON DELETE SET NULL
- ✅ Created `TemplateCategory` model with `templates()` relationship and `active()` scope
- ✅ Updated `Template` model: added `category()` relationship, changed fillable from 'category' to 'category_id'

**Admin Management:**
- ✅ Created `Admin\TemplateCategoryController` with full CRUD
- ✅ Routes: `/admin/template-categories` (index, store, destroy)
- ✅ Validation: Cannot delete category with existing templates
- ✅ Created admin views for category management (index, create, edit)

**API Endpoints:**
- ✅ Created `Api\TemplateCategoryController`
- ✅ `GET /api/template-categories` - List active categories
- ✅ `POST /api/template-categories` - Create category (admin only)
- ✅ Both endpoints require `auth:sanctum` + 'web' middleware

**Image Editor Integration:**
- ✅ Quick add category feature in save template modal (admin only)
- ✅ Category dropdown in save modal with dynamic loading
- ✅ Quick add modal with AJAX form submission
- ✅ Auto-reload category dropdown after adding new category
- ✅ Auto-select newly created category after quick add
- ✅ Permission check: Regular users see no category fields, admins see all controls

**Permission System:**
- ✅ Regular users: Save templates WITHOUT category (always private)
- ✅ Super admin: Can assign category + make templates public
- ✅ TemplateController@store: Role-based category assignment logic

**Default Data:**
- ✅ Created `TemplateCategorySeeder` with 6 default categories:
  - Social Media (icon: share)
  - Marketing (icon: megaphone)
  - Business (icon: briefcase)
  - Events (icon: calendar)
  - Education (icon: book)
  - Tech (icon: cpu)

**UX Improvements:**
- ✅ Fixed image editor deselect on background click
  - Changed background `listening: false` → `listening: true`
  - Updated stage click handler to check for background
  - Added `layer.batchDraw()` for visual refresh

**Documentation:**
- ✅ Updated database schema section with new tables
- ✅ Updated template categories & types section
- ✅ Fixed all excerpt length references (150 chars consistently)
- ✅ Added missing variables (`featured_image`, `brand_font`) to documentation
- ✅ Updated Flow 1 to match actual AmplifyController implementation
- ✅ Added Phase 6 completion details

**Files Created:**
- `database/migrations/2025_12_26_073804_create_template_categories_table.php`
- `database/migrations/2025_12_26_073825_modify_templates_table_for_categories.php`
- `database/seeders/TemplateCategorySeeder.php`
- `app/Models/TemplateCategory.php`
- `app/Http/Controllers/Admin/TemplateCategoryController.php`
- `app/Http/Controllers/Api/TemplateCategoryController.php`
- `resources/views/admin/template-categories/index.blade.php`
- `resources/views/admin/template-categories/create.blade.php`
- `resources/views/admin/template-categories/edit.blade.php`

**Files Modified:**
- `app/Models/Template.php` (changed 'category' to 'category_id', added relationship)
- `app/Http/Controllers/TemplateController.php` (added role-based category logic)
- `resources/views/image-editor/index.blade.php` (quick add category, deselect fix)
- `resources/views/templates/index.blade.php` (group by category display)
- `routes/web.php` (added admin category routes)
- `routes/api.php` (added API category routes)
- `04-social-media-amplify-architecture.md` (comprehensive documentation update)

**Next Phase:**
Phase 7: Advanced Features (scheduled publishing, AI suggestions, batch processing)

---

### v0.4.0 - 2025-12-26 07:15 WIB
**Phase 1.5 COMPLETED** ✅

**Database Updates:**
- ✅ Added `template_type` field to `ane_templates`
- ✅ Migration: `2025_12_26_000417_add_template_type_to_templates.php`
- ✅ Updated all existing templates to 'article' type

**Template Model:**
- ✅ Added `template_type` to fillable
- ✅ Added scopes: `articleTemplates()`, `standaloneTemplates()`

**API Endpoints:**
- ✅ Created `Api\TemplateController` with full CRUD
- ✅ Endpoints: index, show, store, update, destroy, duplicate, applyBranding
- ✅ Filters: category, template_type, search
- ✅ Auth: `auth:sanctum` middleware
- ✅ Policies: TemplatePolicy enforced

**Routes:**
- ✅ `GET /api/templates` - List all accessible templates
- ✅ `GET /api/templates/{id}` - Get template detail
- ✅ `POST /api/templates` - Create new template
- ✅ `PUT /api/templates/{id}` - Update template
- ✅ `DELETE /api/templates/{id}` - Delete template
- ✅ `POST /api/templates/{id}/duplicate` - Duplicate template
- ✅ `POST /api/templates/{id}/apply-branding` - Apply branding variables

**Next:**
- Phase 1.6: Branding Integration
- Phase 1.7: Amplify Backend Logic

---

### v0.3.0 - 2025-12-26 07:00 WIB
**Architecture Revision: Template-Driven Editor** 🎨

**Major Conceptual Changes:**
- ✅ **Branding is Universal** - Branding bukan hanya untuk article, tapi untuk SEMUA template
- ✅ **Template Type System** - Added `template_type` field (article vs standalone)
- ✅ **Category Expansion** - Extended categories: news, event, promo, announcement, registration, product, quote, custom
- ✅ **Variable System Clarified** - Branding variables (always) vs Article variables (conditional)
- ✅ **Load Branding Feature** - User bisa load branding di editor untuk replace logo/colors

**New User Flows:**
- ✅ Flow 2: Create Standalone Content (event, promo, announcement)
- ✅ Flow 3: Edit Asset di Asset Library
- ✅ Flow 4: Create Template (custom template with variable marking)

**Documentation Added:**
- ✅ Variable System & Branding Integration section
- ✅ Variable marking examples (JSON structure)
- ✅ Backend helper function `applyBrandingToTemplate()`
- ✅ Template Categories & Types explanation
- ✅ Usage scenarios (amplify vs editor load)

**Database Schema Updates:**
- ⏳ **TODO**: Add `template_type` field to `ane_templates` table
- ⏳ **TODO**: Update category ENUM with new values

**Next Steps:**
- Add `template_type` migration
- Update Template model with new scopes
- Update TemplateSeeder with template_type
- Continue Phase 1.5 implementation

---

### v0.2.0 - 2025-12-26 06:30 WIB
**Phase 1.1 - 1.4 COMPLETED** ✅

**Database & Models:**
- ✅ Created `ane_templates` table with full schema
- ✅ Added `default_template_id` to `ane_user_brandings`
- ✅ Added `canvas_json` to `ane_social_assets`
- ✅ Created `Template` model with scopes, relationships, helpers
- ✅ Updated `UserBranding` model with `defaultTemplate()` relationship
- ✅ Updated `SocialAsset` model with `canvas_json` field

**Authorization:**
- ✅ Created `TemplatePolicy` with view/create/update/delete logic
- ✅ Public templates accessible by all, user templates owner-only

**Default Templates:**
- ✅ Created `TemplateSeeder` with 3 templates:
  - News Article Default (is_default, category: news)
  - Blog Article Default (is_default, category: article)
  - Quote Post (category: quote)
- ✅ All templates use Konva JSON format with variable placeholders
- ✅ Seeder executed successfully - 3 templates in database

**Template Variables:**
- Supported: `{brand_logo}`, `{brand_color_primary}`, `{brand_color_secondary}`, `{article_title}`, `{article_excerpt}`, `{article_author}`, `{website_url}`

**Next Steps:**
- Phase 1.5: Template API Endpoints (TemplateController)
- Phase 1.6: Branding Integration (update forms)
- Phase 1.7: Amplify Backend Logic (PostController@amplify)

---

### v0.1.0 - 2025-12-26 (Initial)
**Architecture Designed**
- Documented user flows (0-3)
- Defined database schema
- Created implementation roadmap (Phase 1-6)
- Clarified multiple brandings per website strategy

---

**Last Updated**: 2025-12-26 16:00 WIB
**Status**: ✅ Phase 6 COMPLETED - Dynamic Template Categories System
**Next Phase**: Phase 7 - Advanced Features (Scheduled Publishing, AI Suggestions, Batch Processing)
