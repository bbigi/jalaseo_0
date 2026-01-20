# aneWP Content Distribution Blueprint

## Goal
Make users feel: **"konten gue hidup di mana-mana."**

aneWP bukan sekadar SEO tool. aneWP adalah **Content Growth Engine**:
**1 artikel → jadi banyak aset → tersebar multi-channel → ada feedback loop → grow.**

---

## Core Concept
### Source → Assets → Channels → Feedback
- **Source**: artikel berita (single source of truth)
- **Assets**: *single primary post* + opsi turunan ringan
- **Channels**: IG / FB / X / LinkedIn
- **Feedback**: performa balik ke aneWP untuk rekomendasi tindakan berikutnya

> Untuk **news**, default-nya adalah **1 artikel = 1 post utama per channel**. Turunan (story, thread) bersifat opsional & kontekstual, bukan wajib.

> Distribusi bukan "share link", tapi **repurpose engine**.

---

## Content DNA (wajib untuk tiap artikel)
Setiap artikel yang masuk Content Studio dipecah jadi "DNA" agar bisa dipakai ulang tanpa nulis dari nol.

**Fields (disimpan sebagai JSON / DB):**
- `headline` (H1)
- `subheadline` (1 kalimat ringkas)
- `key_points[]` (3 poin utama)
- `hot_take` (angle/opini ringan)
- `cta` (ajak komentar / simpan / baca)
- `keywords[]` (10 kandidat keyword/hashtag)
- `summary_short` (≤ 140 chars)
- `summary_medium` (≤ 300 chars)
- `summary_long` (≤ 700 chars)
- `tone` (neutral / informative / urgent / inspiring)
- `intent` (breaking / evergreen / refresh)

---

## Asset Factory (output minimal dari 1 artikel berita)

### A) Copy Assets (News-first)
Default **1 artikel = 1 post per channel**:
1) **IG Feed Caption** (headline + konteks singkat)
2) **FB Page Post** (ringkas + informatif)
3) **X Post** (breaking / straight news)
4) **LinkedIn Post** (impact & relevance)

**Opsional (jika relevan):**
- X Thread (klarifikasi / kronologi)
- IG Story (breaking alert)

### B) Visual Assets (template-driven, simple)
Untuk news, fokus **satu visual utama**.

Ukuran terkini yang diprioritaskan:
- **IG Feed Portrait 4:5** (1080×1350) — *primary*
- **IG Story 9:16** (1080×1920) — *optional breaking*

> Ukuran 1:1 tidak diprioritaskan untuk news, hanya fallback jika diperlukan.
Minimal 3 ukuran, setiap artikel punya versi:
- **IG Feed 1:1** (1080×1080)
- **IG Portrait 4:5** (1080×1350)
- **Story 9:16** (1080×1920)

**Template style:** flat / clean / typography strong, konsisten brand.

---

## Lightweight Visual Editor (Simple & Mandatory)
Editor **SUDAH termasuk** di blueprint ini dan bersifat **wajib**.

Editor ini adalah **last-mile tweak** sebelum publish (bukan design tool).

**Bisa:**
- Edit text (headline, subheadline)
- Replace image (featured image / upload)
- Theme color (limited, preset)

**Tidak:**
- Drag bebas semua layer
- Upload font bebas
- Efek visual kompleks

**Konsep teknis:** Fabric.js (Canvas API) + template JSON → render PNG.

> Target edit time: **≤ 30 detik per post**.

---

## Distribution Plan: "Sebar luas" yang bener

### Channel Map (per artikel)
Minimum **2 channel utama** per artikel (misal IG + FB), lainnya opsional.

Checklist channel:
- Instagram (Feed + Story)
- Facebook Page (post)
- X (post / thread)
- LinkedIn (post)
- (opsional) Email digest
- (opsional) WA/Telegram broadcast

### Format Map (adaptasi per channel)
- **IG**: visual-heavy (template) + hook singkat
- **FB**: naratif + ajak diskusi
- **X**: hot take + thread pendek
- **LinkedIn**: insight + impact profesional

> Prinsip: **adaptasi format**, bukan copy-paste.

---

## Scheduling Blueprint (News Context)

Untuk **news**, distribusi bersifat **immediate**, bukan spread 7 hari.

### Default rule:
- Artikel publish → generate asset → **publish hari yang sama**

### Optional follow-up (jika berita penting):
- H+1: klarifikasi / update (X / FB)
- H+2: konteks / dampak (LinkedIn)

> Evergreen & feature content baru pakai multi-day distribution. News **tidak dipaksa** masuk calendar panjang.

---

## Smart Distribution Rules (v1)
Rules ini bikin aneWP terasa "pinter" bahkan sebelum autopublish.

- Jika `intent=breaking` → schedule immediate untuk 1–2 channel utama
- Jika `intent=evergreen` → schedule jam optimal (ditentukan per channel)
- Jika keyword intent `transactional` → CTA lebih kuat
- Jika keyword intent `informational` → tone edukasi + CTA save/share
- Jika perform bagus → generate ulang format kedua (carousel/thread) dan jadwalkan ulang
- Jika perform jelek → ganti hook + visual → re-run distribution

---

## Technical Architecture

### Tech Stack
- **Visual Template Engine**: Fabric.js (Canvas API, no recurring cost)
- **DNA Extraction AI**: **Auto-fallback chain dari admin settings** (GeneralSetting::getDefaultTextAI())
  - Default: `['gemini', 'deepseek']`
  - Admin bisa ubah via `/admin/settings`
  - AIService otomatis fallback jika provider pertama gagal
  - **TIDAK hardcode provider** - semua dinamis dari database
- **Storage Strategy**: User-scoped folders (`storage/app/public/users/{user_id}/social/`)
- **Multi-tenancy**: All models filter by `created_by` (verified ✅)

### Database Schema

**Table 1: ane_content_dna**
```sql
- post_id (FK to ane_posts)
- created_by (FK to ane_users)
- headline, subheadline, hot_take, cta
- key_points (JSON array)
- keywords (JSON array)
- summary_short/medium/long
- tone (enum: neutral|informative|urgent|inspiring)
- intent (enum: breaking|evergreen|refresh)
```

**Table 2: ane_social_assets**
```sql
- post_id (FK to ane_posts)
- created_by (FK to ane_users)
- channel (enum: instagram|facebook|twitter|linkedin)
- asset_type (enum: feed|story|thread)
- copy_text
- copy_version (int, for A/B testing)
- visual_path (storage path)
- visual_size (4:5, 9:16, 1:1)
- visual_metadata (JSON template config)
- status (enum: draft|ready|scheduled|published)
```

**Table 3: ane_distribution_schedule**
```sql
- asset_id (FK to ane_social_assets)
- created_by (FK to ane_users)
- scheduled_at, published_at
- platform_post_id (external platform ID)
- publish_response (JSON)
- publish_status (enum: pending|success|failed)
```

