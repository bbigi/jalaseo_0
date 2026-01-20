# TikTok Trends Hybrid Content Strategy

**Panduan Lengkap: Dari Trending Topic ke Multi-Platform Content**

---

## Overview

Strategi **Hybrid Approach** menggabungkan kecepatan (quick win) dengan kekuatan SEO (long-term traffic). Satu riset trending menghasilkan 4 artikel: 1 roundup + 3 deep dive.

**Prinsip Dasar:**
- **Research Once, Publish Everywhere** (ROPE)
- **Quick Win + Long-term SEO**
- **Content Repurposing** (1 topik → 5+ format)

**Target Traffic:**
- Google Search (blog articles)
- YouTube Search (video embed)
- TikTok/Instagram FYP (short clips)
- Pinterest Search (visual pins)
- Twitter/LinkedIn (social shares)

---

## Phase 1: Research (Social Insights)

### Step 1: User Melakukan Search Trending

**User Journey:**
1. Login ke AneWP → menu **Insights > Social Insights**
2. Klik **Search Trending**
3. Pilih platform: **TikTok** (atau Twitter/Google Trends)
4. Pilih negara: **ID** (Indonesia) atau US/UK/MY/SG
5. Keyword: **kosongkan** (untuk trending umum) atau isi keyword spesifik
6. Klik **Search**

**System Response:**
- API call ke RapidAPI (TikTok Scraper7)
- Fetch 20 trending videos/topics
- Save ke database:
  - Table: `ane_social_searches` (metadata)
  - Table: `ane_social_results` (20 trending topics)
- Redirect ke **Results Page**

### Step 2: Review Trending Results

**User melihat 20 trending topics dengan data:**
- Content/Title
- Author (@username)
- Engagement metrics (likes, comments, shares)
- Hashtags
- Link (TikTok URL)
- Total engagement score (calculated)

**Contoh Data:**

| No | Content | Author | Likes | Comments | Shares | Total Engagement |
|----|---------|--------|-------|----------|--------|------------------|
| 1 | Cara Budget Traveling ke Jepang $500 | @traveler_id | 125K | 3.2K | 8.5K | 136.7K |
| 2 | Resep Ayam Geprek Viral ala Warteg | @kuliner_nusantara | 98K | 2.1K | 4.3K | 104.4K |
| 3 | 5 Tips Keuangan Gen Z Harus Tahu | @finansial_muda | 87K | 1.8K | 3.9K | 92.7K |
| ... | ... | ... | ... | ... | ... | ... |

---

## Phase 2: Selection (Manual User Action)

### Step 3: User Memilih Trending Topics untuk Dijadikan Konten

**User Action:**
1. Di Results Page, setiap trending topic ada **checkbox** atau **tombol "Select"**
2. User **pilih 1-10 trending topics** yang mau dijadikan konten
3. User klik **"Create Content from Selected"**

**System Shows Modal/Page:**
```
┌────────────────────────────────────────┐
│ Create Content Strategy                │
├────────────────────────────────────────┤
│ Selected: 10 trending topics           │
│                                        │
│ Choose Content Strategy:               │
│ ○ Roundup Article Only (1 artikel)    │
│ ● Hybrid Strategy (1 roundup + 3 deep)│
│ ○ Deep Dive All (10 artikel terpisah) │
│                                        │
│ [Cancel]  [Generate Content Plan]     │
└────────────────────────────────────────┘
```

**User pilih: Hybrid Strategy** ✅

### Step 4: System Auto-Rank Trending Topics (AI-Powered)

**Ranking Criteria (Weighted Score):**

1. **Engagement Score (40%)**
   - Formula: `(likes * 1) + (comments * 5) + (shares * 10)`
   - Reasoning: Shares > Comments > Likes (in terms of virality)

2. **Evergreen Potential (30%)**
   - AI analyzes content semantics
   - Check if topic is:
     - ✅ Timeless (how-to, tips, tutorial)
     - ❌ Temporary (news, drama, celebrity gossip)
   - Example:
     - "Cara Budget Traveling" → Evergreen score: 9/10
     - "Drama Influencer X vs Y" → Evergreen score: 2/10

3. **SEO Potential (20%)**
   - Cross-check keyword dengan Google Trends API
   - Check search volume untuk topik terkait
   - Example: "budget traveling jepang" → 12K searches/month

4. **Monetization Potential (10%)**
   - Check if topic has affiliate/ads potential
   - Categories with high monetization:
     - ✅ Travel, Finance, Tech, Health
     - ❌ Drama, Gossip, Entertainment

**System Output:**
```
Top 10 Trending Topics (Ranked by AI):

TIER 1 (Deep Dive Candidates - TOP 3):
1. Budget Traveling ke Jepang $500 (Score: 94/100)
   - Engagement: 136.7K (High)
   - Evergreen: 9/10 (Travel how-to)
   - SEO: 12K searches/month
   - Monetization: High (affiliate travel gear, booking)

2. 5 Tips Keuangan Gen Z (Score: 88/100)
   - Engagement: 92.7K (High)
   - Evergreen: 8/10 (Financial tips)
   - SEO: 8.5K searches/month
   - Monetization: High (finance affiliate)

3. Cara Memulai Freelance dari Nol (Score: 82/100)
   - Engagement: 78.3K (Medium-High)
   - Evergreen: 9/10 (How-to guide)
   - SEO: 6.2K searches/month
   - Monetization: Medium (course affiliate)

TIER 2 (Roundup Only - Remaining 7):
4. Resep Ayam Geprek Viral (Score: 71/100)
5. 10 Film Netflix Terbaik Bulan Ini (Score: 68/100)
6. Drama Influencer X vs Y (Score: 45/100)
... (dan seterusnya)
```

---

## Phase 3: Content Creation (AI-Powered)

### Step 5A: Generate Roundup Article (Quick Win)

