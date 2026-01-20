# 05 - Autoshare Sosial Media

## 1. Status Blueprint Saat Ini
- Autoshare/autopublish masih berada di **Phase 6** dan ditandai sebagai _future work_ pada blueprint utama ([ane_wp_content_distribution_blueprint.md](ane_wp_content_distribution_blueprint.md#L1443-L1494)).
- Seluruh pondasi sebelum autopublish (DNA, copy, visual, library, calendar, scheduling) sudah rampung hingga Phase 5, sehingga kita tinggal menambahkan layer eksekusi otomatis.
- Prinsip yang harus dijaga: autopublish hanya melanjutkan alur yang sudah ada—mengambil `SocialAsset` yang berstatus _scheduled_, mengeksekusi publish per channel, lalu mengirim balik status/performance ke sistem.

## 2. Apa yang Harus Dilakukan
- Bangun modul **Publish Connector** per channel (IG, FB, X, LinkedIn) lengkap dengan OAuth, refresh token, dan rate-limit handling.
- Tambahkan **AutoshareService** yang menarik data dari `ane_distribution_schedule`, memanggil connector sesuai channel, serta menyimpan `platform_post_id`, response, dan status.
- Integrasikan dengan **queue worker** supaya setiap publish berjalan asinkron + retry exponential backoff.
- Kembangkan **monitoring & feedback loop**: log success/failure, webhook listener (jika ada), dan pemetaan performa balik ke rekomendasi scheduling.

## 2.1 Langkah Pasti (Prioritas Meta: Facebook, Instagram, Threads)
1) **Schema + Storage**
   - Tabel `ane_social_accounts`: `id, user_id, provider, account_id, page_id, ig_business_account_id, username, access_token_enc, refresh_token_enc, expires_at, scope, status, metadata`.
   - Tambah kolom di `ane_distribution_schedule`: `platform_post_id`, `attempt_count`, `last_error`, `publish_payload`, `published_at`.
   - Enkripsi token (Laravel encryption) + atur rotating key policy.
2) **OAuth Connect/Disconnect**
   - Route/UI connect: redirect ke Meta OAuth (scopes: `pages_show_list`, `pages_read_engagement`, `pages_manage_posts`, `instagram_basic`, `instagram_content_publish`, `business_management` jika perlu).
   - Callback simpan token + auto-refresh job (cron) yang refresh sebelum `expires_at - 24h`.
   - Disconnect: revoke token + tandai `status=revoked`.
3) **Connector Meta (FB/IG/Threads)**
   - Interface: `publish(SocialAsset $asset, DistributionSchedule $schedule): PublishResult`.
   - Instagram: upload media (container) → poll status → publish; set `location`, `tags` jika ada.
   - Facebook Page: simple `/feed` untuk text/link; `/photos` untuk image; batching optional.
   - Threads (via Meta): gunakan endpoint Threads (sama token) jika tersedia di app; fallback: tandai "not enabled" sampai app dapat permission.
   - Tangani rate-limit (429) dengan exponential backoff + pencatatan quota headers.
4) **Autoshare Orchestrator**
   - Job `PublishScheduledAssetJob` tarik schedule `pending` pada waktunya, lock row, pilih connector sesuai `provider`, jalankan, simpan `platform_post_id`, `status`.
   - Retry policy: 3–5 kali, exponential backoff, stop pada error permanent (400/permission).
   - Simpan response mentah untuk debugging.
5) **Monitoring & Webhook**
   - Webhook endpoint untuk Meta (page/IG) jika mau catat status; verifikasi signature.
   - Log ke `ane_social_publish_logs` (atau JSON column) + notifikasi in-app/email jika gagal.
6) **UAT & Go-Live Checklist**
   - Uji connect/disconnect, refresh token otomatis, publish IG image, FB link, Threads text.
   - Verifikasi status di calendar berubah `pending → success/failed` + ada error message.
   - Load test ringan untuk memastikan rate-limit aman.

## 3. Kebutuhan & Persiapan
- **Kredensial API**: Meta (IG/FB), X (Twitter) v2, LinkedIn Marketing API; termasuk App ID/Secret, redirect URI, permissions (content_publish, manage_pages, dsb).
- **Storage token aman**: tabel baru (mis. `ane_social_accounts`) untuk menyimpan access/refresh token terenkripsi per user + scope per channel.
- **Job Infrastructure**: Redis/Database queue siap pakai, supervisor/PM2 untuk worker, serta konfigurasi retry/backoff.
- **Callback/Webhook Endpoint**: URL publik + verifikasi signature untuk menerima status posting (jika channel mendukung).
- **Compliance**: pastikan ToS tiap platform (rate limits, creative guidelines) dipenuhi sebelum go-live.

## 4. Blueprint Langkah Implementasi
1. **Schema & Model Extension**
   - Buat `ane_social_accounts` (user/channel/token) + relasi ke `User` dan `SocialAsset`.
   - Tambah kolom penting di `ane_distribution_schedule` seperti `attempt_count`, `last_error`, `publish_payload`.
