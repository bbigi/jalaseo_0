# Advance Competitor Ranked Keywords

Blueprint fitur **Advanced Competitor Ranked Keywords** untuk halaman kompetitor (1 kompetitor = 1 halaman).

---

## 1. Tujuan Fitur
Fitur ini digunakan untuk **melakukan riset keyword lanjutan** pada **satu domain kompetitor** menggunakan **DataForSEO Labs – Ranked Keywords**.

> Ini **bukan verifikasi** keyword dari Serper / RapidAPI.
> Ini adalah **riset murni baru** langsung dari DataForSEO (cost-aware).

Fitur ini:
- Opsional
- Hanya bisa dijalankan pada **competitor yang diset sebagai Primary**
- Setiap user maksimal **2 Primary Competitors**

---

## 2. Flow UI (End-to-End)

### A. Halaman Index Competitor
URL:
```
/competitors
```

Action baru di kolom **Actions**:
- **Set as Primary**
- Jika sudah primary → badge **Primary**

Rules:
- Maksimal 2 competitor bisa menjadi Primary
- Jika limit tercapai:
  - Tombol disabled
  - Tooltip: `Max 2 primary competitors.`

---

### B. Halaman View Competitor
URL:
```
/competitors/view/{competitor_id}
```

Jika competitor berstatus **Primary**, tampilkan section baru di bagian bawah:

#### Section Title
**Advanced Analysis**

#### Description
Get ranked keywords and SERP metrics directly from DataForSEO.

#### Button
**Run Advanced Competitor Analysis**

---

## 3. Flow Backend

### A. Trigger
Button **Run Advanced Competitor Analysis** memanggil:
```
POST /competitors/{id}/advanced-ranked-keywords/run
```

---

### B. Validation
- Competitor harus `is_primary = true`
- Cek quota user (maks 2 primary)
- Prevent double-run (status processing)

---

### C. Background Job
Job:
```
AnalyzeCompetitorRankedKeywords
```

Service:
```
DataForSEORankedKeywordService
```

---

## 4. DataForSEO Configuration

Endpoint:
```
v3/dataforseo_labs/google/ranked_keywords/live
```

Request Payload (default):
```json
{
  "target": "{competitor_domain}",
  "location_code": 2360,
  "language_code": "id",
  "historical_serp_mode": "live",
  "ignore_synonyms": false,
  "include_clickstream_data": false,
  "item_types": ["organic"],
  "load_rank_absolute": false,
  "limit": 50,
  "order_by": ["keyword_data.keyword_info.search_volume,desc"]
}
```

Notes:
- Limit default 50 (safe-cost)
- Bisa ditingkatkan (100) via config
- Tidak mengirim keyword satu per satu (anti high-cost)

---

## 5. Data Processing

### A. Summary Metrics (Header Cards)
Diambil dari:
```
result[0].metrics.organic
```

Disimpan:
- total_keywords → `count`
- traffic_estimate → `etv`
- avg_rank_distribution:
  - pos_1
  - pos_2_3
  - pos_4_10
  - pos_11_20
  - pos_21_100

---

### B. Keyword List (Table)
Dari:
```
result[0].items
```

Disimpan per keyword:
- keyword
- search_volume
- cpc
- competition_level
- rank_absolute
- rank_group
- url
- title
- etv
- is_new

---

## 6. Database Structure

### A. Table: ane_competitor_advanced_sessions
- id
- competitor_id
- user_id
- status (processing | completed | failed)
- progress
- error_message
- started_at
- finished_at

---

### B. Table: ane_competitor_ranked_keywords
- competitor_id
- keyword
- search_volume
- cpc
- competition_level
- rank_absolute
- rank_group
- url
- etv
- source = dataforseo

---

## 7. UI Output

### A. Summary Cards
- Total Ranked Keywords
- Estimated Organic Traffic
- Ranking Distribution (chart)

---

### B. Keyword Table
Default sort:
- Search Volume (DESC)

Columns:
- Keyword
- Volume
- Position
- URL
- Traffic Est.
- Status

