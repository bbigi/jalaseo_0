# SOP Pembuatan Template Visual

Dokumentasi standar untuk membuat template baru di sistem Visual Editor.

## Lokasi File

Template disimpan di: `/storage/app/templates/universal-templates.json`

## Struktur Template

Setiap template adalah objek JSON dengan struktur berikut:

```json
{
  "id": "unique-template-id",
  "name": "Template Display Name",
  "category": "news|social|marketing",
  "description": "Deskripsi singkat template",
  "supportedRatios": ["1:1", "4:5", "16:9", "9:16"],
  "defaultSettings": {},
  "layoutRules": {}
}
```

### 1. Metadata Template

#### `id` (required)
- **Type:** String
- **Format:** kebab-case (lowercase, dash-separated)
- **Contoh:** `"news-template-1"`, `"social-quote-card"`
- **Aturan:** Harus unik, tidak boleh duplikat

#### `name` (required)
- **Type:** String
- **Contoh:** `"News Template 1"`, `"Quote Card"`
- **Aturan:** Nama yang ditampilkan ke user di template selector

#### `category` (required)
- **Type:** String
- **Pilihan:** `"news"`, `"social"`, `"marketing"`
- **Fungsi:** Untuk grouping/filtering template

#### `description` (required)
- **Type:** String
- **Contoh:** `"Layout berita dengan background brand color, accent line, logo, headline, image, dan footer"`
- **Aturan:** Jelaskan fitur utama template (1-2 kalimat)

#### `supportedRatios` (required)
- **Type:** Array of strings
- **Pilihan:** `"1:1"` (square), `"4:5"` (Instagram feed), `"16:9"` (landscape), `"9:16"` (story)
- **Contoh:** `["1:1", "4:5", "16:9", "9:16"]`
- **Aturan:** List semua aspect ratio yang template support

### 2. Default Settings

Object yang berisi semua variable yang bisa di-customize. Ini adalah nilai default sebelum branding diterapkan.

**Contoh:**
```json
"defaultSettings": {
  "backgroundColor": "#3b82f6",
  "accentLineColor": "#3b82f6",
  "accentLineHeightPx": 4,
  "headlineFont": "Inter",
  "headlineFontSizeBase": 48,
  "headlineColor": "#ffffff",
  "textAlign": "left",
  "logoPosition": "top-right",
  "logoMaxWidthPercent": 15,
  "footerBackgroundColor": "#1e40af",
  "footerTextColor": "#ffffff",
  "footerFontSize": 14
}
```

**Naming Convention:**
- Warna: `xxxColor` (contoh: `backgroundColor`, `accentLineColor`)
- Font: `xxxFont` (contoh: `headlineFont`)
- Ukuran: `xxxSizeBase` atau `xxxSizePx` (contoh: `headlineFontSizeBase`)
- Posisi: `xxxPosition` (contoh: `logoPosition`)
- Persentase: `xxxPercent` (contoh: `logoMaxWidthPercent`)

**⚠️ PENTING: Warna Default vs Branding Dinamis**

Sistem memiliki 2 kategori warna:

**A. Warna TETAP (Default Template):**
Warna ini **TIDAK PERNAH** berubah mengikuti branding user, selalu menggunakan nilai default dari template.

- `backgroundColor` - Background utama (harus terang untuk kontras dengan accent)
  - **Default:** `#f5f5f5` (abu-abu terang)
  - **Logika:** Dipaksa tetap menggunakan default di `TemplateLayoutEngine.php` line 31-33
  - **Alasan:** Agar accent line dan elemen branding terlihat jelas

- `footerTextColor` - Warna text footer (harus kontras dengan footer background)
  - **Default:** `#ffffff` (putih)
  - **Logika:** Dipaksa tetap menggunakan default di `TemplateLayoutEngine.php` line 37-39
  - **Alasan:** Agar text tetap readable terhadap footer background yang dinamis

**B. Warna DINAMIS (Mengikuti Branding):**
Warna ini **OTOMATIS BERUBAH** sesuai branding user yang dipilih.

- `accentLineColor` - Garis accent/separator
  - **Default:** `#3b82f6` (biru)
  - **Dinamis:** → `branding.primary_color`
  - **Logika:** Override di `TemplateLayoutEngine.php` line 53-55

- `footerBackgroundColor` - Background footer
  - **Default:** `#3b82f6` (biru)
  - **Dinamis:** → `branding.primary_color`
  - **Logika:** Override di `TemplateLayoutEngine.php` line 56-59

- `overlayColor` - Overlay gradient (news-overlay template)
  - **Default:** `#dc2626` (merah)
  - **Dinamis:** → `branding.primary_color` (ALWAYS override)
  - **Logika:** Override di `TemplateLayoutEngine.php` line 64

- `headlineColor` - Warna text headline
  - Tetap sesuai default (putih/hitam untuk kontras)
  - Bisa di-custom manual oleh user via Customization panel

**C. Template Variables Dinamis:**
- `{{websiteUrl}}` → Otomatis dari `branding.website`
- Logo → Otomatis dari `branding.logo.file_path`
- Font → Otomatis dari `branding.default_font` (opsional)

---

## Font System

### Font Loading (Google Fonts)

Sistem load 5 font dari Google Fonts dengan 3 weight (400, 700, 900):

**Daftar Font Tersedia:**
1. **Inter** (default)
2. **Poppins**
3. **Roboto**
4. **Montserrat**
5. **Open Sans**

**Font Weights:**
- `400` - Normal
- `700` - Bold
- `900` - Black

**Lokasi:** [resources/views/layouts/dashboard.blade.php](resources/views/layouts/dashboard.blade.php) line 10
```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Poppins:wght@400;700;900&family=Roboto:wght@400;700;900&family=Montserrat:wght@400;700;900&family=Open+Sans:wght@400;700;900&display=swap" rel="stylesheet">
```

