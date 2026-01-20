# PANDUAN MIGRASI: WEBSITE ANALYTICS (GSC + GA4)

**Project:** anewplaravel (Laravel 12)
**Source:** anewp (Native PHP MVC)
**Tanggal:** 2025-12-15
**Status:** Dokumentasi - Belum Implementasi

---

## 📋 EXECUTIVE SUMMARY

### Fitur yang Akan Dimigrasi

**Website Analytics Dashboard** adalah fitur analytics komprehensif yang mengintegrasikan 3 sumber data utama:

1. **Google Search Console (GSC)** - Keyword rankings, clicks, impressions, CTR, position
2. **Google Analytics 4 (GA4)** - Traffic metrics, user behavior, referral sources
3. **AI-Powered Insights** - Strategic recommendations berdasarkan gabungan data GSC + GA4

### Manfaat Utama

- ✅ **100% FREE** - Tidak ada biaya API (GSC & GA4 gratis)
- ✅ **Real Data** - Data aktual dari Google, bukan estimasi
- ✅ **Comprehensive** - Gabungan SEO (GSC) + Traffic (GA4) + AI Analysis
- ✅ **Backlink Alternative** - GA4 referral sources sebagai alternatif backlink checker berbayar
- ✅ **Multi-Tenant Safe** - Setiap user hanya lihat website mereka sendiri

---

## 📚 REFERENSI SERVICE SOURCE (anewp)

### **Service Files yang Sudah Dibaca:**
1. ✅ **GoogleSearchConsoleService.php** - OAuth, keyword sync, top keywords, opportunities
2. ✅ **GoogleAnalytics4Service.php** - OAuth, traffic sync, referral sources, page stats
3. ✅ **AIArticleService.php** - Multi-provider AI (Gemini, Claude, GPT), News/Blog generation, Firecrawl integration
4. ✅ **NewsInsightService.php** - News search history, cache management, insight conversion
5. ✅ **SerperSearchService.php** - Google Search API wrapper, autocomplete suggestions

### **Key Technical Patterns Found:**
- **OAuth Per-User:** GSC & GA4 menggunakan per-user OAuth (NOT admin panel API keys)
- **Token Auto-Refresh:** Check `token_expires_at`, auto-refresh jika < 5 menit
- **Background Sync:** All data sync via Laravel Queue (prevent timeout)
- **Multi-Tenant Isolation:** All queries MUST filter by `created_by = auth()->id()`
- **Firecrawl Integration:** AIArticleService uses FirecrawlService for full article scraping
- **AI Providers:** Gemini 2.0 Flash (primary), Claude 3.5 Sonnet, GPT-4o Mini, Groq (fallback)
- **Word Count by Type:** News: 500-800 words, Blog: 1000-1500 words
- **Serper API:** Used for News Insights search + Deep Research autocomplete

---

## 🎯 FITUR-FITUR LENGKAP

### **1. Google Search Console Integration**

#### **1.1. OAuth 2.0 Authentication**
- Flow: User click "Connect GSC" → Google OAuth consent → Callback dengan tokens
- Scope: `https://www.googleapis.com/auth/webmasters.readonly`
- Tokens:
  - `access_token` - Expire 1 jam, untuk API calls
  - `refresh_token` - Permanen, untuk renew access_token
- Property Matching: Auto-detect GSC property berdasarkan domain website

#### **1.2. Keyword Performance Data**
**Metrics yang Disimpan:**
- `keyword` - Search query dari Google
- `clicks` - Total klik dari SERP
- `impressions` - Total tampilan di SERP
- `ctr` - Click-through rate (clicks/impressions)
- `position` - Average ranking position (1-100)
- `date` - Tanggal metrics
- `country` - Country code (ALL, US, ID, etc)
- `device` - Device type (ALL, DESKTOP, MOBILE, TABLET)
- `page_url` - Landing page URL

**Data Range:**
- Default: Last 30 days
- Max: 16 months (GSC limitation)
- Sync Strategy:
  - Daily: Last 7 days (fresh data)
  - Weekly: Last 30 days (historical)
  - Monthly: Last 90 days (trends)

**API Limits:**
- 25,000 requests/day (FREE tier)
- Max 5,000 rows per request
- Pagination untuk dataset besar

#### **1.3. Top Performing Keywords**
**View:** `v_gsc_top_keywords`
- Aggregate 30 hari terakhir
- Sort by total clicks DESC
- Metrics: total_clicks, total_impressions, avg_ctr, avg_position
- Color coding:
  - Position 1-3: Green (Top hasil)
  - Position 4-10: Blue (Page 1)
  - Position 11+: Yellow (Page 2+)

#### **1.4. Keyword Opportunities**
**Kriteria:**
- High impressions (>100)
- Low CTR (<5%)
- Position 5-20 (Potential untuk optimasi)

**Use Case:**
"Keyword ini sudah ranking bagus tapi CTR rendah → Optimasi title/meta description bisa boost clicks"

#### **1.5. Page Performance**
**Table:** `ane_gsc_pages`
- Track per-page metrics
- Landing page analysis
- Top pages by clicks

---

### **2. Google Analytics 4 Integration**

#### **2.1. OAuth 2.0 Authentication**
- Flow: User click "Connect GA4" → Google OAuth consent → Callback dengan tokens
- Scope: `https://www.googleapis.com/auth/analytics.readonly`
- Property ID: User input manual (GA4 Property ID, contoh: `123456789`)
- Same OAuth client bisa digunakan untuk GSC + GA4

#### **2.2. Traffic Metrics (Daily)**
**Table:** `ane_ga4_traffic_data`

**Metrics yang Disimpan:**
- `users` - Total unique users
- `new_users` - New users (first visit)
- `sessions` - Total sessions
- `page_views` - Total page views
- `avg_session_duration` - Average session time (seconds)
- `bounce_rate` - Percentage bounce (%)
- `engagement_rate` - Engagement rate (%)
- `conversions` - Total conversions (optional)

**Aggregation:**
- Summary 30 hari terakhir
- Daily breakdown
- Trend analysis (compare dengan 7 hari lalu)

#### **2.3. Referral Traffic Sources**
**Table:** `ane_ga4_referral_sources`

**Purpose:** Alternatif backlink checker tanpa biaya (Ahrefs/SEMrush mahal)

**Data:**
- `source` - Referring domain (e.g., "facebook.com", "google.com")
- `medium` - Traffic medium ("referral", "organic", "social", "email")
- `users` - Users from this source
- `sessions` - Sessions from this source
- `page_views` - Page views from this source
- `avg_session_duration` - Avg session time
- `bounce_rate` - Bounce rate from this source

**Use Case:**
- Identifikasi sumber traffic terbaik
- Alternatif backlink report (domain mana yang kirim traffic)
- ROI analysis per channel

**Color Coding Bounce Rate:**
- <40%: Green (Excellent)
- 40-70%: Yellow (Average)
- >70%: Red (Poor)

#### **2.4. Page-Level Stats**
**Table:** `ane_ga4_page_stats`

**Metrics:**
- `page_path` - URL path
- `page_title` - Page title
- `page_views` - Total views
- `unique_page_views` - Unique views
- `avg_time_on_page` - Average time
- `entrances` - Entry pages
- `exits` - Exit pages

**Use Case:**
- Content performance analysis
- High exit pages (optimization needed)

---

### **3. AI-Powered Analytics Insights**

#### **3.1. Data Analysis with AI**
**Input Data:**
```json
{
  "gsc": {
    "total_keywords": 1234,
    "keywords_with_clicks": 456,
    "total_clicks": 5000,
    "total_impressions": 100000,
    "avg_ctr": 5.2,
    "avg_position": 12.5
  },
  "ga4": {
    "total_users": 3000,
    "total_sessions": 4500,
    "total_pageviews": 12000,
    "avg_bounce_rate": 45.5,
    "avg_session_duration": 180
  }
}
```

**AI Provider:** Gemini 2.0 Flash (category: `analytics_insights`)

**Output:**
1. **SEO Performance Analysis**
   - Keyword coverage assessment
   - Position improvement opportunities
   - CTR optimization tips

