# Website Management - Quick Reference & Testing Guide

## Access URLs

**After login at http://127.0.0.1:8000/login:**

- **List websites:** http://127.0.0.1:8000/websites
- **Add website:** http://127.0.0.1:8000/websites/create
- **Edit website:** http://127.0.0.1:8000/websites/{id}/edit
- **View details:** http://127.0.0.1:8000/websites/{id}

## Test Credentials

```
Email: test@example.com
Password: Test1234!
```

## API Reference (for curl/Postman testing)

### Create Website

```bash
curl -X POST http://127.0.0.1:8000/websites \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -d "name=My%20Website&url=https://example.com&username=admin&app_password=xxxx%20xxxx&category=technology_software"
```

### Update Website

```bash
curl -X PUT http://127.0.0.1:8000/websites/1 \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -d "name=Updated&url=https://updated.com&username=admin&category=news_media&status=inactive"
```

### Delete Website

```bash
curl -X DELETE http://127.0.0.1:8000/websites/1 \
  -H "X-CSRF-TOKEN: {csrf_token}"
```

### Test Connection (AJAX)

```bash
curl -X POST http://127.0.0.1:8000/websites/1/test-connection \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -H "Accept: application/json"
```

## Encryption Details

### How App Password Storage Works

1. **Input:** User enters `xxxx xxxx xxxx xxxx`
2. **Storage:** Laravel Crypt encrypts to: `eyJpdiI6IjM...` (JSON with iv, value, mac)
3. **Retrieval:** Auto-decrypted via `getAppPasswordAttribute()` mutator
4. **Display:** Masked form shows `••••••••••••••••••••••••••••`

### Manual Encryption/Decryption in Code

```php
// Encrypt
$encrypted = Crypt::encryptString('plaintext');

// Decrypt
$plaintext = Crypt::decryptString($encrypted);

// Via model (automatic)
$website->app_password = 'new_password'; // Auto-encrypted on save
$password = $website->app_password; // Auto-decrypted on retrieval
```

## Database Queries

### List All Websites for User

```php
// Via Model
$websites = Website::where('created_by', auth()->id())->get();

// Via Query Builder
$websites = DB::table('ane_websites')
    ->where('created_by', auth()->id())
    ->get();
```

### Get Website with Stats

```php
$website = Website::where('id', 1)
    ->withCount('analyses')
    ->withCount('keywords')
    ->first();
```

### Decrypt Password for Integration

```php
$website = Website::find(1);
$plainPassword = $website->app_password; // Already decrypted
```

## Validation Rules

**Create validation:**
- name: required|string|max:100|unique:ane_websites,name
- url: required|url|max:255
- username: required|string|max:100
- app_password: required|string|max:255
- category: required|string|in:business_industrial,shopping_ecommerce,...

**Update validation:**
- Same as create, but app_password is nullable

## Authorization Rules

- **View/Edit/Delete:** Only website owner (created_by = auth()->id())
- **Policy:** WebsitePolicy::own()
- **Exception:** 403 Forbidden if not owner

## Category Enum Values (14 options)

```
'business_industrial'
'shopping_ecommerce'
'technology_software'
'education_content'
'news_media'
'health_beauty'
'food_beverage'
'travel_hospitality'
'finance_investment'
'real_estate'
'automotive'
'entertainment'
'personal_brand'
'other'
```

## Status Values

```
'active'   → Website enabled for analysis/publishing
'inactive' → Website disabled temporarily
```

## Common Tasks

### Add New Website

1. Go to `/websites/create`
2. Fill form:
   - Name: "My Blog"
   - URL: "https://myblog.com"
   - Username: "admin"
   - App Password: (from WordPress → Users → Profile → App Passwords)
   - Category: "Technology & Software"
3. Click "Add Website"
4. Test connection (optional)

### Edit Website Details

1. Go to `/websites`
2. Click pencil icon
3. Modify any field
4. Leave app_password blank to keep current
5. Click "Update Website"

### Delete Website

1. Go to `/websites`
2. Click trash icon
3. Confirm deletion
4. Website and all related data (analyses, keywords) deleted

### Test WordPress Connection

1. Go to `/websites/{id}/edit`
2. Click "Test WordPress Connection" button
3. See status: ✓ Success or ✗ Failed
4. If failed, check:
   - URL is correct HTTPS
   - Username matches WordPress admin
   - App password is current (not revoked)
   - REST API enabled on WordPress

## Troubleshooting

### "Website name already exists"
- Name must be unique across all your websites
- Solution: Use different name or check existing websites list

### "Invalid URL format"
- Must be valid URL with http:// or https://
- Examples: ✓ https://example.com, ✗ example.com

### Form not submitting
- Check browser console for JavaScript errors
- Verify CSRF token is present: `<meta name="csrf-token">`
- Check app_password field is not empty on create

### Test connection failing
- Verify WordPress site is accessible
- Check app password hasn't been revoked in WordPress
- Verify REST API is enabled: https://yoursite.com/wp-json/wp/v2/posts
- Check SSL certificate is valid (HTTPS)

### Decryption fails in integration
- Never decrypt in database queries (mutator handles it)
- Always use Model: `$website->app_password`
- Never use raw DB query: `DB::raw('app_password')`

## Migration from anewp

This is a complete rewrite from anewp's native PHP to Laravel:

| Aspect | anewp | anewplaravel |
|--------|-------|-------------|
| Auth | Session files | Laravel Auth guard |
| Validation | Manual + arrays | Request->validate() |
| Encryption | Manual crypto | Crypt mutators |
| Authorization | Role check | WebsitePolicy |
| Database | PDO queries | Eloquent ORM |
| Errors | setFlash() | Session flash |
| Relationships | Manual JOINs | Eloquent relationships |

All functionality preserved, enhanced with Laravel security/elegance.

## Next: Website Analysis

The following tables are ready for implementation:

- `ane_website_analyses` - Analysis job results and status
- `ane_keywords` - Keywords extracted from analysis
- `ane_keyword_traces` - Historical ranking data

Next phase: Implement `AnalyzeWebsiteJob` to crawl and extract keywords.