### Storage Structure
```
storage/app/public/users/{user_id}/
  ├── media/           # Existing media library
  ├── social/
      ├── instagram/
      │   ├── feed/    # 4:5 (1080x1350)
      │   └── story/   # 9:16 (1080x1920)
      ├── facebook/
      ├── twitter/
      └── linkedin/
```

---

## Implementation Roadmap

### 🎯 Current Status: **Phase 5.2 ✅ COMPLETE → MVP COMPLETE (Phases 1-5 Done)**

---

## ✅ Phase 5.2 Completion Report

**Date Completed:** December 22, 2025

**What Was Built:**
- SchedulingService dengan smart scheduling logic
- Intent-based scheduling rules (breaking/evergreen/default)
- Optimal posting times per channel
- Auto-schedule method
- Bulk scheduling functionality
- Reschedule method
- Scheduling recommendations
- AmplifyController integration

**Deliverable:** ✅ Intelligent scheduling system dengan AI-driven recommendations

**Technical Details:**
- Smart scheduling berdasarkan `ContentDNA.intent`:
  - Breaking: `now()->addMinutes(30)` (immediate)
  - Evergreen: Staggered across days with `addDays($dayOffset)`
  - Default: Same day dengan optimal times
- Optimal times array per channel (morning, noon, evening slots)
- Bulk scheduling: accepts array of asset IDs + single datetime
- Reschedule: updates `scheduled_at` + resets status to 'pending'
- Recommendations: returns array dengan type, message, suggested_date
- AmplifyController: optional `auto_schedule` boolean parameter

---

## ✅ Phase 5.1 Completion Report

**Date Completed:** December 22, 2025

**What Was Built:**
- CalendarController dengan multi-tenant event loading
- Calendar view dengan FullCalendar.js v6
- Color-coded events per channel (IG/FB/X/LinkedIn)
- Hover tooltip dengan quick preview
- Click-to-edit navigation
- Monthly & weekly view toggle
- Dark theme CSS customization
- Channel legend

**Deliverable:** ✅ Visual calendar untuk monitoring distribution schedule

**Technical Details:**
- FullCalendar.js via CDN (v6.1.10)
- Event source: API endpoint `/api/calendar/events`
- Multi-tenant: `where('created_by', auth()->id())`
- Eager loading: `with(['asset.post:id,title', 'asset'])`
- Event transform: DistributionSchedule → FullCalendar event format
- Color mapping: channel → hex color codes
- Tooltip positioning: fixed z-50 dengan dynamic left/top
- Event click: redirect ke `/posts/{id}/edit`

---

## ✅ Phase 4.2 Completion Report

**Date Completed:** December 22, 2025

**What Was Built:**
- exportPack method di AssetLibraryController
- ZIP archive builder dengan ZipArchive
- Multi-folder structure (visuals, captions, metadata, README)
- Export button di post editor (conditional rendering)
- Asset count badge
- Multi-tenant authorization
- Auto-cleanup temp files

**Deliverable:** ✅ Single-click download semua asset dalam 1 ZIP file

**Technical Details:**
- ZIP structure: visuals/ + captions/ + metadata.json + README.md
- Caption files grouped by channel (instagram.txt, facebook.txt, etc)
- Metadata includes: post info, full DNA data, asset stats, export timestamp
- Authorization: `created_by` check before export
- Temp files: `storage/app/temp/` dengan auto-delete after download
- Button visibility: conditional (`@if($post->contentDNA && $post->socialAssets()->count() > 0)`)

---

## ✅ Phase 4.1 Completion Report

**Date Completed:** December 22, 2025

**What Was Built:**
- AssetLibraryController dengan multi-tenant filtering
- Asset library view dengan responsive grid layout
- Real-time search dengan 500ms debounce
- Channel & status filters dengan URL persistence
- Copy to clipboard functionality dengan visual feedback
- Download visual button (conditional)
- Thumbnail grid dengan channel/status badges
- Pagination (24 items per page)
- Empty state guidance

**Deliverable:** ✅ User bisa browse, filter, search, dan manage semua social assets

**Technical Details:**
- Multi-tenant: `where('created_by', auth()->id())`
- Eager loading: `with(['post:id,title'])`
- Search scope: copy_text + post title (LIKE query)
- Filter state persistent via URL parameters
- Clipboard API untuk copy functionality
- Responsive grid: 1/2/3/4 columns (mobile/tablet/desktop/XL)

---

## ✅ Phase 3.3 Completion Report

**Date Completed:** December 21, 2025

**What Was Built:**
- Visual editor Blade component dengan Fabric.js integration
- Template selector dropdown yang load dari API endpoint
- Live text editing (headline + subheadline) dengan instant canvas update
- Image replacement via drag & drop dengan FileReader preview
- Color theme selector (3 presets: modern, bold, minimal)
- Real-time canvas rendering dengan Fabric.js
- Download PNG functionality (client-side export)
- Save & Upload functionality (canvas → blob → FormData → server)
- Alpine.js reactive component dengan lifecycle management
- Global trigger function `openVisualEditor(assetId)`
- Loading states & error handling

**Deliverable:** ✅ Lightweight editor siap untuk quick visual tweaks (≤30 detik)

**Technical Approach:**
- Client-side rendering (Fabric.js Canvas API)
- Server provides template data + DNA variables
- User edits → real-time canvas update
- Export → convert to PNG blob → upload to user-scoped storage
- Auto-update SocialAsset (visual_path, visual_size, visual_metadata)

---

## ✅ Phase 3.2 Completion Report

**Date Completed:** December 21, 2025

**What Was Built:**
- VisualTemplateService for template data preparation
- VisualController with 3 endpoints
- Featured image URL extraction from Post
- Variable replacement system (DNA → template variables)
- PNG upload handler with user-scoped storage
- Auto-update SocialAsset (visual_path, visual_size, visual_metadata)
- Template listing API by channel + size

**Deliverable:** ✅ Backend API siap untuk client-side Fabric.js rendering

**Approach:** Hybrid - Server prepares data, client renders with Fabric.js, uploads PNG back to server

---

## ✅ Phase 3.1 Completion Report

**Date Completed:** December 21, 2025

**What Was Built:**
- Installed Fabric.js library via NPM
- Created template JSON schema with variable support
- Built 7 templates across 3 sizes (4:5, 9:16, 1:1)
- Template registry with helper functions
- Color presets (modern, bold, minimal)
- Dynamic variable replacement system ({{headline}}, {{featuredImage}}, etc)

**Deliverable:** ✅ Template library siap untuk rendering client-side

**Note:** Server-side rendering (node-canvas) skipped due to native dependency conflicts. Will use client-side Fabric.js rendering instead.

---

## ✅ Phase 2.3 Completion Report

**Date Completed:** December 21, 2025

**What Was Built:**
- Updated Step 3 in amplify-builder.blade.php with full editing interface
- Editable textarea per channel with character counter
- Character limit validation (IG: 2200, FB: 63206, X: 280, LinkedIn: 3000)
- Version selector for A/B testing
- Auto-save with 2-second debounce
- Regenerate button per asset
- SocialAssetController with update() and regenerate() methods
- SocialAssetPolicy for multi-tenant authorization
- 2 new routes for asset management

