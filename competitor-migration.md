# Competitor Intelligence Migration (Legacy ➜ Laravel)

## Scope
- Modul **Competitors** berbasis website seperti di `anewp` lama.
- Endpoint utama: `/competitors` (bukan per keyword session).
- Analisis berjalan dengan job + polling status (mirip legacy).

## Implementasi Saat Ini (Laravel)
- Tables: `ane_competitor_profiles`, `ane_competitor_analysis_sessions`, `ane_competitor_keywords`, `ane_competitor_raw` + views `view_competitors_with_status` & `view_top_competitors`.
- Model:
  - `App\Models\CompetitorProfile`
  - `App\Models\CompetitorAnalysisSession`
  - `App\Models\CompetitorKeyword`
  - `App\Models\CompetitorRaw`
- Controller: `App\Http\Controllers\CompetitorController`
  - `index`, `store`, `show`, `destroy`, `analyzeStart`, `analyzePoll`
- Routes:
  - `GET /competitors`
  - `POST /competitors`
  - `GET /competitors/view/{competitor}` + `/competitors/detail/{competitor}`
  - `DELETE /competitors/delete/{competitor}`
  - `POST /competitors/analyze/start`
  - `POST /competitors/analyze/poll/{sessionId}`
- Views:
  - `resources/views/competitors/index.blade.php`
  - `resources/views/competitors/show.blade.php`

## Alur UI (mirip legacy)
1) User buka `/competitors` → list competitor + modal “Add Competitor”.
2) Submit form → simpan `ane_competitor_profiles`.
3) Klik “Analyze” → create session + dispatch job.
4) Polling status → update UI → reload saat selesai.
5) Klik “View” → detail + keyword list.

## API yang Dipakai (MVP)
- `KeywordInsightService::getKeywordsFromUrl()` untuk keyword discovery.
  - Dipakai di `CompetitorAnalysisService`.
- (Optional) DataForSEO, Firecrawl, AIService bisa ditambah untuk hasil lebih kaya.

## Langkah Lanjut (Enhancement)
1) Tambah DataForSEO SERP task untuk posisi/ranking nyata.
2) Hitung `avg_rank`, `traffic_share`, `top_urls`.
3) AI insight & comparison (`comparison_data`).
4) UI chart distribusi (intent/type/format) + top pages.

## Catatan
- Domain disimpan tanpa `http/https` dan tanpa `www`.
- Ownership: user hanya bisa akses competitor yang dibuatnya (super_admin bypass).
