# WordPress Scheduling System - Cara Kerja

## Cara Kerja (Simple Explanation)

### 1️⃣ User Schedule Post
```
User → Create Post → Pilih "Schedule" → Set tanggal/waktu → Save
         ↓
    Post dikirim ke WordPress dengan status "future"
         ↓
    WordPress otomatis publish saat waktu tiba
         ↓
    Status di aplikasi kita: "scheduled"
```

### 2️⃣ System Detect Post Sudah Published (Otomatis)
```
Setiap 15 menit:
    Scheduler cek post yang status "scheduled"
         ↓
    Dispatch job ke queue (1 job per post) - NON BLOCKING!
         ↓
    Queue worker (background) check ke WordPress API
         ↓
    Jika status WordPress = "publish" → Update database kita jadi "published"
```

### 3️⃣ Kenapa Tidak Berat Server?

**❌ CARA LAMA (Sequential):**
```
500 posts × 1 detik per check = 500 detik (8+ menit) BLOCKING!
Server stuck, user tidak bisa akses aplikasi
```

**✅ CARA BARU (Queue-based):**
```
Scheduler: Dispatch 500 jobs dalam 2 detik → SELESAI (NON BLOCKING!)
Queue worker: Process 500 jobs di background dalam 500 detik
Server tetap responsif, user tetap bisa akses aplikasi
```

---

## Setup Production (Sekali Saja)

### **Cara Otomatis (Recommended)**

Jalankan script setup:

```bash
cd /Users/webane/sites/anewplaravel
./setup-production-queue.sh
```

Script akan setup:
1. ✅ Cron job untuk scheduler (auto-check setiap 15 menit)
2. ✅ Queue worker yang jalan di background selamanya

### **Cara Manual (Jika script gagal)**

#### 1. Setup Cron Job (Laravel Scheduler)

```bash
# Edit crontab
crontab -e

# Tambahkan baris ini:
* * * * * cd /Users/webane/sites/anewplaravel && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

#### 2. Setup Queue Worker (macOS - LaunchAgent)

Buat file: `~/Library/LaunchAgents/com.anewplaravel.queue.plist`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.anewplaravel.queue</string>
    <key>ProgramArguments</key>
    <array>
        <string>/usr/bin/php</string>
        <string>/Users/webane/sites/anewplaravel/artisan</string>
        <string>queue:work</string>
        <string>--sleep=3</string>
        <string>--tries=3</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>KeepAlive</key>
    <true/>
    <key>StandardOutPath</key>
    <string>/Users/webane/sites/anewplaravel/storage/logs/queue-worker.log</string>
    <key>StandardErrorPath</key>
    <string>/Users/webane/sites/anewplaravel/storage/logs/queue-worker-error.log</string>
</dict>
</plist>
```

Load service:
```bash
launchctl load ~/Library/LaunchAgents/com.anewplaravel.queue.plist
```

---

## Verifikasi Setup

### 1. Cek Cron Job
```bash
crontab -l | grep schedule
```

Output yang benar:
```
* * * * * cd /Users/webane/sites/anewplaravel && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

### 2. Cek Queue Worker (macOS)
```bash
launchctl list | grep anewplaravel
```

Output yang benar (PID tidak boleh "-"):
```
12345   0   com.anewplaravel.queue
```

### 3. Test Manual
```bash
# Dispatch job manual
php artisan wordpress:sync-status

# Cek log
tail -f storage/logs/queue-worker.log
```

---

## Monitoring

### Lihat Log Queue Worker
```bash
tail -f storage/logs/queue-worker.log
```

### Lihat Log Laravel (Error)
```bash
tail -f storage/logs/laravel.log
```

### Cek Jumlah Job di Queue
```bash
php artisan tinker --execute="echo DB::table('jobs')->count();"
```

---

## Troubleshooting

### Queue Worker Tidak Jalan

**Cek status:**
```bash
launchctl list | grep anewplaravel
```

**Restart:**
```bash
launchctl unload ~/Library/LaunchAgents/com.anewplaravel.queue.plist
launchctl load ~/Library/LaunchAgents/com.anewplaravel.queue.plist
```

### Scheduler Tidak Jalan

**Cek cron:**
```bash
crontab -l
```

**Test manual:**
```bash
php artisan schedule:run
```

### Post Status Tidak Update

**Cek WordPress post status manual:**
```bash
php artisan tinker
```

```php
$target = App\Models\PostTarget::find(11);
$wpService = new App\Services\WordPressPublishService();
$wpStatus = $wpService->checkPostStatus($target);
echo "WordPress Status: " . $wpStatus;
```

---

## FAQ

### Q: Apakah berat untuk server jika user banyak?
**A:** Tidak! Queue worker process jobs secara background. Scheduler hanya dispatch jobs (non-blocking). Server tetap responsif.

### Q: Berapa lama delay dari WordPress publish sampai status update di aplikasi?
**A:** Maksimal 15 menit (interval scheduler). Bisa dipercepat dengan ubah `everyFifteenMinutes()` jadi `everyFiveMinutes()`.

### Q: Apakah bisa detect post yang awalnya draft terus di-publish manual di WordPress?
**A:** Bisa! System check semua post dengan `wp_post_id`, tidak peduli awalnya draft atau scheduled.

### Q: Bagaimana jika WordPress down saat check status?
**A:** Job akan retry 3x dengan delay 60 detik. Jika gagal semua, error log di `storage/logs/laravel.log`.

### Q: Apakah perlu setup ulang setelah restart server?
**A:** Tidak! LaunchAgent (macOS) atau systemd (Linux) otomatis start queue worker saat boot.

---

## Commands Reference

| Command | Keterangan |
|---------|------------|
| `php artisan wordpress:sync-status` | Manual sync (untuk testing) |
| `php artisan queue:work` | Start queue worker manual (dev) |
| `php artisan schedule:run` | Run scheduler manual (dev) |
| `php artisan queue:failed` | Lihat failed jobs |
| `php artisan queue:retry all` | Retry semua failed jobs |
| `launchctl list \| grep anewplaravel` | Cek status worker (macOS) |
| `tail -f storage/logs/queue-worker.log` | Monitor worker log |

---

## File References

- **Job:** `app/Jobs/CheckWordPressStatus.php`
- **Service:** `app/Services/WordPressPublishService.php`
- **Command:** `app/Console/Commands/SyncWordPressStatus.php`
- **Scheduler:** `routes/console.php`
- **Controller:** `app/Http/Controllers/PostController.php`