### Font Properties di Template

**A. Default Settings (Template JSON):**
```json
"defaultSettings": {
  "headlineFont": "Inter",
  "headlineFontSizeBase": 56,
  "headlineFontWeight": "700",
  "subheadlineFontSize": 24,
  "subheadlineFontWeight": "400"
}
```

**B. Font Customization (Visual Editor):**

User dapat mengubah via Customization Panel:
- **Font Family** - Pilih dari 5 font tersedia (Inter, Poppins, Roboto, Montserrat, Open Sans)
- **Font Size** - Headline (12-120px), Subheadline (12-60px)
- **Font Weight** - 400 (Normal), 700 (Bold), 900 (Black)
- **Font Color** - Color picker untuk headline dan subheadline

**C. Frontend Implementation:**

Font weight harus di-convert ke numeric untuk Fabric.js:

**Lokasi:** [resources/views/components/visual-editor.blade.php](resources/views/components/visual-editor.blade.php) line 946-950
```javascript
const convertFontWeight = (weight) => {
    if (weight === 'normal') return 400;
    if (weight === 'bold') return 700;
    return parseInt(weight) || 400;
};
```

**Applied saat render:** Line 966
```javascript
fontWeight: fontWeightValue,  // Numeric value (400/700/900)
```

**Applied saat update:** Line 552
```javascript
const weight = value === 'normal' ? 400 : (value === 'bold' ? 700 : parseInt(value) || 400);
obj.set('fontWeight', weight);
```

**PENTING:**
- Fabric.js hanya menerima numeric fontWeight, bukan CSS keywords
- Semua font harus di-load dari Google Fonts dengan weight yang diperlukan
- Jika weight tidak tersedia di font file, akan fallback ke closest available weight

---

### 3. Layout Rules

Object yang mendefinisikan semua elemen visual dalam template.

#### Tipe Elemen

**a) Rectangle (`"type": "rect"`)**
Untuk background, overlay, garis, footer, dll.

```json
"background": {
  "type": "rect",
  "position": "fullscreen",
  "fill": "{{backgroundColor}}",
  "zIndex": 1
}
```

**b) Text (`"type": "text"`)**
Untuk headline, subheadline, footer text, dll.

```json
"headline": {
  "type": "text",
  "position": "custom",
  "left": "5%",
  "top": "22%",
  "width": "90%",
  "fontSize": "{{headlineFontSizeBase}}",
  "fontFamily": "{{headlineFont}}",
  "fill": "{{headlineColor}}",
  "fontWeight": "bold",
  "textAlign": "left",
  "lineHeight": 1.15,
  "text": "{{headline}}",
  "zIndex": 3
}
```

**c) Image (`"type": "image"`)**
Untuk featured image.

```json
"image": {
  "type": "image",
  "position": "custom",
  "left": "5%",
  "top": "45%",
  "width": "90%",
  "heightPercent": 35,
  "src": "{{featuredImage}}",
  "zIndex": 2
}
```

**d) Logo (`"type": "logo"`)**
Untuk brand logo user.

```json
"logo": {
  "type": "logo",
  "position": "top-right",
  "marginPercent": 3,
  "maxWidthPercent": 15,
  "zIndex": 5
}
```

#### Position Options

**Preset Positions:**
- `"fullscreen"` - Memenuhi seluruh canvas
- `"top"` - Di bagian atas
- `"bottom"` - Di bagian bawah
- `"center"` - Di tengah
- `"top-left"`, `"top-right"`, `"bottom-left"`, `"bottom-right"` - Untuk logo

**Custom Position:**
```json
"position": "custom",
"left": "5%",
"top": "22%",
"width": "90%",
"heightPercent": 35
```

- `left`, `right`, `top`, `bottom` - String dengan % atau px
- `width`, `height` - String dengan % atau px
- `heightPercent`, `widthPercent` - Number (akan dikonversi ke %)

---

### Spacing & Padding Guidelines

**PENTING:** Untuk mencegah text "nabrak" tepi canvas, ikuti aturan spacing berikut:

#### Text Elements (Headline, Subheadline)

**Formula:** `left + width ≤ 95%` (sisakan minimal 5% untuk right padding)

**Recommended Values:**
- **Left padding:** `5%` (standard)
- **Width:** `85%` (balanced) atau `90%` (maksimal)
- **Right padding:** `10%` (balanced) atau `5%` (minimal)

**Contoh BENAR:**
```json
"headline": {
  "type": "text",
  "position": "custom",
  "left": "5%",
  "width": "85%",    // 5% + 85% = 90%, sisa 10% right padding ✅
  "textAlign": "left"
}
```

**Contoh SALAH:**
```json
"headline": {
  "type": "text",
  "position": "custom",
  "left": "5%",
  "width": "95%",    // 5% + 95% = 100%, text akan nabrak kanan ❌
  "textAlign": "left"
}
```

#### Image Elements

Images boleh fullwidth (100%) atau dengan padding minimal:

```json
"image": {
  "type": "image",
  "position": "custom",
  "left": "0%",
  "width": "100%",   // OK untuk image, tidak ada text yang overflow
  "heightPercent": 49
}
```

#### Logo Elements

Logo menggunakan `marginPercent` untuk spacing:

```json
"logo": {
  "type": "logo",
  "position": "top-right",
  "marginPercent": 3,        // 3% dari edge
  "maxWidthPercent": 20
}
```

**Best Practices:**
- Headline dengan `textAlign: "left"` → gunakan `width: 85%`
- Headline dengan `textAlign: "center"` → boleh `width: 90%`
- Headline dengan `textAlign: "right"` → gunakan `width: 85%`, `left: 10%`
- Selalu test di visual editor untuk verifikasi tidak ada overflow

