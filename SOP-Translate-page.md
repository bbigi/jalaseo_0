## SOP: Menerapkan Terjemahan Halaman

Tujuan: memastikan setiap halaman bisa tampil sesuai bahasa user (locale singkat, mis. `en`, `id`) dengan langkah konsisten, cepat, dan minim salah.

### 1) Persiapan Locale
- Standar simpan locale di DB: kode pendek (mis. `en`, `id`).
- Middleware `ApplyUserPreferences` sudah set `app()->setLocale($user->locale)`.
- Form profile:
  - Input tampilan memakai nama bahasa, nilai hidden simpan kode pendek.
  - Validation menerima kode panjang/nama, tetapi disimpan sebagai kode pendek.

### 2) Struktur Kamus
- `resources/lang/{locale}/common.php` → kata/aksi umum (Download, Edit, Delete, Save, Actions, dsb).
- File per domain/halaman: mis. `ai-templates.php`, `assets.php`, `profile.php`, dst. Isi khusus teks halaman tersebut.
- (Opsional) JSON global `resources/lang/{locale}.json` untuk kata-kata pendek lintas halaman.
- Default fallback: `en`.

### 3) Cara Menandai Teks di View
- Sebelum membuat kunci baru, cek dulu apakah kata/aksi sudah ada di `common.php` atau file domain lain (reuse dulu).
- Ganti teks hardcode dengan helper:
  - `__('common.download')`, `__('ai-templates.title')`, `trans_choice('ai-templates.subtitle_count', $count, ['count' => $count])`.
- Hindari merangkai string manual; lebih baik gunakan placeholder:
  - `__('ai-templates.subtitle_count', ['count' => $count])` atau `trans_choice` bila perlu plural.
- Untuk tombol/link umum, gunakan kunci `common.*` agar konsisten lintas halaman.

### 4) Menambah Kamus
- Tambah kunci EN dan ID secara berdampingan:
  - `resources/lang/en/{file}.php`
  - `resources/lang/id/{file}.php`
- Pastikan kunci sama, isi terjemahan sesuai.

### 5) Sumber Data Bahasa & Negara
- Gunakan helper yang ada: `App\Helpers\CountryHelper::getLanguages()` dan `getCountries()`.
- Tampilkan nama/flag di UI, simpan kode pendek (bahasa: 2 huruf; negara: kode ISO 2).

### 6) Checklist Uji
- Set locale user ke ID, reload halaman: semua label mengacu ke file `id`.
- Set locale ke EN: kembali ke teks Inggris.
- Pastikan tidak ada teks hardcode tersisa (cari dengan `rg "Your text"`).

### 7) Pola Cepat Menerjemahkan Halaman Baru
1. Identifikasi teks hardcode di view/controller.
2. Cek `common.php` dan file domain lain: reuse kunci jika sama.
3. Jika belum ada, tambah kunci di file lang domain (atau `common` jika umum).
4. Ganti teks di view dengan `__()` / `trans_choice()`.
5. Uji dengan locale `id` dan `en`.
6. Bila ada kata umum berulang, pindah ke/pakai `common.php`.

### 8) Catatan
- Simpan kode pendek di DB; hindari kode panjang untuk mengurangi variasi folder.
- Jika butuh fallback JSON untuk kata-kata singkat, isi `resources/lang/{locale}.json`, tetapi tetap utamakan file PHP per domain untuk struktur yang rapi.
