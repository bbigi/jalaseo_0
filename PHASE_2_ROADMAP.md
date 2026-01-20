# Phase 2: Dashboard & Core Features - Implementation Roadmap

## Overview
Phase 2 will implement the dashboard with analytics, deep research module, AI article generator, and multi-website publishing capabilities.

## Phase 2 Tasks (Ordered by Priority)

### Task 1: Dashboard Controller & Enhancement
**Status:** 🔴 Not Started
**Time Estimate:** 2-3 hours
**Priority:** 🔴 Critical (blocks everything else)

#### Deliverables:
1. Create `app/Http/Controllers/DashboardController.php`
   - `show()` - Display dashboard with user stats
   - `getStats()` - Fetch analytics (JSON API)
   - `getRecentActivity()` - Recent user activity

2. Enhance `resources/views/dashboard.blade.php`
   - Fetch actual data from database:
     - Total posts count
     - Total insights count
     - Total websites count
     - API quota usage percentage
   - Navigation sidebar
   - Recent activity feed
   - Quick action buttons

#### Code Outline:
```php
// DashboardController.php
public function show(Request $request)
{
    $user = $request->user();
    
    $stats = [
        'total_posts' => $user->posts()->count(),
        'total_insights' => $user->insights()->count(),
        'total_websites' => $user->websites()->count(),
        'api_usage' => [
            'used' => $user->api_quota_used,
            'limit' => $user->api_quota_limit,
            'percentage' => ($user->api_quota_used / $user->api_quota_limit) * 100,
        ],
        'subscription' => [
            'plan' => $user->subscription_plan,
            'status' => $user->subscription_status,
            'days_remaining' => $user->getSubscriptionDaysRemaining(),
        ],
    ];
    
    return view('dashboard', ['stats' => $stats]);
}
```

#### Test Cases:
- [ ] Dashboard loads for verified users
- [ ] Dashboard blocked for unverified users
- [ ] Stats show correct counts
- [ ] Navigation links work
- [ ] Logout button functions

---

### Task 2: Website Management Module
**Status:** 🔴 Not Started
**Time Estimate:** 3-4 hours
**Priority:** 🟠 High

#### Deliverables:
1. Create `app/Http/Controllers/WebsiteController.php`
   - `index()` - List user's websites
   - `create()` - Show create form
   - `store()` - Save new website
   - `show()` - Show website details
   - `edit()` - Edit website
   - `update()` - Save changes
   - `destroy()` - Delete website

2. Create views:
   - `resources/views/websites/index.blade.php` - List websites
   - `resources/views/websites/create.blade.php` - Create form
   - `resources/views/websites/show.blade.php` - Website details
   - `resources/views/websites/edit.blade.php` - Edit form

3. Routes:
   ```php
   Route::middleware(['auth', 'verified'])->group(function () {
       Route::resource('websites', WebsiteController::class);
   });
   ```

#### Database Considerations:
- `ane_websites` table already exists
- Columns: id, user_id, name, url, wordpress_api_key, status, created_at, updated_at

#### Test Cases:
- [ ] Create website (store WordPress URL & API key)
- [ ] List user's websites
- [ ] Edit website details
- [ ] Delete website
- [ ] Validate WordPress API connectivity

---

### Task 3: Deep Research Module
**Status:** 🔴 Not Started
**Time Estimate:** 4-5 hours
**Priority:** 🟠 High

#### Deliverables:
1. Create `app/Services/DeepResearchService.php`
   - Migrate logic from PHP native app
   - Methods: `search()`, `analyze()`, `generateReport()`
   - Cache results using Laravel cache

2. Create `app/Http/Controllers/DeepResearchController.php`
   - `index()` - Show search interface
   - `search()` - Process search query
   - `show()` - Show research details

3. Create views:
   - `resources/views/deep-research/index.blade.php` - Search form
   - `resources/views/deep-research/results.blade.php` - Results display

4. Routes:
   ```php
   Route::middleware(['auth', 'verified'])->group(function () {
       Route::resource('deep-research', DeepResearchController::class);
   });
   ```

#### Integration with APIs:
- Google Trends API
- Semrush API (if available)
- SEMrush API for competitor analysis

#### Queuing:
- Long-running searches should be queued
- Use `php artisan queue:work` in development