2. **Traffic Quality Analysis**
   - User engagement assessment
   - Bounce rate interpretation
   - Session quality insights

3. **Strategic Recommendations**
   - Top 3-5 actionable recommendations
   - Priority ranking
   - Expected impact estimation

4. **Priority Actions**
   - Immediate actions (quick wins)
   - Short-term goals (1-3 months)
   - Long-term strategy (3-6 months)

#### **3.2. AI Prompt Structure**
```
Kamu adalah SEO & Analytics expert profesional.

WEBSITE DATA:
- GSC: {gsc_summary}
- GA4: {ga4_summary}

TASK: Analisis data di atas dan berikan:

1. SEO PERFORMANCE:
- Assessment ranking & CTR
- Keyword opportunities
- Position improvement potential

2. TRAFFIC QUALITY:
- User behavior analysis
- Bounce rate interpretation
- Engagement assessment

3. STRATEGIC RECOMMENDATIONS:
- Top 5 actionable recommendations
- Priority ranking (High/Medium/Low)
- Expected impact

4. PRIORITY ACTIONS:
- Quick wins (1-2 weeks)
- Short-term goals (1-3 months)
- Long-term strategy (3-6 months)

FORMAT: HTML dengan <ul>, <li>, <strong>, <p>. Jangan pakai JSON.
TONE: Professional tapi easy to understand.
BAHASA: Indonesian
```

---

## 🗄️ DATABASE SCHEMA

### **Tabel-Tabel Baru (8 Tables + 3 Views)**

#### **GSC Tables (5 tables)**

##### **1. ane_gsc_connections**
```sql
CREATE TABLE ane_gsc_connections (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    website_id BIGINT UNSIGNED NOT NULL,
    property_url VARCHAR(500) NOT NULL,
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at DATETIME,
    verified TINYINT(1) DEFAULT 0,
    last_sync_at DATETIME,
    sync_status ENUM('pending', 'syncing', 'completed', 'failed') DEFAULT 'pending',
    sync_error TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (website_id) REFERENCES ane_websites(id) ON DELETE CASCADE,
    UNIQUE KEY uk_website_property (website_id, property_url),
    INDEX idx_last_sync (last_sync_at),
    INDEX idx_sync_status (sync_status)
);
```

##### **2. ane_gsc_keywords**
```sql
CREATE TABLE ane_gsc_keywords (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    website_id BIGINT UNSIGNED NOT NULL,
    connection_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    keyword VARCHAR(500) NOT NULL,
    clicks INT DEFAULT 0,
    impressions INT DEFAULT 0,
    ctr DECIMAL(5,4),
    position DECIMAL(5,2),
    country VARCHAR(10) DEFAULT 'ALL',
    device VARCHAR(20) DEFAULT 'ALL',
    page_url VARCHAR(1000),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (website_id) REFERENCES ane_websites(id) ON DELETE CASCADE,
    FOREIGN KEY (connection_id) REFERENCES ane_gsc_connections(id) ON DELETE CASCADE,

    UNIQUE KEY uk_keyword_date (website_id, date, keyword, country, device),
    INDEX idx_website_date (website_id, date),
    INDEX idx_keyword (keyword),
    INDEX idx_position (position),
    INDEX idx_clicks (clicks DESC),
    INDEX idx_impressions (impressions DESC)
);
```

##### **3. ane_gsc_pages**
```sql
CREATE TABLE ane_gsc_pages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    website_id BIGINT UNSIGNED NOT NULL,
    connection_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    page_url VARCHAR(1000) NOT NULL,
    clicks INT DEFAULT 0,
    impressions INT DEFAULT 0,
    ctr DECIMAL(5,4),
    position DECIMAL(5,2),
    country VARCHAR(10) DEFAULT 'ALL',
    device VARCHAR(20) DEFAULT 'ALL',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (website_id) REFERENCES ane_websites(id) ON DELETE CASCADE,
    FOREIGN KEY (connection_id) REFERENCES ane_gsc_connections(id) ON DELETE CASCADE,

    UNIQUE KEY uk_page_date (website_id, date, page_url(255), country, device),
    INDEX idx_website_date (website_id, date),
    INDEX idx_page_url (page_url(255)),
    INDEX idx_clicks (clicks DESC)
);
```

##### **4. ane_gsc_sync_jobs**
```sql
CREATE TABLE ane_gsc_sync_jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    connection_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    job_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    progress INT DEFAULT 0,
    current_step VARCHAR(255),
    keywords_synced INT DEFAULT 0,
    pages_synced INT DEFAULT 0,
    error_message TEXT,
    started_at DATETIME,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (connection_id) REFERENCES ane_gsc_connections(id) ON DELETE CASCADE,
    INDEX idx_status (job_status),
    INDEX idx_connection (connection_id, created_at DESC)
);
```

##### **5. ane_keyword_cache**
```sql
CREATE TABLE ane_keyword_cache (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    keyword VARCHAR(500) NOT NULL,
    location VARCHAR(50) DEFAULT 'ID',
    language VARCHAR(10) DEFAULT 'id',
    search_volume INT,
    cpc DECIMAL(8,2),
    competition DECIMAL(3,2),
    competition_level VARCHAR(20),
    trend_data JSON,
    related_keywords JSON,
    serp_features JSON,
    difficulty_score INT,
    cached_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    data_source VARCHAR(50) DEFAULT 'rapidapi',

    UNIQUE KEY uk_keyword_location (keyword, location, language),
    INDEX idx_expires (expires_at),
    INDEX idx_search_volume (search_volume DESC),
    INDEX idx_difficulty (difficulty_score)
);
```

#### **GA4 Tables (4 tables)**

##### **6. ane_ga4_connections**
```sql
CREATE TABLE ane_ga4_connections (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    website_id BIGINT UNSIGNED NOT NULL,
    property_id VARCHAR(100) NOT NULL,
    account_id VARCHAR(100),
    property_name VARCHAR(255),
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL,
    token_expires_at DATETIME NOT NULL,
    verified TINYINT(1) DEFAULT 1,
    last_sync_at DATETIME,
    sync_status VARCHAR(50) DEFAULT 'idle',
    sync_error TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (website_id) REFERENCES ane_websites(id) ON DELETE CASCADE,
    UNIQUE KEY uk_website_ga4 (website_id),
    INDEX idx_verified (verified),
    INDEX idx_sync_status (sync_status)
);
```

##### **7. ane_ga4_traffic_data**
```sql
CREATE TABLE ane_ga4_traffic_data (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    website_id BIGINT UNSIGNED NOT NULL,
    connection_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    users INT DEFAULT 0,
    new_users INT DEFAULT 0,
    sessions INT DEFAULT 0,
    page_views INT DEFAULT 0,
    avg_session_duration DECIMAL(10,2) DEFAULT 0,
    bounce_rate DECIMAL(5,2) DEFAULT 0,
    engagement_rate DECIMAL(5,2) DEFAULT 0,
    conversions INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (website_id) REFERENCES ane_websites(id) ON DELETE CASCADE,
    FOREIGN KEY (connection_id) REFERENCES ane_ga4_connections(id) ON DELETE CASCADE,

    UNIQUE KEY uk_daily_traffic (website_id, date),
    INDEX idx_website_date (website_id, date),
    INDEX idx_connection_date (connection_id, date)
);
```

##### **8. ane_ga4_referral_sources**
```sql
CREATE TABLE ane_ga4_referral_sources (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    website_id BIGINT UNSIGNED NOT NULL,
    connection_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    source VARCHAR(255) NOT NULL,
    medium VARCHAR(100) NOT NULL,
    users INT DEFAULT 0,
    sessions INT DEFAULT 0,
    page_views INT DEFAULT 0,
    avg_session_duration DECIMAL(10,2) DEFAULT 0,
    bounce_rate DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (website_id) REFERENCES ane_websites(id) ON DELETE CASCADE,
    FOREIGN KEY (connection_id) REFERENCES ane_ga4_connections(id) ON DELETE CASCADE,

    UNIQUE KEY uk_source_daily (website_id, date, source, medium),
    INDEX idx_website_date (website_id, date),
    INDEX idx_source_medium (source, medium),
    INDEX idx_date (date)
);
```

