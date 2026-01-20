# Asset Library Helper Component

Reusable Alpine.js-based modal component untuk memilih social media assets dari mana saja dalam aplikasi.

## File Location

```
/resources/views/components/asset-library.blade.php
```

## Cara Pakai

### 1. Include Component di Layout

Tambahkan di layout utama (contoh: `layouts/dashboard.blade.php`):

```blade
<!-- Di bagian bawah sebelum </body> -->
<x-asset-library />
```

### 2. Buka Modal dari JavaScript

```javascript
// Cara 1: Dengan callback
window.dispatchEvent(new CustomEvent('open-asset-library', {
    detail: {
        callback: function(asset) {
            console.log('User selected asset:', asset);
            // asset.id
            // asset.copy_text
            // asset.hashtags
            // asset.channel
            // asset.visual_path
        }
    }
}));

// Cara 2: Tanpa callback (hanya untuk browse)
window.dispatchEvent(new CustomEvent('open-asset-library'));
```

### 3. Contoh Penggunaan Real

**Contoh 1: Insert Asset ke Calendar Event**

```blade
<!-- Di halaman calendar -->
<button onclick="openAssetPicker()">
    Select Social Asset
</button>

<input type="hidden" id="selected_asset_id" name="asset_id">
<div id="asset_preview" class="hidden">
    <!-- Preview asset yang dipilih -->
</div>

<script>
function openAssetPicker() {
    window.dispatchEvent(new CustomEvent('open-asset-library', {
        detail: {
            callback: function(asset) {
                // Set hidden input
                document.getElementById('selected_asset_id').value = asset.id;

                // Show preview
                document.getElementById('asset_preview').innerHTML = `
                    <img src="/storage/${asset.visual_path}" class="w-32 h-40 object-cover rounded">
                    <p>${asset.channel} - ${asset.status}</p>
                `;
                document.getElementById('asset_preview').classList.remove('hidden');
            }
        }
    }));
}
</script>
```

**Contoh 2: Select Multiple Assets untuk Batch Operation**

```javascript
let selectedAssets = [];

function openBatchSelector() {
    window.dispatchEvent(new CustomEvent('open-asset-library', {
        detail: {
            callback: function(asset) {
                selectedAssets.push(asset);
                updateBatchList();
            }
        }
    }));
}

function updateBatchList() {
    const list = document.getElementById('batch-list');
    list.innerHTML = selectedAssets.map(asset => `
        <div class="flex items-center gap-2">
            <img src="/storage/${asset.visual_path}" class="w-12 h-15 object-cover rounded">
            <span>${asset.channel}</span>
        </div>
    `).join('');
}
```

## Struktur Component

### Alpine.js Data Structure

```javascript
{
    isOpen: false,              // Modal visibility
    activeTab: 'library',       // Current tab
    loading: false,             // Loading state
    assets: [],                 // Asset list
    selectedAsset: null,        // Currently selected asset
    callback: null,             // Callback function
    autoSaveTimeout: null,      // Auto-save debounce
    autoSaveStatus: null,       // 'saving' | 'saved' | null

    filters: {
        search: '',             // Search query
        channel: '',            // Filter by channel
        status: ''              // Filter by status
    },

    pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 24
    }
}
```

### Methods

```javascript
// Init - Setup global event listener
init()

// Modal control
open()
close()

// Data loading
loadAssets(page = 1)

// Selection
selectAsset(asset)
confirmSelection()

// Auto-save (untuk edit di detail panel)
triggerAutoSave()
autoSaveAsset()
```

## Fitur

### 1. Filter & Search
- **Search**: Cari berdasarkan copy text atau post title
- **Channel Filter**: Instagram, Facebook, Twitter, LinkedIn, TikTok
- **Status Filter**: Draft, Ready, Published

### 2. Grid View
- Aspect ratio 4:5 (Instagram feed format)
- Channel badges dengan icon dan brand colors
- Selection highlight (blue ring)
- Hover state untuk visual feedback

### 3. Detail Panel (Right Sidebar)
- Preview image 4:5 ratio
- Editable fields dengan auto-save:
  - Copy Text (textarea)
  - Hashtags (textarea)
- Auto-save status indicator (Saving... / Saved!)
- Metadata display (post, channel, status, created date)
- Action buttons (Select, Download)

### 4. Pagination
- 24 assets per page
- Previous/Next navigation
- Page indicator

## API Endpoints yang Dipakai

Component ini bergantung pada route dan controller yang sudah ada:

```php
// routes/web.php
Route::get('/api/assets/data', [AssetLibraryController::class, 'getData']);
Route::post('/assets/update/{asset}', [AssetLibraryController::class, 'update']);
```

### API Response Format

**GET /api/assets/data**
```json
{
    "success": true,
    "assets": {
        "data": [
            {
                "id": 1,
                "post": {"id": 1, "title": "Post Title"},
                "copy_text": "Caption text...",
                "hashtags": "#example #test",
                "channel": "instagram",
                "status": "ready",
                "visual_path": "social-assets/image.png",
                "created_at": "2025-01-15T10:00:00.000000Z"
            }
        ],
        "current_page": 1,
        "last_page": 5,
        "per_page": 24
    }
}
```

**POST /assets/update/{id}**
```json
{
    "success": true,
    "message": "Asset updated successfully"
}
```

## Customization

### Custom Channels

Edit props di component:

```blade
<x-asset-library :channels="['instagram', 'facebook', 'custom-channel']" />
```

### Custom Styling

Component menggunakan Tailwind CSS. Override classes sesuai kebutuhan:

```blade
<!-- Contoh: Ubah warna selection ring -->
<div :class="selectedAsset && selectedAsset.id === asset.id ? 'ring-2 ring-purple-500' : ''"
```

### Disable Auto-Save

Hapus method `triggerAutoSave()` dan `autoSaveAsset()` jika tidak perlu edit di detail panel.

## Tips & Best Practices

1. **Callback Pattern**: Selalu gunakan callback untuk mendapatkan asset yang dipilih
2. **Multiple Selection**: Jika perlu multiple selection, panggil modal berkali-kali dan simpan array
3. **Authorization**: Component otomatis filter assets by `created_by = auth()->id()`
4. **Performance**: Pagination otomatis (24 per page), tidak perlu load all assets
5. **Mobile Responsive**: Grid otomatis adjust (2 cols mobile, 4 cols desktop)

## Perbedaan dengan Media Library

| Feature | Media Library | Asset Library |
|---------|--------------|---------------|
| **Purpose** | Upload & manage images/files | Browse & select social assets |
| **Tabs** | Library, Upload, AI Generate | Library only |
| **Aspect Ratio** | 1:1 (square) | 4:5 (Instagram feed) |
| **Badges** | File type | Channel (Instagram, FB, etc.) |
| **Edit Fields** | Title, Alt Text, Description | Copy Text, Hashtags |
| **Upload** | Yes | No (assets created via Visual Editor) |

## Troubleshooting

### Modal tidak muncul
- Pastikan component sudah di-include di layout
- Check console untuk error JavaScript
- Pastikan Alpine.js sudah ter-load

### Filter tidak bekerja
- Check API endpoint `/api/assets/data` response
- Pastikan controller `AssetLibraryController::getData()` sudah implement filter

### Auto-save gagal
- Check route `/assets/update/{id}` terdaftar
- Pastikan CSRF token tersedia
- Check authorization (user harus owner asset)

## Changelog

**v1.0.0 (2025-01-15)**
- Initial release
- Basic filter & search
- Detail panel dengan auto-save
- Channel badges
- Pagination support