**Deliverable:** ✅ User bisa edit copy real-time dengan auto-save

---

## ✅ Phase 2.2 Completion Report

**Date Completed:** December 21, 2025

**What Was Built:**
- `resources/views/components/amplify-builder.blade.php` - Full modal UI with 3-step wizard
- Step 1: Channel selection with brand-colored cards (IG gradient, FB blue, X black, LinkedIn blue)
- Step 2: Loading state with animated progress bar + status messages
- Step 3: Results preview with DNA card + social copy grid (2 columns)
- Error state with retry functionality
- Alpine.js reactive component with global trigger function
- Real-time AJAX to AmplifyController endpoints

**Deliverable:** ✅ Modal popup siap trigger dari post editor

---

## ✅ Phase 2.1 Completion Report

**Date Completed:** December 21, 2025

**What Was Built:**
- `app/Http/Controllers/AmplifyController.php` - API controller for DNA extraction & social copy generation
- Methods: `generate(Request $request, Post $post)`, `preview(Post $post)`
- Routes: `POST /posts/{post}/amplify`, `GET /posts/{post}/amplify/preview`
- Authorization via PostPolicy (update & view permissions)
- Dependency injection for ContentDNAService & SocialCopyService
- JSON responses with DNA + assets data

**Deliverable:** ✅ Backend API ready for frontend integration

---

## ✅ Phase 1.4 Completion Report

**Date Completed:** December 21, 2025

**What Was Built:**
- `test_social_distribution.php` - Manual test script
- Fixed ContentDNAService string truncation (Str::limit for all fields)
- End-to-end test: 1 post → 1 DNA → 4 social assets

**Test Results:** ✅ ALL PASS
- Phase 1.1: Database & Models - OK
- Phase 1.2: DNA Extraction - OK
- Phase 1.3: Social Copy Generation - OK
- Database verification: 1 DNA + 4 assets created

**Deliverable:** ✅ Backend tested & working

---

## ✅ Phase 1.3 Completion Report

**Date Completed:** December 21, 2025

**What Was Built:**
- `app/Services/SocialCopyService.php` - Platform-specific copy generation
- Methods: `generateCopy()`, `generateAllCopies()`, `regenerateCopy()`
- 4 platform-specific prompts: Instagram (feed/story), Facebook, Twitter (post/thread), LinkedIn
- Auto-store to ane_social_assets table
- Copy cleanup (markdown removal, formatting)
- Version tracking for A/B testing

**Deliverable:** ✅ DNA → 4 channel-specific captions

---

## ✅ Phase 1.2 Completion Report

**Date Completed:** December 21, 2025

**What Was Built:**
- `app/Services/ContentDNAService.php` - AI-powered DNA extraction service
- Methods: `extractDNA()`, `reExtractDNA()`, `buildExtractionPrompt()`, `parseAIResponse()`, `validateDNAData()`
- Auto-fallback AI integration (uses GeneralSetting::getDefaultTextAI())
- JSON parsing with markdown cleanup
- Strict validation (3 key_points, 10 keywords, char limits, enum validation)
- Auto-store to ane_content_dna table

**Deliverable:** ✅ AI extraction service ready for use

---

## ✅ Phase 1.1 Completion Report

**Date Completed:** December 21, 2025

### What Was Built:

#### 1. Database Migrations (3 tables)
All migrations created and executed successfully:

- **ane_content_dna** - Stores extracted DNA from posts
  - Fields: headline, subheadline, hot_take, cta, key_points (JSON), keywords (JSON), summaries (short/medium/long), tone, intent
  - Foreign keys: post_id → ane_posts, created_by → ane_users
  - File: `database/migrations/2025_12_21_153654_create_ane_content_dna_table.php`

- **ane_social_assets** - Stores generated social media content
  - Fields: channel, asset_type, copy_text, copy_version, visual_path, visual_size, visual_metadata (JSON), status
  - Foreign keys: post_id → ane_posts, created_by → ane_users
  - File: `database/migrations/2025_12_21_153717_create_ane_social_assets_table.php`

- **ane_distribution_schedule** - Tracks publishing schedule
  - Fields: scheduled_at, published_at, platform_post_id, publish_response (JSON), publish_status
  - Foreign keys: asset_id → ane_social_assets, created_by → ane_users
  - File: `database/migrations/2025_12_21_153741_create_ane_distribution_schedule_table.php`

#### 2. Eloquent Models (3 models + relationships)

- **ContentDNA** (`app/Models/ContentDNA.php`)
  - JSON casting for key_points and keywords
  - Relationships: belongsTo Post, belongsTo User (creator)

- **SocialAsset** (`app/Models/SocialAsset.php`)
  - JSON casting for visual_metadata
  - Relationships: belongsTo Post, belongsTo User (creator), hasOne DistributionSchedule
  - Accessor: getVisualUrlAttribute() for asset URLs

- **DistributionSchedule** (`app/Models/DistributionSchedule.php`)
  - DateTime casting for scheduled_at and published_at
  - JSON casting for publish_response
  - Relationships: belongsTo SocialAsset, belongsTo User (creator)
  - Helper methods: isPending(), isPublished(), isFailed()

- **Post Model Updated** (`app/Models/Post.php`)
  - Added: hasOne ContentDNA relationship
  - Added: hasMany SocialAsset relationship
  - Added: socialAssetsByChannel($channel) helper method

### Multi-Tenancy Verification ✅
- All tables include `created_by` field (indexed)
- All models have `creator()` relationship to User
- Data isolation ready for per-user filtering

### Database Indices Created:
- Single column: post_id, created_by, asset_id, scheduled_at
- Composite: (created_by, intent), (created_by, channel), (created_by, status), (created_by, scheduled_at), (post_id, channel, asset_type)

### Deliverable Status: ✅ **100% COMPLETE**
Database schema and models are fully ready for Phase 1.2 (ContentDNAService).

---

### **Phase 1: Foundation (Backend Core)**
**Goal:** DNA extraction + copy generation working end-to-end

#### 1.1 Database Setup ✅ **100% COMPLETE**
- [x] Create migration: `ane_content_dna`
- [x] Create migration: `ane_social_assets`
- [x] Create migration: `ane_distribution_schedule`
- [x] Create Model: `ContentDNA.php`
- [x] Create Model: `SocialAsset.php`
- [x] Create Model: `DistributionSchedule.php`
- [x] Add relationships to `Post.php`

**Deliverable:** ✅ Database ready, models with relationships

**Files Created:**
- `database/migrations/2025_12_21_153654_create_ane_content_dna_table.php`
- `database/migrations/2025_12_21_153717_create_ane_social_assets_table.php`
- `database/migrations/2025_12_21_153741_create_ane_distribution_schedule_table.php`
- `app/Models/ContentDNA.php`
- `app/Models/SocialAsset.php`
- `app/Models/DistributionSchedule.php`
- `app/Models/Post.php` (updated with new relationships)