##### **9. ane_ga4_page_stats**
```sql
CREATE TABLE ane_ga4_page_stats (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    website_id BIGINT UNSIGNED NOT NULL,
    connection_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    page_path VARCHAR(500) NOT NULL,
    page_title VARCHAR(255),
    page_views INT DEFAULT 0,
    unique_page_views INT DEFAULT 0,
    avg_time_on_page DECIMAL(10,2) DEFAULT 0,
    entrances INT DEFAULT 0,
    exits INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (website_id) REFERENCES ane_websites(id) ON DELETE CASCADE,
    FOREIGN KEY (connection_id) REFERENCES ane_ga4_connections(id) ON DELETE CASCADE,

    UNIQUE KEY uk_page_daily (website_id, date, page_path(255)),
    INDEX idx_website_date (website_id, date),
    INDEX idx_page_path (page_path(255)),
    INDEX idx_date (date)
);
```

#### **Database Views (3 views)**

##### **v_gsc_keywords_latest**
```sql
CREATE OR REPLACE VIEW v_gsc_keywords_latest AS
SELECT
    k.website_id,
    w.name AS website_name,
    k.keyword,
    k.date,
    k.clicks,
    k.impressions,
    k.ctr,
    k.position,
    k.country,
    k.device,
    k.page_url,
    (SELECT position FROM ane_gsc_keywords
     WHERE website_id = k.website_id AND keyword = k.keyword
     AND date = DATE_SUB(k.date, INTERVAL 7 DAY) LIMIT 1) AS position_7d_ago,
    (SELECT clicks FROM ane_gsc_keywords
     WHERE website_id = k.website_id AND keyword = k.keyword
     AND date = DATE_SUB(k.date, INTERVAL 7 DAY) LIMIT 1) AS clicks_7d_ago
FROM ane_gsc_keywords k
INNER JOIN ane_websites w ON k.website_id = w.id
WHERE k.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY k.website_id, k.date DESC, k.clicks DESC;
```

##### **v_gsc_top_keywords**
```sql
CREATE OR REPLACE VIEW v_gsc_top_keywords AS
SELECT
    website_id,
    keyword,
    SUM(clicks) AS total_clicks,
    SUM(impressions) AS total_impressions,
    AVG(ctr) AS avg_ctr,
    AVG(position) AS avg_position,
    COUNT(DISTINCT date) AS days_tracked
FROM ane_gsc_keywords
WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY website_id, keyword
HAVING total_clicks > 0
ORDER BY total_clicks DESC;
```

##### **v_gsc_connections_status**
```sql
CREATE OR REPLACE VIEW v_gsc_connections_status AS
SELECT
    c.id AS connection_id,
    c.website_id,
    w.name AS website_name,
    w.url AS website_url,
    c.property_url,
    c.verified,
    c.last_sync_at,
    c.sync_status,
    TIMESTAMPDIFF(HOUR, c.last_sync_at, NOW()) AS hours_since_sync,
    (SELECT COUNT(*) FROM ane_gsc_keywords WHERE connection_id = c.id) AS total_keywords,
    (SELECT COUNT(*) FROM ane_gsc_pages WHERE connection_id = c.id) AS total_pages
FROM ane_gsc_connections c
INNER JOIN ane_websites w ON c.website_id = w.id
WHERE w.status = 'active';
```

---

## 🔑 API CREDENTIALS & SETUP

### **Google Cloud Console Setup**

#### **Step 1: Create OAuth 2.0 Credentials**

1. **Buka Google Cloud Console:**
   - URL: https://console.cloud.google.com
   - Login dengan Google Account

2. **Create atau Select Project:**
   - Create new project: "AneWP Laravel Analytics"
   - Atau select existing project

3. **Enable APIs:**
   ```
   ✅ Google Search Console API
   ✅ Google Analytics Data API
   ```
   - Navigate: APIs & Services → Library
   - Search & Enable kedua API di atas

4. **Create OAuth 2.0 Client ID:**
   - Navigate: APIs & Services → Credentials
   - Click: Create Credentials → OAuth client ID
   - Application type: **Web application**
   - Name: "AneWP Laravel Production"

   **Authorized redirect URIs:**
   ```
   https://yourdomain.com/websites/gsc-callback
   https://yourdomain.com/websites/ga4-callback
   ```

   **For Development:**
   ```
   http://127.0.0.1:8000/websites/gsc-callback
   http://127.0.0.1:8000/websites/ga4-callback
   ```

5. **Download JSON Credentials:**
   - Click pada Client ID yang baru dibuat
   - Download JSON file
   - Extract:
     - `client_id` - Untuk GOOGLE_CLIENT_ID
     - `client_secret` - Untuk GOOGLE_CLIENT_SECRET

#### **Step 2: OAuth Consent Screen**

Configure OAuth consent screen:
- User type: **External** (for multi-tenant SaaS)
- App name: "AneWP Analytics"
- User support email: your@email.com
- Developer contact: your@email.com
- Scopes:
  ```
  https://www.googleapis.com/auth/webmasters.readonly
  https://www.googleapis.com/auth/analytics.readonly
  ```

**Test Users (During Development):**
- Add your Google account sebagai test user
- Max 100 test users untuk unpublished app

**Publishing (For Production):**
- Submit for verification (required jika >100 users)
- Approval process: 1-2 weeks

---

### **Laravel Environment Variables**

Add to `.env`:

```env
# Google OAuth Credentials (Same for GSC + GA4)
GOOGLE_CLIENT_ID=123456789-abcdefghijklmnopqrstuvwxyz.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-abcdefghijklmnopqrstuvwxyz
GOOGLE_REDIRECT_URI_GSC=http://127.0.0.1:8000/websites/gsc-callback
GOOGLE_REDIRECT_URI_GA4=http://127.0.0.1:8000/websites/ga4-callback

# Google API Scopes
GOOGLE_SCOPE_GSC=https://www.googleapis.com/auth/webmasters.readonly
GOOGLE_SCOPE_GA4=https://www.googleapis.com/auth/analytics.readonly
```

---

### **Additional API Keys (For Integrated Features)**

Meskipun bukan bagian utama GSC/GA4, fitur terintegrasi memerlukan API keys berikut:

```env
# ============================================
# SERPER API (News Insights & Deep Research)
# ============================================
# Provider: Serper.dev (Google Search API)
# Free Tier: 2,500 searches/month
# Paid: $50/month for 5,000 searches
# URL: https://serper.dev
SERPER_API_KEY=your-serper-api-key-here

# ============================================
# FIRECRAWL API (Article Content Scraping)
# ============================================
# Provider: Firecrawl.dev
# Free Tier: 500 credits/month
# Paid: $29/month for 5,000 credits
# URL: https://firecrawl.dev
FIRECRAWL_API_KEY=your-firecrawl-api-key-here

# ============================================
# AI PROVIDERS (Article Generation)
# ============================================
# Already exists in Laravel project from previous features
GEMINI_API_KEY=your-gemini-api-key
ANTHROPIC_API_KEY=your-claude-api-key
OPENAI_API_KEY=your-openai-api-key

# ============================================
# DATAFORSEO API (Deep Research - Optional)
# ============================================
# Provider: DataForSEO.com
# Used for: Search volume, keyword difficulty, competition
# Free Tier: None (Paid only)
# Paid: $1.00 per 1000 keywords
# Alternative: Use Serper autocomplete + RapidAPI Keyword Tool
DATAFORSEO_LOGIN=your-dataforseo-email
DATAFORSEO_PASSWORD=your-dataforseo-password
```

**Catatan Penting:**
- **SERPER_API_KEY:** WAJIB untuk News Insights & Deep Research autocomplete
- **FIRECRAWL_API_KEY:** Opsional, fallback ke snippet jika tidak ada
- **AI Provider Keys:** Already configured di Laravel project
- **DATAFORSEO:** Opsional, bisa diganti dengan RapidAPI Keyword Tool (lebih murah)