---

#### Z-Index Layering

**Aturan:**
- Background: `zIndex: 1`
- Image/Overlay: `zIndex: 2`
- Text: `zIndex: 3-4`
- Logo/Footer: `zIndex: 5`

Semakin tinggi z-index, semakin di depan.

### 4. Template Variables

Variables yang bisa digunakan di `layoutRules` dengan format `{{variableName}}`:

**Dari Default Settings:**
- `{{backgroundColor}}`, `{{accentLineColor}}`, dll.
- Semua settings bisa dijadikan variable

**Dari Content DNA:**
- `{{headline}}` - Headline utama
- `{{subheadline}}` - Sub-headline
- `{{featuredImage}}` - URL gambar utama

**Dari Branding:**
- `{{websiteUrl}}` - URL website dari branding
- Logo otomatis load dari branding (tidak perlu variable)

## Contoh Lengkap: News Template 1

```json
{
  "id": "news-template-1",
  "name": "News Template 1",
  "category": "news",
  "description": "Layout berita dengan background brand color, accent line, logo, headline, image, dan footer dengan website URL",
  "supportedRatios": ["1:1", "4:5", "16:9", "9:16"],
  "defaultSettings": {
    "backgroundColor": "#3b82f6",
    "accentLineColor": "#3b82f6",
    "accentLineHeightPx": 4,
    "headlineFont": "Inter",
    "headlineFontSizeBase": 48,
    "headlineColor": "#ffffff",
    "textAlign": "left",
    "logoPosition": "top-right",
    "logoMaxWidthPercent": 15,
    "footerBackgroundColor": "#1e40af",
    "footerTextColor": "#ffffff",
    "footerFontSize": 14,
    "footerPaddingPercent": 2
  },
  "layoutRules": {
    "background": {
      "type": "rect",
      "position": "fullscreen",
      "fill": "{{backgroundColor}}",
      "zIndex": 1
    },
    "logo": {
      "type": "logo",
      "position": "{{logoPosition}}",
      "marginPercent": 3,
      "maxWidthPercent": "{{logoMaxWidthPercent}}",
      "zIndex": 5
    },
    "accentLine": {
      "type": "rect",
      "position": "custom",
      "left": "5%",
      "top": "18%",
      "width": "25%",
      "heightPx": "{{accentLineHeightPx}}",
      "fill": "{{accentLineColor}}",
      "zIndex": 2
    },
    "headline": {
      "type": "text",
      "position": "custom",
      "left": "5%",
      "top": "22%",
      "width": "90%",
      "fontSize": "{{headlineFontSizeBase}}",
      "fontFamily": "{{headlineFont}}",
      "fill": "{{headlineColor}}",
      "fontWeight": "bold",
      "textAlign": "{{textAlign}}",
      "lineHeight": 1.15,
      "text": "{{headline}}",
      "zIndex": 3
    },
    "image": {
      "type": "image",
      "position": "custom",
      "left": "5%",
      "top": "45%",
      "width": "90%",
      "heightPercent": 35,
      "src": "{{featuredImage}}",
      "zIndex": 2
    },
    "footer": {
      "type": "rect",
      "position": "bottom",
      "heightPercent": 6,
      "fill": "{{footerBackgroundColor}}",
      "zIndex": 4
    },
    "footerText": {
      "type": "text",
      "position": "custom",
      "right": "3%",
      "bottom": "1.5%",
      "fontSize": "{{footerFontSize}}",
      "fontFamily": "Inter",
      "fill": "{{footerTextColor}}",
      "fontWeight": "normal",
      "textAlign": "right",
      "text": "{{websiteUrl}}",
      "zIndex": 5
    }
  }
}
```

## Langkah-langkah Membuat Template Baru

### 1. Rencanakan Layout

Gambar wireframe template di kertas/Figma:
- Tentukan posisi setiap elemen (background, logo, headline, image, footer)
- Ukur persentase/pixel untuk setiap elemen
- Tentukan warna default dan mana yang dinamis

### 2. Buat ID Unik

Format: `category-name-number`
Contoh: `news-template-1`, `social-quote-2`

### 3. Definisikan Default Settings

List semua variable yang bisa di-customize:
- Warna (background, accent, text)
- Font (family, size, weight)
- Ukuran (logo, padding, margin)
- Posisi (logo position)

### 4. Buat Layout Rules

Untuk setiap elemen visual:

**a) Background/Overlay:**
```json
"background": {
  "type": "rect",
  "position": "fullscreen",
  "fill": "{{backgroundColor}}",
  "zIndex": 1
}
```

**b) Logo:**
```json
"logo": {
  "type": "logo",
  "position": "top-right",
  "marginPercent": 3,
  "maxWidthPercent": 15,
  "zIndex": 5
}
```

**c) Accent Line/Separator:**
```json
"accentLine": {
  "type": "rect",
  "position": "custom",
  "left": "5%",
  "top": "18%",
  "width": "25%",
  "heightPx": 4,
  "fill": "{{accentLineColor}}",
  "zIndex": 2
}
```

**d) Headline:**
```json
"headline": {
  "type": "text",
  "position": "custom",
  "left": "5%",
  "top": "22%",
  "width": "90%",
  "fontSize": "{{headlineFontSizeBase}}",
  "fontFamily": "{{headlineFont}}",
  "fill": "{{headlineColor}}",
  "fontWeight": "bold",
  "textAlign": "left",
  "lineHeight": 1.15,
  "text": "{{headline}}",
  "zIndex": 3
}
```

**e) Featured Image:**
```json
"image": {
  "type": "image",
  "position": "custom",
  "left": "5%",
  "top": "45%",
  "width": "90%",
  "heightPercent": 35,
  "src": "{{featuredImage}}",
  "zIndex": 2
}
```

