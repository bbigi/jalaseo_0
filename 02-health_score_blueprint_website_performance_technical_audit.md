# Health Score Blueprint (v1)

Tujuan: memberikan **skor kesehatan website yang objektif, actionable, dan ringan biaya**, berbasis data Google resmi (GSC + PageSpeed Insights), tanpa crawler berat.

Blueprint ini ditujukan agar **mudah dipahami & dieksekusi oleh GPT‑5.1 Codex** di stack Laravel, serta aman untuk scale.

---

## 1. Definisi Health Score

**Health Score** adalah skor komposit (0–100) yang merepresentasikan:
- Apakah halaman bisa diindeks dengan benar
- Apakah website cepat & stabil bagi user
- Apakah memenuhi Page Experience Google

Health Score ≠ SEO Rank
Health Score = *fondasi teknis agar SEO & content bisa menang*

---

## 2. Sumber Data (Resmi & Stabil)

### A. Google Search Console (WAJIB)
Digunakan untuk **real-user & indexing data**.

Data yang diambil:
- Coverage summary (Indexed / Not indexed)
- Page Experience
- Core Web Vitals (field data)
- Mobile Usability

Model:
- GSCConnection
- GSCPage
- GSCCoverageIssue

---

### B. PageSpeed Insights API (WAJIB)
Digunakan untuk **lab performance + root cause**.

Endpoint:
- https://www.googleapis.com/pagespeedonline/v5/runPagespeed

Data yang diambil:
- Performance score
- LCP, INP, CLS
- TTFB, FCP
- Lighthouse audits:
  - render-blocking-resources
  - unused-javascript
  - unused-css-rules
  - modern-image-formats
  - offscreen-images
  - third-party-summary

Catatan:
- API key milik platform (aneWP)
- Tidak perlu login user

---

## 3. URL Scope (Cost Control)

Agar murah & cepat:

Default URL yang discan:
1. Homepage
2. Top 3–5 landing pages (by impressions dari GSC)

Total default: **maks 6 URL** per run

User optional:
- Pilih 1 URL tambahan (manual)

---

## 4. Komposisi Health Score

### Bobot Final (v1)

| Komponen | Bobot |
|--------|------|
| Indexing & Coverage | 40% |
| Performance (Speed) | 40% |
| Page Experience | 20% |

Total = 100%

---

## 5. Perhitungan Detail

### A. Indexing & Coverage Score (40%)

Formula:

Indexed Ratio = indexed_pages / total_pages

Score:
- ≥ 98% → 40
- 95–97% → 32
- 90–94% → 24
- < 90% → 16

Penalty:
- Critical error (server error, redirect error) → −5 (max −10)

---

### B. Performance Score (40%)

Ambil dari PSI:

Performance Score = average(lighthouse.performance * 100)

Mapping:
- ≥ 90 → 40
- 80–89 → 32
- 70–79 → 24
- 50–69 → 16
- < 50 → 8

---

### C. Page Experience Score (20%)

Ambil dari GSC:

| Metric | Pass | Fail |
|------|------|------|
| Core Web Vitals | +10 | 0 |
| Mobile Usability | +5 | 0 |
| HTTPS | +5 | 0 |

---

## 6. Health Status Label

| Score | Status | Color |
|------|--------|-------|
| 90–100 | Excellent | Green |
| 75–89 | Good | Blue |
| 60–74 | Needs Improvement | Yellow |
| < 60 | Critical | Red |

---

## 7. Backend Flow (Laravel)

### Endpoint
POST /websites/{website}/health/run

### Flow
1. Validate GSC connected
2. Create HealthSession (status=processing)
3. Fetch GSC summary
4. Select URLs
5. Call PageSpeed API per URL
6. Normalize metrics
7. Calculate scores
8. Save snapshot
9. Mark session completed

---

## 8. Database Tables

### ane_health_sessions
- id
- website_id
- status (processing/completed/failed)
- health_score
- indexing_score
- performance_score
- page_experience_score
- summary_json
- created_at