---

#### 1.2 DNA Extraction Service ✅ **100% COMPLETE**
- [x] Create `app/Services/ContentDNAService.php`
- [x] Method: `extractDNA(Post $post): ContentDNA`
- [x] AI prompt engineering (11 DNA fields)
- [x] JSON parsing & validation
- [x] Auto-fallback handling (via AIService)
- [x] Store to `ane_content_dna` table

**Deliverable:** ✅ 1 artikel → DNA tersimpan di database

**File Created:**
- `app/Services/ContentDNAService.php`

---

#### 1.3 Social Copy Adaptation Service ✅ **100% COMPLETE**
- [x] Create `app/Services/SocialCopyService.php`
- [x] Method: `generateCopy(ContentDNA $dna, string $channel): SocialAsset`
- [x] Platform-specific prompts:
  - [x] Instagram prompt template (feed + story)
  - [x] Facebook prompt template
  - [x] Twitter/X prompt template (post + thread)
  - [x] LinkedIn prompt template
- [x] Store to `ane_social_assets` table

**Deliverable:** ✅ DNA → 4 channel-specific captions

**File Created:**
- `app/Services/SocialCopyService.php`

---

#### 1.4 Testing & Validation ✅ **100% COMPLETE**
- [x] Manual test script: `test_social_distribution.php`
- [x] End-to-end test: Post → DNA → 4 Social Assets
- [x] Bug fix: Added Str::limit() truncation to ContentDNAService
- [x] Database verification: 1 DNA + 4 assets created successfully

**Deliverable:** ✅ Backend tested & working

**File Created:**
- `test_social_distribution.php`

**Test Results:**
- ✓ DNA extraction with real post
- ✓ Social copy generation (Instagram, Facebook, Twitter, LinkedIn)
- ✓ Database persistence verified

---

### **Phase 2: Amplify Builder UI**
**Goal:** User bisa trigger DNA extraction & lihat hasil copy

#### 2.1 Routes & Controller ✅ **100% COMPLETE**
- [x] Route: `POST /posts/{id}/amplify`
- [x] Route: `GET /posts/{id}/amplify/preview`
- [x] Create `AmplifyController.php`
- [x] Method: `generate(Post $post)` (trigger DNA + Copy)
- [x] Method: `preview(Post $post)` (show results)

**Deliverable:** ✅ API endpoints ready for frontend integration

**Files Created:**
- `app/Http/Controllers/AmplifyController.php`

**Routes Added:**
- `POST /posts/{post}/amplify` → AmplifyController@generate
- `GET /posts/{post}/amplify/preview` → AmplifyController@preview

---

#### 2.2 Amplify Modal UI ✅ **100% COMPLETE**
- [x] Create Blade component: `<x-amplify-builder />`
- [x] Channel toggles (IG / FB / X / LinkedIn)
- [x] DNA preview card
- [x] Copy preview cards per channel
- [x] Loading states (DNA extraction ~5-10 seconds)
- [x] Error handling UI

**Deliverable:** ✅ Modal popup dengan preview copy per channel

**Files Created:**
- `resources/views/components/amplify-builder.blade.php`

**Features:**
- 3-step wizard: Channel selection → Loading → Results preview
- Brand-colored channel cards (IG gradient, FB blue, X black, LinkedIn blue)
- Real-time progress bar with loading messages
- DNA preview (headline, tone, key points)
- Social copy grid (2 columns) with character count
- Error state with retry functionality
- Alpine.js reactive component with global `openAmplifyModal(postId)` function

---

#### 2.3 Copy Editing Interface ✅ **100% COMPLETE**
- [x] Editable textarea per channel
- [x] Character counter (IG: 2200, X: 280, etc)
- [x] Version selector (v1 / v2 for A/B test)
- [x] Save draft functionality
- [x] Real-time auto-save

**Deliverable:** ✅ User bisa edit & save copy

**Files Created/Modified:**
- `resources/views/components/amplify-builder.blade.php` (updated Step 3)
- `app/Http/Controllers/SocialAssetController.php`
- `app/Policies/SocialAssetPolicy.php`
- `routes/web.php` (added 2 routes)

**Features:**
- Editable textarea per channel (4-6 rows, Twitter shorter)
- Real-time character counter with limit validation (IG: 2200, FB: 63206, X: 280, LinkedIn: 3000)
- Character limit warning (red border if exceeded)
- Version selector dropdown (v1, v2, v3, etc for A/B testing)
- Regenerate button per asset (calls SocialCopyService)
- Auto-save with 2-second debounce
- Saved/Unsaved indicator per asset
- Save Drafts button (batch save all)
- Mark as Ready button (change status to 'ready')
- Collapsible DNA preview card

**Routes Added:**
- `PATCH /social-assets/{asset}` → SocialAssetController@update
- `POST /social-assets/{asset}/regenerate` → SocialAssetController@regenerate

---

### **Phase 3: Visual Template System**
**Goal:** Generate PNG dari template + DNA

#### 3.1 Fabric.js Setup ✅ **100% COMPLETE**
- [x] Install Fabric.js via NPM
- [x] Create template JSON schema
- [x] Create base templates:
  - [x] IG Feed 4:5 template (3 variants)
  - [x] IG Story 9:16 template (2 variants)
  - [x] FB/LinkedIn 1:1 template (2 variants)
- [x] Server-side rendering: Client-side approach (node-canvas skipped due to dependency issues)

**Deliverable:** ✅ Template library ready for rendering

**Files Created:**
- `resources/js/visual-templates/schema.js` - Template JSON schema + color presets
- `resources/js/visual-templates/instagram-feed.js` - 3 variants (Modern, Bold, Minimal)
- `resources/js/visual-templates/instagram-story.js` - 2 variants (Breaking, Feature)
- `resources/js/visual-templates/social-square.js` - 2 variants (Professional, Vibrant)
- `resources/js/visual-templates/index.js` - Template registry + helper functions

**Templates Built:**
- **IG Feed (4:5 - 1080x1350):** Modern Blue, Bold Red, Minimal Clean
- **IG Story (9:16 - 1080x1920):** Breaking News, Feature Story
- **FB/LinkedIn (1:1 - 1080x1080):** Professional, Vibrant

**Features:**
- Dynamic variable replacement ({{headline}}, {{subheadline}}, {{featuredImage}})
- 3 color presets: modern (blue), bold (red), minimal (black/white)
- Standard dimensions for each size
- Template categorization by channel + size

---

#### 3.2 Visual Template Service ✅ **100% COMPLETE**
- [x] Create `app/Services/VisualTemplateService.php`
- [x] Method: `generateVisual(ContentDNA $dna, string $size, string $template): array`
- [x] Image placement logic (featured image URL extraction)
- [x] Text overlay rendering (variable replacement)
- [x] Export to PNG (save to user folder: `storage/users/{id}/social/{channel}/`)
- [x] Update `ane_social_assets.visual_path`

