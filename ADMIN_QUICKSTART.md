# Quick Start Guide - Admin API Key Management

## Database is Ready ✅

Your Laravel application is now running with a **clean, production-ready database**.

---

## Login Credentials

```
Email:    webane.com@gmail.com
Password: Test1234!
```

---

## Server Status

```
Server URL: http://127.0.0.1:8000
Dev Server: Running on port 8000
```

---

## How to Test Admin API Key Management

### Step 1: Start Fresh Server
```bash
cd /Users/webane/sites/anewplaravel

# Clear everything first
rm -f storage/logs/laravel.log
php artisan config:clear && php artisan cache:clear

# Start server
php artisan serve --host=127.0.0.1 --port=8000
```

### Step 2: Login
```
Open: http://127.0.0.1:8000/login
Email: webane.com@gmail.com
Password: Test1234!
```

### Step 3: Access Admin API Keys
```
URL: http://127.0.0.1:8000/admin/api-keys
```

You should see:
- 3 existing API credentials (gemini, openai, deepseek)
- Grouped by category (article_generation, image_generation)
- Each with display name, status, and masked key

### Step 4: Add New Credential
```
Click "Add API Key" button
1. Select Category: article_generation or image_generation
2. Provider dropdown auto-populates based on category
3. Enter API Key
4. (Optional) Display Name
5. (Optional) Description
6. Click "Add API Key"
```

### Step 5: Monitor Logs
In another terminal, watch the logs in real-time:
```bash
tail -f /Users/webane/sites/anewplaravel/storage/logs/laravel.log
```

You should see:
```
[2025-12-12 HH:MM:SS] local.INFO: admin.api-keys.store attempt {...}
[2025-12-12 HH:MM:SS] local.INFO: admin.api-keys.store validated {...}
[2025-12-12 HH:MM:SS] local.INFO: admin.api-keys.store success {...}
```

---

## Database Schema Reference

### ane_users
- **id**: Auto-increment primary key
- **email**: Unique identifier for login
- **role**: 'user' or 'super_admin'
- **subscription_status**: active/inactive/cancelled/expired
- **api_quota_used / api_quota_limit**: For rate limiting

### ane_api_credentials
- **category**: article_generation or image_generation
- **provider**: gemini, openai, deepseek, etc.
- **api_key**: Encrypted at rest
- **is_active**: Boolean flag
- **composite unique**: (category, provider) ensures one key per provider per category

### sessions
- For server-side session storage
- Configured in: `config/session.php` (SESSION_DRIVER=database)

---

## File Locations

### Models
```
app/Models/User.php
app/Models/ApiCredential.php
```

### Controllers
```
app/Http/Controllers/Admin/ApiKeyController.php
```

### Views
```
resources/views/admin/api-keys/index.blade.php
resources/views/admin/api-keys/create.blade.php
resources/views/admin/api-keys/edit.blade.php
```

### Migrations
```
database/migrations/2025_12_12_000001_create_ane_users_table.php
database/migrations/2025_12_12_100100_create_api_credentials_table.php
database/migrations/2025_12_12_110000_create_sessions_table.php
```

### Middleware
```
app/Http/Middleware/EnsureUserIsAdmin.php
```

---

## Common Tasks

### Check All Credentials in DB
```bash
php artisan tinker
> \App\Models\ApiCredential::all();
```

### View Masked Keys
```bash
php artisan tinker
> \App\Models\ApiCredential::find(1)->getMaskedApiKey();
// Output: AIza•••••••••••••••••••••••••••••••rY04
```

### Test Encryption/Decryption
```bash
php artisan tinker
> \$cred = \App\Models\ApiCredential::find(1);
> \$cred->api_key;  // Decrypted automatically
```

### Count Records
```bash
mysql -u root -proot anewp_laravel -e "
SELECT 'Users' as table_name, COUNT(*) as count FROM ane_users
UNION ALL
SELECT 'API Credentials', COUNT(*) FROM ane_api_credentials
UNION ALL
SELECT 'Sessions', COUNT(*) FROM sessions;
"
```

---

## Important Notes

### 🔐 API Key Encryption
- API keys are **encrypted at rest** using Laravel's Crypt facade
- Keys are automatically decrypted when accessed via the model
- In logs and UI, keys are **masked** for security (e.g., `AIza•••••rY04`)

### 🔑 Session Management
- Sessions are stored in the `sessions` table (database driver)
- CSRF protection enabled by default
- Session lifetime: 120 minutes (configured in `.env`)

### 🗂️ Database Integrity
- **Soft deletes** enabled: deleted records can be recovered
- **Composite unique index** on (category, provider) prevents duplicates
- **Foreign key** from api_credentials to users (with cascade delete)

### 📊 Indices for Performance
```
ane_users:
  - PRIMARY (id)
  - UNIQUE (email)
  - INDEX (role, subscription_status, email_verified_at)

ane_api_credentials:
  - PRIMARY (id)
  - UNIQUE (category, provider)
  - INDEX (category, provider, is_active, user_id)

sessions:
  - PRIMARY (id)
  - INDEX (user_id, last_activity)
```

---

## Troubleshooting

### If credentials don't appear after submit
1. Check the logs: `tail -f storage/logs/laravel.log`
2. Look for `admin.api-keys.store` entries
3. Verify the admin user is logged in (check session cookie)
4. Verify validation passes (look for `admin.api-keys.store validated` log)

### If you get "Table doesn't exist" error
All tables should exist. If not:
```bash
php artisan migrate --force
```

### If you need to reset everything
```bash
# Drop database and recreate
mysql -u root -proot -e "DROP DATABASE anewp_laravel; CREATE DATABASE anewp_laravel;"

# Run migrations
php artisan migrate --force

# Seed admin user (create script in database/seeders/)
```

---

## What's Next

1. **Build User Preference System**: Let users select provider per category
2. **Create API Orchestrator Service**: Route requests to correct API based on user preference
3. **Add Usage Tracking**: Track API calls per user/provider
4. **Implement Fallback Logic**: If primary provider fails, try secondary
5. **Add Admin Audit Logs**: Track who added/modified credentials

---

**Setup Date:** December 12, 2025
**Database Status:** ✅ Production Ready
**Last Updated:** 08:30 UTC

Questions? Check the logs, check the database, and verify the schema!