#### Test Cases:
- [ ] Search form displays
- [ ] Search processes without errors
- [ ] Results display correctly
- [ ] Data caches properly
- [ ] Queue jobs process

---

### Task 4: AI Article Generator
**Status:** 🔴 Not Started
**Time Estimate:** 3-4 hours
**Priority:** 🟠 High

#### Deliverables:
1. Create `app/Services/AIArticleService.php`
   - Integrate with OpenAI/Anthropic API
   - Methods: `generateArticle()`, `improveContent()`, `formatContent()`
   - Token counting & cost tracking

2. Create `app/Http/Controllers/ArticleGeneratorController.php`
   - `create()` - Show generator interface
   - `generate()` - Process AI generation
   - `show()` - Show generated article

3. Create views:
   - `resources/views/articles/generate.blade.php` - Generator form
   - `resources/views/articles/preview.blade.php` - Preview generated content

4. Integrate with existing Post model

#### Configuration:
```php
// config/services.php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => 'gpt-4',
    'max_tokens' => 2000,
],
```

#### Queue System:
- Article generation via queue jobs
- User notified when complete
- Can preview in real-time

#### Test Cases:
- [ ] Article generator form shows
- [ ] API calls processed correctly
- [ ] Generated content saves to Post model
- [ ] User notified on completion
- [ ] Queue jobs process

---

### Task 5: Multi-Website Publishing
**Status:** 🔴 Not Started
**Time Estimate:** 3-4 hours
**Priority:** 🟠 High

#### Deliverables:
1. Create `app/Services/WordPressAPIService.php`
   - Migrate from PHP native app
   - Methods: `authenticate()`, `createPost()`, `updatePost()`, `publishPost()`
   - Handle multiple WordPress instances

2. Create `app/Http/Controllers/PublishingController.php`
   - `create()` - Show publishing form
   - `publish()` - Publish to WordPress
   - `history()` - Show publishing history

3. Create views:
   - `resources/views/publishing/form.blade.php` - Publish form
   - `resources/views/publishing/history.blade.php` - History

4. Routes:
   ```php
   Route::middleware(['auth', 'verified'])->group(function () {
       Route::post('publish', PublishingController::class);
       Route::get('publishing-history', [PublishingController::class, 'history']);
   });
   ```

#### Database:
- `ane_posts` - article content
- `ane_post_media` - featured images
- Create `posting_history` table for tracking

#### Test Cases:
- [ ] Connect to WordPress site
- [ ] Create post on WordPress
- [ ] Track published articles
- [ ] Handle multiple websites
- [ ] Error handling for API failures

---

### Task 6: Analytics Dashboard
**Status:** 🔴 Not Started
**Time Estimate:** 2-3 hours
**Priority:** 🟡 Medium

#### Deliverables:
1. Create `app/Http/Controllers/AnalyticsController.php`
   - `dashboard()` - Show analytics overview
   - `getMetrics()` - JSON API for chart data

2. Enhance dashboard.blade.php with charts
   - Posts published over time
   - Content performance
   - API usage trends
   - Visitor statistics

3. Integrate with Google Analytics API

#### Libraries:
- Chart.js or ApexCharts for visualizations
- Laravel Google Analytics package

#### Test Cases:
- [ ] Charts load and render
- [ ] Data updates in real-time
- [ ] API calls succeed
- [ ] Performance acceptable

---

### Task 7: Billing & Subscription System
**Status:** 🔴 Not Started
**Time Estimate:** 4-5 hours
**Priority:** 🟡 Medium

#### Deliverables:
1. Create `app/Services/StripeService.php`
   - Integrate Stripe payment processing
   - Methods: `createCustomer()`, `createSubscription()`, `handleWebhook()`

2. Create `app/Http/Controllers/BillingController.php`
   - `pricing()` - Show pricing plans
   - `subscribe()` - Handle subscription
   - `manage()` - Manage subscription
   - `webhook()` - Handle Stripe webhooks

3. Create views:
   - `resources/views/billing/pricing.blade.php` - Pricing page
   - `resources/views/billing/manage.blade.php` - Manage subscription

4. Database migrations for subscription tracking

