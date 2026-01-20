# aneWP Analytics Blueprint – Competitors + Speed Intelligence

## 1) Add-on module: Speed Intelligence (PageSpeed / Lighthouse)

### Goal
Bikin speed test yang **bukan cuma skor**, tapi **diagnosis + prioritas fix** (quick wins), plus **snapshot history** buat compare before/after.

### Data sources
- **Google PageSpeed Insights API (PSI)**
  - Ngasih: Lighthouse lab metrics + audits + opportunities + diagnostics.
  - Kadang ngasih **CrUX field data** (real user) kalau origin/page eligible.
- **CrUX/Field data**
  - Kalau gak ada, UI harus bilang: *“Field data belum tersedia (traffic kurang / belum eligible)”*.

### What’s special to expose in UI
- **Core Web Vitals**
  - LCP, INP, CLS (lab + field kalau ada)
- **Big offenders**
  - render-blocking, unused JS/CSS, large images, third-party heavy scripts, font blocking
- **Estimated savings**
  - ms saving / KB saving dari opportunities
- **Snapshot comparison**
  - run A vs run B (delta scores + delta metrics + issue resolved/added)

---

## 2) Database design

### New tables

#### `ane_pagespeed_runs`
- `id`
- `website_id` (fk)
- `url` (tested URL)
- `strategy` enum: `mobile|desktop`
- `status` enum: `queued|running|success|error`
- `error_message` nullable
- `progress` tinyint default 0
- `lighthouse_version` nullable
- `fetch_time` datetime nullable
- `scores` json nullable
  - `{ performance, accessibility, best_practices, seo, pwa }`
- `lab_metrics` json nullable
  - `{ fcp_ms, lcp_ms, tbt_ms, cls, si_ms, tti_ms, inp_ms? }`
- `field_metrics` json nullable
  - `{ lcp_p75_ms, inp_p75_ms, cls_p75, lcp_good_ratio, inp_good_ratio, cls_good_ratio, period }`
- `opportunities` json nullable
  - list of `{ id, title, savings_ms, savings_bytes, items_count, top_items[] }`
- `diagnostics` json nullable
  - list of `{ id, title, description, details? }`
- `resources_summary` json nullable
  - `{ total_bytes, js_bytes, css_bytes, img_bytes, font_bytes, third_party_count }`
- timestamps

#### `ane_pagespeed_run_items` (optional, kalau mau queryable)
- `run_id`
- `type` enum `opportunity|diagnostic`
- `audit_id`
- `title`
- `savings_ms` nullable
- `savings_bytes` nullable
- `payload` json

> MVP boleh **cukup simpan json di runs** dulu (lebih cepet), bikin item table belakangan kalau butuh filter/sort advance.

---

## 3) Backend services + jobs

### Service: `PageSpeedService`
Responsibilities:
- Call PSI endpoint untuk URL + strategy
- Normalize response jadi struktur internal (scores, metrics, opportunities)
- Handle edge cases:
  - missing CrUX
  - audits null
  - lighthouseResult missing pieces

**Output mapping (contoh):**
- scores → `lighthouseResult.categories.*.score * 100`
- lab metrics → dari `lighthouseResult.audits` (e.g. `largest-contentful-paint`, `cumulative-layout-shift`, `interactive`, `total-blocking-time`, `first-contentful-paint`, `speed-index`)
- opportunities → audits dengan `details.type = opportunity` atau yang punya `numericValue` savings
- diagnostics → audits category diagnostics / atau `details.type = diagnostic`
- field metrics → dari `loadingExperience` / `originLoadingExperience` kalau ada

### Job: `RunPageSpeedTest`
Flow:
1) validate website + url
2) create run (queued → running)
3) call service PSI
4) store normalized result
5) update status success + progress 100
6) on error: status error + log error

### Double-run guard
- kalau ada run `running` untuk (website_id + url + strategy) → jangan bikin baru.

---

## 4) Routes + controllers

### Website Analytics page section
- `POST /websites/{website}/pagespeed/run`
  - body: `{ url, strategy }`
- `GET /websites/{website}/pagespeed/status?run_id=...` atau latest by strategy
- `GET /websites/{website}/pagespeed/history?url=...&strategy=...`
- `GET /websites/{website}/pagespeed/runs/{run}` (detail)

### New UI page
- `GET /websites/{website}/speed-insights`
  - dropdown URL (homepage / tracked pages)
  - toggle strategy mobile/desktop
  - tabs: Overview | Issues | Opportunities | History

---

## 5) UI spec

### A) Add section on `Website Analytics` page (below “Your Competitors” section)
**Title:** `Speed Intelligence`

Layout:
- Top row:
  - URL selector (default homepage)
  - strategy switch: Mobile / Desktop
  - CTA: `Run Test`

Show cards:
- Performance score
- Core Web Vitals (LCP / INP / CLS)
- Page weight summary (JS/CSS/IMG/FONT)
- Field Data badge:
  - `Real User Data: Available` / `Not Available`

Mini list (Top 5):
- `Top Performance Killers`
  - each row: audit title + savings ms + severity badge

CTA:
- `View Speed Insights` → halaman detail

### B) Speed Insights detail page
**Header**
- URL dropdown
- Strategy toggle
- Run info: last run time + status
- Compare dropdown: compare with previous run

**Overview**
- scores + cwvs
- field metrics (if available)

**Opportunities table**
Columns:
- Opportunity
- Est. savings (ms)
- Est. savings (KB)
- Affected items
- Action: `Create Fix Plan` / `Mark as Done`

**Diagnostics**
- grouped list: JS / CSS / Images / Fonts / Third-party

**History**
- list of runs + sparkline (optional)
- compare view: what improved / regressed

---

## 6) “Fix Plan” layer (optional, tapi keren banget)

### Table: `ane_pagespeed_fix_plans`
- `website_id`
- `run_id`
- `title`
- `items` json (selected opportunities)
- `status` enum `open|in_progress|done`
- `notes`

UI:
- select multiple opportunities → generate plan otomatis (AI-assisted copy)

---

## 7) How it connects to Competitor Gap Analysis

Karena sekarang kita punya:
- **Keyword gaps** (GSC vs competitor ranked keywords)
- **Performance bottlenecks** (speed) yang bisa ngefek ke SEO & UX

Di halaman `Analytics Gaps`, tambah **small badge** di header competitor selection:
- `Site Health: Good / Needs Work` (based on CWV)

Dan di `Opportunity table`, kalau keyword high volume tapi page performance buruk:
- show hint: `⚠ Page speed risk` (optional, kalau kita tau landing page dari GSC)

---

## 8) Edge cases + UX rules

- Kalau **gak ada keyword match** antara website vs competitor:
  - default tampilkan **Content Gaps** (our_rank null) dari competitor_rank <= 50
  - plus message: `No direct keyword overlap found — showing competitor-driven gaps.`

- Kalau **no CrUX field data**:
  - UI jelasin singkat: `Field data needs enough real users. Showing lab data for now.`

- Kalau PSI rate limit / error:
  - status `error` + tombol `Retry`

---

## 9) MVP scope (biar cepet jadi)

MVP (week 1):
- runs table (json storage)
- run job + status
- analytics page section + speed insights page (overview + opportunities)

Phase 2:
- fix plans
- item table queryable
- compare view yang proper
- connect speed risk ke SEO opportunities