**f) Footer:**
```json
"footer": {
  "type": "rect",
  "position": "bottom",
  "heightPercent": 6,
  "fill": "{{footerBackgroundColor}}",
  "zIndex": 4
},
"footerText": {
  "type": "text",
  "position": "custom",
  "right": "3%",
  "bottom": "1.5%",
  "fontSize": 14,
  "fontFamily": "Inter",
  "fill": "{{footerTextColor}}",
  "fontWeight": "normal",
  "textAlign": "right",
  "text": "{{websiteUrl}}",
  "zIndex": 5
}
```

### 5. Test di Visual Editor

1. Buka visual editor di browser
2. Pilih asset yang ingin di-edit
3. Pilih template baru dari dropdown
4. Cek layout di semua aspect ratio (1:1, 4:5, 16:9, 9:16)
5. Test dengan branding berbeda
6. Test dengan headline panjang dan pendek

### 6. Validasi JSON

Sebelum commit, pastikan JSON valid:
```bash
cat storage/app/templates/universal-templates.json | python -m json.tool
```

Atau gunakan online JSON validator.

## Tips & Best Practices

### 1. Responsive Design

- Gunakan persentase (`%`) untuk lebar/tinggi agar responsive
- Gunakan pixel (`px`) hanya untuk elemen kecil (garis accent, border)
- Test di semua ratio (1:1, 4:5, 16:9, 9:16)

### 2. Typography

- Font size base untuk headline: 40-60px
- Font size untuk body text: 14-18px
- Line height: 1.1-1.2 untuk headline, 1.4-1.6 untuk body
- Gunakan font yang aman: Inter, Roboto, Arial

### 3. Color Contrast

- Background gelap → Text putih
- Background terang → Text hitam/abu gelap
- Pastikan WCAG AA compliance (contrast ratio min 4.5:1)

### 4. Z-Index Management

Layer dari belakang ke depan:
1. Background (z-index: 1)
2. Image/Overlay (z-index: 2)
3. Text content (z-index: 3-4)
4. Logo/UI elements (z-index: 5)

### 5. Brand Integration

**Aturan Warna:**

✅ **BOLEH dijadikan dinamis:**
- `accentLineColor` - Garis/separator (mengikuti brand primary)
- `footerBackgroundColor` - Background footer (mengikuti brand primary)
- `overlayColor` - Overlay gradient (mengikuti brand primary, untuk news-overlay template)