#### Configuration:
```php
// config/stripe.php
'plans' => [
    'free' => ['id' => 'price_free', 'price' => 0, 'api_quota' => 100],
    'pro' => ['id' => 'price_pro', 'price' => 29, 'api_quota' => 10000],
    'enterprise' => ['id' => 'price_ent', 'price' => 99, 'api_quota' => 100000],
],
```

#### Test Cases:
- [ ] Stripe integration works
- [ ] Payment processing succeeds
- [ ] Webhooks handled correctly
- [ ] Subscription status updates
- [ ] Invoice generation

---

## Implementation Sequence

```
Week 1:
└── Task 1: Dashboard Controller ✓ (Foundation)

Week 2:
├── Task 2: Website Management ✓
└── Task 3: Deep Research Module ✓

Week 3:
├── Task 4: AI Article Generator ✓
└── Task 5: Publishing System ✓

Week 4:
├── Task 6: Analytics Dashboard ✓
└── Task 7: Billing System ✓
```

## Testing Strategy

### Unit Tests:
```bash
# Create tests/Unit/Services/DeepResearchServiceTest.php
php artisan make:test Services/DeepResearchServiceTest --unit

# Run tests
php artisan test
```

### Feature Tests:
```bash
# Create tests/Feature/DashboardTest.php
php artisan make:test DashboardTest

# Create tests/Feature/WebsiteTest.php
php artisan make:test WebsiteTest

# Run specific test
php artisan test tests/Feature/DashboardTest.php
```

### Manual Testing Checklist:
- [ ] Authentication flow works end-to-end
- [ ] Dashboard displays for all users
- [ ] Each module CRUD operations work
- [ ] API integrations succeed
- [ ] Email notifications send
- [ ] Error handling displays properly
- [ ] Mobile responsive design works

## Performance Optimization (Phase 2+)

- [ ] Implement query caching with Redis
- [ ] Add database indexes for frequently queried fields
- [ ] Optimize asset loading (minify CSS/JS)
- [ ] Implement pagination for large datasets
- [ ] Cache API responses
- [ ] Add rate limiting for API endpoints
- [ ] Database query optimization (eager loading)

## Security Enhancements (Phase 2+)

- [ ] Add two-factor authentication
- [ ] Implement API rate limiting
- [ ] Add CORS policies
- [ ] Validate all user inputs
- [ ] Sanitize output for XSS prevention
- [ ] Add CSRF tokens to forms
- [ ] Implement audit logging
- [ ] Add password strength validation

## Deployment Checklist (Before Production)

- [ ] Environment variables set correctly
- [ ] Database migrations run
- [ ] Assets compiled (`npm run build`)
- [ ] SSL certificate configured
- [ ] Email provider configured (SendGrid, AWS SES, etc.)
- [ ] Stripe keys configured
- [ ] API keys secured
- [ ] Logging configured
- [ ] Backup system in place
- [ ] Monitoring & alerting set up

## Files to Create Summary

```
Controllers (5):
├── DashboardController.php
├── WebsiteController.php
├── DeepResearchController.php
├── ArticleGeneratorController.php
├── PublishingController.php
├── AnalyticsController.php
└── BillingController.php

Services (3):
├── DeepResearchService.php
├── AIArticleService.php
├── WordPressAPIService.php
├── StripeService.php

Views (12+):
├── dashboard.blade.php (enhanced)
├── websites/index.blade.php
├── websites/create.blade.php
├── websites/show.blade.php
├── websites/edit.blade.php
├── deep-research/index.blade.php
├── deep-research/results.blade.php
├── articles/generate.blade.php
├── articles/preview.blade.php
├── publishing/form.blade.php
├── publishing/history.blade.php
├── billing/pricing.blade.php
├── billing/manage.blade.php
└── analytics/dashboard.blade.php

Migrations (3):
├── Create subscription tracking columns
├── Create publishing_history table
└── Create API usage logs table

Config (2):
├── config/stripe.php
└── config/openai.php
```

## Next Steps

1. **Start with Task 1:** Create DashboardController and enhance dashboard view
2. **Test thoroughly:** Ensure each task works before moving to next
3. **Monitor performance:** Check database queries and API calls
4. **Gather feedback:** Test with real users and iterate
5. **Prepare for production:** Security audit and performance testing

---
**Created:** December 12, 2024
**Estimated Duration:** 4 weeks
**Status:** Ready to implement
**Next Phase Start:** After Phase 1 completion & testing