**Cost Optimization Strategy:**
1. **Serper:** Cache search results for 6 hours (reduce repeat searches)
2. **Firecrawl:** Only fetch when user explicitly generates article from URL
3. **DataForSEO:** Cache keyword data for 30 days (keyword metrics jarang berubah)

---

### **API Credentials Storage (Admin Panel)**

**TIDAK PERLU!** OAuth credentials disimpan per-user, BUKAN di admin panel.

**Why?**
- GSC & GA4 require **per-user OAuth** (user consent required)
- Berbeda dengan API key (Gemini, OpenAI) yang platform-wide
- Setiap user harus authorize akses ke GSC/GA4 mereka sendiri

**Storage:**
- User connects → OAuth tokens stored in `ane_gsc_connections` atau `ane_ga4_connections`
- Tokens encrypted (optional, recommended)
- Auto-refresh when expired

---

## 📦 COMPOSER DEPENDENCIES

### **Required Packages**

Add to `composer.json`:

```json
{
    "require": {
        "google/apiclient": "^2.15",
        "google/analytics-data": "^0.14"
    }
}
```

**Install:**
```bash
composer require google/apiclient google/analytics-data
```

**What do these packages provide?**

1. **google/apiclient** (Google API PHP Client)
   - OAuth 2.0 authentication
   - Google Search Console API
   - Token management (refresh, expire handling)
   - HTTP client untuk Google APIs

2. **google/analytics-data** (GA4 Data API)
   - GA4 reporting
   - Metrics & dimensions
   - Data query builder

---

## 🔄 OAUTH FLOW DIAGRAM

### **GSC OAuth Flow**

```
User                    Laravel                 Google                   Database
  |                        |                        |                        |
  |  1. Click "Connect GSC"|                        |                        |
  |----------------------->|                        |                        |
  |                        | 2. Generate Auth URL   |                        |
  |                        |----------------------->|                        |
  |                        | 3. Return Auth URL     |                        |
  |                        |<-----------------------|                        |
  | 4. Redirect to Google  |                        |                        |
  |<-----------------------|                        |                        |
  |                        |                        |                        |
  | 5. User Consent        |                        |                        |
  |----------------------->|                        |                        |
  |                        | 6. Callback with code  |                        |
  |                        |<-----------------------|                        |
  |                        | 7. Exchange code for tokens                     |
  |                        |----------------------->|                        |
  |                        | 8. Return access + refresh tokens               |
  |                        |<-----------------------|                        |
  |                        | 9. Verify GSC access   |                        |
  |                        |----------------------->|                        |
  |                        | 10. List properties    |                        |
  |                        |<-----------------------|                        |
  |                        | 11. Save connection    |                        |
  |                        |---------------------------------------->        |
  | 12. Success redirect   |                        |                        |
  |<-----------------------|                        |                        |
```

### **Token Refresh Flow**

```
Laravel                 Database                Google
  |                        |                        |
  | 1. API call needed     |                        |
  |                        |                        |
  | 2. Get connection      |                        |
  |----------------------->|                        |
  | 3. Return connection   |                        |
  |<-----------------------|                        |
  |                        |                        |
  | 4. Check token expiry  |                        |
  | (if expired < now)     |                        |
  |                        |                        |
  | 5. Refresh token       |                        |
  |--------------------------------------->         |
  | 6. Return new access_token                      |
  |<---------------------------------------|        |
  |                        |                        |
  | 7. Update connection   |                        |
  |----------------------->|                        |
  | 8. Proceed API call    |                        |
  |--------------------------------------->         |
```

---

## 🏗️ ARCHITECTURE LAYERS

### **Layer 1: Routes**
```php
// routes/web.php

// GSC Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/websites/connect-gsc/{website}', [GSCController::class, 'connect'])->name('gsc.connect');
    Route::get('/websites/gsc-callback', [GSCController::class, 'callback'])->name('gsc.callback');
    Route::post('/websites/gsc-sync/{website}', [GSCController::class, 'sync'])->name('gsc.sync');
    Route::post('/websites/gsc-disconnect/{website}', [GSCController::class, 'disconnect'])->name('gsc.disconnect');
});

// GA4 Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/websites/connect-ga4/{website}', [GA4Controller::class, 'connect'])->name('ga4.connect');
    Route::get('/websites/ga4-callback', [GA4Controller::class, 'callback'])->name('ga4.callback');
    Route::post('/websites/ga4-sync/{website}', [GA4Controller::class, 'sync'])->name('ga4.sync');
    Route::post('/websites/ga4-property/{website}', [GA4Controller::class, 'updateProperty'])->name('ga4.property');
    Route::post('/websites/ga4-disconnect/{website}', [GA4Controller::class, 'disconnect'])->name('ga4.disconnect');
});

// Analytics Dashboard
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/website-analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/website-analytics/view/{website}', [AnalyticsController::class, 'view'])->name('analytics.view');
    Route::post('/website-analytics/generate-ai-insights/{website}', [AnalyticsController::class, 'generateAIInsights'])->name('analytics.ai-insights');
});
```

### **Layer 2: Controllers**

**GSCController.php**
- `connect()` - Generate OAuth URL, redirect user
- `callback()` - Handle OAuth callback, save tokens
- `sync()` - Trigger sync job (keywords + pages)
- `disconnect()` - Remove connection & data

**GA4Controller.php**
- `connect()` - Generate OAuth URL, redirect user
- `callback()` - Handle OAuth callback, save tokens
- `sync()` - Trigger sync job (traffic + referrals)
- `updateProperty()` - Update GA4 property ID
- `disconnect()` - Remove connection & data

**AnalyticsController.php**
- `index()` - List all websites dengan analytics status
- `view()` - Detailed analytics dashboard (GSC + GA4 data)
- `generateAIInsights()` - AI analysis via AJAX

### **Layer 3: Services**

**GoogleSearchConsoleService.php**
- `getAuthorizationUrl($websiteId)` - Generate OAuth URL
- `handleCallback($code, $websiteId)` - Exchange code untuk tokens
- `syncKeywords($connectionId, $startDate, $endDate)` - Sync keyword data
- `syncPages($connectionId, $startDate, $endDate)` - Sync page data
- `refreshAccessToken($connection)` - Auto-refresh expired token
- `getTopKeywords($websiteId, $days)` - Get top performing keywords
- `getKeywordOpportunities($websiteId)` - Get optimization opportunities

**GoogleAnalytics4Service.php**
- `getAuthorizationUrl($websiteId)` - Generate OAuth URL
- `handleCallback($code, $websiteId)` - Exchange code untuk tokens
- `syncTrafficData($connectionId, $startDate, $endDate)` - Sync traffic metrics
- `syncReferralSources($connectionId, $startDate, $endDate)` - Sync referral data
- `syncPageStats($connectionId, $startDate, $endDate)` - Sync page stats
- `refreshAccessToken($connection)` - Auto-refresh expired token
- `getTrafficSummary($websiteId, $days)` - Get traffic summary
- `getTopReferrals($websiteId, $days)` - Get top referral sources

**AIAnalyticsService.php** (New)
- `generateInsights($gscData, $ga4Data)` - Generate AI insights
- Provider: Gemini 2.0 Flash
- Category: `analytics_insights` (new)
- Prompt template: Analyze GSC + GA4 → Strategic recommendations

### **Layer 4: Models**

**GSCConnection.php**
```php
protected $fillable = [
    'website_id', 'property_url', 'access_token', 'refresh_token',
    'token_expires_at', 'verified', 'last_sync_at', 'sync_status', 'sync_error'
];

protected $casts = [
    'token_expires_at' => 'datetime',
    'last_sync_at' => 'datetime',
    'verified' => 'boolean'
];

// Relationships
public function website() {
    return $this->belongsTo(Website::class);
}

public function keywords() {
    return $this->hasMany(GSCKeyword::class, 'connection_id');
}

public function pages() {
    return $this->hasMany(GSCPage::class, 'connection_id');
}

// Helpers
public function isTokenExpired() {
    return $this->token_expires_at < now();
}

public static function getActiveConnection($websiteId) {
    return self::where('website_id', $websiteId)
               ->where('verified', true)
               ->first();
}
```