### ane_health_page_metrics
- session_id
- url
- performance_score
- lcp
- inp
- cls
- ttfb
- issues_json

---

## 9. UI – Website Analytics Page

Section baru (di bawah analytics):

### Website Health
- Health Score (big number)
- Status badge
- Sub scores (Indexing / Performance / Experience)
- Button: Run Health Check / Re-run

### Performance Issues (Auto Prioritized)
Table:
- Issue
- Impact (High/Medium/Low)
- Affected URLs
- Recommendation

---

## 10. Output JSON (Snapshot)

{
  "health_score": 82,
  "status": "Good",
  "indexing": { "score": 32, "indexed_ratio": 0.96 },
  "performance": { "score": 28, "avg_lighthouse": 71 },
  "experience": { "score": 15 },
  "top_issues": [
    "Render blocking resources",
    "Unused JavaScript",
    "LCP above threshold"
  ]
}

---

## 11. Future Upgrade (Phase 2)
- Internal crawler (broken links, redirect chain)
- JS rendering audit
- Health trend chart
- Compare vs competitor average

---

## 12. Prinsip Penting

- Jangan gabung keyword & health
- Health = technical foundation
- Actionable > lengkap
- Murah > ribet

---

Status: **READY TO IMPLEMENT (v1)**


---

## Phase 2 Add-ons – Competitive, Priority & AI Layer

### A. Health Score vs Competitors (Benchmark Layer)

**Tujuan**: bikin user langsung ngerti posisi websitenya *relatif* terhadap kompetitor, bukan angka absolut doang.

**Sumber data**:
- Health Score Website (GSC + PSI)
- Health Score Competitor (PSI-only, tanpa GSC)

**Metode**:
- Untuk competitor: hanya jalankan PageSpeed Insights (homepage + 1–2 URL utama)
- Tidak pakai GSC competitor (karena tidak punya akses)

**Output UI**:
- Card: `Your Health Score vs Competitor Avg`
- Bar/Donut chart: You | Competitor A | Competitor B
- Label:
  - Ahead of market
  - Slightly behind
  - Critical gap

**Logic**:
- competitor_avg = average PSI score competitor
- delta = your_score − competitor_avg

---

### B. Priority Engine (What To Fix First)

**Tujuan**: user tidak overwhelmed, tau harus ngapain dulu.

**Input**:
- Lighthouse audits (impact score)
- GSC coverage & CWV issues

**Priority Formula (simple & explainable)**:
```
priority_score = impact_weight × affected_urls × severity
```

**Severity mapping**:
- Error / CWV Failed = 3
- Warning = 2
- Opportunity = 1

**Output UI**:
- Table: Top Fixes This Week
  - Issue
  - Impact (High / Medium / Low)
  - Est. Score Gain (+8, +5, etc)
  - Fix type (Dev / Content / Image)

---

### C. AI Explanation Layer (Human-readable Insight)

**Tujuan**: user awam langsung paham tanpa baca teknis.

**Trigger**:
- Setelah health check selesai

**AI Prompt Input**:
- Health score
- Top 5 issues
- Competitor avg score
- CWV status

**AI Output Example**:
> "Website kamu sehat di sisi indexing, tapi performa tertahan karena JavaScript berlebih dan gambar belum teroptimasi. Dibanding kompetitor utama, skor kamu tertinggal sekitar 12 poin. Jika kamu memperbaiki 2 issue teratas, potensi kenaikan skor hingga +15 dalam 1–2 minggu."

**UI**:
- Section: "What This Means"
- Bullet recommendation mingguan

---

### D. Health → Keyword → Competitor Connection

**Bridge logic**:
- Jika Health Score < 70 → tampilkan warning sebelum generate mass content
- Rekomendasi: fix performance dulu

**UI Hook**:
- Button: `Fix Health First`
- Tooltip di Generate Content

---

### E. Data Model Additions

**Tables**:
- `ane_health_snapshots`
- `ane_health_issues`
- `ane_health_competitor_snapshots`

**Snapshot-based** (time-series ready):
- compare week-over-week
- show trend arrow