❌ **JANGAN dijadikan dinamis:**
- `backgroundColor` - Background utama (HARUS tetap terang #f5f5f5)
- `headlineColor` - Warna text headline (HARUS kontras dengan background)
- `footerTextColor` - Warna text footer (HARUS kontras dengan footer background)

**Alasan:** Background harus tetap terang (#f5f5f5) agar accent line yang menggunakan warna branding terlihat jelas. Jika background juga mengikuti branding, accent line tidak akan terlihat.

**Logo & Font:**
- Logo otomatis load dari `branding.logo.file_path`
- Font otomatis dari `branding.default_font` (jika ada)
- Website URL otomatis dari `branding.website.url` (relasi ke Website model)

### 6. Performance

- Jangan gunakan terlalu banyak elemen (max 10 objects per template)
- Hindari nested positioning yang kompleks
- Keep it simple

## Troubleshooting

### Template tidak muncul di dropdown
- Cek syntax JSON valid
- Cek `id` unik (tidak duplikat)
- Cek `supportedRatios` include ratio yang dipilih

### Layout tidak sesuai
- Cek posisi `left`, `top`, `width`, `height`
- Pastikan z-index benar
- Test di ratio berbeda

### Warna tidak dinamis
- Pastikan setting name mengikuti convention (`xxxColor`)
- Cek di `TemplateLayoutEngine.php` apakah variable di-override

### Text terpotong
- Increase `width` atau reduce `fontSize`
- Adjust `lineHeight`
- Gunakan `textAlign` yang sesuai

## Backend Logic: Color Override System

### TemplateLayoutEngine.php

File ini mengatur bagaimana warna dari branding diterapkan ke template.

**Lokasi:** `/app/Services/TemplateLayoutEngine.php`

**Line 26-54:** Color override logic
```php
// Merge default settings with custom
$settings = array_merge($template['defaultSettings'] ?? [], $customSettings);

// CRITICAL: Force backgroundColor to use template default (never from branding or custom)
// This ensures accent line is always visible against light background
if (isset($template['defaultSettings']['backgroundColor'])) {
    $settings['backgroundColor'] = $template['defaultSettings']['backgroundColor'];
}

// Apply branding if available
if ($branding) {
    // Logo
    if (empty($settings['brandLogo']) && $branding->logo) {
        $settings['brandLogo'] = asset('storage/' . $branding->logo->file_path);
    }

    // Store branding colors (for reference)
    $settings['primaryColor'] = $branding->primary_color;
    $settings['secondaryColor'] = $branding->secondary_color;
    $settings['defaultFont'] = $branding->default_font;

    // DYNAMIC COLOR OVERRIDES (if not manually customized)
    // Only override accent line and footer with brand colors
    if (!isset($customSettings['accentLineColor'])) {
        $settings['accentLineColor'] = $branding->primary_color;
    }
    if (!isset($customSettings['footerBackgroundColor'])) {
        $settings['footerBackgroundColor'] = $branding->primary_color;
    }
}
```

**Urutan Prioritas Warna:**
1. **Template Default** (backgroundColor) - TERTINGGI, tidak bisa dioverride
2. **Branding Dynamic** (accentLineColor, footerBackgroundColor) - Override jika tidak ada custom
3. **User Custom** (dari Customization panel) - User bisa override manual

### VisualController.php

File ini menyiapkan data template sebelum dikirim ke frontend.

**Lokasi:** `/app/Http/Controllers/VisualController.php`

**Line 88-105:** Template variable preparation
```php
// Prepare template variables
$customSettings['featuredImage'] = $asset->visual_metadata['featured_image'] ?? ($dna->featured_image_url ?? asset('images/placeholder.jpg'));
$customSettings['headline'] = $asset->visual_metadata['headline'] ?? ($dna->headline ?? $asset->post->title);
$customSettings['subheadline'] = $asset->visual_metadata['subheadline'] ?? ($dna->subheadline ?? '');

// Brand logo from request (manual picker) or from branding
if (!empty($validated['brandLogo'])) {
    $customSettings['brandLogo'] = $validated['brandLogo'];
} elseif ($branding && $branding->logo) {
    $customSettings['brandLogo'] = asset('storage/' . $branding->logo->file_path);
}

// Website URL from branding (for footer in News Template 1)
// Priority: 1) Branding website URL, 2) Fallback to webane.com
if ($branding && $branding->website && !empty($branding->website->url)) {
    // Extract domain from URL (remove https://)
    $url = parse_url($branding->website->url, PHP_URL_HOST);
    $customSettings['websiteUrl'] = $url ?? $branding->website->url;
} else {
    $customSettings['websiteUrl'] = 'webane.com'; // Fallback if no branding
}
```

### visual-editor.blade.php

File ini merender template di frontend (Fabric.js canvas).

**Lokasi:** `/resources/views/components/visual-editor.blade.php`

**Line 997-1020:** Rect rendering logic
```javascript
} else if (objDef.type === 'rect') {
    // CRITICAL: Only use overlayColor for actual overlay rects (news-overlay template)
    // For other rects (background, accent line, footer), use color from backend (objDef.fill)
    const isOverlay = objDef.objectType === 'overlay';

    const rect = new fabric.Rect({
        left: objDef.left ?? 0,
        top: objDef.top,
        width: objDef.width ?? (this.fabricCanvas ? (this.fabricCanvas.getWidth() / (this.fabricCanvas.getZoom() || 1)) : 1080),
        height: objDef.height,
        fill: isOverlay ? (this.customSettings.overlayColor || objDef.fill) : objDef.fill,
        opacity: isOverlay ? (this.customSettings.overlayOpacity ?? objDef.opacity ?? 1) : (objDef.opacity ?? 1),
        selectable: false,
        evented: false,
        objectType: objDef.objectType || 'overlay'
    });
```

**Penting:** Frontend membedakan antara:
- **Overlay rect** (`objectType === 'overlay'`) - Gunakan `customSettings.overlayColor`
- **Rect lainnya** (background, accent, footer) - Gunakan `objDef.fill` dari backend

**Line 1021-1037:** Text template variable replacement
```javascript
} else if (objDef.type === 'text') {
    // Replace template variables
    let text = objDef.text || '';
    let isHeadline = false;
    let isSubheadline = false;

    if (text.includes('{{headline}}')) {
        text = this.editableHeadline;
        isHeadline = true;
    } else if (text.includes('{{subheadline}}')) {
        text = this.editableSubheadline;
        isSubheadline = true;
    } else if (text.includes('{{websiteUrl}}')) {
        // Replace with actual website URL from settings
        text = this.customSettings.websiteUrl || 'webane.com';
        console.log('[TEXT RENDER] Footer websiteUrl:', text);
    }
```

**Template Variables yang Didukung:**
- `{{headline}}` - Judul artikel (editable)
- `{{subheadline}}` - Subjudul artikel (editable)
- `{{websiteUrl}}` - Domain website dari branding (auto dari `branding.website.url`)
- `{{featuredImage}}` - URL featured image (auto dari post)
- `{{brandLogo}}` - URL logo brand (auto dari branding)

## File Terkait

- Template JSON: `/storage/app/templates/universal-templates.json`
- Layout Engine: `/app/Services/TemplateLayoutEngine.php` (line 26-54 untuk color logic)
- Visual Controller: `/app/Http/Controllers/VisualController.php` (line 88-105 untuk template vars)
- Frontend Component: `/resources/views/components/visual-editor.blade.php` (line 997-1020 untuk rect rendering)

## Branding Dropdown Integration (Critical Pattern)

### Problem Overview

Ketika user mengubah branding preset dropdown di Visual Editor, template harus:
1. **Reload template dari backend** dengan branding baru
2. **Update warna dinamis** (accentLineColor, footerBackgroundColor) sesuai brand color
3. **Tetap mempertahankan warna tetap** (backgroundColor tetap #f5f5f5)
4. **Update logo dan websiteUrl** sesuai branding baru

### Required Implementation (3 Files)

#### 1. Frontend: visual-editor.blade.php

**A. Kirim branding_id ke backend saat loadTemplate()**

Line 460-466:
```javascript
body: JSON.stringify({
    template_id: templateId,
    size: this.currentRatio,
    brandLogo: this.brandLogo,
    branding_id: this.selectedBranding // CRITICAL: Send branding_id to backend
})
```

**B. Update selectedBranding SEBELUM reload template**

Line 566-578:
```javascript
async applyBranding(brandingId) {
    console.log('[APPLY BRANDING] Branding changed to:', brandingId);

    // Update selected branding FIRST before reloading template
    this.selectedBranding = brandingId;

    // CRITICAL: Reload template with new branding data from backend
    // This ensures backgroundColor stays #f5f5f5, accentLineColor/footerBackgroundColor get new brand colors
    if (this.currentTemplate) {
        this.templateChanged = true; // Force fresh render, ignore canvas_state
        await this.loadTemplate(this.currentTemplate);
    }
}
```

**Penting:**
- `this.selectedBranding = brandingId` HARUS dipanggil SEBELUM `loadTemplate()`
- `this.templateChanged = true` memaksa render fresh, ignore canvas_state lama
- JANGAN manual update warna di frontend, biarkan backend yang handle

#### 2. Backend: VisualController.php

**A. Tambahkan validation untuk branding_id**

Line 34-39:
```php
$validated = $request->validate([
    'template_id' => 'required|string',
    'size' => 'required|in:4:5,9:16,1:1,16:9,4:3',
    'brandLogo' => 'nullable|string',
    'branding_id' => 'nullable|exists:ane_user_brandings,id', // CRITICAL
]);
```

**B. Prioritaskan branding_id dari request (dropdown baru diubah)**

Line 70-96:
```php
// Get user branding from request (user just changed dropdown) or saved metadata
// Priority order:
// 1. branding_id from request (user just changed dropdown in visual editor) - HIGHEST PRIORITY
// 2. Saved branding_id from asset metadata (previous selection)
// 3. Auto-select branding for post's website (if post has website_id)
// 4. Fallback: Use first available branding for this user

$requestBrandingId = $validated['branding_id'] ?? null;
$savedBrandingId = $asset->visual_metadata['branding_id'] ?? null;

if ($requestBrandingId) {
    // User just changed branding dropdown - use request value
    $branding = UserBranding::with(['logo', 'website'])->find($requestBrandingId);
} elseif ($savedBrandingId) {
    // Use previously saved branding
    $branding = UserBranding::with(['logo', 'website'])->find($savedBrandingId);
} elseif ($asset->post->website_id) {
    // Auto-select branding for post's website
    $branding = UserBranding::where('user_id', auth()->id())
        ->where('website_id', $asset->post->website_id)
        ->with(['logo', 'website'])
        ->first();
} else {
    // CRITICAL: Always provide branding for templates that need it
    $branding = UserBranding::where('user_id', auth()->id())
        ->with(['logo', 'website'])
        ->first();
}
```

**Penting:**
- `$requestBrandingId` HARUS prioritas tertinggi (user baru ubah dropdown)
- SELALU eager load `->with(['logo', 'website'])` untuk avoid N+1 queries
- Fallback ke first available branding untuk template yang butuh branding (News Template 1)

#### 3. Backend: TemplateLayoutEngine.php (Sudah Benar, Jangan Diubah)

Line 26-60 sudah handle color override dengan benar:

```php
// CRITICAL: Force backgroundColor to use template default (never from branding or custom)
if (isset($template['defaultSettings']['backgroundColor'])) {
    $settings['backgroundColor'] = $template['defaultSettings']['backgroundColor'];
}

// Apply branding if available
if ($branding) {
    // Dynamic color overrides (if not manually customized)
    if (!isset($customSettings['accentLineColor'])) {
        $settings['accentLineColor'] = $branding->primary_color;
    }
    if (!isset($customSettings['footerBackgroundColor'])) {
        $settings['footerBackgroundColor'] = $branding->primary_color;
    }
}
```

### Testing Checklist

Untuk memastikan branding dropdown bekerja dengan benar:

- [ ] Buat 2 branding preset dengan warna berbeda (contoh: Hijau #3c864b dan Merah #ad2d1a)
- [ ] Load template dengan branding preset pertama (hijau)
- [ ] Ubah dropdown ke branding preset kedua (merah)
- [ ] **Expected:** Accent line dan footer background berubah ke merah, background tetap #f5f5f5
- [ ] **Expected:** Logo berubah ke logo branding kedua
- [ ] **Expected:** Footer text (websiteUrl) berubah ke domain branding kedua
- [ ] Ubah kembali ke branding pertama (hijau)
- [ ] **Expected:** Semua warna, logo, dan URL kembali ke branding pertama
- [ ] Ubah template ke News Overlay
- [ ] **Expected:** Branding tetap sama, template berubah
- [ ] Ubah kembali ke News Template 1
- [ ] **Expected:** Branding tetap sama, warna tetap sesuai branding terakhir dipilih

### Common Mistakes (JANGAN LAKUKAN INI)

❌ **SALAH:** Manual update warna di frontend `applyBranding()`
```javascript
// JANGAN SEPERTI INI
async applyBranding(brandingId) {
    const branding = this.brandings.find(b => b.id == brandingId);
    this.customSettings.accentLineColor = branding.primary_color; // ❌ SALAH
    this.renderCanvas(); // ❌ Tidak reload dari backend
}
```

✅ **BENAR:** Reload template dari backend
```javascript
// YANG BENAR
async applyBranding(brandingId) {
    this.selectedBranding = brandingId;
    this.templateChanged = true;
    await this.loadTemplate(this.currentTemplate); // ✅ Reload dari backend
}
```

❌ **SALAH:** Tidak kirim branding_id ke backend
```javascript
body: JSON.stringify({
    template_id: templateId,
    size: this.currentRatio
    // ❌ MISSING: branding_id
})
```

✅ **BENAR:** Kirim branding_id
```javascript
body: JSON.stringify({
    template_id: templateId,
    size: this.currentRatio,
    branding_id: this.selectedBranding // ✅ BENAR
})
```

❌ **SALAH:** Backend tidak prioritaskan branding_id dari request
```php
// ❌ SALAH: Hanya pakai saved metadata
$branding = UserBranding::find($asset->visual_metadata['branding_id']);
```

✅ **BENAR:** Prioritaskan request (user baru ubah dropdown)
```php
// ✅ BENAR: Request > Saved > Auto-select > Fallback
if ($requestBrandingId) {
    $branding = UserBranding::with(['logo', 'website'])->find($requestBrandingId);
} elseif ($savedBrandingId) {
    // ...
}
```

## Instagram-Style Crop Pattern (Featured Image)

### Overview

Pattern terbaru untuk featured image dengan crop editor ala Instagram:
- Image bisa di-drag dan di-scale **di dalam frame fixed**
- Frame tetap di posisi yang sama (tidak ikut geser)
- User bisa adjust posisi image untuk crop yang diinginkan
- Image maintain aspect ratio (tidak distort)

### Implementation Pattern

#### 1. Template JSON

Featured image harus punya dimensi exact dan zIndex yang benar:

```json
"image": {
  "type": "image",
  "position": "custom",
  "left": "0%",
  "top": "45%",
  "width": "100%",
  "heightPercent": 49,
  "src": "{{featuredImage}}",
  "zIndex": 2
}
```

**CRITICAL:**
- `left: "0%"`, `width: "100%"` untuk edge-to-edge
- `heightPercent` calculate dari posisi elements (45% start, 94% footer start = 49% height)
- `zIndex` harus lebih tinggi dari background (0.1) tapi lebih rendah dari text (4)

#### 2. Frontend Rendering (renderObject)

Location: `visual-editor.blade.php` line ~807-845

```javascript
// Featured image: use EXACT dimensions from backend (already calculated)
targetWidth = objDef.width;
targetHeight = objDef.height;
// COVER mode: scale proportionally to fill frame (keep aspect ratio)
scale = Math.max(targetWidth / img.width, targetHeight / img.height);

// Scale image to cover frame (keep aspect ratio)
img.set({
    left: objDef.left + targetWidth / 2,
    top: objDef.top + targetHeight / 2,
    originX: 'center',
    originY: 'center',
    scaleX: scale,
    scaleY: scale,
    selectable: true,
    evented: true,
    hasControls: true,
    hasBorders: true,
    lockScalingFlip: true,
    lockRotation: true, // Lock rotation untuk crop editor
    objectType: 'featuredImage',
    zIndex: objDef.zIndex || 2, // Save zIndex for restore
    cornerStyle: 'circle',
    cornerSize: 16,
    cornerColor: '#3b82f6',
    borderColor: '#3b82f6',
    transparentCorners: false
});

// ClipPath dengan absolutePositioned: TRUE
// Ini bikin clipPath tidak ikut transform image
const clipRect = new fabric.Rect({
    left: objDef.left,
    top: objDef.top,
    width: targetWidth,
    height: targetHeight,
    absolutePositioned: true, // CRITICAL: clipPath fixed, not follow image transform
    fill: 'transparent',
    stroke: 'transparent'
});

img.clipPath = clipRect;
```

**Key Points:**
- `Math.max()` = cover mode (fill frame, keep aspect ratio)
- Image positioned at center of frame (`left + targetWidth/2`, `top + targetHeight/2`)
- `originX: 'center'`, `originY: 'center'` untuk center-based positioning
- `lockRotation: true` agar user tidak bisa rotate (crop saja)
- **`absolutePositioned: true`** pada clipPath = frame tetap fixed, image geser di dalamnya

#### 3. Save/Restore (Canvas State Persistence)

**A. Save zIndex Property**

Location: `visual-editor.blade.php` line ~1165-1170

```javascript
// CRITICAL: ClipPath properties for Instagram-style crop
'clipPath',
'absolutePositioned',
// Z-index for correct layering on restore
'zIndex'
```

**CRITICAL:** Harus include `'zIndex'` di `toJSON()` agar tersimpan.

**B. Avoid zIndex: 0 Issue**

JavaScript treats `0` as falsy, jadi background dengan `zIndex: 0` jadi `zIndex: 999` saat restore.

**Solution:** Gunakan `zIndex: 0.1` untuk background (bukan 0).

```json
"background": {
  "type": "rect",
  "position": "fullscreen",
  "fill": "{{backgroundColor}}",
  "zIndex": 0.1,  // CRITICAL: Not 0 (treated as falsy)
  "objectType": "background"
}
```

**C. Recreate ClipPath on Restore**

Location: `visual-editor.blade.php` line ~640-654

```javascript
// CRITICAL: Recreate clipPath with absolutePositioned
// fabric.js doesn't restore absolutePositioned correctly
if (obj.clipPath && obj.clipPath.left !== undefined) {
    const clipRect = new fabric.Rect({
        left: obj.clipPath.left,
        top: obj.clipPath.top,
        width: obj.clipPath.width,
        height: obj.clipPath.height,
        absolutePositioned: true, // FORCE absolute positioning
        fill: 'transparent',
        stroke: 'transparent'
    });
    obj.clipPath = clipRect;
    console.log('[RESTORE] Recreated absolutePositioned clipPath');
}
```

**Why:** fabric.js tidak serialize/restore `absolutePositioned` property dengan benar. Harus manual recreate.

**D. Sort Objects by zIndex**

Location: `visual-editor.blade.php` line ~668-672

```javascript
const sortedObjects = objects.slice().sort((a, b) => {
    const aZ = a.zIndex !== undefined ? a.zIndex : 999;
    const bZ = b.zIndex !== undefined ? b.zIndex : 999;
    return aZ - bZ;
});
```

**CRITICAL:**
- Check `!== undefined` (NOT `|| 999`)
- Reason: `zIndex: 0` would become 999 with `||` operator

#### 4. Must Have: objectType for All Rects

Location: Template JSON

```json
"background": {
  "type": "rect",
  "objectType": "background",  // CRITICAL: Required
  "zIndex": 0.1
},
"accentLine": {
  "type": "rect",
  "objectType": "accentLine",  // CRITICAL: Required
  "zIndex": 4
},
"footer": {
  "type": "rect",
  "objectType": "footer",  // CRITICAL: Required
  "zIndex": 4
}
```

**Why:** Frontend `renderObject` defaults to `objectType: 'overlay'` if not provided:

```javascript
objectType: objDef.objectType || 'overlay'  // Line 937
```

Without `objectType`, semua rect jadi overlay dan bisa corrupt z-index.

### Testing Checklist

- [ ] **Create:** Upload featured image → Image muncul dengan benar (tidak distort)
- [ ] **Crop:** Drag image → Frame tetap fixed, image bergeser di dalamnya
- [ ] **Scale:** Scale image dengan corner controls → Frame tetap fixed
- [ ] **Save:** Save visual → Image tersimpan dengan crop position
- [ ] **Restore:** Close dan edit lagi → Image muncul dengan crop position yang sama
- [ ] **Z-Index:** Image di atas background, di bawah text/logo
- [ ] **Multiple Templates:** Ganti template → Image tidak corrupt, z-index tetap benar

### Common Issues & Solutions

#### Issue 1: Image tidak muncul setelah save/restore

**Symptom:** Canvas kosong atau hanya background + text, image hilang

**Root Cause:** ClipPath tidak restore dengan benar (missing `absolutePositioned: true`)

**Solution:** Recreate clipPath di `restoreCanvasState()` (line 640-654)

#### Issue 2: Background zIndex jadi 999

**Symptom:** Background render di paling depan, menutupi semua

**Root Cause:** `zIndex: 0` treated as falsy → `zIndex || 999` = 999

**Solution:**
- Gunakan `zIndex: 0.1` di template JSON (bukan 0)
- Check `!== undefined` saat sort (bukan `|| 999`)

#### Issue 3: Rect jadi overlay (objectType salah)

**Symptom:** Background/accent/footer punya `objectType: 'overlay'`

**Root Cause:** Template JSON tidak define `objectType`, frontend default ke `'overlay'`

**Solution:** Tambahkan `"objectType": "background"` dll. ke semua rect di template JSON

#### Issue 4: Image terdistort (stretched)

**Symptom:** Image tidak maintain aspect ratio

**Root Cause:** Pakai `Math.min()` (contain mode) atau manual scaleX/scaleY berbeda

**Solution:** Pakai `Math.max()` (cover mode) dengan scaleX = scaleY

### Pattern Summary

```
┌─────────────────────────────────┐
│  Fixed ClipPath Frame           │  ← absolutePositioned: true
│  ┌───────────────────────────┐  │
│  │                           │  │
│  │   Image (draggable)       │  │  ← User bisa drag/scale
│  │   Math.max (cover mode)   │  │  ← Aspect ratio maintained
│  │                           │  │
│  └───────────────────────────┘  │
└─────────────────────────────────┘
```

**Key Concept:** Frame tetap, image geser di dalamnya (seperti Instagram crop editor).

## Changelog

**v1.4.0 (2025-12-23)**
- ✅ **NEW:** Instagram-Style Crop Pattern untuk featured image
  - Image bisa di-drag/scale di dalam frame fixed dengan clipPath absolutePositioned
  - Aspect ratio maintained dengan Math.max (cover mode)
  - ClipPath recreate on restore untuk fix absolutePositioned property loss
  - Files: visual-editor.blade.php (line 807-845 render, 640-654 restore, 668-672 sort)
- ✅ **CRITICAL FIX:** Background zIndex 0 issue (treated as falsy)
  - Solution: Use zIndex: 0.1 in template JSON, check !== undefined in sort
- ✅ **CRITICAL FIX:** objectType required for all rects
  - Without objectType, rects default to 'overlay' and corrupt z-index
  - Solution: Add "objectType": "background" / "accentLine" / "footer" to template JSON
- ✅ Added comprehensive testing checklist for crop editor
- ✅ Added common issues & solutions section

**v1.3.0 (2025-12-23)**
- ✅ **CRITICAL FIX:** Branding dropdown now properly updates template colors
  - Before: Changing branding preset didn't update colors (branding_id not sent to backend)
  - After: Frontend sends branding_id in request, backend prioritizes it, colors update correctly
  - Files changed: visual-editor.blade.php (line 465, 570), VisualController.php (line 38, 76-96)
- ✅ Added comprehensive "Branding Dropdown Integration" section to SOP
  - Documents required implementation in 3 files (frontend, backend controller, layout engine)
  - Includes testing checklist for branding dropdown
  - Lists common mistakes to avoid
  - Shows correct vs incorrect implementation patterns

**v1.2.0 (2025-12-23)**
- ✅ Fixed websiteUrl to properly load from `branding.website.url` (relasi ke Website model)
- ✅ Added eager loading for `website` relation in VisualController
- ✅ Added text template variable replacement for `{{websiteUrl}}` in visual-editor.blade.php
- ✅ Documented all supported template variables (headline, subheadline, websiteUrl, featuredImage, brandLogo)
- ✅ Fixed footer text to parse domain only (without https://) using `parse_url()`
- ✅ **CRITICAL FIX:** Added fallback to use first available branding if asset has no branding_id/website_id
  - Before: Template failed to load branding if asset.visual_metadata['branding_id'] was null
  - After: Auto-selects first UserBranding for current user as fallback
  - Impact: Footer text ({{websiteUrl}}) now always shows domain instead of "webane.com" fallback

**v1.1.0 (2025-12-23)**
- ✅ Added comprehensive branding integration documentation
- ✅ Documented color override system (TETAP vs DINAMIS)
- ✅ Added backend logic reference (TemplateLayoutEngine.php, VisualController.php)
- ✅ Added frontend rendering logic (visual-editor.blade.php)
- ✅ Fixed backgroundColor to always use template default (#f5f5f5)
- ✅ Clarified which colors follow branding (accentLineColor, footerBackgroundColor)

**v1.0.0 (2025-01-15)**
- Initial SOP documentation
- News Template 1 example
- Complete property reference