**GSCKeyword.php**
```php
protected $fillable = [
    'website_id', 'connection_id', 'date', 'keyword', 'clicks',
    'impressions', 'ctr', 'position', 'country', 'device', 'page_url'
];

protected $casts = [
    'date' => 'date',
    'ctr' => 'float',
    'position' => 'float'
];

public function website() {
    return $this->belongsTo(Website::class);
}

public function connection() {
    return $this->belongsTo(GSCConnection::class, 'connection_id');
}
```

**GA4Connection.php**
```php
protected $fillable = [
    'website_id', 'property_id', 'account_id', 'property_name',
    'access_token', 'refresh_token', 'token_expires_at',
    'verified', 'last_sync_at', 'sync_status', 'sync_error'
];

protected $casts = [
    'token_expires_at' => 'datetime',
    'last_sync_at' => 'datetime',
    'verified' => 'boolean'
];

// Relationships
public function website() {
    return $this->belongsTo(Website::class);
}

public function trafficData() {
    return $this->hasMany(GA4TrafficData::class, 'connection_id');
}

public function referralSources() {
    return $this->hasMany(GA4ReferralSource::class, 'connection_id');
}

// Helpers
public function isTokenExpired() {
    return $this->token_expires_at < now();
}
```

**GA4TrafficData.php**
```php
protected $fillable = [
    'website_id', 'connection_id', 'date', 'users', 'new_users',
    'sessions', 'page_views', 'avg_session_duration', 'bounce_rate',
    'engagement_rate', 'conversions'
];

protected $casts = [
    'date' => 'date',
    'avg_session_duration' => 'float',
    'bounce_rate' => 'float',
    'engagement_rate' => 'float'
];
```

**GA4ReferralSource.php**
```php
protected $fillable = [
    'website_id', 'connection_id', 'date', 'source', 'medium',
    'users', 'sessions', 'page_views', 'avg_session_duration', 'bounce_rate'
];

protected $casts = [
    'date' => 'date',
    'avg_session_duration' => 'float',
    'bounce_rate' => 'float'
];
```

### **Layer 5: Jobs (Background Processing)**

**SyncGSCKeywords.php**
```php
class SyncGSCKeywords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    protected $connectionId;
    protected $startDate;
    protected $endDate;

    public function handle()
    {
        $service = new GoogleSearchConsoleService();
        $service->syncKeywords($this->connectionId, $this->startDate, $this->endDate);
    }
}
```

**SyncGA4Traffic.php**
```php
class SyncGA4Traffic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    protected $connectionId;
    protected $startDate;
    protected $endDate;

    public function handle()
    {
        $service = new GoogleAnalytics4Service();
        $service->syncTrafficData($this->connectionId, $this->startDate, $this->endDate);
        $service->syncReferralSources($this->connectionId, $this->startDate, $this->endDate);
    }
}
```

### **Layer 6: Policies**

**AnalyticsPolicy.php**
```php
class AnalyticsPolicy
{
    public function view(User $user, Website $website)
    {
        return $user->id === $website->created_by;
    }

    public function sync(User $user, Website $website)
    {
        return $user->id === $website->created_by;
    }

    public function connect(User $user, Website $website)
    {
        return $user->id === $website->created_by;
    }

    public function disconnect(User $user, Website $website)
    {
        return $user->id === $website->created_by;
    }
}
```

---

## 🎨 UI/UX COMPONENTS

### **Dashboard Layout**

**File:** `resources/views/analytics/index.blade.php`

**Structure:**
```blade
@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Website Analytics</h1>
            <p class="text-gray-600">View analytics from GSC, GA4, and AI insights</p>
        </div>
        <a href="{{ route('websites.create') }}" class="btn-primary">
            Add Website
        </a>
    </div>

    <!-- Websites Table -->
    <div class="bg-white rounded-lg shadow">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>Website</th>
                    <th>Keywords Tracked</th>
                    <th>Research Sessions</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($websites as $website)
                <tr>
                    <td>{{ $website->name }}</td>
                    <td>{{ $website->keyword_count }}</td>
                    <td>{{ $website->research_sessions_count }}</td>
                    <td>{{ $website->updated_at->diffForHumans() }}</td>
                    <td>
                        <a href="{{ route('analytics.view', $website) }}" class="btn-primary">
                            View Analytics
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### **Analytics View (Detailed)**

**File:** `resources/views/analytics/view.blade.php`

**Sections:**
1. **Header** - Website name, breadcrumbs, action menu
2. **GSC Section** (if connected)
   - Connection status badge
   - Sync button
   - 5 metric cards (Keywords, Clicks, Impressions, CTR, Position)
   - Top performing keywords table (10 items)
   - Keyword opportunities table (high impressions, low CTR)
3. **GA4 Section** (if connected)
   - Connection status badge
   - Sync button
   - 6 metric cards (Users, Sessions, Pageviews, Duration, Bounce, Engagement)
   - Top referral sources table
4. **AI Insights Section**
   - Generate button
   - 4 insight cards (SEO, Traffic, Recommendations, Actions)
5. **Modals**
   - GA4 settings modal (property ID config)
   - Add competitor modal

### **Color Coding Standards**

```css
/* Position Badges */
.position-1-3 { @apply bg-green-500 text-white; }  /* Top 3 */
.position-4-10 { @apply bg-blue-500 text-white; }  /* Page 1 */
.position-11-20 { @apply bg-yellow-500 text-white; } /* Page 2 */
.position-21-plus { @apply bg-gray-500 text-white; } /* Page 3+ */

/* Bounce Rate Badges */
.bounce-excellent { @apply bg-green-500 text-white; } /* <40% */
.bounce-average { @apply bg-yellow-500 text-white; } /* 40-70% */
.bounce-poor { @apply bg-red-500 text-white; } /* >70% */