**Deliverable:** ✅ Service siap untuk client-side rendering + upload

**Files Created:**
- `app/Services/VisualTemplateService.php`
- `app/Http/Controllers/VisualController.php`

**Routes Added:**
- `GET /social-assets/{asset}/templates` → VisualController@getTemplates
- `POST /social-assets/{asset}/template-data` → VisualController@getTemplateData
- `POST /social-assets/{asset}/upload-visual` → VisualController@uploadVisual

**Features:**
- Template data preparation (DNA → template variables)
- Featured image URL extraction from Post
- Client-side rendering support (returns template + variables for Fabric.js)
- PNG upload handler (saves to user-scoped folder)
- Auto-update visual_path, visual_size, visual_metadata
- Template listing by channel + size

---

#### 3.3 Lightweight Editor UI ✅ **100% COMPLETE**
- [x] Canvas editor component
- [x] Template selector dropdown
- [x] Text editing (headline, subheadline)
- [x] Image replacement (drag & drop)
- [x] Color theme selector (3-5 presets)
- [x] Live preview
- [x] Export button

**Deliverable:** ✅ User bisa edit visual & export PNG

**Files Created:**
- `resources/views/components/visual-editor.blade.php`

**Features:**
- Full-screen modal dengan 3-column layout (sidebar controls, canvas preview, footer actions)
- Template selector dropdown (loads from API)
- Live text editing (headline + subheadline) dengan instant preview
- Image replacement via drag & drop file input
- Color theme selector (modern blue, bold red, minimal black/white)
- Fabric.js canvas rendering dengan real-time updates
- Download PNG button (local export)
- Save & Upload button (uploads to server + updates SocialAsset)
- Alpine.js reactive component dengan global `openVisualEditor(assetId)` trigger
- Loading states untuk rendering & uploading
- ESC key close support

---

### **Phase 4: Asset Library & Export**
**Goal:** User bisa lihat history & download pack

#### 4.1 Asset Library UI ✅ **100% COMPLETE**
- [x] Route: `GET /assets` (list all social assets)
- [x] Filter by channel
- [x] Filter by status (draft/ready/published)
- [x] Search by keyword
- [x] Thumbnail grid view
- [x] Copy to clipboard button

**Deliverable:** ✅ User bisa browse & manage semua asset

**Files Created:**
- `app/Http/Controllers/AssetLibraryController.php`
- `resources/views/assets/index.blade.php`

**Features:**
- Multi-tenant asset listing (`created_by` filter)
- 4-column responsive grid (XL: 4 cols, LG: 3 cols, MD: 2 cols)
- Real-time search dengan debounce (500ms)
- Channel filter dropdown (IG/FB/X/LinkedIn)
- Status filter dropdown (draft/ready/scheduled/published)
- Visual thumbnail preview (aspect-square)
- Channel badge (gradient IG, blue FB/LinkedIn, black X)
- Status badge (color-coded)
- Copy to clipboard dengan success feedback
- Download visual button (jika ada visual_path)
- Pagination (24 items per page)
- Empty state guidance
- URL parameter persistence (filter state tetap saat reload)

**Routes Added:**
- `GET /assets` → AssetLibraryController@index
- `GET /api/assets/data` → AssetLibraryController@getData (for AJAX)

---

#### 4.2 Export Pack ✅ **100% COMPLETE**
- [x] Method: `exportPack(Post $post): ZipArchive`
- [x] Include PNGs (all sizes)
- [x] Include `captions.txt` per channel
- [x] Include `metadata.json` (DNA + stats)
- [x] Download as ZIP

**Deliverable:** ✅ Single-click download semua asset 1 artikel

**Files Modified:**
- `app/Http/Controllers/AssetLibraryController.php` (added exportPack method)
- `resources/views/posts/edit.blade.php` (added Export Pack button)

**Features:**
- ZIP export dengan struktur folder:
  - `/visuals/` - PNG files (channel-assetType.png)
  - `/captions/` - Text files per channel (instagram.txt, facebook.txt, etc)
  - `metadata.json` - Full DNA + asset stats + post info
  - `README.md` - Export documentation
- Multi-tenant authorization check
- Auto-cleanup (deleteFileAfterSend)
- Export button conditional (hanya muncul jika ada DNA + assets)
- Asset count badge di button
- Purple-pink gradient button (visual cue for special action)

**ZIP Structure:**
```
social-pack-{post_id}-{timestamp}.zip
├── visuals/
│   ├── instagram-feed.png
│   ├── facebook-feed.png
│   ├── twitter-feed.png
│   └── linkedin-feed.png
├── captions/
│   ├── instagram.txt
│   ├── facebook.txt
│   ├── twitter.txt
│   └── linkedin.txt
├── metadata.json
└── README.md
```

**Routes Added:**
- `GET /posts/{post}/export-pack` → AssetLibraryController@exportPack

---

### **Phase 4.3: Visual Editor Optimization**

**Goal:** Transform Visual Editor menjadi universal template system dengan customizable branding dan multi-ratio support.

---

#### **Problem Analysis**

**Current System Limitations:**
1. Template hardcoded per channel dan size (ig-feed-modern hanya untuk Instagram 4:5)
2. Tidak ada customization (warna, font, logo brand user)
3. Setiap rasio butuh template terpisah (tidak scalable)
4. Canvas state save/restore masih basic (tidak simpan customization settings)

**Desired State:**
1. **Universal Template**: 1 template bisa dipakai untuk semua platform dan rasio
2. **Multi-Ratio Support**: User pilih rasio (1:1, 4:5, 16:9, 4:3, 9:16) → template auto-adjust
3. **Customizable Settings**: Warna overlay, font, opacity, alignment
4. **Brand Integration**: Upload logo, simpan brand colors, apply otomatis ke semua visual

---

#### **4.3.1: Database Schema - User Branding**

**Migration:** `create_user_branding_table`

```sql
CREATE TABLE ane_user_branding (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    logo_media_id BIGINT UNSIGNED NULL,
    primary_color VARCHAR(7) DEFAULT '#dc2626',
    secondary_color VARCHAR(7) DEFAULT '#2563eb',
    default_font VARCHAR(50) DEFAULT 'Inter',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES ane_users(id) ON DELETE CASCADE,
    FOREIGN KEY (logo_media_id) REFERENCES ane_media(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_branding (user_id)
);
```

**Model:** `app/Models/UserBranding.php`

**Relationships:**
- `belongsTo(User)` - One branding per user
- `belongsTo(Media, 'logo_media_id')` - Logo image

---

#### **4.3.2: Template Structure Refactor**

**New Template Schema (Percentage-Based Layout):**

**File:** `storage/app/templates/universal-templates.json`

