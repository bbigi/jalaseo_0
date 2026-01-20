Brainstorming: Cara Tracking Keyword Opsi 1: Manual Input
User input keyword manual
Simpan ke ane_keywords dengan source='manual'
❌ Tidak scalable
Opsi 2: Sync dari GSC (Current)
Connect GSC via OAuth
Fetch keyword performance (impressions, clicks, position)
Save ke ane_keywords + ane_keyword_traces (history)
✅ Real data, ❌ Butuh GSC access
Opsi 3: Import dari Deep Research
User klik "Save Selected" di Deep Research result
Keyword masuk ane_keywords dengan source='deep_research'
✅ Mudah, data sudah ada
Opsi 4: Auto-track dari Generated Article
Saat generate artikel, auto-save focus keyword ke ane_keywords
Link via website_id
✅ Otomatis, konsisten
Opsi 5: Bulk Import (Excel/CSV)
Upload file keyword list
Parse & insert ke ane_keywords
✅ Cepat untuk banyak keyword
Rekomendasi: Combine Opsi 2 (GSC) + Opsi 3 (Deep Research) + Opsi 4 (Auto from article). GSC untuk real ranking, Deep Research untuk planning, auto-track untuk monitoring artikel.

Konsep: 3-Layer Keyword Management
1. Keyword Pool (ane_keywords)
Fungsi: Semua keyword yang user punya dari berbagai sumber Kolom tambahan:

- source (enum: 'deep_research', 'gsc_sync', 'manual', 'auto_article')
- priority (enum: 'high', 'medium', 'low', 'none')
- tracking_status (enum: 'active', 'paused', 'archived')
- target_position (int) - posisi target di Google (misal: 10)
- current_position (int) - posisi sekarang dari GSC
- first_tracked_at (timestamp)
- last_updated_at (timestamp)
2. Deep Research → Keyword Flow
Button "Save to My Keywords" di Deep Research:
Insert ke ane_keywords dengan source='deep_research'
Default priority='medium', tracking_status='active'
User bisa langsung set priority saat save
3. Keyword Dashboard (halaman baru: /keywords)
Tab 1: All Keywords - Grid view semua keyword Tab 2: High Priority - Filter priority='high' Tab 3: Tracking Active - Real-time ranking dari GSC Actions per keyword:
Set Priority (High/Medium/Low)
Pause/Resume Tracking
View Ranking History (chart)
Generate Article (langsung ke AI generator dengan keyword prefill)
4. Priority Logic
High Priority (max 10 keywords):
Daily GSC sync
Alert jika ranking turun >5 posisi
Show di dashboard homepage
Medium Priority (unlimited):
Weekly GSC sync
No alerts
Low Priority:
Monthly sync
Archive candidates
UI Flow Suggestion
Deep Research Result Page:

[✓] keyword A (vol: 2900, priority: 83)
    [Save] → Modal: Set Priority [High|Medium|Low] → Saved to My Keywords
Keyword Dashboard:

┌─────────────────────────────────────────────────┐
│ My Keywords (486)                                │
│ [All] [High Priority (3)] [Active Tracking (10)] │
├─────────────────────────────────────────────────┤
│ Keyword              | Vol  | Pos | Priority    │
│ jual kopi bubuk      | 2900 | 15  | 🔴 High     │
│   ↳ Target: #10  Current: #15  📈 +2 this week  │
│   [Generate Article] [View History] [Edit]       │
└─────────────────────────────────────────────────┘

Keyword Pool Strategy (Benar):
1. GSC Keywords (sudah diindex Google)
Source: gsc_sync
Auto-import saat sync GSC
Punya data current_position real dari Google
Status: tracking_status='active' by default
2. Deep Research Keywords (target keyword baru)
Source: deep_research
User pilih dari hasil AI recommendations
Belum ada di Google (posisi null/0)
Status: tracking_status='active' untuk monitor kapan mulai masuk index
Logic Flow:
Saat Save dari Deep Research:

// Check apakah keyword sudah ada di GSC
$existing = Keyword::where('created_by', auth()->id())
    ->where('keyword', $keywordText)
    ->first();

if ($existing && $existing->source === 'gsc_sync') {
    // Keyword SUDAH ada di Google
    // Update: tambahkan flag 'deep_research' sebagai secondary source
    $existing->update([
        'has_deep_research_data' => true, // flag tambahan
        'priority' => 'high' // auto-upgrade priority
    ]);
} else {
    // Keyword BARU (belum di Google)
    Keyword::create([
        'keyword' => $keywordText,
        'source' => 'deep_research',
        'priority' => 'high', // default untuk keyword research
        'current_position' => null, // belum ada ranking
        'tracking_status' => 'active'
    ]);
}
Benefit:
User bisa lihat:
Keyword di Google (from GSC) → Monitor ranking yang sudah ada
Keyword Target (from Deep Research) → Plan konten baru
Overlap → Keyword yang mereka research ternyata udah ada di Google (bonus!)
Dashboard view:

┌─ My Keywords ─────────────────────────────────┐
│ [All (150)] [In Google (45)] [Targets (105)]  │
├───────────────────────────────────────────────┤
│ ✓ jual kopi bubuk      | #15 | GSC + Research │ ← Overlap
│ ○ kopi bubuk premium   |  -  | Research       │ ← Belum di Google
│ ✓ harga kopi robusta   | #8  | GSC            │ ← Dari Google
└───────────────────────────────────────────────┘
Benar kan pemahaman ini?