**Content Specification:**
- **Title:** "10 Trending TikTok Indonesia Minggu Ini (Desember 2024)"
- **Word Count:** 800-1000 kata
- **Structure:**
  ```markdown
  # 10 Trending TikTok Indonesia Minggu Ini

  [Intro 100 kata: context, why trending matters]

  ## 1. Budget Traveling ke Jepang Cuma 7 Juta
  [Summary 80-100 kata]
  **Hashtags:** #budgettravel #jepang #traveltips
  **Engagement:** 136.7K (likes, comments, shares)
  **Link:** [Lihat Video](https://tiktok.com/@traveler_id/video/123)

  ## 2. 5 Tips Keuangan Gen Z Harus Tahu
  [Summary 80-100 kata]
  ...

  [Lanjut sampai 10]

  ---
  **Baca Juga:**
  - [Deep Dive: Panduan Budget Traveling Jepang](#) ← Link ke artikel deep dive
  - [Deep Dive: Tips Keuangan Gen Z Lengkap](#)
  - [Deep Dive: Cara Mulai Freelance dari Nol](#)
  ```

**SEO Settings:**
- Focus Keyword: "trending tiktok indonesia"
- Meta Description: "10 topik viral TikTok Indonesia minggu ini: budget traveling, tips keuangan, freelance, dan lainnya. Update terbaru!"
- Article Type: **News/Blog** (time-sensitive)

**Publishing:**
- Status: **Draft** (user review dulu)
- Target Website: User pilih dari dropdown (WordPress sites)
- Scheduled: **Immediate** (trending = publish ASAP)

**AI Provider:** Gemini 2.0 Flash (fast, cheap, good for listicle)

### Step 5B: Generate Deep Dive Articles (Long-term SEO)

**Untuk Top 3 Trending Topics, generate 3 artikel terpisah:**

---

#### Deep Dive Article #1: Budget Traveling Jepang

**Content Specification:**
- **Title:** "Cara Budget Traveling ke Jepang Cuma Rp 7 Juta (Panduan Lengkap 2025)"
- **Word Count:** 1500-2000 kata
- **Structure:**
  ```markdown
  # Cara Budget Traveling ke Jepang Cuma Rp 7 Juta

  [Intro 150 kata: pain point, promise, authority]

  ## Kenapa Jepang Mahal? (Mitos vs Fakta)
  [200 kata]

  ## Budget Breakdown: 7 Hari Jepang = Rp 7 Juta
  | Item | Budget |
  |------|--------|
  | Tiket pesawat | Rp 2.5 juta |
  | Akomodasi (hostel) | Rp 1.2 juta |
  | Transportasi (JR Pass) | Rp 1.5 juta |
  | Makan | Rp 1 juta |
  | Wisata | Rp 800K |
  | **Total** | **Rp 7 juta** |

  ## 5 Tips Hemat Traveling Jepang
  ### 1. Beli Tiket 3-4 Bulan Sebelumnya
  [300 kata: detail, screenshot, tips]

  ### 2. Pakai JR Pass (Hemat 50%)
  [300 kata]

  ### 3. Menginap di Hostel/Capsule Hotel
  [300 kata]

  ### 4. Makan di Konbini (7-Eleven, Lawson)
  [300 kata]

  ### 5. Wisata Gratis (Taman, Temple,街 Walk)
  [300 kata]

  ## Itinerary 7 Hari Budget Jepang
  [400 kata: day-by-day breakdown]

  ## FAQ Budget Traveling Jepang
  [200 kata: 5-7 pertanyaan umum]

  ## Kesimpulan + CTA
  [100 kata: summary, encourage to start planning]

  ---
  **Baca Juga:**
  - [10 Trending TikTok Minggu Ini](#) ← Link balik ke roundup
  - [Tips Keuangan untuk Traveling](#) ← Internal linking
  ```

**SEO Settings:**
- Focus Keyword: "budget traveling jepang"
- Meta Description: "Panduan lengkap budget traveling Jepang cuma Rp 7 juta untuk 7 hari. Tips hemat tiket, hotel, makan, dan transportasi."
- Article Type: **Blog** (evergreen)

**Featured Image:**
- Generate AI image: "Japan cherry blossom with budget travel concept"
- Or: Search Unsplash via API

**Publishing:**
- Status: **Draft**
- Scheduled: **Tomorrow** (give time for SEO optimization)

**AI Provider:** Gemini 2.0 Flash (untuk draft) → User bisa switch ke ChatGPT/Claude untuk polish

---

#### Deep Dive Article #2: Tips Keuangan Gen Z

