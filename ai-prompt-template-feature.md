# AI Prompt Template Feature (Guideline)

## Tujuan
- Memisahkan **AI Settings** (provider/model/size/tone/etc) dari **Prompt Template**.
- Prompt template bisa dikelola **super_admin** tanpa mengubah kode.
- User biasa hanya memilih template & setting, tidak mengedit prompt langsung.

## Prinsip Desain
1) **Prompt bukan bagian wajib user template**  
   User memilih AI Template (settings), prompt diambil dari library/preset.
2) **Prompt dapat di‑override oleh super_admin**  
   Prompt disimpan di DB agar bisa eksperimen.
3) **Backward compatible**  
   Jika prompt DB kosong, fallback ke preset internal.

## Struktur Data yang Disarankan (MVP)
### Tabel: `ane_ai_prompt_templates`
Fields:
- `id`
- `name` (string, unique) → “Blog Post - Comprehensive”, “News Article - Breaking”
- `category` (enum/string) → `blog|news|tutorial|listicle|review|custom`
- `description` (text)
- `prompt_template` (longText) → isi prompt
- `is_active` (bool)
- `created_by` (nullable, FK → user)
- `updated_by` (nullable, FK → user)
- `created_at`, `updated_at`

### Tabel: `ane_ai_generate_templates`
Tambahan field:
- `prompt_template_id` (nullable FK → `ane_ai_prompt_templates.id`)

## Alur Sistem (Target)
1) User membuat AI Template (settings only).  
2) Super_admin menyiapkan Prompt Templates di Admin panel.  
3) Pada proses generate:
   - Jika `prompt_template_id` ada → gunakan prompt DB.  
   - Jika tidak ada → fallback ke preset default internal.

## Backend Flow (Pseudo)
```
prompt = template.prompt_template_id
  ? PromptTemplate::find(id)->prompt_template
  : DefaultPromptLibrary::byArticleType(template.article_type)
```

## Admin UI (Super Admin Only)
Menu baru: **Admin → Prompt Templates**
Fitur:
- List, create, edit, clone, deactivate
- Preview prompt
- Test prompt (optional: dry‑run)

## Migrasi Bertahap
1) Tambah tabel prompt templates.
2) Tambah admin CRUD.
3) Update generate pipeline untuk lookup prompt DB.
4) (Opsional) Migrasi template lama untuk auto‑assign prompt.

## Catatan Implementasi
- Prompt template sebaiknya **versioned** (optional):
  - `version` int, `is_default`.
- Pertimbangkan **audit log** perubahan prompt untuk eksperimen.
- Gunakan caching ringan untuk prompt aktif.

## Status
Not implemented. Dokumentasi ini sebagai guideline untuk fase berikutnya.