---

### F. Roadmap Phase 3 (Optional, Future)

- Mini crawler (status code, meta, canonical)
- JS-render audit
- Alert system (health drop)
- Auto-task generation (Notion / Jira)

---

### Positioning Note (Internal)

Health Score = foundation layer.
Keyword & Competitor = growth layer.
AI Generate = execution layer.

aneWP = full SEO operating system, not just a keyword tool.

# Phase 3 — Health Intelligence System

Tujuan Phase 3 adalah mengubah data mentah (GSC, PageSpeed, snapshot internal) menjadi **insight, prioritas, dan aksi otomatis**. Ini bukan sekadar fitur tambahan, tapi layer otak yang membuat aneWP terasa seperti *SEO Operating System*.

---

## 1. Health Trend Engine

### Objective

Mendeteksi **perubahan kualitas website dari waktu ke waktu** dan menjelaskan *penyebabnya*, bukan hanya status naik/turun.

### Data Source

* Google Search Console (GSC)
* PageSpeed Insights (PSI)
* Internal Health Snapshot (Phase 2)

### Data yang Disimpan per Snapshot

* performance_score
* indexed_ratio
* cwv_pass_ratio
* lcp_avg
* inp_avg
* cls_avg

### Trend Logic

Bandingkan snapshot N dengan N-1:

* delta_health
* delta_lcp
* delta_indexed
* delta_cwv

Output sistem:

* “Health turun 6 poin karena LCP naik +1.3s pada 18 URL.”
* “Index coverage naik +8%, performa stabil.”

---

## 2. Health Alert System

### Objective

Memberi peringatan otomatis saat website mengalami degradasi signifikan.

### Trigger Rules (Default)

* Health score turun ≥ 5 poin
* CWV pass ratio turun ≥ 10%
* Performance score < 50
* Indexed pages drop signifikan

### Alert Output

* In-app notification
* Dashboard banner

### Contoh Copy UI

> “Website kamu butuh perhatian. Performa turun karena halaman utama melambat.”

---

## 3. Weekly Auto Priority Generator

### Objective

Mengubah masalah teknis menjadi **task mingguan yang bisa dieksekusi**.

### Input

* Health alerts
* Trend delta
* Severity score

### Processing

* Grouping issue (Performance / Indexing / CWV)
* Scoring impact
* Pilih Top 3 prioritas

### Output (Weekly Focus)

* Reduce render-blocking JS (Impact: High)
* Compress images on 12 pages (Impact: Medium)
* Fix 4 non-indexed URLs (Impact: Medium)

Disimpan per minggu dan ditampilkan di dashboard.

---

## 4. Health vs Competitor Trend

### Objective

Menunjukkan posisi website user **dibanding kompetitor**, bukan secara absolut.

### Data

* Health trend user
* Rata-rata health trend competitor primary

### Logic

market_delta = user_health_trend − competitor_avg_trend

### Output UI

* “You’re improving faster than competitors”
* “Competitors improving faster — potential ranking risk”

---

## 5. Auto Guardrail System

### Objective

Mencegah user melakukan aksi yang tidak efisien saat kondisi website belum sehat.

### Rules

Jika:

* Health score < 60
* Performance score < 50

Maka:

* Fitur generate konten massal dibatasi
* Muncul notice:

> “Fix site health first to avoid wasting content effort.”

---

## 6. Data Model Tambahan

### ane_health_snapshots

* website_id
* performance_score
* lcp_avg
* inp_avg
* cls_avg
* indexed_ratio
* cwv_pass_ratio
* created_at

### ane_health_alerts

* website_id
* type
* message
* severity
* resolved_at

### ane_health_weekly_tasks

* website_id
* week
* tasks_json

---

## 7. UX Principle

User tidak perlu:

* setting threshold
* setting jadwal
* paham teknis

User hanya:

* buka dashboard
* lihat “What to do this week”

---

## 8. Outcome Phase 3

aneWP setelah Phase 3:

* Bukan SEO checker
* Bukan keyword tool
* **SEO Operating System**