```json
[
  {
    "id": "news-overlay",
    "name": "News Overlay",
    "category": "news",
    "description": "Full image dengan gradient overlay dan headline bold",
    "supportedRatios": ["1:1", "4:5", "16:9", "4:3", "9:16"],

    "defaultSettings": {
      "overlayColor": "#dc2626",
      "overlayOpacity": 0.95,
      "overlayHeightPercent": 40,
      "headlineFont": "Inter",
      "headlineFontSizeBase": 56,
      "headlineColor": "#ffffff",
      "textAlign": "left",
      "showSubheadline": false,
      "logoPosition": "top-right",
      "logoMaxWidthPercent": 18.5
    },

    "layoutRules": {
      "image": {
        "type": "image",
        "position": "fullscreen",
        "widthPercent": 100,
        "heightPercent": 100,
        "zIndex": 1
      },
      "overlay": {
        "type": "rect",
        "position": "bottom",
        "heightPercent": "{{overlayHeightPercent}}",
        "fill": "{{overlayColor}}",
        "opacity": "{{overlayOpacity}}",
        "zIndex": 2
      },
      "headline": {
        "type": "text",
        "position": "bottom",
        "paddingPercent": 5.5,
        "widthPercent": 88.8,
        "fontSize": "{{headlineFontSizeBase}}",
        "fontFamily": "{{headlineFont}}",
        "fill": "{{headlineColor}}",
        "fontWeight": "bold",
        "textAlign": "{{textAlign}}",
        "lineHeight": 1.2,
        "zIndex": 3
      },
      "logo": {
        "type": "logo",
        "position": "{{logoPosition}}",
        "marginPercent": 3.7,
        "maxWidthPercent": "{{logoMaxWidthPercent}}",
        "zIndex": 4
      }
    }
  },
  {
    "id": "minimal-clean",
    "name": "Minimal Clean",
    "category": "professional",
    "description": "Image top, text bottom dengan background putih bersih",
    "supportedRatios": ["1:1", "4:5", "16:9", "4:3"],

    "defaultSettings": {
      "backgroundColor": "#ffffff",
      "imageHeightPercent": 60,
      "headlineFont": "Inter",
      "headlineFontSizeBase": 64,
      "headlineColor": "#000000",
      "subheadlineColor": "#6b7280",
      "textAlign": "left",
      "showSubheadline": true,
      "logoPosition": "bottom-right",
      "logoMaxWidthPercent": 15
    },

    "layoutRules": {
      "background": {
        "type": "rect",
        "position": "fullscreen",
        "fill": "{{backgroundColor}}",
        "zIndex": 0
      },
      "image": {
        "type": "image",
        "position": "top",
        "heightPercent": "{{imageHeightPercent}}",
        "widthPercent": 100,
        "zIndex": 1
      },
      "headline": {
        "type": "text",
        "position": "after-image",
        "paddingPercent": 7.4,
        "widthPercent": 85.1,
        "fontSize": "{{headlineFontSizeBase}}",
        "fontFamily": "{{headlineFont}}",
        "fill": "{{headlineColor}}",
        "fontWeight": "bold",
        "textAlign": "{{textAlign}}",
        "lineHeight": 1.2,
        "zIndex": 2
      },
      "subheadline": {
        "type": "text",
        "position": "after-headline",
        "paddingPercent": 7.4,
        "widthPercent": 85.1,
        "fontSize": 28,
        "fontFamily": "{{headlineFont}}",
        "fill": "{{subheadlineColor}}",
        "textAlign": "{{textAlign}}",
        "lineHeight": 1.5,
        "visible": "{{showSubheadline}}",
        "zIndex": 2
      },
      "logo": {
        "type": "logo",
        "position": "{{logoPosition}}",
        "marginPercent": 3.7,
        "maxWidthPercent": "{{logoMaxWidthPercent}}",
        "zIndex": 3
      }
    }
  }
]
```

---

#### **4.3.3: Layout Calculation Engine**

**Service:** `app/Services/TemplateLayoutEngine.php`

**Methods:**
1. `calculateLayout(template, ratio, customSettings)` - Convert percentage rules to absolute pixels
2. `applyUserBranding(layout, userBranding)` - Inject logo + brand colors
3. `generateCanvasObjects(layout, variables)` - Output Fabric.js objects array

**Ratio Dimensions Mapping:**
```php
protected $ratioDimensions = [
    '1:1'   => ['width' => 1080, 'height' => 1080],
    '4:5'   => ['width' => 1080, 'height' => 1350],
    '16:9'  => ['width' => 1920, 'height' => 1080],
    '4:3'   => ['width' => 1440, 'height' => 1080],
    '9:16'  => ['width' => 1080, 'height' => 1920],
];
```

**Example Calculation:**
```php
// Template rule: heightPercent: 40
// Ratio: 4:5 (1080x1350)
$overlayHeight = 1350 * 0.4 = 540px
$overlayTop = 1350 - 540 = 810px

// Template rule: paddingPercent: 5.5
// Ratio: 4:5 (1080x1350)
$headlinePadding = 1080 * 0.055 = 59.4 ≈ 60px
$headlineLeft = 60px
$headlineWidth = 1080 - (60 * 2) = 960px
```

---

#### **4.3.4: Visual Editor UI Enhancement**

**Component:** `resources/views/components/visual-editor.blade.php`

**New UI Sections:**

**1. Template & Ratio Selector (Top)**
```blade
<div class="flex gap-4 mb-6">
    <!-- Template Dropdown -->
    <select x-model="currentTemplate" @change="loadTemplate()">
        <option value="">Select Template...</option>
        <template x-for="tpl in templates">
            <option :value="tpl.id" x-text="tpl.name"></option>
        </template>
    </select>

    <!-- Ratio Selector (Segmented Control) -->
    <div class="flex gap-2">
        <template x-for="ratio in availableRatios">
            <button @click="changeRatio(ratio)"
                    :class="currentRatio === ratio ? 'bg-blue-600' : 'bg-gray-700'">
                <span x-text="ratio"></span>
            </button>
        </template>
    </div>
</div>
```

