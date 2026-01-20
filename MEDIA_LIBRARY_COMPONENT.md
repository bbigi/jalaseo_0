# Media Library Component - User Guide

## Overview

Media Library adalah **reusable Blade component** yang menyediakan interface lengkap untuk:
- Browse media library (4-column grid)
- Upload files (drag & drop)
- AI image generation (integrated)
- Edit metadata (title, alt text, description, caption)
- Search & filter
- Delete media

Component ini **sudah include di dashboard layout**, jadi bisa dipanggil dari mana saja tanpa perlu include ulang.

---

## Component Structure

```
resources/views/components/media-library.blade.php  → View component
app/View/Components/MediaLibrary.php                 → PHP class (auto-generated)
```

**API Endpoints:**
- `GET  /api/media/list` → Load media library
- `GET  /api/media/search?q=keyword` → Search media
- `POST /media/upload` → Upload file
- `PATCH /api/media/{id}/metadata` → Update metadata
- `DELETE /media/{id}` → Delete media

**Image Generation (integrated):**
- Uses existing `/image-generation/store` endpoint
- Uses existing `/image-generation/generate` endpoint
- Auto-detects language from post title (`context_title`)
- Auto-generates metadata (title, alt_text, description)

---

## How to Use (Simple Example)

### 1. Add Button to Open Modal

```blade
<button onclick="openMediaLibrary()" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
    Select Image
</button>
```

### 2. Add Hidden Field to Store Selected Media ID

```blade
<input type="hidden" name="featured_media_id" id="featuredMediaId" value="{{ old('featured_media_id', $post->featured_media_id ?? '') }}">
```

### 3. Add JavaScript Function

```javascript
function openMediaLibrary() {
    window.dispatchEvent(new CustomEvent('open-media-library', {
        detail: {
            callback: function(media) {
                // Called when user clicks "Set Featured Image"
                document.getElementById('featuredMediaId').value = media.id;
                console.log('Selected media:', media);

                // Update preview if needed
                document.getElementById('imagePreview').src = media.full_url;
            }
        }
    }));
}

// Or listen to global event
window.addEventListener('media-selected', function(e) {
    const media = e.detail;
    console.log('Media selected:', media);
});
```

---

## Full Example (Featured Image Selector)

```blade
<!-- Featured Image Section -->
<div class="bg-white rounded-xl border border-gray-200 p-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3">Featured Image</h3>

    <!-- Hidden field -->
    <input type="hidden" name="featured_media_id" id="featuredMediaId" value="{{ old('featured_media_id', $post->featured_media_id ?? '') }}">

    <!-- Preview -->
    <div id="featuredImagePreview" class="{{ $post->featured_media_id ? '' : 'hidden' }}">
        <img id="featuredImageThumb"
             src="{{ $post->featuredMedia ? asset('storage/' . $post->featuredMedia->file_path) : '' }}"
             class="w-full h-auto rounded-lg border border-gray-200 mb-2">
        <button type="button" onclick="removeFeaturedImage()"
                class="text-xs text-red-600 hover:text-red-700">
            Remove
        </button>
    </div>

    <!-- Select Button -->
    <button type="button" onclick="openMediaLibrary()"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        {{ $post->featured_media_id ? 'Change Image' : 'Select Image' }}
    </button>
</div>

<script>
function openMediaLibrary() {
    window.dispatchEvent(new CustomEvent('open-media-library', {
        detail: {
            callback: function(media) {
                // Update hidden field
                document.getElementById('featuredMediaId').value = media.id;

                // Update preview
                document.getElementById('featuredImageThumb').src = media.full_url;
                document.getElementById('featuredImagePreview').classList.remove('hidden');
            }
        }
    }));
}

function removeFeaturedImage() {
    document.getElementById('featuredMediaId').value = '';
    document.getElementById('featuredImagePreview').classList.add('hidden');
}
</script>
```

---

## Media Object Structure

When user selects an image, the callback receives this object:

```javascript
{
    id: 123,
    file_name: "my-image.jpg",
    file_type: "image",
    file_size: 524288,  // bytes
    title: "My Image Title",
    alt_text: "SEO-friendly alt text",
    description: "Detailed description",
    caption: "Image caption",
    full_url: "http://127.0.0.1:8000/storage/media/my-image.jpg",
    created_at: "2025-12-20T10:30:00.000000Z"
}
```

---

## Advanced Usage

### 1. Programmatically Open Modal

```javascript
// Open with callback
window.dispatchEvent(new CustomEvent('open-media-library', {
    detail: {
        callback: function(media) {
            alert('Selected: ' + media.title);
        }
    }
}));

// Or listen to global event
window.addEventListener('media-selected', function(e) {
    console.log('Media selected:', e.detail);
});
```

### 2. Multiple Image Selectors on Same Page

```blade
<!-- First selector -->
<button onclick="openMediaForFeatured()">Select Featured Image</button>
<input type="hidden" id="featuredImageId" name="featured_image_id">

<!-- Second selector -->
<button onclick="openMediaForThumbnail()">Select Thumbnail</button>
<input type="hidden" id="thumbnailImageId" name="thumbnail_image_id">

<script>
function openMediaForFeatured() {
    window.dispatchEvent(new CustomEvent('open-media-library', {
        detail: {
            callback: function(media) {
                document.getElementById('featuredImageId').value = media.id;
            }
        }
    }));
}

function openMediaForThumbnail() {
    window.dispatchEvent(new CustomEvent('open-media-library', {
        detail: {
            callback: function(media) {
                document.getElementById('thumbnailImageId').value = media.id;
            }
        }
    }));
}
</script>
```

### 3. Pre-fill Form with Selected Media

```javascript
window.dispatchEvent(new CustomEvent('open-media-library', {
    detail: {
        callback: function(media) {
            // Update multiple fields
            document.getElementById('imageId').value = media.id;
            document.getElementById('imageUrl').value = media.full_url;
            document.getElementById('imageTitle').value = media.title;
            document.getElementById('imageAlt').value = media.alt_text;
        }
    }
}));
```

---

## Component Features

### 1. Media Library Tab
- 4-column responsive grid
- Square aspect ratio (1:1) thumbnails
- Click to select
- Blue ring indicator for selected image
- Search with debounce (300ms)
- Refresh button
- Loading state
- Empty state

### 2. Upload Tab
- Drag & drop support
- Click to browse
- Progress bar
- Auto-switch to library after upload
- Auto-select uploaded image

### 3. AI Generate Tab
- Prompt input (textarea)
- Style dropdown (Photo, Cartoon, Cinematic, Abstract, Anime)
- Size dropdown (1:1, 4:3, 9:16)
- Generate button
- Auto-fetch post title for language detection
- Auto-generate metadata in detected language
- Auto-switch to library after generation
- Auto-select generated image

### 4. Detail Panel (Right)
- Full image preview
- Editable metadata fields:
  - Title
  - Alt Text
  - Description
  - Caption
- Auto-save on blur
- File info (name, size, date)
- Primary action: "Set Featured Image"
- Secondary action: "Delete"

---

## API Endpoints Details

### Load Media List
```
GET /api/media/list

Response:
{
    "success": true,
    "media": [
        {
            "id": 123,
            "file_name": "image.jpg",
            "file_type": "image",
            "file_size": 524288,
            "title": "Title",
            "alt_text": "Alt text",
            "description": "Description",
            "caption": "Caption",
            "full_url": "http://...",
            "created_at": "2025-12-20T..."
        }
    ]
}
```

### Search Media
```
GET /api/media/search?q=keyword

Searches in: title, file_name, alt_text
```

### Update Metadata
```
PATCH /api/media/123/metadata

Body:
{
    "title": "New title",
    "alt_text": "New alt text",
    "description": "New description",
    "caption": "New caption"
}

Response:
{
    "success": true,
    "media": { ... }
}
```