**Content Specification:**
- **Title:** "5 Tips Keuangan yang Bisa Bikin Gen Z Kaya di Usia 30"
- **Word Count:** 1500-2000 kata
- **Structure:** (similar pattern dengan artikel #1)

**SEO Settings:**
- Focus Keyword: "tips keuangan gen z"
- Meta Description: "5 tips keuangan praktis untuk Gen Z: nabung, investasi, passive income. Mulai dari Rp 100 ribu per bulan!"

**Publishing:** Draft, scheduled tomorrow

---

#### Deep Dive Article #3: Freelance dari Nol

**Content Specification:**
- **Title:** "Cara Memulai Freelance dari Nol (Panduan Pemula 2025)"
- **Word Count:** 1500-2000 kata

**SEO Settings:**
- Focus Keyword: "cara memulai freelance"
- Meta Description: "Panduan lengkap freelance untuk pemula: platform, pricing, portfolio, client hunting. Tanpa modal, tanpa pengalaman."

**Publishing:** Draft, scheduled day after tomorrow

---

### Step 6: Content Review & Publish

**User Action:**
1. Review 4 artikel (1 roundup + 3 deep dive)
2. Edit jika perlu (Gutenberg editor / manual edit)
3. Select target WordPress website
4. Select category per artikel
5. Publish:
   - Roundup: **Immediate**
   - Deep Dive #1: **Tomorrow 09:00**
   - Deep Dive #2: **Day +2, 09:00**
   - Deep Dive #3: **Day +3, 09:00**

**System Action:**
- Save draft ke `ane_posts`
- Queue publish job ke `ane_post_targets`
- WordPress REST API publish (background job)

---

## Phase 4: Content Repurposing (Social Media)

**NOTE:** Feature ini belum ada, akan dikembangkan di fase berikutnya.

### Step 7A: Generate YouTube Video Script (Long-form)

**Dari Deep Dive Article #1 → YouTube Video**

**User Action:**
1. Di artikel "Budget Traveling Jepang", klik **"Repurpose to YouTube"**
2. System auto-generate:
   - **Video Title:** "Budget Traveling Jepang Cuma 7 Juta! (Budget Breakdown + Tips)"
   - **Video Script:** 8-10 menit (from article outline)
   - **Description:** SEO-optimized (with link to blog)
   - **Timestamps:** Auto-generated dari H2 headings

**Output Format:**
```markdown
# Video Script: Budget Traveling Jepang

## Intro (0:00 - 0:45)
"Halo travelers! Pernah kepikiran ke Jepang tapi takut mahal?
Di video ini gue bakal breakdown budget traveling Jepang cuma 7 juta untuk 7 hari..."

## Budget Breakdown (0:45 - 2:30)
[Show screen: budget table]
"Jadi total budget kita itu 7 juta, breakdown-nya kayak gini..."

## Tip #1: Tiket Pesawat (2:30 - 4:00)
...

[Lanjut sampai outro]

## Outro (9:00 - 9:45)
"Kalau mau baca artikel lengkapnya dengan itinerary detail,
link ada di description ya! Jangan lupa subscribe..."
```

**User Export:**
- Copy script ke Google Docs / Notion
- Record video (via smartphone/camera)
- Edit di CapCut/DaVinci Resolve
- Upload ke YouTube
- Embed YouTube video di blog article (update draft)

---

### Step 7B: Generate Short-form Video Script (TikTok/Reels/Shorts)

**Dari Deep Dive Article #1 → 5 Short Clips**

**User Action:**
1. Klik **"Repurpose to Short Videos"**
2. System generate 5 short scripts (30-60 detik each)

**Output:**

**Clip #1: Hook (Budget Breakdown)**
```
[Hook visual: "Jepang 7 Hari = Rp 7 Juta?!"]

SCRIPT:
"Siapa bilang ke Jepang harus puluhan juta?
Gue buktiin 7 hari di Jepang cuma habis 7 juta!
Breakdown-nya: tiket 2.5 juta, hostel 1.2 juta,
JR Pass 1.5 juta, makan 1 juta, wisata 800K.
Link tutorial lengkap di bio!"

[CTA: "Link di bio 👆"]
```

**Clip #2: Tip #1 (Tiket Murah)**
```
[Hook visual: "Tiket Jepang Cuma 2 Juta!"]

SCRIPT:
"Rahasia dapet tiket Jepang murah: beli 3-4 bulan sebelumnya,
pakai Skyscanner, flexible tanggal, avoid peak season.
Gue dapet Jakarta-Tokyo PP cuma 2.5 juta!"

[CTA: "Save buat next trip! 💾"]
```

... (3 clips lainnya untuk tip #2, #3, #4)

**User Export:**
- Download script as TXT/JSON
- Record dengan smartphone
- Edit di CapCut (add subtitles, transitions, music)
- Upload ke TikTok, Instagram Reels, YouTube Shorts
- Bio link: ke blog article

---

### Step 7C: Generate Pinterest Pin (Visual)

**User Action:**
1. Klik **"Create Pinterest Pin"**
2. System generate:
   - **Pin Title:** "Budget Traveling Jepang Cuma 7 Juta (Panduan Lengkap)"
   - **Pin Description:** SEO keywords + CTA
   - **Pin Image:** AI-generated infographic

**Infographic Design (Auto-generated via Canva API / AI):**
```
┌─────────────────────────────┐
│  BUDGET TRAVELING JEPANG    │
│      CUMA 7 JUTA! 🇯🇵        │
├─────────────────────────────┤
│  ✈️ Tiket: Rp 2.5 juta       │
│  🏨 Hotel: Rp 1.2 juta       │
│  🚄 JR Pass: Rp 1.5 juta     │
│  🍜 Makan: Rp 1 juta         │
│  🗼 Wisata: Rp 800K          │
├─────────────────────────────┤
│  TOTAL: RP 7 JUTA (7 HARI)  │
│                             │
│  📖 Baca Panduan Lengkap:   │
│  anewp.com/budget-jepang    │
└─────────────────────────────┘
```

**User Export:**
- Download PNG (1000x1500px, Pinterest-optimized)
- Upload ke Pinterest
- Link: ke blog article

---

### Step 7D: Generate Twitter/LinkedIn Thread

**User Action:**
1. Klik **"Create Twitter Thread"**
2. System generate thread (8-10 tweets)

**Output:**
```
Tweet 1/9 (Hook):
Siapa bilang ke Jepang harus puluhan juta?
Gue baru balik dari Tokyo, Kyoto, Osaka.
7 hari. Total habis: Rp 7 juta.
Thread ini bakal kasih breakdown + tips lengkap.
Let's go! 🧵👇

Tweet 2/9 (Budget Table):
Budget Breakdown:
✈️ Tiket PP: Rp 2.5 juta
🏨 Hostel (6 malam): Rp 1.2 juta
🚄 JR Pass (7 hari): Rp 1.5 juta
🍜 Makan (7 hari): Rp 1 juta
🗼 Wisata: Rp 800K
💰 Total: Rp 7 juta

Tweet 3/9 (Tip #1):
TIP #1: Beli tiket 3-4 bulan sebelum berangkat.
Gue pakai Skyscanner, flexible dates, avoid Golden Week.
Result: Jakarta-Tokyo PP cuma Rp 2.5 juta (maskapai: AirAsia/Scoot)

... (lanjut sampai tweet 9)

Tweet 9/9 (CTA):
Full itinerary 7 hari + tips detail ada di blog gue:
[Link]

Kalau helpful, RT biar temen kalian juga bisa ke Jepang hemat! 🙏
```

**User Export:**
- Copy thread → paste ke Twitter/X
- Or: Schedule via Buffer/Hootsuite

---

## Phase 5: Analytics & Optimization (Future Feature)

**Track Performance:**
- Blog: Google Analytics (traffic, bounce rate, time on page)
- YouTube: Views, watch time, CTR
- TikTok/Reels: Views, shares, saves
- Pinterest: Impressions, clicks

**Optimization Loop:**
- Artikel dengan traffic tinggi → Repurpose lagi (update, expand)
- Artikel dengan traffic rendah → Improve SEO, add backlinks
- Social posts dengan engagement tinggi → Amplify (promote, repost)

---

## Summary: Full Workflow (Hybrid Strategy)

```
DAY 1: Research + Quick Win
├─ User search TikTok trending (10 results)
├─ User select 10 topics
├─ System rank topics (AI scoring)
├─ Generate roundup article (800 kata)
└─ Publish roundup → Share to social media

DAY 2: Deep Dive #1
├─ Generate article "Budget Traveling Jepang" (1800 kata)
├─ User review & publish
└─ Update roundup article (add internal link)

DAY 3: Deep Dive #2
├─ Generate article "Tips Keuangan Gen Z" (1700 kata)
├─ User review & publish
└─ Update roundup + article #1 (internal linking)

DAY 4: Deep Dive #3
├─ Generate article "Cara Mulai Freelance" (1600 kata)
├─ User review & publish
└─ Complete internal linking web

DAY 5-7: Content Repurposing (Social Media)
├─ Generate YouTube scripts (3 videos)
├─ Generate Short clips (15 clips = 5 per article)
├─ Generate Pinterest pins (3 pins)
└─ Generate Twitter threads (3 threads)

WEEK 2+: Distribution
├─ Upload YouTube videos → Embed di blog
├─ Post short clips daily (TikTok, Reels, Shorts)
├─ Pin to Pinterest (evergreen traffic)
└─ Post threads to Twitter/LinkedIn
```

---

## Expected Results (Traffic Projection)

**Roundup Article:**
- Traffic source: Social shares, direct traffic
- Lifespan: 1 week (trending-based)
- Est. traffic: 500-2000 visits (first week)

**Deep Dive Articles (Each):**
- Traffic source: Google Search (organic)
- Lifespan: 6-12 months (evergreen)
- Est. traffic: 200-500 visits/month (after 3 months ranking)

**Total Traffic (1 Hybrid Strategy):**
- Week 1: ~2000 visits (roundup spike)
- Month 1: ~1500 visits (roundup fading, deep dive starting)
- Month 3+: ~2000+ visits/month (deep dive articles ranking)

**Multiply by 4 (1 trending search per week):**
- Month 3: ~8000 visits/month
- Month 6: ~12K-15K visits/month (compounding effect)

---

## Tech Requirements (Development Roadmap)

### Phase 1 (DONE ✅):
- [x] Social Insights search (TikTok, Twitter, Google Trends)
- [x] Results display with engagement metrics
- [x] Search history

### Phase 2 (IN PROGRESS):
- [ ] Checkbox selection (multi-select trending topics)
- [ ] Content strategy modal (Roundup / Hybrid / Deep Dive All)
- [ ] AI ranking system (engagement + evergreen + SEO + monetization scoring)

### Phase 3 (TODO):
- [ ] Roundup article generator (Gemini API integration)
- [ ] Deep dive article generator (long-form AI)
- [ ] Internal linking automation (roundup ↔ deep dive)
- [ ] WordPress auto-publish (scheduled)

### Phase 4 (FUTURE):
- [ ] YouTube script generator
- [ ] Short-form video script generator
- [ ] Pinterest pin generator (Canva API / AI image)
- [ ] Twitter thread generator
- [ ] Social media scheduling integration (Buffer API)

### Phase 5 (FUTURE):
- [ ] Analytics dashboard (GA4 + YouTube + TikTok API)
- [ ] Performance tracking (which topics = high traffic)
- [ ] Auto-optimization suggestions (improve low-performing articles)

---

## Database Schema Changes Needed

### New Tables:

**1. `ane_content_strategies`**
```sql
CREATE TABLE ane_content_strategies (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    created_by BIGINT NOT NULL,
    search_id BIGINT NOT NULL COMMENT 'FK to ane_social_searches',
    strategy_type ENUM('roundup', 'hybrid', 'deep_dive_all') NOT NULL,
    selected_topics JSON COMMENT 'Array of result IDs',
    ranked_topics JSON COMMENT 'AI-ranked with scores',
    status ENUM('pending', 'generating', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (search_id) REFERENCES ane_social_searches(id) ON DELETE CASCADE
);
```

**2. Update `ane_posts` table:**
```sql
ALTER TABLE ane_posts
ADD COLUMN content_strategy_id BIGINT NULL COMMENT 'FK to ane_content_strategies',
ADD COLUMN article_role ENUM('roundup', 'deep_dive') NULL COMMENT 'Role in hybrid strategy',
ADD COLUMN source_topic_id BIGINT NULL COMMENT 'FK to ane_social_results (which trending topic)',
ADD FOREIGN KEY (content_strategy_id) REFERENCES ane_content_strategies(id) ON DELETE SET NULL;
```

**3. `ane_social_media_repurposes`** (Future)
```sql
CREATE TABLE ane_social_media_repurposes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT NOT NULL COMMENT 'FK to ane_posts',
    platform ENUM('youtube', 'tiktok', 'instagram', 'pinterest', 'twitter') NOT NULL,
    format ENUM('video_script', 'short_script', 'pin_design', 'thread') NOT NULL,
    content TEXT COMMENT 'Generated content (script, description, etc)',
    media_url VARCHAR(1000) NULL COMMENT 'Generated image/video URL',
    status ENUM('draft', 'published') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES ane_posts(id) ON DELETE CASCADE
);
```

---

## AI Prompts Templates

### Prompt #1: Roundup Article Generator

```
You are a professional content writer specializing in viral trending topics.

TASK: Generate a roundup article featuring {count} trending TikTok topics.

INPUT DATA:
{json_trending_topics}

OUTPUT REQUIREMENTS:
- Title: Catchy, SEO-optimized, include year/month
- Introduction: 100-150 words, explain why these topics are trending
- For each topic:
  - Heading (H2): Topic name
  - Summary: 80-100 words
  - Engagement stats: Display likes, comments, shares
  - Hashtags: List 3-5 relevant hashtags
  - Link: "Lihat Video" with TikTok URL
- Conclusion: 80-100 words, CTA to read deep dive articles
- Internal links: Link to 3 deep dive articles (placeholders)

TONE: Casual, engaging, Indonesian language
FORMAT: Markdown
WORD COUNT: 800-1000 words
```

### Prompt #2: Deep Dive Article Generator

```
You are an expert content writer specializing in comprehensive how-to guides.

TASK: Generate an in-depth article about "{topic}" based on TikTok trending data.

TRENDING DATA:
- Content: {content}
- Engagement: {likes} likes, {comments} comments, {shares} shares
- Hashtags: {hashtags}
- Author: {author}

OUTPUT REQUIREMENTS:
- Title: SEO-optimized, include year, specific value proposition
- Structure:
  1. Introduction (150 words): Pain point, promise, authority
  2. Background/Context (200 words): Why this topic matters
  3. Main Content (1000-1200 words):
     - 5-7 subtopics (H2 headings)
     - Each subtopic: 200-300 words
     - Include examples, data, actionable tips
  4. FAQ Section (200 words): 5-7 common questions
  5. Conclusion (100 words): Summary + CTA
- Internal Links:
  - Link to roundup article: "[10 Trending TikTok Minggu Ini](#)"
  - Link to 2 related articles (placeholders)

TONE: Professional yet approachable, Indonesian language
FORMAT: Markdown with proper heading hierarchy
WORD COUNT: 1500-2000 words
SEO: Include target keyword in title, H2, intro, conclusion
```

### Prompt #3: YouTube Script Generator

```
You are a YouTube content strategist and scriptwriter.

TASK: Convert this blog article into a YouTube video script.

ARTICLE CONTENT:
{article_markdown}

OUTPUT REQUIREMENTS:
- Video Title: Catchy, include hook (numbers, questions, bold claims)
- Video Duration: 8-12 minutes
- Script Structure:
  1. Intro (0:00 - 0:45): Hook, introduce topic, channel CTA
  2. Main Content (0:45 - 9:00):
     - Break article H2 headings into video chapters
     - Include timestamps
     - Add visual cues: "[Show screen: ...]", "[B-roll: ...]"
     - Add engagement prompts: "Comment below...", "Don't forget to like..."
  3. Outro (9:00 - 9:45): Summary, CTA (subscribe, check blog), next video teaser
- Description:
  - SEO-optimized (include keywords)
  - Timestamps (auto-generated from script)
  - Link to blog article
  - Social media links

TONE: Conversational, energetic (for camera delivery)
FORMAT: Markdown with timestamps
```

### Prompt #4: Short-form Video Script Generator

```
You are a TikTok/Reels/Shorts content creator specializing in viral short videos.

TASK: Convert this article into 5 short video scripts (30-60 seconds each).

ARTICLE CONTENT:
{article_summary}

OUTPUT REQUIREMENTS:
For each script:
- Hook (3 seconds): Bold statement, question, or statistic
- Body (20-40 seconds): 1 key point from article
- CTA (3-5 seconds): "Link di bio", "Save ini", "Comment pengalaman kamu"

5 SCRIPTS:
1. Hook/Teaser (highlight main value proposition)
2. Tip #1 (first actionable tip)
3. Tip #2 (second actionable tip)
4. Myth Busting (common misconception related to topic)
5. Personal Story (anecdote or example)

TONE: Fast-paced, casual, Gen Z friendly
FORMAT: Plain text with visual cues
DURATION: 30-60 seconds per script
```

---

## Best Practices

### Content Quality:
- ✅ Always fact-check trending data (don't blindly trust TikTok content)
- ✅ Add unique insights (don't just summarize trending videos)
- ✅ Include data/statistics from credible sources
- ✅ Add personal experience/case studies (if applicable)

### SEO Optimization:
- ✅ Use long-tail keywords (3-5 words)
- ✅ Optimize meta descriptions (under 160 characters)
- ✅ Add alt text to images
- ✅ Internal linking strategy (silo structure)
- ✅ Update old articles with new trending topics (content freshness)

### Content Repurposing:
- ✅ Don't just copy-paste (adapt format for each platform)
- ✅ YouTube = deep, TikTok = hook, Pinterest = visual
- ✅ Use platform-specific CTAs (YouTube: subscribe, TikTok: like/share)

### Monetization:
- ✅ Add affiliate links (travel gear, finance apps, courses)
- ✅ Insert ads strategically (after H2, mid-article)
- ✅ Create product reviews for high-intent topics

---

## Troubleshooting

### Problem: Trending topic sudah tidak trending saat artikel publish
**Solution:**
- Focus on evergreen angle (how-to, tips) instead of news angle
- Example: Instead of "TikTok Viral: Budget Jepang", use "Cara Budget Traveling Jepang (Proven Tips 2025)"

### Problem: AI-generated content terlalu generic
**Solution:**
- Add specific data (budget breakdown, itinerary, screenshots)
- Include personal anecdotes (user can edit after generation)
- Use fact-checking layer (validate AI claims with Google Search API)

### Problem: Semua trending topics tidak evergreen (drama, gossip, news)
**Solution:**
- Use "Roundup Only" strategy (1 artikel, publish cepat)
- Skip deep dive (not worth SEO effort)
- Focus on social media distribution (viral potential)

---

## Next Steps (After Completing Social Insights)

1. **Develop Content Strategy Module:**
   - Multi-select UI for trending topics
   - AI ranking system
   - Strategy selection (Roundup/Hybrid/Deep Dive)

2. **Integrate with Article Generator:**
   - Link Social Insights → AI Article Generator
   - Pre-fill data from trending topics
   - Auto-generate multiple articles in batch

3. **Build Social Media Repurposing Module:**
   - YouTube script generator
   - Short-form video script generator
   - Pinterest pin generator
   - Twitter thread generator

4. **Analytics Dashboard:**
   - Track which trending topics → high traffic
   - A/B test roundup vs deep dive performance
   - ROI calculator (time spent vs traffic gained)

---

## EXTENDED ROADMAP: Multi-Platform Content Ecosystem

### Unified Feature Map: Social Insights → Multi-Platform Content

```
┌────────────────────────────────────────────────────────────┐
│                    PHASE 1: RESEARCH                       │
│  Social Insights (TikTok/Twitter/Google Trends)            │
│  News Insights (Serper API)                                │
└──────────────────┬─────────────────────────────────────────┘
                   │
                   ▼
┌────────────────────────────────────────────────────────────┐
│                  PHASE 2: CONTENT CREATION                 │
│  ├─ Blog Articles (WordPress)                              │
│  ├─ Social Media Posts:                                    │
│  │   ├─ Instagram (Carousel, Reels, Story)                 │
│  │   ├─ TikTok (Video script)                              │
│  │   ├─ Twitter (Thread)                                   │
│  │   ├─ YouTube (Shorts script)                            │
│  │   ├─ Pinterest (Pin design)                             │
│  │   └─ LinkedIn (Professional post)                       │
│  └─ Product Photos (AI Enhancement):                       │
│      ├─ Background removal                                 │
│      ├─ Studio lighting                                    │
│      ├─ Virtual staging                                    │
│      └─ Variations (10+ styles)                            │
└──────────────────┬─────────────────────────────────────────┘
                   │
                   ▼
┌────────────────────────────────────────────────────────────┐
│                  PHASE 3: SCHEDULING                       │
│  - Best time optimization (AI-powered)                     │
│  - Multi-platform queue (Instagram 12:00, TikTok 18:00)    │
│  - Timezone handling (user location)                       │
└──────────────────┬─────────────────────────────────────────┘
                   │
                   ▼
┌────────────────────────────────────────────────────────────┐
│                  PHASE 4: AUTO POSTING                     │
│  OAuth Integration:                                        │
│  ├─ Instagram Graph API                                    │
│  ├─ TikTok Creator API                                     │
│  ├─ Twitter API v2                                         │
│  ├─ YouTube Data API                                       │
│  ├─ Pinterest API                                          │
│  └─ LinkedIn API                                           │
└──────────────────┬─────────────────────────────────────────┘
                   │
                   ▼
┌────────────────────────────────────────────────────────────┐
│                  PHASE 5: ANALYTICS                        │
│  - Track engagement (likes, comments, shares, views)       │
│  - ROI tracking (trending topic → traffic → conversions)   │
│  - A/B testing (which caption performs better)             │
│  - Auto-optimization (adjust posting time based on data)   │
└────────────────────────────────────────────────────────────┘
```

---

## Extended Development Roadmap (2025)

### **Q1 2025: Content Creation Foundation**
1. ✅ Social Insights (TikTok/Twitter/Google Trends) - **DONE**
2. ✅ News Insights (Serper API) - **ALREADY EXISTS**
3. 🚧 Content Strategy Module (Hybrid approach) - **IN PROGRESS**
4. 🚧 AI Article Generator (Blog creation) - **NEXT**

### **Q2 2025: Visual Content & Product Photography**
5. ⏳ **Benchmark Sulap Foto** (AI Photo Enhancement)
   - Background removal (Rembg/Clipdrop API)
   - Studio lighting (Stability AI Upscale)
   - Virtual staging (DALL-E 3 Inpainting)
   - Style transfer (10+ variations: white bg, lifestyle, flat lay)
6. ⏳ Instagram Carousel Generator (from trending topics)
7. ⏳ Pinterest Pin Generator (infographic creation via Canva API)

### **Q3 2025: Social Media Automation**
8. ⏳ **Social Media Account Connection** (OAuth Integration)
   - Instagram Business API
   - TikTok Creator API
   - Twitter API v2
   - YouTube Data API v3
   - Pinterest API v5
   - LinkedIn API v2
9. ⏳ **Content Scheduling System**
   - Laravel Queue jobs (database driver)
   - Best time optimization (AI-powered based on engagement data)
   - Timezone handling (user location detection)
10. ⏳ **Auto Posting Engine**
    - Multi-platform publishing (parallel execution)
    - Error handling & retry mechanism
    - Rate limit handling (per-platform)

### **Q4 2025: Advanced Features**
11. ⏳ **News → Social Media Repurposing**
    - Auto-generate carousel from news articles
    - Script generator for TikTok/Reels (30-60s)
    - Twitter thread generator (8-10 tweets)
12. ⏳ **Analytics Dashboard**
    - Engagement tracking (cross-platform)
    - ROI measurement (content → traffic → conversions)
    - Performance comparison (platform A vs B)
13. ⏳ **A/B Testing Module**
    - Caption variations (auto-test 3 versions)
    - Posting time optimization (data-driven)
    - Hashtag effectiveness (which tags = high reach)

---

## Feature Deep Dive: Benchmark Sulap Foto (AI Product Photography)

### Use Cases

**A. E-commerce Content Creation:**
```
Trending TikTok: "Cara Foto Produk Aesthetic ala Brand Besar"
    ↓
Benchmark Sulap Foto:
    ├─ Upload foto produk biasa (smartphone photo)
    ├─ AI transform → Studio-quality photo
    ├─ Generate variations: white bg, lifestyle, flat lay
    └─ Auto-generate caption (AI-powered)
    ↓
Social Media Posting:
    ├─ Instagram: 3 feed posts (different angles)
    ├─ TikTok: Behind-the-scenes (before-after sulap foto)
    └─ Pinterest: Product pins (catalog style)
```

**B. Content Creator Workflow:**
```
User workflow:
1. Search TikTok trending: "Produk viral Shopee"
2. Pilih produk trending (misal: Stanley Cup dupe)
3. Upload foto produk (low quality)
4. AI Benchmark Sulap Foto:
   - Background removal
   - Studio lighting simulation
   - Color grading
   - Add props (virtual staging)
5. Generate 10 variations (different backgrounds, angles)
6. Auto-create social media posts:
   - Instagram: Grid layout (9 photos aesthetic)
   - TikTok: Product showcase video (slideshow + music)
   - Pinterest: Product catalog pin
7. Schedule posting (Instagram 12:00, TikTok 18:00, Pinterest 15:00)
```

**Tech Stack:**
- **AI Models:**
  - Background removal: Rembg API / Clipdrop
  - Image enhancement: Stability AI Upscale
  - Virtual staging: DALL-E 3 Inpainting
  - Style transfer: Midjourney API (via third-party)
- **Workflow:**
  - Upload image → Process → Generate variations → Create posts → Schedule

---

## Feature Deep Dive: Berita → Konten Sosial Media

### Flow Architecture

```
News Insights (Serper API)
    +
Social Insights (TikTok trending)
    ↓
AI Content Repurposing:
    ├─ Instagram Carousel (10 slides infographic)
    ├─ TikTok News Summary (45 detik script)
    ├─ Twitter Thread (10 tweets breakdown)
    ├─ YouTube Shorts (news recap script)
    └─ LinkedIn Post (professional angle)
    ↓
Auto Posting dengan Scheduling
```

### Real-World Example: iPhone 16 Pro Launch

**Input:**
- News search: "iPhone 16 Pro rilis"
- TikTok trending: "Review iPhone 16 Pro vs Samsung S24"

**AI Content Generator Output:**

**1. Instagram Carousel (10 Slides):**
```
Slide 1: Hook - "iPhone 16 Pro: Worth the Upgrade?"
Slide 2: Spesifikasi utama (chip, kamera, baterai)
Slide 3: Harga Indonesia vs US
Slide 4: Fitur baru (Action Button, USB-C)
Slide 5: Kamera comparison (vs iPhone 15 Pro)
Slide 6: Pro: What we love
Slide 7: Con: What's missing
Slide 8: Who should upgrade?
Slide 9: Who should skip?
Slide 10: CTA - "Comment pengalaman kamu!"
```

**2. TikTok Script (45 detik):**
```
[0-5s] Hook: "iPhone 16 Pro baru rilis, worth it gak sih?"
[5-15s] Spesifikasi: A18 Pro, 48MP camera, Titanium body
[15-30s] Harga: 19 juta (256GB), naik 2 juta dari tahun lalu
[30-40s] Verdict: Upgrade kalo dari iPhone 14 ke bawah
[40-45s] CTA: "Comment mending iPhone apa Android!"
```

**3. Twitter Thread (10 Tweets):**
```
Tweet 1/10 (Hook):
iPhone 16 Pro baru rilis. Gue udah pake 3 hari.
Ini review jujur tanpa sponsor.
Thread 🧵👇

Tweet 2/10 (Specs):
Spesifikasi iPhone 16 Pro:
- Chip: A18 Pro (3nm)
- RAM: 8GB
- Kamera: 48MP main, 12MP ultrawide, 5x telephoto
- Baterai: 3,582 mAh
- Harga: Rp 19 juta (256GB)

... (lanjut sampai 10)

Tweet 10/10 (Conclusion + CTA):
Worth the upgrade? Tergantung iPhone kamu sekarang:
✅ iPhone 14 ke bawah: YES
❌ iPhone 15 Pro: SKIP
🤔 iPhone 13/14 Pro: tunggu promo

Full review ada di blog (link di bio)
RT kalo helpful! 🙏
```

**4. YouTube Shorts Script (60 detik):**
```
[0-5s] Hook: "iPhone 16 Pro review 60 detik!"
[5-20s] Unboxing + first impression
[20-40s] Camera test (sample photos/video)
[40-55s] Battery life test (result)
[55-60s] Verdict + CTA subscribe
```

**5. LinkedIn Post (Professional Angle):**
```
Apple just released iPhone 16 Pro. Here's what matters for professionals:

📱 Performance: A18 Pro chip (20% faster for video editing)
📸 Camera: 48MP ProRAW (essential for content creators)
🔋 Battery: All-day video calls (tested 8+ hours)
💰 ROI: If your work relies on mobile content creation, worth the investment.

My verdict: Upgrade if you're on iPhone 14 or older and use your phone for professional work.

#iPhone16Pro #TechReview #ProductivityTools
```

---

## Feature Deep Dive: Auto Posting ke Sosial Media

### Architecture

```
┌─────────────────────────────────┐
│  Content Creation Layer         │
│  (AI-generated posts)           │
└───────────┬─────────────────────┘
            │
            ▼
┌─────────────────────────────────┐
│  Scheduling Layer               │
│  - Queue system (Laravel Queue) │
│  - Timezone handling            │
│  - Best time optimization (AI)  │
└───────────┬─────────────────────┘
            │
            ▼
┌─────────────────────────────────┐
│  API Integration Layer          │
│  ├─ Instagram Graph API         │
│  ├─ TikTok Creator API          │
│  ├─ Twitter API v2              │
│  ├─ YouTube Data API            │
│  ├─ Pinterest API               │
│  ├─ LinkedIn API                │
│  └─ Facebook Pages API          │
└───────────┬─────────────────────┘
            │
            ▼
┌─────────────────────────────────┐
│  Analytics Layer (Future)       │
│  - Track engagement per post    │
│  - A/B test captions            │
│  - Optimize posting time        │
└─────────────────────────────────┘
```

### Database Schema

```sql
-- Table: ane_social_accounts
CREATE TABLE ane_social_accounts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    created_by BIGINT NOT NULL,
    platform ENUM('instagram', 'tiktok', 'twitter', 'youtube', 'pinterest', 'linkedin', 'facebook'),
    account_name VARCHAR(255),
    account_id VARCHAR(255) COMMENT 'Platform user ID',
    access_token TEXT COMMENT 'OAuth token',
    refresh_token TEXT,
    token_expires_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES ane_users(id) ON DELETE CASCADE
);

-- Table: ane_social_posts
CREATE TABLE ane_social_posts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    created_by BIGINT NOT NULL,
    content_strategy_id BIGINT NULL COMMENT 'FK to ane_content_strategies (from trending)',
    post_type ENUM('image', 'carousel', 'video', 'text', 'story') NOT NULL,
    caption TEXT,
    media_urls JSON COMMENT 'Array of image/video URLs',
    hashtags JSON,
    status ENUM('draft', 'scheduled', 'published', 'failed') DEFAULT 'draft',
    scheduled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES ane_users(id) ON DELETE CASCADE
);

-- Table: ane_social_post_targets
CREATE TABLE ane_social_post_targets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT NOT NULL,
    account_id BIGINT NOT NULL COMMENT 'FK to ane_social_accounts',
    platform_post_id VARCHAR(255) NULL COMMENT 'ID dari platform (misal: Instagram post ID)',
    status ENUM('pending', 'publishing', 'published', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    published_at TIMESTAMP NULL,
    engagement_likes INT DEFAULT 0,
    engagement_comments INT DEFAULT 0,
    engagement_shares INT DEFAULT 0,
    engagement_views INT DEFAULT 0,
    last_synced_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES ane_social_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES ane_social_accounts(id) ON DELETE CASCADE
);
```

---

## Real-World End-to-End Workflow

**Scenario:** Content creator mau bikin konten tentang trending topic "Budget Traveling Jepang"

### **Day 1: Research (5 menit)**
1. Login AneWP → Social Insights
2. Search TikTok trending → "Budget Traveling Jepang" muncul
3. Klik "Create Content Strategy" → Pilih **Hybrid**
4. System auto-rank 10 trending topics → Top 3 untuk deep dive

### **Day 1: Content Creation (30 menit - AI-powered)**
5. AI generate:
   - 1 roundup blog (800 kata)
   - 3 deep dive blogs (1500 kata each)
   - 15 Instagram carousels (5 per artikel)
   - 15 TikTok scripts (30-60 detik each)
   - 5 YouTube Shorts scripts
   - 10 Pinterest pins
   - 5 Twitter threads

### **Day 1: Product Photo Enhancement (10 menit)**
6. User upload 5 foto produk (travel gear: backpack, shoes, camera)
7. AI Benchmark Sulap Foto:
   - Remove background
   - Add studio lighting
   - Generate 10 variations (white bg, lifestyle, flat lay)
8. Auto-add to Instagram carousel (product showcase slides)

### **Day 2-7: Scheduling & Auto Posting (0 menit - fully automated)**
9. User review & approve posts
10. Set schedule:
    - Instagram: Daily 12:00 (carousel)
    - TikTok: Daily 18:00 (video)
    - Pinterest: Daily 15:00 (pin)
    - Twitter: Daily 09:00 (thread)
11. System auto-post semua konten (35+ posts dalam 1 minggu)

### **Week 2+: Analytics & Optimization**
12. Dashboard shows:
    - Instagram carousel #3 → 5K likes (best performing)
    - TikTok video #7 → 50K views (viral)
    - Pinterest pin #2 → 200 clicks ke blog
13. AI recommendation:
    - "Create more content like TikTok #7 (product review angle)"
    - "Best posting time for TikTok: 19:00 (engagement +30%)"

---

## Kesimpulan: Sinergi Total dengan TikTok Trends

**Korelasi 4 Fitur Utama:**

✅ **1. Content Creation** = Core output dari trending research
✅ **2. Benchmark Sulap Foto** = Visual enhancement layer untuk product content
✅ **3. Berita → Sosmed** = Content repurposing automation (news angle)
✅ **4. Auto Posting** = Distribution layer (force multiplier: 1 trending → 50+ posts)

**Sinergi:**
- Feature-feature ini **saling melengkapi**
- Build once, use everywhere (1 trending topic → 50+ content pieces across 6 platforms)
- **Full automation** dari research sampai analytics
- **ROI maksimal**: 1 jam kerja → 1 minggu konten otomatis

**Next Development Priority:**
1. Complete Hybrid Strategy (Content Strategy Module)
2. Benchmark Sulap Foto (Q2 2025)
3. Social Media Auto Posting (Q3 2025)
4. News → Social Media Repurposing (Q4 2025)

---

**END OF GUIDE**

---

*Last Updated: December 14, 2024*
*Author: AneWP Development Team*
*Status: Living Document (will be updated as features are developed)*