/* Connection Status */
.status-connected { @apply bg-green-100 text-green-800 border-green-500; }
.status-syncing { @apply bg-blue-100 text-blue-800 border-blue-500; }
.status-failed { @apply bg-red-100 text-red-800 border-red-500; }
.status-not-connected { @apply bg-gray-100 text-gray-800 border-gray-500; }
```

---

## ⚙️ IMPLEMENTATION STEPS

### **Phase 1: Database & Models (Week 1)**

**Day 1-2: Migrations**
- [ ] Create migration: `create_gsc_connections_table`
- [ ] Create migration: `create_gsc_keywords_table`
- [ ] Create migration: `create_gsc_pages_table`
- [ ] Create migration: `create_gsc_sync_jobs_table`
- [ ] Create migration: `create_keyword_cache_table`
- [ ] Create migration: `create_ga4_connections_table`
- [ ] Create migration: `create_ga4_traffic_data_table`
- [ ] Create migration: `create_ga4_referral_sources_table`
- [ ] Create migration: `create_ga4_page_stats_table`
- [ ] Create database views (v_gsc_keywords_latest, v_gsc_top_keywords, v_gsc_connections_status)
- [ ] Run migrations and verify schema

**Day 3-4: Models**
- [ ] Create model: `GSCConnection.php` (with relationships)
- [ ] Create model: `GSCKeyword.php`
- [ ] Create model: `GSCPage.php`
- [ ] Create model: `GSCSyncJob.php`
- [ ] Create model: `KeywordCache.php`
- [ ] Create model: `GA4Connection.php` (with relationships)
- [ ] Create model: `GA4TrafficData.php`
- [ ] Create model: `GA4ReferralSource.php`
- [ ] Create model: `GA4PageStat.php`
- [ ] Add relationships to `Website.php` (gscConnection, ga4Connection)

**Day 5: Policies**
- [ ] Create policy: `AnalyticsPolicy.php`
- [ ] Register policy in `AuthServiceProvider`
- [ ] Test authorization rules

### **Phase 2: OAuth & Services (Week 2)**

**Day 1-2: Google OAuth Setup**
- [ ] Setup Google Cloud Console project
- [ ] Enable GSC API & GA4 API
- [ ] Create OAuth 2.0 credentials
- [ ] Configure OAuth consent screen
- [ ] Add test users
- [ ] Add env variables (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET)

**Day 3-5: GSC Service**
- [ ] Create service: `GoogleSearchConsoleService.php`
- [ ] Method: `getAuthorizationUrl()`
- [ ] Method: `handleCallback()`
- [ ] Method: `syncKeywords()`
- [ ] Method: `syncPages()`
- [ ] Method: `refreshAccessToken()`
- [ ] Method: `getTopKeywords()`
- [ ] Method: `getKeywordOpportunities()`
- [ ] Test OAuth flow
- [ ] Test keyword sync

**Day 6-7: GA4 Service**
- [ ] Create service: `GoogleAnalytics4Service.php`
- [ ] Method: `getAuthorizationUrl()`
- [ ] Method: `handleCallback()`
- [ ] Method: `syncTrafficData()`
- [ ] Method: `syncReferralSources()`
- [ ] Method: `syncPageStats()`
- [ ] Method: `refreshAccessToken()`
- [ ] Method: `getTrafficSummary()`
- [ ] Method: `getTopReferrals()`
- [ ] Test OAuth flow
- [ ] Test traffic sync

### **Phase 3: Controllers & Routes (Week 3)**

**Day 1-2: GSC Controller**
- [ ] Create controller: `GSCController.php`
- [ ] Method: `connect()` - Redirect to OAuth
- [ ] Method: `callback()` - Handle OAuth callback
- [ ] Method: `sync()` - Trigger sync job
- [ ] Method: `disconnect()` - Remove connection
- [ ] Add routes in `routes/web.php`
- [ ] Test connection flow end-to-end

**Day 3-4: GA4 Controller**
- [ ] Create controller: `GA4Controller.php`
- [ ] Method: `connect()` - Redirect to OAuth
- [ ] Method: `callback()` - Handle OAuth callback
- [ ] Method: `sync()` - Trigger sync job
- [ ] Method: `updateProperty()` - Set GA4 property ID
- [ ] Method: `disconnect()` - Remove connection
- [ ] Add routes in `routes/web.php`
- [ ] Test connection flow end-to-end

**Day 5-7: Analytics Controller**
- [ ] Create controller: `AnalyticsController.php`
- [ ] Method: `index()` - List websites
- [ ] Method: `view()` - Detailed dashboard
- [ ] Method: `generateAIInsights()` - AI analysis
- [ ] Add routes in `routes/web.php`
- [ ] Test data retrieval

### **Phase 4: Background Jobs (Week 3-4)**

**Day 1-2: GSC Sync Jobs**
- [ ] Create job: `SyncGSCKeywords.php`
- [ ] Create job: `SyncGSCPages.php`
- [ ] Implement retry logic (3 attempts)
- [ ] Implement progress tracking
- [ ] Test with queue worker

**Day 3-4: GA4 Sync Jobs**
- [ ] Create job: `SyncGA4Traffic.php`
- [ ] Create job: `SyncGA4Referrals.php`
- [ ] Create job: `SyncGA4Pages.php`
- [ ] Implement retry logic (3 attempts)
- [ ] Implement progress tracking
- [ ] Test with queue worker

**Day 5: Scheduled Tasks**
- [ ] Create command: `SyncAllGSCConnections.php` (daily cron)
- [ ] Create command: `SyncAllGA4Connections.php` (daily cron)
- [ ] Add to `app/Console/Kernel.php`
- [ ] Test cron schedule

### **Phase 5: AI Analytics Service (Week 4)**

**Day 1-2: AI Service**
- [ ] Create service: `AIAnalyticsService.php`
- [ ] Method: `generateInsights($gscData, $ga4Data)`
- [ ] Create prompt template for analytics
- [ ] Add API category: `analytics_insights` to admin panel
- [ ] Test AI generation

**Day 3-4: Integration**
- [ ] Integrate AI service dengan AnalyticsController
- [ ] Create AJAX endpoint for AI insights
- [ ] Implement loading state
- [ ] Implement error handling
- [ ] Test end-to-end

### **Phase 6: UI/Views (Week 5)**

**Day 1-2: Dashboard Index**
- [ ] Create view: `resources/views/analytics/index.blade.php`
- [ ] Website list table
- [ ] Connection status badges
- [ ] Action buttons
- [ ] Responsive design (Tailwind)

**Day 3-5: Analytics View**
- [ ] Create view: `resources/views/analytics/view.blade.php`
- [ ] Header & breadcrumbs
- [ ] GSC section (cards + tables)
- [ ] GA4 section (cards + tables)
- [ ] AI insights section
- [ ] Modals (GA4 settings, add competitor)
- [ ] Responsive design
- [ ] Charts (optional: Chart.js or ApexCharts)

**Day 6-7: Components**
- [ ] Create component: `MetricCard.blade.php`
- [ ] Create component: `ConnectionBadge.blade.php`
- [ ] Create component: `PositionBadge.blade.php`
- [ ] Create component: `BounceRateBadge.blade.php`
- [ ] Polish UI/UX
- [ ] Cross-browser testing

### **Phase 7: Testing & Debugging (Week 6)**

**Day 1-2: Unit Tests**
- [ ] Test: `GSCConnection` model
- [ ] Test: `GA4Connection` model
- [ ] Test: `GoogleSearchConsoleService`
- [ ] Test: `GoogleAnalytics4Service`
- [ ] Test: `AIAnalyticsService`

**Day 3-4: Feature Tests**
- [ ] Test: GSC OAuth flow
- [ ] Test: GA4 OAuth flow
- [ ] Test: Keyword sync
- [ ] Test: Traffic sync
- [ ] Test: AI insights generation
- [ ] Test: Authorization policies

**Day 5-7: Integration & Bug Fixing**
- [ ] End-to-end testing
- [ ] Fix bugs
- [ ] Performance optimization
- [ ] Documentation updates

### **Phase 8: Documentation & Deployment (Week 6-7)**

**Day 1-2: Documentation**
- [ ] Update README.md
- [ ] Create user guide (How to connect GSC)
- [ ] Create user guide (How to connect GA4)
- [ ] API documentation

**Day 3-4: Deployment Preparation**
- [ ] Environment variables setup (production)
- [ ] Google OAuth production credentials
- [ ] Database migrations (production)
- [ ] Queue worker setup (production)

**Day 5: Deployment**
- [ ] Deploy to production
- [ ] Verify OAuth flow
- [ ] Monitor logs
- [ ] Fix deployment issues

---

## 🧪 TESTING STRATEGY

### **Manual Testing Checklist**

**GSC Connection:**
- [ ] User can click "Connect GSC"
- [ ] User redirected to Google OAuth consent
- [ ] After consent, callback works correctly
- [ ] Tokens saved in database
- [ ] Connection badge shows "Connected"
- [ ] Sync button triggers sync job
- [ ] Keywords appear in table after sync
- [ ] Disconnect removes connection & data

**GA4 Connection:**
- [ ] User can click "Connect GA4"
- [ ] User redirected to Google OAuth consent
- [ ] After consent, callback works correctly
- [ ] Tokens saved in database
- [ ] User can input GA4 Property ID
- [ ] Sync button triggers sync job
- [ ] Traffic data appears in cards after sync
- [ ] Referral sources appear in table
- [ ] Disconnect removes connection & data

**AI Insights:**
- [ ] Generate button triggers AI call
- [ ] Loading state shows spinner
- [ ] AI insights render with HTML
- [ ] 4 cards populated (SEO, Traffic, Recommendations, Actions)
- [ ] Error handling works

**Multi-Tenant Security:**
- [ ] User A cannot view User B's analytics
- [ ] Authorization policies enforced
- [ ] Database queries always filter by `created_by`

**Token Refresh:**
- [ ] Expired token auto-refreshes before API call
- [ ] Refresh token persists in database
- [ ] No errors when token expires

### **Automated Tests**

**Example: Feature Test**

```php
// tests/Feature/GSCConnectionTest.php

public function test_user_can_connect_gsc()
{
    $user = User::factory()->create();
    $website = Website::factory()->create(['created_by' => $user->id]);

    $this->actingAs($user)
         ->get(route('gsc.connect', $website))
         ->assertRedirect(); // Redirect to Google OAuth

    // Mock OAuth callback
    $this->actingAs($user)
         ->get(route('gsc.callback', ['code' => 'test-auth-code', 'state' => $website->id]))
         ->assertRedirect(route('analytics.view', $website))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('ane_gsc_connections', [
        'website_id' => $website->id,
        'verified' => true
    ]);
}

