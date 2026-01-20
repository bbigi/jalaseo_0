# Rencana Analisa Menyeluruh Website (Halaman Website Show)

Dokumen ini merangkum apa saja yang akan kita tampilkan sebagai analisa utuh + rekomendasi di halaman `websites/show.blade.php`, dengan memanfaatkan modul yang sudah ada.

## Tujuan
- Memberi satu panel ringkasan kesehatan situs: SEO, konten, kompetitor, kecepatan, dan pertumbuhan keyword.
- Menyajikan rekomendasi prioritas (actionable) yang bisa langsung di-klik/ditindaklanjuti.
- Menjaga konsistensi dengan gaya “Website Intelligence” yang sudah ada.

## Sumber Data yang Dipakai
- **Search Console & GA4**: klik, impresi, CTR, posisi rata-rata, sesi, konversi (jika tersedia).
- **Riset Keyword Mendalam (Deep Research)**: peluang keyword + prioritas AI.
-,**Keyword Tersimpan**: daftar yang ditrack + target posisi.
- **Analisis Kompetitor**: perbandingan domain-level, pangsa keyword.
- **Analisis Gap Kompetitif**: 10 keyword gap utama + rekomendasi AI (yang sudah kita buat).
- **Speed Intelligence**: skor Lighthouse (mobile/desktop), rekomendasi PageSpeed.
- **Skor Kesehatan**: status audit teknis terakhir.

## Blok Ringkasan yang Ditampilkan
1) **Kartu KPI Utama**  
   - Traffic organik (GSC/GA4), CTR, posisi rata-rata, sesi GA4, konversi (jika ada).  
   - Tombol “Lihat detail” ke modul SC/GA4.
2) **Opportunity Snapshot**  
   - 3 keyword peluang (dari Deep Research atau Gap) + CTA generate konten/duplicate.  
   - 3 konten yang perlu diperbaiki (keyword tersimpan atau gap konten) + CTA buka halaman konten/generate artikel.
3) **Kompetitor & Gap**  
   - 3 gap utama (ETV tertinggi, kompetitor rank >20) + CTA “Generate rekomendasi” (pakai modul rekomendasi gap).  
   - Ringkasan posisi vs kompetitor: win rate, median delta.
4) **Speed & Health**  
   - Skor PageSpeed (mobile/desktop), trend singkat, CTA “Lihat detail” / “Jalankan ulang”.  
   - Skor kesehatan teknis terbaru + status (hijau/kuning/merah).
5) **Rencana 2 Minggu (AI)**  
   - Render rekomendasi terakhir yang sudah disimpan (dari `ane_gap_recommendations`) atau tombol generate jika kosong.  
   - Menampilkan judul + jadwal (Week 1–2) + CTA untuk generate ulang.

## Interaksi & CTA
- Semua kartu punya tombol “Buka”/“Lihat detail” menuju modul masing-masing (konsisten dengan daftar Website Intelligence).
- Rekomendasi AI: jika sudah ada, tampilkan; jika belum, tombol generate. Simpan hasil di DB (sudah ada).
- “Regenerate” untuk jalankan ulang rekomendasi dengan filter/kompetitor aktif.
- CTA khusus: “Generate artikel”, “Duplicate via competitor URL”, “Track keyword”.

## Bahasa & UI
- Mengikuti locale user (id/en), gunakan label dari file lang yang sudah ada; hindari hardcode.
- Gaya Tailwind card ala Website Intelligence (bg-slate-50, border, rounded-2xl, shadow-sm, badge warna).

## Teknis Singkat
- Ambil rekomendasi terakhir via `ane_gap_recommendations` untuk website + kompetitor + locale (+ filter/search jika diterapkan).
- Jika tidak ada data, tampilkan tombol generate rekomendasi (pakai endpoint yang sudah dibuat).
- Pastikan query data lain (GSC/GA4, speed, health) menggunakan policy `view` yang sudah ada.

## Next Steps
1) Tambah partial di `websites/show.blade.php` untuk blok “Analisa Menyeluruh” dengan data di atas.  
2) Wire data dari controller (atau view composer) agar ringkasan tidak menambah banyak query.  
3) Reuse komponen/tailwind style Website Intelligence untuk konsistensi.  
4) Uji di locale id/en, dan pastikan fallback jika data kosong.  
5) Opsional: simpan riwayat rekomendasi dan tampilkan selector “Lihat versi sebelumnya”.
