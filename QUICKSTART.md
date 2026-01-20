# AneWP Laravel - Quick Start Guide

## Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+

## Installation & Setup

### 1. Initial Setup (First Time Only)
```bash
cd /Users/webane/sites/anewplaravel

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations (database already imported)
php artisan migrate

# Start development server
php artisan serve

# In another terminal, start Vite for assets
npm run dev
```

### 2. Database Configuration
Already configured in `.env`:
- Host: 127.0.0.1
- Database: anewp_db
- User: root
- Password: root

The `anewp_db.sql` was imported and all migrations have been run.

## Development Workflow

### Start the Application
```bash
# Terminal 1: Laravel server (runs on http://localhost:8000)
php artisan serve

# Terminal 2: Vite frontend build
npm run dev
```

### View Email Verification Emails
Verification emails are logged to:
```bash
# Watch the log in real-time
tail -f storage/logs/laravel.log | grep -i "envelope"
```

Look for the email content in the log output.

## Available Routes

### Public Routes (No Login Required)
- `GET /` - Home page
- `GET /login` - Login form
- `POST /login` - Process login
- `GET /register` - Registration form
- `POST /register` - Process registration

### Protected Routes (Login Required)
- `POST /logout` - Sign out
- `GET /email-verification-notice` - Email verification notice
- `POST /email-verification-send` - Resend verification email
- `GET /verify-email/{id}/{hash}` - Verify email (from email link)

### Verified Routes (Login + Email Verified Required)
- `GET /dashboard` - User dashboard

## Testing the Authentication Flow

### Method 1: Web Browser
1. Open http://localhost:8000
2. Click "Sign Up"
3. Enter: name, email, password
4. Check `storage/logs/laravel.log` for verification email content
5. Copy the verification URL from logs
6. Visit that URL to verify email
7. You'll be redirected to dashboard

### Method 2: Artisan Tinker (Quick Testing)
```bash
php artisan tinker

# Create a test user
>>> $user = \App\Models\User::create([
...     'name' => 'Test User',
...     'email' => 'test@example.com',
...     'password' => bcrypt('password'),
...     'subscription_plan' => 'free',
... ]);

# Verify their email
>>> $user->markEmailAsVerified();

# Check verification
>>> $user->hasVerifiedEmail()
=> true

# Log the user in
>>> Auth::login($user);
>>> exit
```

## Code Structure

```
anewplaravel/
├── app/
│   ├── Http/
│   │   └── Controllers/Auth/
│   │       ├── RegisterController.php
│   │       ├── LoginController.php
│   │       └── EmailVerificationController.php
│   ├── Mail/
│   │   └── VerifyEmail.php
│   ├── Listeners/
│   │   └── SendEmailVerificationNotification.php
│   ├── Providers/
│   │   └── EventServiceProvider.php
│   └── Models/
│       └── User.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── auth.blade.php
│       ├── auth/
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   └── verify-email.blade.php
│       ├── mail/
│       │   └── verify-email.blade.php
│       └── dashboard.blade.php
├── routes/
│   └── web.php
└── database/
    └── migrations/
        └── 2025_12_12_100000_add_saas_fields_to_ane_users.php
```

## Common Tasks

### Create a New Admin User
```bash
php artisan tinker
>>> \App\Models\User::create([
...     'name' => 'Admin',
...     'email' => 'admin@example.com',
...     'password' => bcrypt('secure-password'),
...     'role' => 'admin',
...     'subscription_plan' => 'enterprise',
...     'subscription_status' => 'active',
...     'email_verified_at' => now(),
... ]);
```

### Reset All Users
```bash
php artisan tinker
>>> \App\Models\User::truncate();
>>> exit
```

### Check User Status
```bash
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $user->hasVerifiedEmail();
>>> $user->hasActiveSubscription();
>>> $user->api_quota_used . ' / ' . $user->api_quota_limit;
```

## Troubleshooting

### Email Not Sending
1. Check log file: `storage/logs/laravel.log`
2. Verify mail driver in `.env`: `MAIL_MAILER=log` (development)
3. Look for "Envelope" entries in logs

### Dashboard Not Accessible
1. Ensure email is verified
2. Check `email_verified_at` column is not null
3. Use tinker to verify: `User::first()->hasVerifiedEmail()`

### Cannot Login After Registration
1. Check Laravel logs for errors
2. Verify database credentials
3. Ensure migrations have run: `php artisan migrate:status`

### Composer Errors
```bash
# Clear composer cache
composer clear-cache

# Reinstall dependencies
composer install --no-interaction --prefer-dist
```

### Database Issues
```bash
# Rollback all migrations
php artisan migrate:reset

# Re-run migrations
php artisan migrate
```

## Environment Variables

Key `.env` variables:
```
APP_NAME=AneWP
APP_ENV=local
APP_DEBUG=true
DB_DATABASE=anewp_db
DB_USERNAME=root
DB_PASSWORD=root
MAIL_MAILER=log
```

For production, update:
- `APP_DEBUG=false`
- `APP_ENV=production`
- `MAIL_MAILER=smtp` (with SMTP credentials)

## Next Steps

Phase 2 implementation will include:
- [ ] Dashboard with analytics
- [ ] Deep Research module
- [ ] AI article generator
- [ ] Multi-website management
- [ ] Billing & subscription system

See `PHASE_1_COMPLETION_SUMMARY.md` for complete details.

## Support

For issues with the authentication system:
1. Check `PHASE_1_COMPLETION_SUMMARY.md` for detailed documentation
2. Review code comments in controllers
3. Check Laravel logs: `storage/logs/laravel.log`
4. Run `php artisan tinker` for quick debugging

---
**Last Updated:** December 12, 2024
**Laravel Version:** 11.x
**Status:** ✅ Production Ready