public function test_user_cannot_view_other_user_analytics()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $website = Website::factory()->create(['created_by' => $user1->id]);

    $this->actingAs($user2)
         ->get(route('analytics.view', $website))
         ->assertForbidden();
}
```

---

## 📦 SERVICE DEPENDENCIES & INTEGRATION

### **1. AIArticleService Integration**

File yang sudah ada di anewp menunjukkan bahwa AI Article Generation terintegrasi dengan fitur analytics:

**Use Cases:**
1. **Generate Article from GSC Keyword Opportunities**
   - User pilih keyword dengan high impressions + low CTR
   - Click "Generate Article for This Keyword"
   - AIArticleService creates SEO-optimized blog post targeting keyword tersebut

2. **Generate Article from Top Performing Keywords**
   - User pilih keyword dengan top position + high clicks
   - AIArticleService creates content to maintain rankings

**Implementation Pattern (From anewp/AIArticleService.php):**
```php
// Method: generateArticleFromContext($sourceContext, $options)
$sourceContext = [
    'title' => "Tips Meningkatkan CTR untuk Keyword: {$keyword}",
    'description' => "Keyword ini ranking posisi {$position} tapi CTR hanya {$ctr}%",
    'source_url' => '', // No URL for keyword-based generation
    'source_name' => 'gsc_keyword_opportunity'
];

$options = [
    'article_type' => 'blog', // Blog article for SEO
    'language' => 'id',
    'word_count' => 1250, // Blog default
    'tone' => 'professional'
];

$result = $aiService->generateArticleFromContext($sourceContext, $options);
```

**Key Features dari AIArticleService:**
- **Multi-Provider Support:** Gemini 2.0 Flash (primary), Claude 3.5 Sonnet, GPT-4o Mini
- **Firecrawl Integration:** Auto-fetch full article content from URL if provided
- **Smart Prompts:** Different prompts for News (journalistic) vs Blog (SEO content)
- **Code Preservation:** Uses `<pre><code>` tags for tutorial/tech content
- **Title Uniqueness:** AI instructed to generate COMPLETELY unique titles each time
- **Source Attribution:** News articles MUST cite sources ("Menurut...", "Dilansir dari...")

### **2. NewsInsightService Integration**

Meskipun ini bukan bagian dari GSC/GA4, NewsInsightService bisa diintegrasikan dengan analytics:

**Use Case:**
- User melihat trending keyword di GSC
- Click "Find News for This Keyword"
- NewsInsightService search news via Serper API
- User convert news results to insights → generate articles

**Pattern:**
```php
// Search news about trending keyword
$newsService = new NewsInsightService();
$result = $newsService->performSearch(
    $userId,
    'kompas.com', // Source
    $keyword,     // Theme from GSC
    'week'        // Time range
);

// Convert to insights
$insights = $newsService->convertToInsights($resultIds, $userId, $websiteId);
```

### **3. SerperSearchService for Deep Research**

**Deep Research Flow (From anewp):**
1. User enters seed keyword (e.g., "SEO tips")
2. SerperSearchService gets autocomplete suggestions
3. DataForSEO API fetches search volume + competition
4. Gemini AI analyzes and recommends:
   - Main target keywords (high volume, low competition)
   - Informational keywords (for content strategy)
5. User generates articles for recommended keywords

**Integration dengan GSC:**
- GSC menunjukkan keyword yang SUDAH ranking
- Deep Research mencari keyword BARU yang related
- Combined strategy: Optimize existing + Target new keywords

### **4. Multi-Tenant Security Pattern**

**CRITICAL:** All services in anewp follow this pattern:

```php
// ✅ CORRECT - Always filter by user
$keywords = GSCKeyword::where('website_id', $websiteId)
    ->whereHas('website', function($query) {
        $query->where('created_by', auth()->id());
    })
    ->get();

// ❌ WRONG - Exposes all users' data
$keywords = GSCKeyword::where('website_id', $websiteId)->get();
```

**Laravel Implementation:**
- Use **WebsitePolicy** to enforce ownership
- All controllers MUST call `$this->authorize('view', $website)` before data access
- All queries MUST scope to `auth()->id()`

### **5. Background Processing Pattern**

**Session-Based Job Queue (anewp pattern):**
```php
// Before long operation
session_write_close(); // Release session lock IMMEDIATELY

// Store job status
$jobData = [
    'status' => 'processing',
    'progress' => 0,
    'message' => 'Syncing GSC keywords...'
];
DB::table('job_status')->insert($jobData);

// Client polls status
// Frontend: setInterval(checkJobStatus, 3000)
```

**Laravel Equivalent:**
- Use Laravel Queues with Redis/Database driver
- Dispatch jobs with `SyncGSCKeywords::dispatch($connectionId)`
- Frontend polls API endpoint for job status
- Use `failed_jobs` table for error tracking

### **6. API Rate Limiting & Cost Optimization**

**GSC API Limits:**
- 25,000 requests/day (FREE)
- Max 5,000 rows per request
- Strategy: Cache results for 6-24 hours, only sync new data daily

**GA4 API Limits:**
- 10,000 requests/day (FREE)
- Max 100,000 rows per request
- Strategy: Aggregate data per day, not per user

**Serper API Limits (News Insights):**
- 2,500 searches/month FREE plan ($50/month for 5,000)
- Strategy: Cache search results for 6 hours (anewp uses this)

**Firecrawl API Limits (Article Generation):**
- 500 credits/month FREE plan
- Strategy: Only use when user explicitly generates article from URL

---

## 🚨 COMMON ISSUES & SOLUTIONS

### **Issue 1: "Invalid grant" error during OAuth**
**Cause:** Refresh token expired atau revoked
**Solution:**
- User needs to disconnect & reconnect
- Google automatically revokes refresh_token after 6 months of inactivity
- Check `token_expires_at` before API calls

### **Issue 2: "Insufficient Permission" error**
**Cause:** User belum grant permission ke GSC/GA4 property
**Solution:**
- Verify user is owner/admin di Google Search Console
- Verify user has read access ke GA4 property
- Check OAuth scopes (`webmasters.readonly`, `analytics.readonly`)

### **Issue 3: GSC property URL mismatch**
**Cause:** Website URL di database ≠ GSC property URL
**Solution:**
- Auto-detect property dari domain matching
- Fallback: User pilih property dari list
- Store `property_url` in `ane_gsc_connections`

### **Issue 4: GA4 Property ID not found**
**Cause:** User input wrong Property ID
**Solution:**
- Validate Property ID format (numeric, 9 digits)
- Test API call after input
- Show clear error message dengan link ke GA4 admin panel

### **Issue 5: Sync timeout (>10 minutes)**
**Cause:** Dataset terlalu besar (millions of keywords)
**Solution:**
- Use pagination (max 5,000 rows per request)
- Process in chunks
- Increase job timeout to 600 seconds
- Use background queue worker

### **Issue 6: Rate limit exceeded**
**Cause:** Too many API calls dalam 24 jam
**Solution:**
- Cache data for 24 hours
- Implement exponential backoff
- Sync only necessary date ranges (last 7 days)
- Monitor daily quota usage

---

## 📊 PERFORMANCE OPTIMIZATION

### **Database Indexing**
```sql
-- GSC Keywords
CREATE INDEX idx_website_date ON ane_gsc_keywords(website_id, date);
CREATE INDEX idx_keyword ON ane_gsc_keywords(keyword);
CREATE INDEX idx_clicks ON ane_gsc_keywords(clicks DESC);
CREATE INDEX idx_position ON ane_gsc_keywords(position);

-- GA4 Traffic
CREATE INDEX idx_website_date ON ane_ga4_traffic_data(website_id, date);
CREATE INDEX idx_connection_date ON ane_ga4_traffic_data(connection_id, date);