**2. Customization Panel (Collapsible Sidebar)**
```blade
<!-- Colors Section -->
<div class="space-y-3">
    <h4 class="text-sm font-semibold">Colors</h4>

    <label>Overlay Color</label>
    <input type="color" x-model="settings.overlayColor"
           @change="updateSetting('overlayColor', $event.target.value)">

    <label>Overlay Opacity</label>
    <input type="range" min="0" max="100"
           x-model="settings.overlayOpacity"
           @input="updateSetting('overlayOpacity', $event.target.value / 100)">
    <span x-text="settings.overlayOpacity + '%'"></span>
</div>

<!-- Typography Section -->
<div class="space-y-3 mt-6">
    <h4 class="text-sm font-semibold">Typography</h4>

    <label>Font Family</label>
    <select x-model="settings.headlineFont"
            @change="updateSetting('headlineFont', $event.target.value)">
        <option value="Inter">Inter</option>
        <option value="Roboto">Roboto</option>
        <option value="Montserrat">Montserrat</option>
        <option value="Poppins">Poppins</option>
        <option value="Playfair Display">Playfair Display</option>
    </select>

    <label>Font Size</label>
    <input type="range" min="32" max="80"
           x-model="settings.headlineFontSizeBase">
    <span x-text="settings.headlineFontSizeBase + 'px'"></span>

    <label>Text Alignment</label>
    <div class="flex gap-2">
        <button @click="updateSetting('textAlign', 'left')"
                :class="settings.textAlign === 'left' ? 'bg-blue-600' : 'bg-gray-700'">
            Left
        </button>
        <button @click="updateSetting('textAlign', 'center')"
                :class="settings.textAlign === 'center' ? 'bg-blue-600' : 'bg-gray-700'">
            Center
        </button>
        <button @click="updateSetting('textAlign', 'right')"
                :class="settings.textAlign === 'right' ? 'bg-blue-600' : 'bg-gray-700'">
            Right
        </button>
    </div>
</div>

<!-- Branding Section -->
<div class="space-y-3 mt-6">
    <h4 class="text-sm font-semibold">Branding</h4>

    <label>Brand Logo</label>
    <button @click="openMediaLibraryForLogo()"
            class="w-full px-4 py-2 bg-gray-700 rounded">
        <span x-show="!brandLogo">Select Logo</span>
        <span x-show="brandLogo">Change Logo</span>
    </button>

    <div x-show="brandLogo" class="mt-2">
        <img :src="brandLogoUrl" class="h-16 object-contain">
        <button @click="removeLogo()" class="text-red-500 text-xs mt-1">
            Remove Logo
        </button>
    </div>

    <label>Logo Position</label>
    <select x-model="settings.logoPosition">
        <option value="top-left">Top Left</option>
        <option value="top-right">Top Right</option>
        <option value="bottom-left">Bottom Left</option>
        <option value="bottom-right">Bottom Right</option>
    </select>
</div>
```

---

#### **4.3.5: Backend API Endpoints**

**New Routes:**
```php
// User Branding Management
Route::get('/user/branding', [UserBrandingController::class, 'show']);
Route::put('/user/branding', [UserBrandingController::class, 'update']);

// Universal Template System
Route::get('/api/templates', [TemplateController::class, 'index']);
Route::get('/api/templates/{id}', [TemplateController::class, 'show']);
Route::post('/api/templates/{id}/render', [TemplateController::class, 'render']);
```

**Controller:** `app/Http/Controllers/UserBrandingController.php`

```php
public function show()
{
    $branding = auth()->user()->branding()->with('logo')->first();

    if (!$branding) {
        // Create default branding
        $branding = auth()->user()->branding()->create([
            'primary_color' => '#dc2626',
            'secondary_color' => '#2563eb',
            'default_font' => 'Inter',
        ]);
    }

    return response()->json([
        'success' => true,
        'branding' => $branding,
    ]);
}

public function update(Request $request)
{
    $validated = $request->validate([
        'logo_media_id' => 'nullable|exists:ane_media,id',
        'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        'default_font' => 'nullable|string|max:50',
    ]);

    $branding = auth()->user()->branding()->updateOrCreate(
        ['user_id' => auth()->id()],
        $validated
    );

    return response()->json([
        'success' => true,
        'branding' => $branding->load('logo'),
    ]);
}
```

---

#### **4.3.6: Implementation Steps**

**Step 1: Database Setup**
- [x] Create migration `create_user_branding_table`
- [x] Create model `UserBranding.php` with relationships
- [x] Run migration

**Step 2: Template System Refactor**
- [x] Create `storage/app/templates/universal-templates.json`
- [x] Create `TemplateLayoutEngine.php` service
- [x] Implement percentage-to-pixel calculation logic
- [x] Add multi-ratio support

**Step 3: User Branding CRUD**
- [x] Create `UserBrandingController.php`
- [x] Add routes for branding management
- [x] Create branding settings page UI

**Step 4: Visual Editor Enhancement**
- [x] Add ratio selector UI
- [x] Add customization panel (colors, fonts, alignment)
- [x] Add logo upload & positioning
- [x] Integrate TemplateLayoutEngine for rendering
- [x] Update save logic to persist custom settings

**Step 5: Template Migration**
- [x] Convert existing templates (News 1, Modern, Minimal) to universal format
- [x] Test all ratios (1:1, 4:5, 16:9, 4:3, 9:16)
- [x] Verify layout consistency across ratios

**Step 6: Testing & Polish**
- [x] Test template switching with custom settings preserved
- [x] Test logo positioning in all corners
- [x] Test font/color changes with live preview
- [x] Test save/restore with custom settings + logo
- [x] Performance testing with complex layouts

---

#### **4.3.7: Expected Deliverables**

**Database:**
- `ane_user_branding` table with logo & color settings
- User relationship: `User::branding()`

**Files Created:**
- `app/Models/UserBranding.php`
- `app/Http/Controllers/UserBrandingController.php`
- `app/Services/TemplateLayoutEngine.php`
- `storage/app/templates/universal-templates.json`
- `resources/views/branding/settings.blade.php`

**Files Modified:**
- `resources/views/components/visual-editor.blade.php` - Enhanced UI
- `app/Http/Controllers/VisualController.php` - Template rendering logic
- `app/Services/VisualTemplateService.php` - Use TemplateLayoutEngine

**Features Delivered:**
1. ✅ Universal template system (1 template → semua platform)
2. ✅ Multi-ratio support (1:1, 4:5, 16:9, 4:3, 9:16)
3. ✅ Customizable colors, fonts, opacity, alignment
4. ✅ Brand logo upload & auto-positioning
5. ✅ Settings persistence per template per user
6. ✅ Live preview saat customization

**User Experience Improvements:**
- Template switching instant dengan settings preserved
- Logo auto-scale based on canvas size
- Font preview dengan Google Fonts integration (optional)
- Color picker dengan recent colors history
- Preset templates: News, Professional, Creative, Minimal

---

### **Phase 5: Calendar & Scheduling**
**Goal:** Plan distribusi multi-day

#### 5.1 Calendar View ✅ **100% COMPLETE**
- [x] Route: `GET /calendar` (monthly view)
- [x] FullCalendar.js integration
- [x] Drag & drop scheduling (disabled for view-only)
- [x] Color coding per channel
- [x] Quick preview on hover

**Deliverable:** ✅ Visual calendar untuk melihat schedule distribusi

**Files Created:**
- `app/Http/Controllers/CalendarController.php`
- `resources/views/calendar/index.blade.php`

