# AneWP Laravel - Complete System Documentation

**Last Updated:** December 13, 2025  
**Status:** Production Ready (Phase 2: Website Management Migration Complete)  
**Framework:** Laravel 11 | **Database:** MySQL | **PHP:** 8.4.15

---

## 📋 Quick Summary

**AneWP** adalah SaaS platform untuk content creation dengan AI integration. Aplikasi ini sudah dalam tahap **Phase 2** dengan fitur utama:

1. ✅ Multi-user authentication system
2. ✅ API credentials management (Gemini, ChatGPT, Claude, DeepSeek)
3. ✅ AI article generation dengan provider selection
4. ✅ Website management (WordPress integration ready)
5. 🔄 Website analysis & keyword tracking (in development)

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.4+
- MySQL 8.0+
- Composer
- Node.js (for assets)

### Installation

```bash
# Clone repository
git clone <repo-url>
cd anewplaravel

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate:fresh
php artisan db:seed

# Build assets
npm run build

# Start server
php artisan serve --host=127.0.0.1 --port=8000
```

---

## 👥 User System & Test Credentials

### Current Users in Database

**Database: `anewp_laravel` → Table: `ane_users`**

| ID | Email | Password | Role | Subscription | Status | Notes |
|---|---|---|---|---|---|---|
| 1 | test@example.com | Test1234! | user | free | verified | Regular user for testing |
| 2 | wasugi@example.com | Test1234! | super_admin | premium | verified | **ADMIN USER - Can access admin panel** |

### 🚨 CRITICAL: User Consistency Rules

⚠️ **ALL AI AGENTS MUST FOLLOW THESE RULES:**

1. **Use `test@example.com` for testing regular features**
2. **Use `wasugi@example.com` for testing admin features**
3. **DO NOT create new users without documentation**
4. **If you must add a user, add it to this table with:**
   - ID
   - Email
   - Password
   - Role (user or super_admin)
   - Purpose (why it exists)
5. **DO NOT change user roles or delete users mid-development**
6. **If user data changes (quota, subscription), document it below**

### User Data Snapshots

**Last Updated: December 13, 2025**

**User 1: test@example.com**
```
├─ Role: user
├─ Email Verified: YES
├─ Subscription Plan: free
├─ Subscription Status: inactive
├─ API Quota Limit: 10 (monthly)
├─ API Quota Used: 0
└─ Purpose: Testing regular user features (websites, generation)
```

**User 2: wasugi@example.com**
```
├─ Role: super_admin
├─ Email Verified: YES
├─ Subscription Plan: premium
├─ Subscription Status: active
├─ Subscription Ends: 1 year from creation
├─ API Quota Limit: 1000 (monthly)
├─ API Quota Used: 0
├─ Admin URLs:
│  ├─ Dashboard: http://127.0.0.1:8000/admin/dashboard
│  └─ API Keys: http://127.0.0.1:8000/admin/api-keys
└─ Purpose: Testing admin panel & API key management
```

### Recreate Users (If Database Reset)

```bash
php artisan tinker
```

**Create Regular User:**
```php
\App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('Test1234!'),
    'email_verified_at' => now(),
    'role' => 'user',
    'subscription_plan' => 'free',
    'subscription_status' => 'inactive',
    'api_quota_limit' => 10,
    'api_quota_used' => 0
]);
```

**Create Super Admin:**
```php
\App\Models\User::create([
    'name' => 'Wasugi',
    'email' => 'wasugi@example.com',
    'password' => bcrypt('Test1234!'),
    'email_verified_at' => now(),
    'role' => 'super_admin',
    'subscription_plan' => 'premium',
    'subscription_status' => 'active',
    'subscription_ends_at' => now()->addYear(),
    'api_quota_limit' => 1000,
    'api_quota_used' => 0
]);
```

---

## 🔐 Authentication System

### Session Configuration
- **Driver:** File-based (`SESSION_DRIVER=file`)
- **Location:** `storage/sessions/`
- **Lifetime:** 120 minutes
- **Storage:** `/storage/sessions/` directory (auto-created)

### Middleware Stack
- `auth` - Check user logged in
- `verified` - Check email verified
- `admin` (EnsureUserIsAdmin) - Check super_admin role

