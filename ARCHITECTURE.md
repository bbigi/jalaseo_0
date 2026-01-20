# AneWP SaaS Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                      ANEWP LARAVEL SAAS                         │
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                       PRESENTATION LAYER                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│   ┌──────────────────────────────────────────────────────────┐  │
│   │          Blade Templates + Tailwind CSS                  │  │
│   │                                                          │  │
│   │  ┌─────────────┬──────────────┬─────────────┐          │  │
│   │  │  Auth       │  Dashboard   │  Features   │          │  │
│   │  │  Forms      │  Analytics   │  Admin      │          │  │
│   │  └─────────────┴──────────────┴─────────────┘          │  │
│   │                                                          │  │
│   │  - login.blade.php                                      │  │
│   │  - register.blade.php                                   │  │
│   │  - verify-email.blade.php                               │  │
│   │  - dashboard.blade.php                                  │  │
│   └──────────────────────────────────────────────────────────┘  │
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                      ROUTING LAYER                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│   ┌──────────────┬──────────────┬──────────────┐               │
│   │ Public       │ Auth         │ Verified     │               │
│   │ Routes       │ Routes       │ Routes       │               │
│   │              │              │              │               │
│   │ /            │ /logout      │ /dashboard   │               │
│   │ /login       │ /email-*     │ /api/*       │               │
│   │ /register    │ /verify-*    │              │               │
│   └──────────────┴──────────────┴──────────────┘               │
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                    CONTROLLER LAYER                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│   ┌─────────────────────────────────────────────────────────┐  │
│   │              Auth Controllers                            │  │
│   │                                                          │  │
│   │  RegisterController      LoginController               │  │
│   │  ├── showRegistrationForm()  ├── showLoginForm()       │  │
│   │  └── register()              ├── login()               │  │
│   │                              └── logout()              │  │
│   │                                                          │  │
│   │  EmailVerificationController                           │  │
│   │  ├── notice()                                          │  │
│   │  ├── verify()                                          │  │
│   │  └── resend()                                          │  │
│   │                                                          │  │
│   └─────────────────────────────────────────────────────────┘  │
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                    BUSINESS LOGIC LAYER                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│   ┌─────────────────────────────────────────────────────────┐  │
│   │              User Model (Eloquent)                       │  │
│   │                                                          │  │
│   │  Traits:                                               │  │
│   │  - HasFactory              - Notifiable                │  │
│   │  - HasApiTokens (Sanctum)  - SoftDeletes               │  │
│   │                                                          │  │
│   │  Relationships:                                        │  │
│   │  - websites()   - posts()                              │  │
│   │  - insights()   - activityLogs()                       │  │
│   │                                                          │  │
│   │  Scopes:                                               │  │
│   │  - active()  - verified()  - subscribed()             │  │
│   │                                                          │  │
│   │  Email Methods:                                        │  │
│   │  - hasVerifiedEmail()                                 │  │
│   │  - markEmailAsVerified()                              │  │
│   │  - sendEmailVerificationNotification()                │  │
│   │                                                          │  │
│   │  Helper Methods:                                       │  │
│   │  - isActive()              - hasActiveSubscription()   │  │
│   │  - canUseAPI()             - hasAPIQuota()             │  │
│   │  - incrementAPIQuota()                                 │  │
│   │  - getSubscriptionDaysRemaining()                      │  │
│   │                                                          │  │
│   └─────────────────────────────────────────────────────────┘  │
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                    NOTIFICATION/EMAIL LAYER                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│   ┌──────────────────────────────────────────────────────────┐ │
│   │  Mailable: VerifyEmail                                  │ │
│   │  ├── Markdown template: resources/views/mail/           │ │
│   │  │   verify-email.blade.php                            │ │
│   │  └── Sends signed URL for 60-minute window            │ │
│   │                                                          │ │
│   │  Listener: SendEmailVerificationNotification           │ │
│   │  └── Triggered on Registered event                    │ │
│   │                                                          │ │
│   │  EventServiceProvider                                  │ │
│   │  └── Maps Registered → SendEmailVerificationNotification│ │
│   │                                                          │ │
│   └──────────────────────────────────────────────────────────┘ │
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                    DATABASE LAYER                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│   MySQL 8.0                                                      │
│   anewp_db                                                       │
│                                                                   │
│   ┌──────────────────────────────────────────────────────────┐ │
│   │  ane_users table                                         │ │
│   │                                                          │ │
│   │  Core Auth Fields:                                     │ │
│   │  ├── id                    ├── email                    │ │
│   │  ├── name                  ├── password                 │ │
│   │  ├── email_verified_at ✨  ├── remember_token           │ │
│   │                                                          │ │
│   │  SaaS Fields: ✨                                        │ │
│   │  ├── subscription_plan                                 │ │
│   │  ├── subscription_status                               │ │
│   │  ├── subscription_ends_at                              │ │
│   │  ├── api_quota_used                                    │ │
│   │  └── api_quota_limit                                   │ │
│   │                                                          │ │
│   │  Timestamps:                                            │ │
│   │  ├── created_at            ├── updated_at              │ │
│   │  └── deleted_at (soft delete)                          │ │
│   │                                                          │ │
│   └──────────────────────────────────────────────────────────┘ │
│                                                                   │
│   ┌──────────────────────────────────────────────────────────┐ │
│   │  personal_access_tokens table (Sanctum)                 │ │
│   │  ├── id          ├── tokenable_id                      │ │
│   │  ├── name        ├── abilities                          │ │
│   │  ├── token       ├── created_at                         │ │
│   │  └── last_used_at                                       │ │
│   │                                                          │ │
│   └──────────────────────────────────────────────────────────┘ │
│                                                                   │
│   ┌──────────────────────────────────────────────────────────┐ │
│   │  Related Tables (Future Implementation)                  │ │
│   │  ├── ane_websites (user_id)                            │ │
│   │  ├── ane_posts (user_id)                               │ │
│   │  ├── ane_insights (user_id)                            │ │
│   │  └── ane_activity_logs (user_id)                       │ │
│   │                                                          │ │
│   └──────────────────────────────────────────────────────────┘ │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## Authentication Flow Diagram

```
                        USER REGISTRATION FLOW
                        ═══════════════════════

    ┌──────────────┐
    │ User Visits  │
    │ /register    │
    └────────┬─────┘
             │
             ▼
    ┌──────────────────────────┐
    │ Show Registration Form   │
    │ (register.blade.php)     │
    └────────┬─────────────────┘
             │
    ┌────────▼─────────────┐
    │ User Submits Form    │
    │ POST /register       │
    └────────┬─────────────┘
             │
    ┌────────▼──────────────────────────────┐
    │ RegisterController@register()          │
    │                                        │
    │ 1. Validate input                     │
    │ 2. Create user with free plan        │
    │ 3. Hash password                      │
    │ 4. Fire Registered event             │
    │ 5. Auto-login user                    │
    └────────┬──────────────────────────────┘
             │
    ┌────────▼──────────────────────────────┐
    │ Event: Registered                     │
    │                                        │
    │ Listener: SendEmailVerificationNotif  │
    │ └─ Mail::to()->send(VerifyEmail)     │
    └────────┬──────────────────────────────┘
             │
    ┌────────▼──────────────────────────────┐
    │ Email Queued/Sent                    │
    │ (in logs: storage/logs/laravel.log)  │
    │                                        │
    │ Contains:                              │
    │ - User name greeting                  │
    │ - Verification button (signed URL)    │
    │ - Link expires in 60 minutes         │
    └────────┬──────────────────────────────┘
             │
    ┌────────▼──────────────────────────────┐
    │ User Redirected to:                  │
    │ /email-verification-notice           │
    │                                        │
    │ Shows: Email sent notice + resend btn │
    └────────┬──────────────────────────────┘
             │
    ┌────────▼──────────────────────────────┐
    │ User Opens Email &                   │
    │ Clicks Verification Link             │
    │                                        │
    │ GET /verify-email/{id}/{hash}        │
    │ with middleware: signed               │
    └────────┬──────────────────────────────┘
             │
    ┌────────▼──────────────────────────────┐
    │ EmailVerificationController@verify() │
    │                                        │
    │ 1. Validate hash matches email       │
    │ 2. Call markEmailAsVerified()        │
    │ 3. Update email_verified_at timestamp│
    └────────┬──────────────────────────────┘
             │
    ┌────────▼──────────────────────────────┐
    │ User Redirected to:                  │
    │ /dashboard                            │
    │                                        │
    │ Dashboard now accessible!            │
    │ (middleware: auth + verified)        │
    └──────────────────────────────────────┘


                        USER LOGIN FLOW
                        ═══════════════════════

    ┌──────────────┐
    │ User Visits  │
    │ /login       │
    └────────┬─────┘
             │
             ▼
    ┌──────────────────────────┐
    │ Show Login Form          │
    │ (login.blade.php)        │
    └────────┬─────────────────┘
             │
    ┌────────▼─────────────┐
    │ User Submits Form    │
    │ POST /login          │
    └────────┬─────────────┘
             │
    ┌────────▼──────────────────────────────┐
    │ LoginController@login()                │
    │                                        │
    │ 1. Validate email & password          │
    │ 2. Find user by email                 │
    │ 3. Check password                     │
    │ 4. Check email_verified_at not null   │
    └────────┬──────────────────────────────┘
             │
        ┌────┴────┐
        │          │
    ┌───▼──┐  ┌───▼──┐
    │Email │  │Email │
    │Verified? │Not Verified?│
    └───┬──┘  └───┬──┘
        │         │
        │    ┌────▼────────────────────────┐
        │    │ Redirect to:                 │
        │    │ /email-verification-notice  │
        │    │                              │
        │    │ Message: Verify email first │
        │    └──────────────────────────────┘
        │
        ▼
    ┌──────────────────────────────────┐
    │ Auth::login($user)               │
    │ Create session                   │
    │ Remember-me cookie (if checked)  │
    └────────┬─────────────────────────┘
             │
    ┌────────▼──────────────────────────────┐
    │ Redirect to:                         │
    │ /dashboard                            │
    │                                        │
    │ User logged in and can access         │
    │ protected dashboard                   │
    └──────────────────────────────────────┘
```

## Middleware Chain Diagram

```
                     MIDDLEWARE CHAINS
                     ═════════════════

Public Routes (/)
├── No middleware
└── Route: / (home page)

Guest Routes (/login, /register)
├── Middleware: guest (redirect to /dashboard if logged in)
├── Route: GET /login → RegisterController@showRegistrationForm
├── Route: GET /register → LoginController@showLoginForm
├── Route: POST /login → LoginController@login
└── Route: POST /register → RegisterController@register

Auth Routes
├── Middleware: auth (require valid session)
├── Route: POST /logout → LoginController@logout
├── Route: GET /email-verification-notice → EmailVerificationController@notice
├── Route: POST /email-verification-send → EmailVerificationController@resend
└── Route: GET /verify-email/{id}/{hash}
    └── Additional: signed (validate signed URL)
        └── Route: EmailVerificationController@verify

Verified Routes (Protected Dashboard)
├── Middleware: auth (require valid session)
├── Middleware: verified (require email_verified_at != null)
└── Route: GET /dashboard (DashboardController@show)
```

## Data Flow: Email Verification

```
User Registration
        │
        ▼
User Model Created
        │
        ▼
Registered Event Fired
        │
        ▼
EventServiceProvider Routes:
Registered → SendEmailVerificationNotification
        │
        ▼
Listener Triggered
        │
        ▼
Mail::to()->send(new VerifyEmail($user))
        │
        ▼
VerifyEmail Mailable Class
        │
        ├─ Creates markdown email
        ├─ Generates signed URL with hash
        ├─ URL: /verify-email/{id}/{hash}
        └─ Expires in 60 minutes
        │
        ▼
Mail Driver (log in dev mode)
        │
        ▼
Written to: storage/logs/laravel.log
        │
        ▼
User Receives Email & Clicks Link
        │
        ▼
EmailVerificationController@verify($id, $hash)
        │
        ├─ Validate: hash_equals($hash, hash('sha256', $user->email))
        │
        ▼
User::markEmailAsVerified()
        │
        ├─ Set email_verified_at = now()
        ├─ Save user
        │
        ▼
User Redirected to /dashboard
        │
        ▼
Dashboard Loads (middleware: auth + verified)
```

## API Token Flow (Sanctum)

```
User API Request
        │
        ▼
Create Token: $user->createToken('token-name')
        │
        ├─ Inserts into personal_access_tokens table
        ├─ Generates unique token hash
        ├─ Returns: PlainTextToken (only visible once)
        │
        ▼
Client Stores: Authorization: Bearer {token}
        │
        ▼
API Request with Token
        │
        ▼
Sanctum Middleware Validates
        │
        ├─ Parses Authorization header
        ├─ Hashes token
        ├─ Looks up in personal_access_tokens
        ├─ Verifies user_id matches
        │
        ▼
Auth::loginUsingId($user_id)
        │
        ▼
User Authenticated for API Routes
        │
        ▼
User Quota Tracked:
        api_quota_used + 1
```

## Key Features Implemented

```
✅ Authentication System
   ├── Registration with validation
   ├── Login with session
   ├── Logout functionality
   ├── Remember-me option
   └── Email verification requirement

✅ Email Verification
   ├── Sends on registration
   ├── Signed URL verification
   ├── Resend functionality
   ├── 60-minute link expiration
   └── Beautiful HTML email template

✅ User Model
   ├── SaaS subscription fields
   ├── API quota tracking
   ├── Email verification methods
   ├── Scopes (active, verified, subscribed)
   ├── Helper methods
   └── Relationships to core entities

✅ Security
   ├── Password hashing
   ├── CSRF protection
   ├── Signed URL verification
   ├── Email verification middleware
   ├── Soft deletes
   └── API token authentication (Sanctum)

✅ Frontend Templates
   ├── Beautiful Tailwind CSS design
   ├── Form validation error display
   ├── Feature showcase cards
   ├── Responsive layout
   ├── Professional branding
   └── Accessible HTML5

✅ Database Schema
   ├── Email verification column
   ├── Subscription plan/status
   ├── API quota tracking
   ├── Soft deletes
   └── Timestamps for audit trail
```

---
**Architecture Version:** 1.0
**Created:** December 12, 2024
**Status:** Production Ready