-- GA4 Referrals
CREATE INDEX idx_source_medium ON ane_ga4_referral_sources(source, medium);
CREATE INDEX idx_date ON ane_ga4_referral_sources(date);
```

### **Query Optimization**

**BAD:**
```php
// N+1 query problem
$websites = Website::where('created_by', auth()->id())->get();
foreach ($websites as $website) {
    $website->gscConnection; // Extra query per website!
}
```

**GOOD:**
```php
// Eager loading
$websites = Website::where('created_by', auth()->id())
    ->with('gscConnection', 'ga4Connection')
    ->get();
```

### **Caching Strategy**

```php
// Cache GSC summary for 1 hour
$gscSummary = Cache::remember("gsc_summary_{$websiteId}", 3600, function () use ($websiteId) {
    return DB::table('v_gsc_top_keywords')
        ->where('website_id', $websiteId)
        ->limit(10)
        ->get();
});

// Cache GA4 traffic for 1 hour
$ga4Traffic = Cache::remember("ga4_traffic_{$websiteId}", 3600, function () use ($websiteId) {
    return GA4TrafficData::where('website_id', $websiteId)
        ->where('date', '>=', now()->subDays(30))
        ->orderBy('date', 'desc')
        ->get();
});
```

### **Background Processing**

```php
// DON'T do this (blocks HTTP response)
public function sync(Website $website)
{
    $service = new GoogleSearchConsoleService();
    $service->syncKeywords($connection->id); // Takes 5 minutes!

    return redirect()->back()->with('success', 'Synced');
}

// DO this (async processing)
public function sync(Website $website)
{
    SyncGSCKeywords::dispatch($connection->id, now()->subDays(30), now());

    return redirect()->back()->with('success', 'Sync started! Check back in a few minutes.');
}
```

---

## 🔐 SECURITY CONSIDERATIONS

### **1. Token Encryption**

**Recommended:** Encrypt OAuth tokens in database

```php
// In GSCConnection model
protected $casts = [
    'access_token' => 'encrypted',
    'refresh_token' => 'encrypted'
];
```

**Why?**
- Tokens are sensitive (full access to user's GSC/GA4)
- Database breach exposes tokens
- Laravel's `Crypt` uses AES-256-CBC

### **2. Multi-Tenant Isolation**

**ALWAYS filter by user:**
```php
// ✅ CORRECT
GSCConnection::where('website_id', $websiteId)
    ->whereHas('website', function ($query) {
        $query->where('created_by', auth()->id());
    })
    ->first();

// ❌ WRONG (exposes all users' data)
GSCConnection::find($connectionId);
```

### **3. Authorization Policies**

```php
// Before any analytics action
$this->authorize('view', $website);
$this->authorize('sync', $website);
$this->authorize('disconnect', $website);
```

### **4. OAuth State Parameter**

**Purpose:** Prevent CSRF attacks

```php
// Generate state
$client->setState($websiteId);

// Validate state in callback
if ($request->state != $expectedWebsiteId) {
    abort(403, 'Invalid state parameter');
}
```

### **5. Rate Limiting**

```php
// In routes/web.php
Route::post('/gsc-sync/{website}', [GSCController::class, 'sync'])
    ->middleware('throttle:5,60'); // Max 5 sync per 60 minutes
```

---

## 📈 MONITORING & LOGGING

### **Key Metrics to Monitor**

1. **OAuth Success Rate**
   - Track callback success/failure
   - Monitor token refresh failures
   - Alert if success rate < 95%

2. **Sync Job Performance**
   - Average sync duration
   - Failed jobs count
   - Retry attempts

3. **API Quota Usage**
   - GSC API calls per day
   - GA4 API calls per day
   - Alert when approaching limit (20,000/25,000)

4. **Data Freshness**
   - Hours since last sync
   - Alert if >48 hours

### **Logging Strategy**

```php
// Log OAuth flow
Log::info('[GSC] OAuth started', ['website_id' => $websiteId]);
Log::info('[GSC] Connection established', ['property_url' => $propertyUrl]);
Log::error('[GSC] OAuth failed', ['error' => $e->getMessage()]);

// Log sync jobs
Log::info('[GSC Sync] Started', ['connection_id' => $connectionId, 'date_range' => "$startDate to $endDate"]);
Log::info('[GSC Sync] Completed', ['keywords_synced' => $count, 'duration' => $duration]);
Log::error('[GSC Sync] Failed', ['error' => $e->getMessage(), 'connection_id' => $connectionId]);
```

---

## 🎓 LEARNING RESOURCES

### **Google APIs Documentation**
- [Google Search Console API](https://developers.google.com/webmaster-tools/search-console-api-original)
- [Google Analytics Data API (GA4)](https://developers.google.com/analytics/devguides/reporting/data/v1)
- [OAuth 2.0 for Web Apps](https://developers.google.com/identity/protocols/oauth2/web-server)

### **Laravel Resources**
- [Laravel Queues](https://laravel.com/docs/12.x/queues)
- [Laravel Policies](https://laravel.com/docs/12.x/authorization)
- [Laravel Encryption](https://laravel.com/docs/12.x/encryption)

### **Libraries**
- [google/apiclient](https://github.com/googleapis/google-api-php-client)
- [google/analytics-data](https://github.com/googleapis/google-cloud-php-analytics-data)

---

## ✅ MIGRATION CHECKLIST

### **Pre-Implementation**
- [ ] Read full MIGRASI-GA4-GSC.md document
- [ ] Setup Google Cloud Console project
- [ ] Create OAuth 2.0 credentials
- [ ] Configure OAuth consent screen
- [ ] Add test users
- [ ] Install composer dependencies (`google/apiclient`, `google/analytics-data`)
- [ ] Add environment variables

### **Database**
- [ ] Create 9 migrations (GSC + GA4 tables)
- [ ] Create 3 database views
- [ ] Run migrations
- [ ] Verify schema

### **Models**
- [ ] Create 9 models dengan relationships
- [ ] Add relationships to Website model
- [ ] Test model queries

### **Services**
- [ ] Implement GoogleSearchConsoleService
- [ ] Implement GoogleAnalytics4Service
- [ ] Implement AIAnalyticsService
- [ ] Test OAuth flow
- [ ] Test sync methods

### **Controllers**
- [ ] Create GSCController
- [ ] Create GA4Controller
- [ ] Create AnalyticsController
- [ ] Add routes
- [ ] Test endpoints

### **Jobs**
- [ ] Create SyncGSCKeywords job
- [ ] Create SyncGA4Traffic job
- [ ] Test queue processing
- [ ] Create scheduled commands

### **Policies**
- [ ] Create AnalyticsPolicy
- [ ] Register in AuthServiceProvider
- [ ] Test authorization

### **Views**
- [ ] Create analytics/index.blade.php
- [ ] Create analytics/view.blade.php
- [ ] Create components (MetricCard, Badges)
- [ ] Test responsive design

### **Testing**
- [ ] Write unit tests
- [ ] Write feature tests
- [ ] End-to-end testing
- [ ] Fix bugs

### **Documentation**
- [ ] Update README.md
- [ ] Create user guides
- [ ] Document API endpoints

### **Deployment**
- [ ] Setup production OAuth credentials
- [ ] Run migrations (production)
- [ ] Configure queue worker
- [ ] Monitor logs
- [ ] Fix deployment issues

---

## 🎯 SUCCESS CRITERIA

Fitur dinyatakan **berhasil** jika:

✅ User dapat connect GSC via OAuth
✅ User dapat connect GA4 via OAuth
✅ Keyword data tersync dari GSC (last 30 days)
✅ Traffic data tersync dari GA4 (last 30 days)
✅ Referral sources tersync dari GA4
✅ Dashboard menampilkan metrics dengan benar
✅ AI insights generate recommendations
✅ Multi-tenant isolation berfungsi (user hanya lihat data mereka)
✅ Token auto-refresh sebelum expire
✅ Background jobs process async tanpa timeout
✅ UI responsive di mobile & desktop
✅ Zero errors di production logs

---

**END OF MIGRATION GUIDE**

Total Estimated Time: **6-7 weeks**
Priority: **HIGH** (Core Feature #2 setelah Website Management)
Complexity: **HIGH** (OAuth, Background Jobs, Multi-API Integration)