### User Roles

| Role | Permissions | Routes |
|---|---|---|
| `user` | Use features, view own data | /dashboard, /websites, /generate |
| `super_admin` | Manage API keys, admin panel | /admin/dashboard, /admin/api-keys/* |

---

## 💾 Database Schema

### Core Tables (7 Total)

#### 1. `ane_users` - User Authentication & Subscription
```
id (PK, BigInt)
name (string)
email (string, UNIQUE)
password (string, hashed bcrypt)
role (enum: 'user', 'super_admin', default: 'user')
email_verified_at (timestamp, nullable)
subscription_plan (string: Free, Starter, Pro)
api_quota_limit (int, default: 10)
api_quota_used (int, default: 0)
api_quota_reset_at (timestamp, nullable)
created_at, updated_at (timestamps)

Indices: email (unique), role
```

#### 2. `ane_api_credentials` - API Keys (ENCRYPTED)
```
id (PK, BigInt)
category (enum: 'article_generation', 'image_generation', 'research')
provider (enum: 'gemini', 'chatgpt', 'claude', 'deepseek')
api_key (string, 255, ENCRYPTED with AES-256)
display_name (string, nullable)
description (text, nullable)
is_active (boolean, default: true)
created_at, updated_at (timestamps)

Constraint: UNIQUE (category, provider) per account
Indices: (category, provider, is_active)
```

#### 3. `ane_websites` - WordPress Sites
```
id (PK, BigInt)
name (varchar 100, UNIQUE)
url (varchar 255)
username (varchar 100)
app_password (string 255, ENCRYPTED with AES-256)
category (enum: 14 options - see below)
status (enum: 'active', 'inactive', default: 'active')
created_by (BigInt, FK → ane_users.id, CASCADE)
created_at, updated_at (timestamps)

Indices: 
- name (unique)
- (created_by, status)
- created_by (foreign key)

Categories (14):
- business_industrial, shopping_ecommerce, technology_software
- education_content, news_media, health_beauty
- food_beverage, travel_hospitality, finance_investment
- real_estate, automotive, entertainment, personal_brand, other
```

#### 4. `ane_website_analyses` - Analysis Results
```
id (PK, BigInt)
website_id (BigInt, FK → ane_websites.id)
type (enum: 'structure', 'seo', 'performance')
status (enum: 'pending', 'running', 'done', 'failed')
result_json (longtext, JSON format)
started_at (datetime, nullable)
finished_at (datetime, nullable)
created_at, updated_at (timestamps)

Indices:
- (website_id, type, status)
```

#### 5. `ane_keywords` - Extracted Keywords
```
id (PK, BigInt)
website_id (BigInt, FK → ane_websites.id)
keyword (string 255)
source (enum: 'analysis', 'manual', 'import')
volume (int, nullable - search volume)
difficulty (int, nullable - 0-100 SEO difficulty)
status (enum: 'active', 'archived', default: 'active')
created_at, updated_at (timestamps)

Constraint: UNIQUE (website_id, keyword)
Indices: (website_id, status), (website_id, keyword)
```

#### 6. `ane_keyword_traces` - Ranking History
```
id (PK, BigInt)
keyword_id (BigInt, FK → ane_keywords.id)
engine (enum: 'google', 'bing')
position (int - rank position)
url (string - ranking URL)
traced_at (datetime)
created_at, updated_at (timestamps)

Indices: (keyword_id, engine, traced_at)
```

#### 7. `sessions` - Laravel Session Storage
```
id (string, PK)
user_id (int, nullable)
ip_address (string, nullable)
user_agent (text, nullable)
payload (text)
last_activity (int)
```

---

## 🔌 API Providers & Credentials

### Currently Active Providers

#### ✅ Gemini (Google)
```
Model: gemini-2.5-flash
Endpoint: https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent
Status: TESTED & WORKING
Tokens: 66 tokens for 2-sentence response
API Key: In ane_api_credentials (encrypted)
```

#### Ready (No Key Currently)
- OpenAI ChatGPT (gpt-3.5-turbo)
- Anthropic Claude (claude-3-sonnet)
- DeepSeek Chat

### Provider Priority (Auto-fallback)
```
Preference Order:
1. Gemini (primary)
2. ChatGPT (secondary)
3. Claude (tertiary)
4. DeepSeek (fallback)

Logic: Check active credentials in ane_api_credentials,
       use specified provider or fallback in order
```

---

## 🎯 Features Status

### Phase 1: Core Foundation ✅ COMPLETE
- [x] User authentication (register, login, email verification)
- [x] Session management (file-based)
- [x] Database schema and migrations
- [x] API credentials CRUD (with encryption)
- [x] Admin dashboard with stats
- [x] User dashboard

### Phase 2: Website Management ✅ COMPLETE
- [x] Website CRUD (add, edit, delete, list)
- [x] WordPress app password encryption (AES-256)
- [x] Category system (14 categories)
- [x] Database schema (4 tables: websites, analyses, keywords, traces)
- [x] View details & test connection
- [x] Authorization (ownership check via WebsitePolicy)
- [x] Proper menu structure (no duplicates)
- [x] Fix deprecated functions (str_limit → Str::limit)

### Phase 3: Content Generation ✅ COMPLETE
- [x] AIService (multi-provider support)
- [x] Article generation UI with provider selection
- [x] Token usage tracking per provider
- [x] Provider priority and fallback logic

### Phase 4: Website Analysis 🔄 IN DEVELOPMENT
- [ ] AnalyzeWebsiteJob (queue job)
- [ ] Website crawling logic
- [ ] Keyword extraction algorithm
- [ ] TraceKeywordJob (SERP ranking tracking)
- [ ] Analytics dashboard

### Phase 5: Publish & Schedule 📅 PLANNED
- [ ] Post scheduling UI
- [ ] WordPress REST API integration
- [ ] Social media publishing hooks
- [ ] Analytics tracking

---

## 📁 Project Structure

```
anewplaravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── WebsiteController.php
│   │   │   ├── GenerationController.php
│   │   │   ├── Admin/
│   │   │   │   ├── AdminDashboardController.php
│   │   │   │   └── ApiKeyController.php
│   │   │   └── Auth/
│   │   │       ├── RegisterController.php
│   │   │       ├── LoginController.php
│   │   │       └── EmailVerificationController.php
│   │   └── Middleware/
│   │       └── EnsureUserIsAdmin.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── ApiCredential.php (with encryption)
│   │   ├── Website.php (with encryption)
│   │   ├── WebsiteAnalysis.php
│   │   ├── Keyword.php
│   │   └── KeywordTrace.php
│   ├── Services/
│   │   └── AIService.php (multi-provider)
│   ├── Policies/
│   │   └── WebsitePolicy.php
│   └── Jobs/
│       ├── AnalyzeWebsiteJob.php (stub)
│       └── TraceKeywordJob.php (stub)
├── database/
│   ├── migrations/ (11 migration files)
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php (main layout, Bootstrap 5)
│       │   └── auth.blade.php
│       ├── auth/
│       │   ├── register.blade.php
│       │   └── login.blade.php
│       ├── dashboard-new.blade.php (main user dashboard)
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   └── api-keys/ (index, create, edit views)
│       ├── websites/
│       │   ├── index.blade.php (table with Str::limit)
│       │   ├── create.blade.php (form with category select)
│       │   └── show.blade.php (detail page)
│       └── generator/
│           └── simple.blade.php
├── routes/
│   └── web.php (all routes defined)
├── storage/
│   ├── logs/ (Laravel logs)
│   ├── sessions/ (file-based sessions)
│   └── app/
├── config/
│   ├── app.php
│   ├── database.php
│   └── session.php (SESSION_DRIVER=file)
├── README.md (THIS FILE - MAIN DOCUMENTATION)
├── MIGRATION_WEBSITE_MANAGEMENT.md
├── WEBSITE_MANAGEMENT_QUICKREF.md
└── ARCHITECTURE_GUIDELINES.md
```

---

## 🔑 Routes & Endpoints

### Public Routes
```
GET  /                      (welcome page)
GET  /login                 (login form)
POST /login                 (process login)
GET  /register              (register form)
POST /register              (process registration)
GET  /email-verification-notice
POST /email-verification-send
GET  /verify-email/{id}/{hash} (email verification link)
POST /logout                (logout, redirects to /)
```

### Protected Routes (auth + verified middleware)
```
GET  /dashboard             (user dashboard - main page)
GET  /generate              (AI generation form)
POST /generate              (process generation, returns tokens)
GET  /websites              (list user's websites)
GET  /websites/create       (add website form)
POST /websites              (create website)
GET  /websites/{id}         (view website details)
GET  /websites/{id}/edit    (edit website form)
PUT  /websites/{id}         (update website)
DELETE /websites/{id}       (delete website)
POST /websites/{id}/test-connection (test WordPress connection)
```

### Admin Routes (auth + verified + admin middleware)
```
GET  /admin/dashboard               (admin stats dashboard)
GET  /admin/api-keys                (list API credentials)
GET  /admin/api-keys/create         (add new API key form)
POST /admin/api-keys                (create API key)
GET  /admin/api-keys/{id}           (view API key details)
PATCH /admin/api-keys/{id}/toggle-active (enable/disable key)
DELETE /admin/api-keys/{id}         (delete API key)
```

---

## 🔒 Encryption Details

### API Credentials (ane_api_credentials.api_key)
- **Method:** Laravel Crypt::encryptString()
- **Algorithm:** AES-256-CBC
- **Mutators:** 
  - `setApiKeyAttribute()` - Encrypts on write
  - `getApiKeyAttribute()` - Decrypts on read
- **Display:** `getMaskedApiKey()` - Shows "xxxx••••••••xxxx"
- **Storage Format:** JSON (IV, encrypted value, MAC tag)

### Website Passwords (ane_websites.app_password)
- **Method:** Laravel Crypt::encryptString()
- **Algorithm:** AES-256-CBC
- **Mutators:**
  - `setAppPasswordAttribute()` - Encrypts on write
  - `getAppPasswordAttribute()` - Decrypts on read
- **Display:** Form shows masked "••••••••••••••••"
- **Storage Format:** JSON (IV, encrypted value, MAC tag)

### Security Notes
- Never decrypt in database queries (use Model attributes)
- Never store decrypted values in logs
- App key must be set in .env: `APP_KEY=base64:xxx`
- Encryption key in APP_KEY is used for all Crypt operations

---

## 🧪 Testing & Verification

### Quick Start (Assumes clean database)

```bash
# 1. Start server
php artisan serve --host=127.0.0.1 --port=8000

# 2. Access application
http://127.0.0.1:8000

# 3. Login
Email: test@example.com
Password: Test1234!

# 4. Navigate
Dashboard → Websites → Add Website
```

### Test Data (Pre-populated in DB)

**Website #1:**
```
Name: Test Blog
URL: https://testblog.com
Username: admin
App Password: (encrypted) → decrypts to "test_app_password_123"
Category: Technology & Software
Status: Active
Created By: test@example.com
```

### Manual Testing via Tinker

```bash
php artisan tinker
```

```php
# Get test user
$user = \App\Models\User::where('email', 'test@example.com')->first();
echo $user->email; // test@example.com

# List user's websites
$websites = \App\Models\Website::where('created_by', $user->id)->get();
foreach ($websites as $w) {
    echo "Name: {$w->name}, App Pass: {$w->app_password}\n";
}

# Test API credential encryption
$cred = \App\Models\ApiCredential::where('category', 'article_generation')->first();
echo "Decrypted: {$cred->api_key}\n";

# Count websites by category
\App\Models\Website::where('created_by', $user->id)
    ->groupBy('category')
    ->selectRaw('category, count(*) as total')
    ->get();
```

---

## 📝 Development Guidelines

### Code Standards
- **PHP:** PSR-12 coding standard
- **Database:** Use Eloquent ORM (never raw queries for sensitive data)
- **Validation:** Use Request classes or inline `validate()`
- **Authorization:** Use Policies and `authorize()` method
- **Encryption:** Use Model mutators, never raw encryption

### When Adding Features

1. **Create migration** in `database/migrations/`
2. **Create model** in `app/Models/` with proper relationships
3. **Create controller** in `app/Http/Controllers/`
4. **Create policy** in `app/Policies/` if authorization needed
5. **Add routes** in `routes/web.php`
6. **Create views** in `resources/views/`
7. **Update README.md** - Document all changes:
   - User changes (if any)
   - Database schema changes
   - New routes and endpoints
   - New features

### Mandatory Rules Before Committing

⚠️ **ALWAYS:**
- [ ] Document user data changes in README User System section
- [ ] Document database schema changes in README
- [ ] Document new routes in README Routes section
- [ ] Test feature with test@example.com user
- [ ] Clear cache: `php artisan config:clear && php artisan cache:clear`
- [ ] Verify no console errors
- [ ] Check database migrations ran successfully
- [ ] Test authorization (403 on wrong user's data)

⚠️ **NEVER:**
- Create users without documenting in README
- Change test user credentials without reason
- Modify existing user data without note
- Store sensitive data unencrypted
- Skip authorization checks
- Use deprecated functions (check Laravel version)
- Leave TODO comments without context

---

## 🐛 Common Issues & Solutions

### Issue: "Menu duplicate items" or "Empty menu links"
**Cause:** Navigation code has multiple links or links to `#`  
**Fix:** See `resources/views/dashboard-new.blade.php:21-33`  
**Solution:** Remove empty links, keep only items with valid routes

### Issue: "Call to undefined function str_limit()"
**Cause:** Laravel 11+ removed global `str_limit()` helper  
**Fix:** Use `\Illuminate\Support\Str::limit($string, $limit)`  
**Location:** `resources/views/websites/index.blade.php:57`

### Issue: "403 Forbidden on /websites/1"
**Cause:** Website not owned by current user  
**Fix:** Verify `created_by` matches `auth()->id()`  
**Check:** WebsitePolicy::own() method

### Issue: "SQLSTATE[HY000]: General error - failed to decrypt"
**Cause:** Trying to decrypt with wrong app key  
**Fix:** Ensure `.env` has correct `APP_KEY=base64:xxx`  
**Note:** This happens if key was regenerated or copied wrong

### Issue: "User keeps changing subscription to Free"
**Cause:** Multiple AI instances creating/modifying same user  
**Fix:** Always use test@example.com, never create new test users  
**Prevention:** Document all users in README

### Issue: "Session not persisting"
**Cause:** SESSION_DRIVER not set to 'file'  
**Fix:** Check `.env`: `SESSION_DRIVER=file`  
**Verify:** `storage/sessions/` exists and is writable

---

## 📚 Supporting Documentation

| File | Purpose | Audience |
|---|---|---|
| **README.md** | **THIS FILE** - System overview & status | All AI agents |
| MIGRATION_WEBSITE_MANAGEMENT.md | Website feature technical details | Developers |
| WEBSITE_MANAGEMENT_QUICKREF.md | Website CRUD testing examples | Testers |
| ARCHITECTURE_GUIDELINES.md | System architecture & patterns | Senior devs |

---

## 📊 Recent Changes (Last 7 Days)

**December 13, 2025:**
- ✅ Fixed menu duplication in dashboard-new.blade.php
- ✅ Fixed str_limit() → Str::limit() compatibility
- ✅ Completed website management migration from anewp
- ✅ Created comprehensive README.md documentation
- ✅ Verified app_password encryption working (AES-256)

---

## 🚀 Next Steps (Priority Order)

1. **Implement AnalyzeWebsiteJob** - Queue job for website crawling
2. **Add keyword extraction** - Parse analysis results, save keywords
3. **Implement TraceKeywordJob** - Track keyword rankings
4. **Build analytics dashboard** - Display keyword metrics
5. **Add WordPress publishing** - REST API integration

---

## 📞 Quick Reference

**Server:** http://127.0.0.1:8000  
**Test User:** test@example.com / Test1234!  
**Database:** anewp_laravel (MySQL)  
**Session Storage:** /storage/sessions/  
**Logs:** /storage/logs/laravel.log  

**Cache Clear Command:**
```bash
php artisan config:clear && php artisan cache:clear
```

**Fresh Database (WARNING - DELETES ALL DATA):**
```bash
php artisan migrate:fresh
```

---

## 📄 License & Ownership

This project is **PRIVATE AND CONFIDENTIAL**.  
All rights reserved. Property of webane.com.

**Last Maintenance:** December 13, 2025  
**Maintained By:** Development Team  
**Status:** Active Development