2. **OAuth Flow per Channel**
   - Endpoint connect/disconnect + UI dalam dashboard.
   - Simpan token terenkripsi + jadwalkan refresh otomatis.
3. **Publish Connectors**
   - Satu kelas per platform dengan interface seragam (`publish(SocialAsset $asset, DistributionSchedule $schedule)`).
   - Tangani format khusus (IG media uploads, FB batch, X text/media rules, LinkedIn share payload).
4. **Autoshare Orchestrator**
   - Job queue `PublishScheduledAssetJob` yang mengambil schedule `pending`, mengunci row, menjalankan connector, lalu update status.
   - Retry policy (max attempts, exponential backoff) + fallback manual jika gagal permanen.
5. **Status & Observability**
   - Update Calendar/Asset Library secara real-time dengan status `pending → success/failed`.
   - Simpan response mentah untuk debugging, tambahkan notifikasi (in-app/email) jika gagal.
6. **Feedback Loop (Optional v1.1)**
   - Tarik metrik performa (impressions, CTR) bila API memungkinkan, feed ke rekomendasi SchedulingService untuk iterasi berikutnya.

> Deliverable akhir: user cukup klik "Schedule"; sistem otomatis mem-posting ke channel terhubung pada waktu yang ditentukan, memantau status, dan memberi tahu jika ada kegagalan.

## 5. Roadmap Channel Lanjutan (ditambah sesuai permintaan)
### Fase 1 — Meta (Facebook, Instagram, Threads)
- Status: prioritas. Ikuti langkah 2.1.

### Fase 2 — X (Twitter)
- Scope & izin: v2 OAuth2 PKCE; scope `tweet.read`, `tweet.write`, `users.read`, `offline.access`.
- Payload: teks, link, media (upload media chunked).
- Rate limit: patuhi header `x-rate-limit-reset`; backoff wajib.
- UAT: connect, refresh token, post teks+media, pastikan status di calendar.

### Fase 3 — TikTok
- Scope: `video.upload`, `video.publish`; pastikan app review untuk publish.
- Payload: upload video → dapat `video_id` → publish dengan caption + cover.
- Webhook: listen status processing/publish jika tersedia.
- UAT: connect, upload+publish 1 video pendek, cek status.

### Fase 4 — YouTube (Posts/Shorts)
- Scope: `youtube.upload`, `youtube.force-ssl`.
- Payload: resumable upload → set title/description/tags/schedule; dukung Shorts (≤60s, vertical).
- Rate limit: quota units; catat response quota di log.
- UAT: connect, upload video terjadwal, verifikasi publish ID.

### Fase 5 — LinkedIn
- Scope: `w_member_social`, `r_organization_social` untuk company page.
- Payload: `UGC`/`shares` untuk text/link/image; video upload multipart.
- UAT: connect user/company, post link+image, cek status.

### Fase 6 — Ekstra (Opsional, jika menarik/feasible)
- Pinterest: pin image dengan board mapping.
- Snapchat Spotlight: video pendek (butuh izin khusus).
- Discord/Slack: webhook blast (tanpa OAuth berat, untuk distribusi cepat).

### Siklus Kerja per Fase
1) Setup kredensial + OAuth flow + storage token.
2) Implement connector + uji end-to-end publish.
3) Tambah ke orchestrator + calendar status.
4) Tambah logging/notifikasi + retry/backoff.
5) Go-live, lalu feedback loop metrik (jika API mendukung).

> Catatan skema terkini: `ane_distribution_schedule` kini memuat `queued_at`, `attempt_count`, `last_error`, `publish_payload` untuk mendukung antrian dan retry.

> OAuth Meta sudah disiapkan: endpoint `GET /oauth/meta/connect` (with `channel` param: facebook/instagram/threads) dan `GET /oauth/meta/callback` yang bertugas tukar code → simpan token & Page/IG business account ke `ane_social_accounts`. Pastikan `META_APP_ID/SECRET` atau Admin OAuth Service `service_name=meta` terisi.

## 6. Standard Terjemahan (mengikuti SOP-Translate-page.md)
- **Lokasi string**: Semua label/teks UI modul autoshare masuk ke `resources/lang/en/social-autoshare.php` dan `resources/lang/id/social-autoshare.php`; gunakan `common.php` untuk kata aksi umum.
- **Kunci yang dipakai di view**: `__('social-autoshare.connect_meta')`, `__('social-autoshare.disconnect')`, `__('social-autoshare.schedule_status_pending')`, `__('social-autoshare.schedule_status_success')`, `__('social-autoshare.error_rate_limit')`, dll. Gunakan placeholder (`['platform' => 'Facebook']`) daripada konkatenasi manual.
- **Middleware locale**: Pastikan `ApplyUserPreferences` sudah aktif; form profil menyimpan kode pendek (`en`, `id`).
- **Checklist uji**: Set locale ke `id` → pastikan semua label modul autoshare berbahasa Indonesia; set ke `en` → kembali ke Inggris; gunakan `rg` untuk memastikan tidak ada teks hardcode tersisa.
