# Architecture Guidelines - AneWP SaaS Platform

**Last Updated:** December 13, 2025

---

## 🎯 Core Principles

### 1. Multi-Tenant Concurrency Architecture

**Requirement:**
- 1 API key akan digunakan oleh **5-10+ users secara bersamaan**
- High concurrent access dalam satu waktu
- Server harus tetap **fast & responsive** meskipun high traffic
- Design harus **scalable** untuk growth

**Implementation Checklist:**
- ✅ Proper database indexing
- ✅ Query optimization (avoid N+1, use eager loading)
- ✅ Caching strategy (Redis-ready untuk production)
- ✅ Async/Queue processing untuk heavy operations
- ✅ Stateless controllers (load balancing ready)
- ✅ Connection pooling
- ✅ Row-level security per user

---

## 💳 Quota System Strategy

### Request-Based Quota (Approved Model)

**Tier Structure:**

#### Free Plan
- **10 articles/month**
- **5 images/month**
- **3 deep research/month**
- Rate limit: 10 requests/hour

#### Starter Plan ($9-19/mo)
- **50 articles/month**
- **25 images/month**
- **15 deep research/month**
- Rate limit: 30 requests/hour

#### Pro Plan ($29-49/mo)
- **200 articles/month**
- **100 images/month**
- **50 deep research/month**
- Rate limit: 100 requests/hour

### Implementation Details

**Database Fields (ane_users table):**
```sql
subscription_plan       VARCHAR(50)   -- free, starter, pro
api_quota_limit         INT           -- Max quota per period
api_quota_used          INT           -- Current usage
api_quota_reset_at      TIMESTAMP     -- Monthly reset date
```

**Tracking Flow:**
```php
// Before expensive operation
if (auth()->user()->api_quota_used >= auth()->user()->api_quota_limit) {
    throw new QuotaExceededException();
}

// After operation completes
auth()->user()->increment('api_quota_used', 1);
```

**Reset Strategy:**
- Monthly reset via cron job
- Reset `api_quota_used = 0` when `now() > api_quota_reset_at`
- Set new `api_quota_reset_at = now()->addMonth()`

---

## 🔐 Security & Multi-Tenant Isolation

### Data Isolation Rules
- **ALWAYS** filter queries by `created_by = auth()->id()`
- Never expose other users' data
- API credentials are shared resources (no user_id FK)
- Validate ownership before update/delete operations

### Example Queries:
```php
// ✅ CORRECT
Post::where('created_by', auth()->id())->get();
Website::where('created_by', auth()->id())->where('status', 'active')->count();

// ❌ WRONG (exposes all users data)
Post::all();
Website::where('status', 'active')->count();
```

---

## ⚡ Performance Optimization Guidelines

### Database Queries
1. **Index frequently queried columns:**
   - `created_by`
   - `status`
   - `category`
   - Composite indexes for common WHERE combinations

2. **Avoid N+1 queries:**
   ```php
   // ✅ CORRECT
   Post::with('website', 'user')->where('created_by', auth()->id())->get();
   
   // ❌ WRONG
   $posts = Post::where('created_by', auth()->id())->get();
   foreach ($posts as $post) {
       $post->website; // N+1 query
   }
   ```

3. **Use pagination:**
   ```php
   Post::where('created_by', auth()->id())->paginate(20);
   ```

### Caching Strategy
- Cache API credentials (rarely change, read-heavy)
- Cache user quotas (check before every operation)
- Cache config/settings
- Use Redis for production (file cache for development)

### Async Processing
- AI content generation → Queue
- Image generation → Queue
- Deep research scraping → Queue
- Email notifications → Queue

**Never block HTTP response untuk heavy operations.**

---

## 🔑 API Credentials Management

### Shared Resource Model
- API credentials belong to **platform**, not individual users
- Multiple users share same pool of API keys
- Rotation/update affects all users instantly
- Track usage per-user, not per-key

### Selection Strategy
```php
// Get active credential for category
$credential = ApiCredential::where('category', 'article_generation')
    ->where('is_active', true)
    ->where('provider', 'gemini')
    ->first();

// Fallback to other provider if primary unavailable
if (!$credential) {
    $credential = ApiCredential::where('category', 'article_generation')
        ->where('is_active', true)
        ->first();
}
```

---

## 📊 Usage Tracking & Analytics

### Track untuk billing & optimization:
1. **Per-user usage:**
   - Total requests per action type
   - Token consumption (background tracking)
   - Response times
   
2. **Per-API-key usage:**
   - Request count
   - Error rate
   - Rate limit hits

3. **System-wide metrics:**
   - Concurrent users
   - Peak usage times
   - Slow queries
   - Queue processing time

---

## 🚀 Deployment & Scaling Considerations

### Ready for Scale:
- Stateless application (horizontal scaling ready)
- Database connection pooling
- Session management: File-based (dev) → Redis (production)
- Queue workers: Supervisor for process management
- CDN untuk static assets
- Load balancer ready

### Monitoring:
- Laravel Telescope (development)
- Laravel Horizon (queue monitoring)
- Database slow query log
- Application performance monitoring (APM)

---

## 📝 Coding Standards for Every Feature

**Before committing code, verify:**

- [ ] Query has proper WHERE filter for multi-tenant isolation
- [ ] Indexes exist for filtered/sorted columns
- [ ] N+1 queries avoided (use eager loading)
- [ ] Heavy operations use Queue
- [ ] Quota check before expensive operations
- [ ] Usage tracking after completion
- [ ] Error handling & logging present
- [ ] Concurrent access safe (no race conditions)
- [ ] Cache strategy considered
- [ ] Security: No data leakage between users

---

## 🎯 Future Enhancements (Phase 2+)

- Real-time quota dashboard
- Usage analytics per user
- Auto top-up credits
- Alert system (80%, 90%, 100% quota)
- API provider failover automation
- Token-based quota upgrade path
- Webhook notifications
- Admin usage reports

---

**This document is the source of truth for architectural decisions. Update as requirements evolve.**