### Upload File
```
POST /media/upload

Body: multipart/form-data
- file: File

Response:
{
    "success": true,
    "media": { ... }
}
```

### Delete Media
```
DELETE /media/123

Response:
{
    "success": true
}
```

---

## Security & Access Control

- All media filtered by `created_by = auth()->id()` or `created_by IS NULL`
- Users can only edit/delete their own media
- System media (`created_by IS NULL`) visible to all but not editable
- File upload max: 10MB
- Allowed types: images only

---

## Styling Notes

- Uses Tailwind CSS 4
- Uses Heroicons (Blade components)
- Uses Alpine.js for reactivity
- Modal backdrop with blur effect
- Responsive grid (4 columns on desktop)
- z-index: 50 (modal)

---

## Common Use Cases

### 1. Post Featured Image
```javascript
// posts/edit.blade.php
openMediaLibrary() → user selects → update featured_media_id
```

### 2. Inline Content Images
```javascript
// article editor
openMediaLibrary() → user selects → insert <img> tag to editor
```

### 3. Media Management Page
```javascript
// media/index.blade.php
openMediaLibrary() → browse/edit/delete media
```

### 4. Profile Picture
```javascript
// profile/edit.blade.php
openMediaLibrary() → user selects → update avatar_media_id
```

---

## Testing Checklist

- [ ] Open modal from button click
- [ ] Load media library (grid shows images)
- [ ] Search media (filter works)
- [ ] Click image to select (blue ring appears)
- [ ] View detail panel (preview + metadata)
- [ ] Edit metadata (auto-save on blur)
- [ ] Upload file via drag & drop
- [ ] Upload file via click to browse
- [ ] Progress bar shows during upload
- [ ] AI Generate tab works
- [ ] Language detection from post title
- [ ] Generated image auto-selected
- [ ] Click "Set Featured Image" (callback fires)
- [ ] Delete image (confirmation → removed from list)
- [ ] Close modal (Esc key or X button)
- [ ] Multiple selectors on same page

---

## Troubleshooting

**Problem: Modal doesn't open**
- Check Alpine.js loaded (`<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>`)
- Check component included in layout (`<x-media-library />`)
- Check event dispatch syntax

**Problem: No images showing**
- Check `/api/media/list` returns data
- Check `created_by` matches `auth()->id()`
- Check storage symlink (`php artisan storage:link`)

**Problem: Upload fails**
- Check max file size (10MB)
- Check file type (images only)
- Check storage permissions

**Problem: Delete doesn't work**
- Check user owns the media (`created_by`)
- Check route method is `DELETE` not `POST`

**Problem: Metadata not saving**
- Check route is `PATCH` not `POST`
- Check CSRF token present

---

## Migration Guide (Old Modal → New Component)

**Before (old iframe modal):**
```blade
<button onclick="openMediaModal()">Select Image</button>

<div id="mediaModal" class="hidden">
    <iframe src="/image-generation/create-modal"></iframe>
</div>
```

**After (new component):**
```blade
<button onclick="openMediaLibrary()">Select Image</button>

<script>
function openMediaLibrary() {
    window.dispatchEvent(new CustomEvent('open-media-library', {
        detail: {
            callback: function(media) {
                document.getElementById('featuredMediaId').value = media.id;
            }
        }
    }));
}
</script>
```

**Benefits:**
- No iframe overhead
- Unified UI across app
- Better performance
- Metadata editing support
- Better UX (no redirect)

---

## Future Enhancements

Possible future additions (not implemented yet):
- [ ] Bulk select & delete
- [ ] Folders/categories
- [ ] Image cropping
- [ ] Image filters
- [ ] Copy URL to clipboard
- [ ] Pagination (currently limit 100)
- [ ] Infinite scroll
- [ ] Image dimensions display
- [ ] EXIF data display
- [ ] Multi-file upload

---

## Credits

Built with:
- Laravel 12
- Alpine.js 3.x
- Tailwind CSS 4
- Heroicons (Blade UI Kit)