aneWP = Google data → keputusan → aksi.

---

## Next Phase (Preview)

Phase 4 (opsional):

* Auto task ke Jira / Notion
* Slack / Email alert
* AI SEO Manager mingguan

# Phase 4 — AI SEO Manager & Automation Layer

Phase 4 menambahkan **lapisan pengambilan keputusan dan otomatisasi** di atas Phase 1–3. Di tahap ini, aneWP tidak hanya menganalisis, tetapi **memberi arahan strategis dan mengorkestrasi aksi**.

---

## 1. AI SEO Manager (Core Concept)

### Objective

Menghadirkan AI persona yang bertindak sebagai **SEO Manager virtual** untuk website user.

### Input Data

* Health Snapshots (Phase 2 & 3)
* Health Trends & Alerts (Phase 3)
* Weekly Priority Tasks (Phase 3)
* Competitor Gap Analysis (Phase 2)
* Keyword Opportunities (User + Competitor)

### Output

**Weekly SEO Brief** dalam bahasa manusia.

Contoh output:

> “Minggu ini performa website kamu menurun karena LCP memburuk di halaman artikel lama. Fokuskan optimasi performa sebelum menambah konten baru.”

Atau:

> “Website kamu membaik lebih cepat dari kompetitor. Ini waktu yang tepat untuk publish konten high-volume.”

---

## 2. Weekly SEO Brief Engine

### Objective

Merangkum seluruh kondisi website menjadi **1–2 paragraf insight strategis**.

### Struktur Brief

* Kondisi umum website (health & trend)
* Perbandingan dengan kompetitor
* Risiko utama
* Peluang utama
* Rekomendasi fokus minggu ini

### Output Channel

* Dashboard card: **“SEO Manager Says”**
* Detail page: Weekly Plan

---

## 3. Smart Recommendation Engine

### Objective

Memberikan rekomendasi berbasis **probabilitas dampak**, bukan saran generik.

### Contoh Rekomendasi

* “Optimasi gambar di 5 halaman ini berpotensi menaikkan traffic +18%.”
* “Konten X sebaiknya ditunda sampai CWV pulih.”

### Logic

* Kombinasi data health + competitor + keyword trend
* Scoring berdasarkan impact vs effort

---

## 4. Opportunity Timing Engine

### Objective

Menentukan **waktu terbaik** untuk bertindak.

### Input

* Health readiness
* Competitor trend (melambat / naik)
* Keyword trend

### Output

* “Now is a good time to publish”
* “Hold — fix technical issues first”

---

## 5. Auto Task Orchestration (Optional)

### Objective

Mengubah rekomendasi menjadi **task nyata**.

### Integrasi (opsional)

* Notion
* Trello
* Jira (future)

### Flow

Weekly Priority → Task otomatis → User eksekusi

aneWP menjadi **source of truth**, bukan sekadar dashboard.

---

## 6. Guarded Automation

### Objective

Mencegah aksi yang merugikan.

### Rules

* Jika Health < threshold → blokir generate massal
* Jika data belum cukup → AI menyatakan “belum saatnya”

### UX Copy

> “Disarankan memperbaiki health terlebih dahulu sebelum lanjut ke konten.”

---

## 7. UI Components

### Dashboard

* Card: 🧠 **SEO Manager Says**
* CTA: View Weekly Plan | Create Tasks | Generate Content (Recommended)

### Weekly Plan Page

* Summary insight
* Task list (auto-generated)
* Opportunity highlights

---

## 8. Data Model (High-Level)

### ane_seo_briefs

* website_id
* week
* summary_text
* risk_notes
* opportunity_notes

### ane_seo_recommendations

* website_id
* type (health / content / timing)
* message
* impact_score

---

## 9. Outcome Phase 4

aneWP berubah dari:

* SEO Tool
* SEO Analyzer

menjadi:

* **AI SEO Manager & Control System**

aneWP = data → insight → decision → action.

---

## Notes

Phase 4 bersifat **incremental & opsional**. Bisa diaktifkan bertahap setelah Phase 3 stabil.