**Features:**
- FullCalendar.js v6 integration via CDN
- Monthly & weekly views
- Color-coded events per channel:
  - Instagram: Pink (#E1306C)
  - Facebook: Blue (#1877F2)
  - Twitter: Black (#000000)
  - LinkedIn: Blue (#0A66C2)
- Hover tooltip dengan quick preview:
  - Post title
  - Channel badge
  - Copy preview (100 chars)
  - Status
  - Scheduled date/time
- Click event → navigate to post editor
- Multi-tenant event loading (`created_by` filter)
- Eager loading: `with(['asset.post'])`
- Dark theme customization (matches dashboard)
- Channel legend di header

**Routes Added:**
- `GET /calendar` → CalendarController@index
- `GET /api/calendar/events` → CalendarController@getEvents

---

#### 5.2 Scheduling Logic ✅ **100% COMPLETE**
- [x] Smart scheduling rules (breaking vs evergreen)
- [x] Optimal time suggestions per channel
- [x] Bulk scheduling
- [x] Reschedule functionality

**Deliverable:** ✅ Intelligent scheduling system dengan auto-recommendations

**Files Created:**
- `app/Services/SchedulingService.php`

**Files Modified:**
- `app/Http/Controllers/AmplifyController.php` (added scheduling integration)

**Features:**
- Smart scheduling based on DNA intent:
  - **Breaking**: Immediate (30 mins from now)
  - **Evergreen**: Staggered across multiple days
  - **Default**: Same day, optimal times
- Optimal posting times per channel:
  - Instagram: 9 AM, 12 PM, 7 PM
  - Facebook: 1 PM, 3 PM, 7 PM
  - Twitter: 8 AM, 12 PM, 5 PM
  - LinkedIn: 8 AM, 12 PM, 5 PM
- Auto-schedule method (`autoSchedule()`)
- Bulk scheduling (`bulkSchedule()`)
- Reschedule existing schedule (`reschedule()`)
- Scheduling recommendations (`getRecommendations()`)
- Integration dengan AmplifyController (optional `auto_schedule` param)

**Methods:**
```php
scheduleAssets($dna, $assetIds, $startDate) // Smart schedule based on intent
autoSchedule($dna, $assetIds) // Auto-schedule dengan default logic
bulkSchedule($assetIds, $scheduledAt) // Schedule multiple assets at once
reschedule($schedule, $newDate) // Update existing schedule
getSuggestedTimes($channel) // Get optimal times for channel
getRecommendations($dna) // Get smart recommendations
```

---







### **Phase 6: Autopublish (Future)**
**Goal:** OAuth + auto-posting ke platform

- [ ] Instagram Graph API integration
- [ ] Facebook Graph API integration
- [ ] Twitter API v2 integration
- [ ] LinkedIn API integration
- [ ] Queue system (Laravel Queue)
- [ ] Retry mechanism
- [ ] Status tracking
- [ ] Performance analytics loop

**Deliverable:** Full automation end-to-end

---

## Modules di aneWP (urutan build yang bener)

### 1) Content Studio
- list artikel + status distribusi (Not started / Partial / Completed)
- tombol utama: **Amplify**

### 2) Amplify Builder
- pilih channel (toggle)
- generate assets (copy + visual)
  - Default visual mengambil featured image post → set `visual_path` + `visual_size='original'` tanpa konsumsi token
  - User bisa buka Visual Editor untuk ganti/crop/replace image; generate AI opsional (token aware)
- preview per channel
- edit ringan (text/image)
- generate posting plan (7-day calendar)

### 3) Asset Library
- semua output tersimpan
- download PNG (3 size)
- copy caption per channel
- versioning (caption v1/v2)

### 4) Calendar
- lihat jadwal distribusi
- reschedule (drag/drop optional)
- export pack harian/mingguan

> Autopublish nanti cuma nambah tombol "Publish/Schedule" per channel.

---

## MVP v1 (News-first, tanpa autopublish)
**Fokus: cepat, jelas, tidak ribet.**

- 1 artikel → 1 post per channel (IG / FB / X / LinkedIn)
- Visual utama: **IG 4:5** (+ Story opsional)
- Editor simple: edit headline + ganti image
- Export pack:
  - PNG (4:5)
  - Caption per channel

> Goal MVP: **berita langsung hidup di sosmed tanpa desain ulang.**

**MVP Scope = Phase 1 + Phase 2 + Phase 4** (Backend + Amplify UI + Export)

---

## Success Criteria (biar jelas menangnya)
User harus bisa bilang:
- "Gue publish artikel sekali, langsung keluar paket sosmed."
- "Gue tinggal tweak 30 detik, upload kelar."
- "Konten gue muncul terus seminggu tanpa mikir."

---

## Integration Points

### Trigger 1: Dari Post Editor/List
```blade
<!-- Button di post edit/index -->
<button onclick="openAmplifyModal({{ $post->id }})" class="btn-amplify">
    📢 Amplify to Social Media
</button>
```

### Trigger 2: Standalone Generator
```
Route: /social/generate
- Tanpa artikel (manual input)
- Generate DNA dari form input
- Flow sama dengan amplify dari artikel
```

---

## Development Notes

### Command Aliases
```bash
# Run migrations
php artisan migrate

# Create service
php artisan make:service ContentDNAService

# Create model with migration
php artisan make:model ContentDNA -m

# Run tests
php artisan test --filter ContentDNA
```

### AI Service Usage
```php
// AIService dengan auto-fallback (JANGAN hardcode provider!)
$aiService = new AIService();
$result = $aiService->generate($prompt, 'article_generation', [
    // 'provider' => 'gemini', // ❌ JANGAN hardcode!
    'max_tokens' => 1500,
    'temperature' => 0.3,
]);

// AIService otomatis:
// 1. Ambil fallback chain dari GeneralSetting::getDefaultTextAI()
// 2. Try provider pertama (misal 'gemini')
// 3. Kalau gagal, auto-fallback ke provider kedua (misal 'deepseek')
// 4. Log setiap attempt + error

// Override provider (opsional, untuk testing):
$result = $aiService->generate($prompt, 'article_generation', [
    'provider' => 'claude', // akan dicoba duluan, lalu fallback ke chain
    'max_tokens' => 1500,
]);
```

### Admin Settings Reference
```php
// Model: GeneralSetting
// Table: ane_general_settings

// Get fallback chain untuk text AI
GeneralSetting::getDefaultTextAI();
// Returns: ['gemini', 'deepseek'] (default)

// Get fallback chain untuk image AI
GeneralSetting::getDefaultImageAI();
// Returns: ['flux'] (default)

// Update via admin panel: /admin/settings
// Super admin bisa drag & drop untuk reorder priority
```

---

## Evaluasi phase 1 - 5

- **Amplify social assets**: flow hanya membuat copy teks, `visual_path` tidak pernah di-set → semua asset `has_visual=false`. Jadwalkan penambahan step generate visual (template/render atau image generator) + simpan `visual_path`/`visual_size`.
- **Authorization**: AmplifyController sempat error `authorize()` undefined; sudah dibereskan (pakai AuthorizesRequests). Tambahkan test/guard untuk mencegah regresi.

**Schedule perbaikan:**
1) Tambah pipeline generate visual saat membuat SocialAsset (gunakan template default untuk IG 4:5 + fallback 1:1/9:16) dan set metadata.  
2) Review API Grok image untuk dukungan aspect/size; update handler jika ada param resmi.  
3) Tambah test minimal untuk Amplify generate (policy + visual created).