Badges:
- **Tracked** (exists in ane_keywords)
- **GSC Query** (exists in ane_gsc_keywords)

Actions:
- Track Keyword
- Generate Content

---

## 8. Cost Control Rules

- Feature hanya muncul jika competitor = Primary
- Max 2 Primary Competitors per user
- Limit default 50 keyword
- No keyword-by-keyword verification

---

## 9. Status & UX

Button States:
- Idle → Run Advanced Competitor Analysis
- Processing → Running…
- Completed → Advanced data ready
- Failed → Retry

Polling endpoint:
```
POST /competitors/advanced-ranked-keywords/poll/{session_id}
```

---

## 10. Scope Boundary

❌ Tidak terhubung dengan:
- Serper
- RapidAPI Keyword Insight
- Keyword verification per item

✅ Murni:
- DataForSEO Labs
- Ranked Keywords
- One competitor per page

---

END OF BLUEPRINT


---

## Laravel Implementation Notes (Codex‑Friendly)

> Section ini dibuat khusus supaya **GPT‑5.1 Codex** langsung paham alur backend Laravel tanpa asumsi framework lain.

### Stack Assumption
- Laravel 10+
- Queue: database / redis
- HTTP Client: native Laravel HTTP or curl wrapper
- Auth: per‑user limit enforced (policy / service layer)

---

### Routes
```php
// Competitor index
GET  /competitors

// Toggle primary competitor
POST /competitors/{competitor}/set-primary

// View competitor detail
GET  /competitors/view/{competitor}

// Run advanced ranked keywords analysis
POST /competitors/{competitor}/advanced-ranked-keywords/run

// Poll analysis progress
GET  /competitors/{competitor}/advanced-ranked-keywords/status
```

---

### Controller Responsibilities

**CompetitorController**
- index(): list competitors
- setPrimary(): enforce max 2 primary competitors per user
- view(): show competitor profile + conditional Advanced button

**CompetitorAdvancedAnalysisController**
- run():
  - validate competitor belongs to user
  - check competitor is_primary = true
  - check per‑user usage limit (max 2 competitors)
  - create analysis session record
  - dispatch job
- status(): return progress, state, error if any

---

### Job: RunAdvancedCompetitorRankedKeywords

```php
handle() {
  1. Mark session = running
  2. Build DataForSEO payload (ranked_keywords)
  3. Send request
  4. Store raw JSON
  5. Normalize metrics
  6. Store summary + top keywords
  7. Mark session = completed
}
```

Retry strategy:
- max 2 retries
- fail → session.status = failed

---

### Database Tables (Minimal)

**competitors**
- id
- user_id
- domain
- is_primary (bool)

**competitor_advanced_sessions**
- id
- competitor_id
- status (pending|running|completed|failed)
- progress (0‑100)
- error_message

**competitor_ranked_keywords_raw**
- session_id
- provider = dataforseo
- payload (json)

**competitor_ranked_keywords_summary**
- session_id
- total_keywords
- etv
- avg_rank
- distribution_json

---

### DataForSEO Payload (Canonical)
```json
{
  "target": "example.com",
  "location_code": 2360,
  "language_code": "id",
  "historical_serp_mode": "live",
  "item_types": ["organic"],
  "limit": 50,
  "order_by": ["keyword_data.keyword_info.search_volume,desc"],
  "include_clickstream_data": false
}
```

---

### UI Logic (Blade / Vue / React)

- Index page:
  - Show **Set as Primary** button
  - Disable when primary count = 2

- View page:
  - If competitor.is_primary === true
    → show **Run Advanced Competitor Analysis**

- After run:
  - show progress spinner
  - auto‑poll every 3–5s

---

### Guardrails (Important)
- ❌ Do NOT reuse Serper / RapidAPI keywords
- ❌ Do NOT send keyword list one‑by‑one to DataForSEO
- ✅ Always use `ranked_keywords` as fresh research
- ✅ Hard‑limit runs per user

---

### Mental Model (for Codex)
> One competitor = one domain = one ranked keywords analysis.
> This feature is **independent** from user website data.

---

