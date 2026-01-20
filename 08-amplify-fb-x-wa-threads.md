# Content Amplification Strategy - Multi-Platform Publishing

**Tanggal:** 5 Januari 2026
**Status:** 📋 Planning Phase
**Author:** Claude Code

---

## Executive Summary

Dokumen ini menjelaskan **strategi amplifikasi konten** dari WordPress ke platform social media (Facebook, Twitter/X, WhatsApp, Threads).

**PRINSIP UTAMA:**
> **WordPress FIRST, Social Media SECOND**
>
> Semua content HARUS dipublish ke WordPress terlebih dahulu sebelum diamplifikasi ke social media. Tujuan: Mendapatkan **WordPress URL** untuk drive traffic ke website.

**Business Logic:**
- ✅ WordPress publish → dapat URL (e.g., `https://example.com/article-title`)
- ✅ Social media posts → share URL + excerpt/preview
- ✅ Traffic dari social media → website (SEO benefit, analytics tracking)
- ❌ Direct publish ke social media tanpa URL → no traffic benefit

---

## 1. CONTENT AMPLIFICATION FLOW

### 1.1 Overall Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                    USER CREATES POST                          │
│            (Title, Content, Featured Image)                   │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌──────────────────────────────────────────────────────────────┐
│               STEP 1: PUBLISH TO WORDPRESS                    │
│  - Create/Update post via WordPress REST API                 │
│  - Status: publish (immediate) OR future (scheduled)          │
│  - Get WordPress URL from response                            │
│  - Store wp_post_id + wp_url in ane_post_targets             │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌─────────────────┐
                    │  WordPress URL  │ ← CRITICAL!
                    │  ACQUIRED ✅    │
                    └─────────────────┘
                              │
                              ▼
┌──────────────────────────────────────────────────────────────┐
│          STEP 2: AMPLIFY TO SOCIAL MEDIA                      │
│  (Only proceed if WordPress publish successful)               │
└──────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┬──────────┐
        ▼                     ▼                     ▼          ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐ ┌──────────┐
│   Facebook   │    │  Twitter/X   │    │   WhatsApp   │ │ Threads  │
│              │    │              │    │              │ │          │
│ Link Share   │    │ Tweet +      │    │ Template     │ │Link Post │
│ with Preview │    │ URL          │    │ Message      │ │          │
└──────────────┘    └──────────────┘    └──────────────┘ └──────────┘
```

### 1.2 Detailed Step-by-Step Flow

**Phase 1: WordPress Publishing (MANDATORY)**

```php
// PostController.php - publish() method

public function publish(Request $request, Post $post)
{
    // 1. Validate WordPress website selected
    if (!$request->website_id) {
        return redirect()->back()->withErrors([
            'website_id' => 'Please select a WordPress website to publish'
        ]);
    }

    // 2. Publish to WordPress FIRST
    $wpService = new WordPressPublishService();

    try {
        $target = $post->targets()->create([
            'website_id' => $request->website_id,
            'status' => 'draft', // Temporary
            'scheduled_at' => $request->scheduled_at,
        ]);

        $wpResult = $wpService->publish($post, $target, 'publish_now');

        // 3. CRITICAL: Extract WordPress URL from response
        $wpUrl = $wpResult['link'] ?? null;
        $wpPostId = $wpResult['id'] ?? null;

        if (!$wpUrl) {
            throw new \Exception('WordPress did not return post URL');
        }

        // 4. Update PostTarget with WordPress URL
        $target->update([
            'wp_post_id' => $wpPostId,
            'wp_url' => $wpUrl,  // ⚠️ NEW COLUMN NEEDED!
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 5. NOW we can proceed with social media amplification
        if ($request->amplify_to_social) {
            return $this->amplifyToSocialMedia($post, $target, $request);
        }

        return redirect()->route('posts.index')
            ->with('success', "Post published to WordPress: {$wpUrl}");

    } catch (\Exception $e) {
        return redirect()->back()->withErrors([
            'publish_error' => "Failed to publish to WordPress: {$e->getMessage()}"
        ]);
    }
}
```

**Phase 2: Social Media Amplification (CONDITIONAL)**

```php
// PostController.php - amplifyToSocialMedia() method

protected function amplifyToSocialMedia(Post $post, PostTarget $wpTarget, Request $request)
{
    // PREREQUISITE CHECK: WordPress URL must exist
    if (!$wpTarget->wp_url) {
        throw new \Exception('Cannot amplify: WordPress URL not available');
    }

    $wpUrl = $wpTarget->wp_url;
    $selectedPlatforms = $request->amplify_platforms ?? []; // ['facebook', 'twitter', 'whatsapp', 'threads']

    $results = [];

    foreach ($selectedPlatforms as $platform) {
        try {
            switch ($platform) {
                case 'facebook':
                    $results['facebook'] = $this->amplifyToFacebook($post, $wpUrl);
                    break;

                case 'twitter':
                    $results['twitter'] = $this->amplifyToTwitter($post, $wpUrl);
                    break;

                case 'whatsapp':
                    $results['whatsapp'] = $this->amplifyToWhatsApp($post, $wpUrl);
                    break;

                case 'threads':
                    $results['threads'] = $this->amplifyToThreads($post, $wpUrl);
                    break;
            }
        } catch (\Exception $e) {
            $results[$platform] = ['error' => $e->getMessage()];
        }
    }

    return redirect()->route('posts.index')
        ->with('success', 'Post published to WordPress and amplified to social media')
        ->with('amplify_results', $results);
}
```

---

## 2. DATABASE SCHEMA CHANGES

### 2.1 Add wp_url Column to ane_post_targets

**Migration:**
```php
// database/migrations/2026_01_05_add_wp_url_to_post_targets.php

public function up()
{
    Schema::table('ane_post_targets', function (Blueprint $table) {
        $table->string('wp_url', 500)->nullable()->after('wp_post_id');
        $table->index('wp_url'); // For lookup
    });
}

public function down()
{
    Schema::table('ane_post_targets', function (Blueprint $table) {
        $table->dropColumn('wp_url');
    });
}
```

**Updated PostTarget Model:**
```php
// app/Models/PostTarget.php

protected $fillable = [
    'post_id',
    'website_id',
    'status',
    'scheduled_at',
    'published_at',
    'wp_post_id',
    'wp_url',           // ✅ NEW FIELD
    'categories',
    'tags',
    'wp_response',
];
```

### 2.2 Create ane_social_amplifications Table

**Purpose:** Track amplification status per platform

```php
// database/migrations/2026_01_05_create_social_amplifications_table.php

Schema::create('ane_social_amplifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained('ane_posts')->onDelete('cascade');
    $table->foreignId('post_target_id')->constrained('ane_post_targets')->onDelete('cascade');

    $table->enum('platform', ['facebook', 'twitter', 'whatsapp', 'threads']);
    $table->enum('status', ['pending', 'success', 'failed'])->default('pending');

    $table->string('platform_post_id')->nullable(); // FB post ID, Tweet ID, etc.
    $table->string('platform_url')->nullable();     // Link to social media post
    $table->text('platform_response')->nullable();  // API response JSON
    $table->text('error_message')->nullable();

    $table->timestamp('scheduled_at')->nullable();  // When to amplify
    $table->timestamp('published_at')->nullable();  // When actually published

    $table->timestamps();

    // Indexes
    $table->index(['post_id', 'platform']);
    $table->index(['status', 'scheduled_at']);
    $table->unique(['post_target_id', 'platform']); // One amplification per platform per WordPress publish
});
```

**Model:**
```php
// app/Models/SocialAmplification.php

class SocialAmplification extends Model
{
    protected $table = 'ane_social_amplifications';

    protected $fillable = [
        'post_id',
        'post_target_id',
        'platform',
        'status',
        'platform_post_id',
        'platform_url',
        'platform_response',
        'error_message',
        'scheduled_at',
        'published_at',
    ];

    protected $casts = [
        'platform_response' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function postTarget()
    {
        return $this->belongsTo(PostTarget::class);
    }
}
```

---

## 3. PLATFORM-SPECIFIC AMPLIFICATION STRATEGIES

### 3.1 Facebook - Link Share with Open Graph

**Strategy:** Post as **link share** (bukan text post) untuk trigger Facebook's link preview crawler.

**Requirements:**
- WordPress post MUST have Open Graph meta tags
- Featured image → og:image
- Title → og:title
- Excerpt → og:description

**Implementation:**
```php
// app/Services/Amplification/FacebookAmplifier.php

class FacebookAmplifier
{
    public function amplify(Post $post, string $wpUrl): array
    {
        // 1. Get user's Facebook Page
        $facebookPage = SocialAccount::where('user_id', $post->created_by)
            ->where('provider', 'meta_facebook')
            ->where('status', 'active')
            ->first();

        if (!$facebookPage) {
            throw new \Exception('No Facebook Page connected');
        }

        // 2. Prepare post data
        $message = $this->generateFacebookMessage($post, $wpUrl);

        // 3. Call Facebook Graph API - Create Link Post
        $pageId = $facebookPage->provider_id; // Facebook Page ID
        $accessToken = $facebookPage->access_token;

        $url = "https://graph.facebook.com/v18.0/{$pageId}/feed";

        $postData = [
            'message' => $message,
            'link' => $wpUrl,  // Facebook will scrape Open Graph data
            'access_token' => $accessToken,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("Facebook API error: {$response}");
        }

        $result = json_decode($response, true);

        // 4. Return result
        return [
            'platform_post_id' => $result['id'] ?? null,
            'platform_url' => "https://facebook.com/{$result['id']}", // Approximate
            'response' => $result,
        ];
    }

    private function generateFacebookMessage(Post $post, string $wpUrl): string
    {
        // Extract first 200 chars as teaser
        $excerpt = \Illuminate\Support\Str::limit(strip_tags($post->content), 200);

        return "{$post->title}\n\n{$excerpt}\n\nRead more: {$wpUrl}";
    }
}
```

**WordPress Side - Open Graph Meta Tags:**
```php
// Add to WordPress theme's header.php or via plugin

<meta property="og:title" content="<?php echo get_the_title(); ?>" />
<meta property="og:description" content="<?php echo get_the_excerpt(); ?>" />
<meta property="og:image" content="<?php echo get_the_post_thumbnail_url(null, 'large'); ?>" />
<meta property="og:url" content="<?php echo get_permalink(); ?>" />
<meta property="og:type" content="article" />
```

### 3.2 Twitter/X - Tweet with URL

**Strategy:** Tweet dengan format: **Title + Excerpt + URL**

**Character Limit:** 280 chars total (URL counts as 23 chars via t.co shortener)

**Implementation:**
```php
// app/Services/Amplification/TwitterAmplifier.php

class TwitterAmplifier
{
    public function amplify(Post $post, string $wpUrl): array
    {
        // 1. Get user's Twitter account
        $twitterAccount = SocialAccount::where('user_id', $post->created_by)
            ->where('provider', 'x')
            ->where('status', 'active')
            ->first();

        if (!$twitterAccount) {
            throw new \Exception('No Twitter account connected');
        }

        // 2. Prepare tweet text (max 280 chars, URL = 23 chars)
        $tweetText = $this->generateTweet($post, $wpUrl);

        // 3. Call Twitter API v2
        $url = 'https://api.twitter.com/2/tweets';

        $postData = [
            'text' => $tweetText,
        ];

        $accessToken = $twitterAccount->access_token;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}",
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            throw new \Exception("Twitter API error: {$response}");
        }

        $result = json_decode($response, true);

        // 4. Return result
        $tweetId = $result['data']['id'] ?? null;
        $username = $twitterAccount->username ?? 'user';

        return [
            'platform_post_id' => $tweetId,
            'platform_url' => "https://twitter.com/{$username}/status/{$tweetId}",
            'response' => $result,
        ];
    }

    private function generateTweet(Post $post, string $wpUrl): string
    {
        // Title + URL = base length
        $baseLength = strlen($post->title) + 23 + 3; // +3 for "\n\n"

        if ($baseLength > 280) {
            // Title too long, truncate
            $maxTitleLength = 280 - 23 - 6; // -6 for "...\n\n"
            $truncatedTitle = \Illuminate\Support\Str::limit($post->title, $maxTitleLength, '...');
            return "{$truncatedTitle}\n\n{$wpUrl}";
        }

        // Add excerpt if space available
        $remainingChars = 280 - $baseLength;

        if ($remainingChars > 50) {
            $excerpt = \Illuminate\Support\Str::limit(strip_tags($post->content), $remainingChars - 3);
            return "{$post->title}\n\n{$excerpt}\n\n{$wpUrl}";
        }

        // Just title + URL
        return "{$post->title}\n\n{$wpUrl}";
    }
}
```

### 3.3 WhatsApp - Template Message with URL

**Strategy:** Send WhatsApp template message (requires Meta approval)

**Challenge:** WhatsApp Business API hanya allow template messages untuk proactive messaging.

**Requirements:**
- Create message template di Meta Business Manager
- Submit for approval (1-2 days review)
- Template must include URL placeholder

**Template Example:**
```
Template Name: article_notification
Language: en_US
Category: MARKETING

Body:
📰 New Article Published!

{{1}}

Read the full article here: {{2}}

Variables:
1. Article title
2. WordPress URL
```

**Implementation:**
```php
// app/Services/Amplification/WhatsAppAmplifier.php

class WhatsAppAmplifier
{
    public function amplify(Post $post, string $wpUrl): array
    {
        // 1. Get WhatsApp Business account
        $whatsappAccount = SocialAccount::where('user_id', $post->created_by)
            ->where('provider', 'whatsapp')
            ->where('status', 'active')
            ->first();

        if (!$whatsappAccount) {
            throw new \Exception('No WhatsApp Business account connected');
        }

        // 2. Get recipient list (from user's contacts or broadcast list)
        $recipients = $this->getWhatsAppRecipients($post->created_by);

        if (empty($recipients)) {
            throw new \Exception('No WhatsApp recipients configured');
        }

        // 3. Send template message to each recipient
        $phoneNumberId = $whatsappAccount->provider_id; // WhatsApp Phone Number ID
        $accessToken = $whatsappAccount->access_token;

        $sentCount = 0;
        $errors = [];

        foreach ($recipients as $recipient) {
            try {
                $this->sendTemplateMessage($phoneNumberId, $accessToken, $recipient, $post, $wpUrl);
                $sentCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to send to {$recipient}: {$e->getMessage()}";
            }
        }

        return [
            'platform_post_id' => null, // WhatsApp doesn't have "post" concept
            'platform_url' => null,
            'response' => [
                'sent_count' => $sentCount,
                'total_recipients' => count($recipients),
                'errors' => $errors,
            ],
        ];
    }

    private function sendTemplateMessage(string $phoneNumberId, string $accessToken, string $recipient, Post $post, string $wpUrl)
    {
        $url = "https://graph.facebook.com/v18.0/{$phoneNumberId}/messages";

        $messageData = [
            'messaging_product' => 'whatsapp',
            'to' => $recipient, // Phone number in international format (e.g., +6281234567890)
            'type' => 'template',
            'template' => [
                'name' => 'article_notification', // Template name (must be approved)
                'language' => [
                    'code' => 'en_US',
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $post->title, // {{1}}
                            ],
                            [
                                'type' => 'text',
                                'text' => $wpUrl, // {{2}}
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}",
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("WhatsApp API error: {$response}");
        }

        return json_decode($response, true);
    }

    private function getWhatsAppRecipients(int $userId): array
    {
        // TODO: Implement recipient management
        // Options:
        // 1. Store in ane_whatsapp_recipients table
        // 2. Import from CSV
        // 3. Use WhatsApp broadcast list

        return []; // Placeholder
    }
}
```

**Alternative (Simpler):**
If template approval too complex, use **WhatsApp Business App** manual sharing:
- Generate shareable link
- User manually sends via WhatsApp Business App
- Not fully automated, but no API restrictions

### 3.4 Threads - Text Post with URL

**Strategy:** Similar to Twitter, text post dengan URL

**Note:** Threads API currently in beta, limited access

**Implementation:**
```php
// app/Services/Amplification/ThreadsAmplifier.php

class ThreadsAmplifier
{
    public function amplify(Post $post, string $wpUrl): array
    {
        // 1. Get user's Threads account (via Instagram Business Account)
        $threadsAccount = SocialAccount::where('user_id', $post->created_by)
            ->where('provider', 'meta_threads')
            ->where('status', 'active')
            ->first();

        if (!$threadsAccount) {
            throw new \Exception('No Threads account connected');
        }

        // 2. Prepare thread text (max 500 chars)
        $threadText = $this->generateThreadText($post, $wpUrl);

        // 3. Call Threads API (Graph API)
        $userId = $threadsAccount->provider_id; // Threads User ID
        $accessToken = $threadsAccount->access_token;

        // Step 1: Create container
        $containerUrl = "https://graph.threads.net/v1.0/{$userId}/threads";

        $containerData = [
            'media_type' => 'TEXT',
            'text' => $threadText,
            'access_token' => $accessToken,
        ];

        $ch = curl_init($containerUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($containerData));

        $response = curl_exec($ch);
        curl_close($ch);

        $container = json_decode($response, true);
        $containerId = $container['id'] ?? null;

        if (!$containerId) {
            throw new \Exception("Failed to create Threads container: {$response}");
        }

        // Step 2: Publish container
        $publishUrl = "https://graph.threads.net/v1.0/{$userId}/threads_publish";

        $publishData = [
            'creation_id' => $containerId,
            'access_token' => $accessToken,
        ];

        $ch = curl_init($publishUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($publishData));

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        $threadId = $result['id'] ?? null;

        return [
            'platform_post_id' => $threadId,
            'platform_url' => "https://threads.net/t/{$threadId}", // Approximate
            'response' => $result,
        ];
    }

    private function generateThreadText(Post $post, string $wpUrl): string
    {
        // Max 500 chars
        $maxLength = 500 - strlen($wpUrl) - 10; // -10 for "\n\nRead: "

        $excerpt = \Illuminate\Support\Str::limit(strip_tags($post->content), $maxLength);

        return "{$post->title}\n\n{$excerpt}\n\nRead: {$wpUrl}";
    }
}
```

---

## 4. PRE-PUBLISH VALIDATION - WORDPRESS URL CHECK

### 4.1 Validation Logic

**Critical Rule:** CANNOT amplify ke social media jika WordPress belum publish.

**Implementation:**
```php
// app/Http/Controllers/PostController.php

public function validateAmplification(Post $post, Request $request)
{
    // 1. Check if user wants to amplify to social media
    if (!$request->has('amplify_to_social')) {
        return true; // No amplification needed, OK to proceed
    }

    // 2. Check if WordPress publish is selected
    if (!$request->has('website_id')) {
        return redirect()->back()->withErrors([
            'amplification_error' => 'Cannot amplify to social media without publishing to WordPress first. Please select a WordPress website.'
        ]);
    }

    // 3. Check if post already published to WordPress
    $existingTarget = PostTarget::where('post_id', $post->id)
        ->where('status', 'published')
        ->whereNotNull('wp_url')
        ->first();

    if ($existingTarget) {
        // Already published, can use existing URL
        return $existingTarget;
    }

    // 4. If not published yet, must publish to WordPress first
    return 'publish_wordpress_first';
}
```

### 4.2 UI/UX Flow

**Scenario 1: Normal Flow (WordPress → Social)**
```
User selects:
☑ Publish to WordPress (Website: example.com)
☑ Amplify to Facebook
☑ Amplify to Twitter

→ System publishes to WordPress first
→ Gets WordPress URL
→ Then amplifies to Facebook & Twitter with URL
```

**Scenario 2: Error Flow (Social Only - BLOCKED)**
```
User selects:
☐ Publish to WordPress
☑ Amplify to Facebook

→ System shows error:
   "❌ Cannot amplify to social media without WordPress URL.
    Please select a WordPress website to publish first."
```

**Scenario 3: Re-Amplify (WordPress Already Published)**
```
User selects:
☐ Publish to WordPress (already published before)
☑ Amplify to LinkedIn (new platform)

→ System detects existing WordPress URL
→ Skips WordPress publish
→ Directly amplifies to LinkedIn with existing URL
```

---

## 5. FRONTEND UI - AMPLIFICATION SELECTOR

### 5.1 Post Edit/Create Form

```blade
<!-- resources/views/posts/edit.blade.php -->

<form method="POST" action="{{ route('posts.update', $post) }}">
    @csrf
    @method('PUT')

    <!-- Existing fields: title, content, featured_media_id, etc. -->

    <!-- Publishing Section -->
    <div class="bg-gray-800 rounded-lg p-6 mt-6">
        <h3 class="text-lg font-semibold text-white mb-4">Publishing Options</h3>

        <!-- WordPress Publish -->
        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="publish_to_wordpress" id="publishToWordPress"
                       {{ optional($post->targets->first())->status === 'published' ? 'checked disabled' : '' }}
                       class="mr-2">
                <span class="text-white">Publish to WordPress</span>
                @if(optional($post->targets->first())->status === 'published')
                    <span class="ml-2 text-green-400 text-sm">✓ Already published</span>
                @endif
            </label>

            <select name="website_id" id="websiteSelect" class="w-full mt-2 bg-gray-700 text-white rounded"
                    {{ optional($post->targets->first())->status === 'published' ? 'disabled' : '' }}>
                <option value="">Select WordPress Website</option>
                @foreach($websites as $site)
                    <option value="{{ $site->id }}"
                            {{ optional($post->targets->first())->website_id == $site->id ? 'selected' : '' }}>
                        {{ $site->name }}
                    </option>
                @endforeach
            </select>

            @if(optional($post->targets->first())->wp_url)
                <div class="mt-2">
                    <a href="{{ $post->targets->first()->wp_url }}" target="_blank"
                       class="text-blue-400 text-sm hover:underline">
                        View on WordPress →
                    </a>
                </div>
            @endif
        </div>

        <!-- Social Media Amplification -->
        <div class="border-t border-gray-700 pt-4 mt-4">
            <label class="flex items-center mb-3">
                <input type="checkbox" name="amplify_to_social" id="amplifyToSocial" class="mr-2">
                <span class="text-white font-semibold">Amplify to Social Media</span>
            </label>

            <div id="socialPlatformSelector" class="hidden ml-6 space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" name="amplify_platforms[]" value="facebook" class="mr-2">
                    <span class="text-white">Facebook</span>
                    <span class="ml-2 text-gray-400 text-sm">(Link share with preview)</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="amplify_platforms[]" value="twitter" class="mr-2">
                    <span class="text-white">Twitter / X</span>
                    <span class="ml-2 text-gray-400 text-sm">(Tweet with URL)</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="amplify_platforms[]" value="whatsapp" class="mr-2">
                    <span class="text-white">WhatsApp</span>
                    <span class="ml-2 text-gray-400 text-sm">(Template message to contacts)</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="amplify_platforms[]" value="threads" class="mr-2">
                    <span class="text-white">Threads</span>
                    <span class="ml-2 text-gray-400 text-sm">(Text post with link)</span>
                </label>

                <!-- Warning if WordPress not selected -->
                <div id="wordpressWarning" class="hidden mt-3 p-3 bg-red-900 border border-red-700 rounded">
                    <p class="text-red-200 text-sm">
                        ⚠️ You must publish to WordPress first to get a URL for social media sharing.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
        Save & Publish
    </button>
</form>

<script>
// Show/hide social platform selector
document.getElementById('amplifyToSocial').addEventListener('change', function() {
    const selector = document.getElementById('socialPlatformSelector');
    selector.classList.toggle('hidden', !this.checked);
    validateAmplification();
});

// Show warning if amplify checked but WordPress not selected
function validateAmplification() {
    const amplifyChecked = document.getElementById('amplifyToSocial').checked;
    const wordpressChecked = document.getElementById('publishToWordPress').checked;
    const warning = document.getElementById('wordpressWarning');

    if (amplifyChecked && !wordpressChecked) {
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }
}

document.getElementById('publishToWordPress').addEventListener('change', validateAmplification);
</script>
```

---

## 6. BENEFITS & SEO IMPACT

### 6.1 Traffic Flow Analysis

**WITHOUT Amplification (Old Way):**
```
User creates post → Publish to WordPress only
└─ Traffic: Only organic search visitors (slow growth)
```

**WITH Amplification (New Way):**
```
User creates post → Publish to WordPress
                  → Share to Facebook (with WordPress URL)
                  → Share to Twitter (with WordPress URL)
                  → Share to WhatsApp (with WordPress URL)
                  → Share to Threads (with WordPress URL)

Traffic Sources:
1. Facebook followers click URL → Website visit
2. Twitter followers click URL → Website visit
3. WhatsApp contacts click URL → Website visit
4. Threads followers click URL → Website visit
5. Organic search (WordPress SEO) → Website visit

Result: 5x traffic channels vs 1 channel!
```

### 6.2 SEO Benefits

1. **Social Signals:**
   - Facebook shares, Twitter retweets → positive ranking signals
   - Google considers social engagement in ranking algorithm

2. **Backlinks (Indirect):**
   - Social media posts → users share on blogs → natural backlinks
   - WhatsApp → users forward → viral potential

3. **Faster Indexing:**
   - Social media posts → Google crawls faster
   - WordPress sitemap + social signals → priority indexing

4. **Brand Awareness:**
   - Multi-platform presence → brand recognition
   - Consistent messaging across platforms

### 6.3 Analytics Tracking

**UTM Parameters for Social URLs:**
```php
// app/Services/Amplification/UrlBuilder.php

class UrlBuilder
{
    public static function buildTrackableUrl(string $wpUrl, string $platform): string
    {
        $params = [
            'utm_source' => $platform,
            'utm_medium' => 'social',
            'utm_campaign' => 'auto_amplification',
            'utm_content' => date('Y-m-d'),
        ];

        $query = http_build_query($params);
        $separator = strpos($wpUrl, '?') !== false ? '&' : '?';

        return "{$wpUrl}{$separator}{$query}";
    }
}

// Usage:
$facebookUrl = UrlBuilder::buildTrackableUrl($wpUrl, 'facebook');
// Result: https://example.com/article?utm_source=facebook&utm_medium=social&utm_campaign=auto_amplification
```

**Google Analytics Dashboard:**
- See traffic source breakdown (Facebook vs Twitter vs WhatsApp)
- Track conversion rates per platform
- Identify best-performing social channel

---

## 7. IMPLEMENTATION CHECKLIST

### Phase 1: Foundation (Week 1)
- [ ] Add `wp_url` column to `ane_post_targets` table
- [ ] Create `ane_social_amplifications` table
- [ ] Update PostTarget model with new field
- [ ] Create SocialAmplification model
- [ ] Update WordPressPublishService to save `wp_url`

### Phase 2: Validation Logic (Week 1)
- [ ] Add pre-publish validation in PostController
- [ ] Implement `validateAmplification()` method
- [ ] Add error handling for missing WordPress URL
- [ ] Unit tests for validation logic

### Phase 3: Platform Amplifiers (Week 2-3)
- [ ] Create `FacebookAmplifier` service
- [ ] Create `TwitterAmplifier` service
- [ ] Create `WhatsAppAmplifier` service
- [ ] Create `ThreadsAmplifier` service
- [ ] Implement UTM tracking for all platforms

### Phase 4: Frontend UI (Week 3)
- [ ] Add amplification selector to post edit form
- [ ] Add JavaScript validation
- [ ] Add "Already published" indicator
- [ ] Add success/error notifications

### Phase 5: Background Jobs (Week 4)
- [ ] Create `AmplifyToSocialMediaJob` queue job
- [ ] Implement retry mechanism for failed amplifications
- [ ] Add cron job for scheduled amplifications
- [ ] Logging and error tracking

### Phase 6: Testing (Week 4)
- [ ] Test WordPress → Facebook amplification
- [ ] Test WordPress → Twitter amplification
- [ ] Test WordPress → WhatsApp amplification
- [ ] Test WordPress → Threads amplification
- [ ] Test error scenarios (no WordPress URL, API failures)
- [ ] Integration tests

---

## 8. KNOWN LIMITATIONS & WORKAROUNDS

### 8.1 WhatsApp Template Approval

**Problem:** Template messages require Meta approval (1-2 days)

**Workaround:**
1. Use pre-approved generic templates initially
2. Submit custom template for long-term use
3. Alternative: Manual sharing via WhatsApp Business App

### 8.2 Twitter API Cost

**Problem:** $200/month minimum for write access

**Workaround:**
1. Start with manual posting (defer Twitter integration)
2. Use Zapier/Make.com integration (user brings own API key)
3. Implement browser extension for one-click tweet

### 8.3 Threads API Beta Access

**Problem:** Threads API currently in limited beta

**Workaround:**
1. Apply for beta access early
2. Use Instagram Business Account workaround
3. Manual posting until API access granted

### 8.4 Open Graph Tags Missing

**Problem:** Old WordPress sites may not have OG tags

**Solution:**
1. Recommend Yoast SEO plugin installation
2. Provide custom PHP snippet for theme
3. Validate OG tags before Facebook amplification

---

## 9. SUCCESS METRICS

### KPIs to Track

| Metric | Target | Measurement |
|--------|--------|-------------|
| WordPress Publish Success Rate | >95% | Published / Total Attempts |
| Social Amplification Success Rate | >90% | Amplified / Total Requests |
| Traffic from Social Media | +50% | Google Analytics (UTM tracking) |
| Avg Time to Amplify | <30 seconds | PostTarget.published_at → SocialAmplification.published_at |
| User Adoption Rate | >70% | Users using amplification / Total users |

### Dashboard Requirements

```
┌────────────────────────────────────────────────────┐
│  Amplification Dashboard                           │
├────────────────────────────────────────────────────┤
│  Last 30 Days:                                     │
│  WordPress Posts:  45 ✅                           │
│  Facebook Shares:  38 (84% success)                │
│  Twitter Shares:   42 (93% success)                │
│  WhatsApp Shares:  12 (27% success - low adoption) │
│  Threads Shares:   5 (11% success - beta API)      │
│                                                    │
│  Top Traffic Source: Twitter (1,245 visits)        │
│  Avg Click-Through Rate: 12.3%                     │
└────────────────────────────────────────────────────┘
```

---

## 10. CONCLUSION

**Key Takeaways:**

1. ✅ **WordPress FIRST** adalah mandatory - semua amplification depends on WordPress URL
2. ✅ **SEO Benefit** significant - multi-channel traffic → faster indexing + social signals
3. ✅ **User Experience** improved - one-click publish ke multiple platforms
4. ✅ **Analytics Tracking** via UTM parameters untuk measure ROI per platform

**Next Steps:**

1. Implement Phase 1 (database schema + validation) - **Priority HIGH**
2. Start with Facebook + Twitter (free APIs) - **Quick Win**
3. Defer WhatsApp + Threads until user demand proven - **Phase 2**
4. Monitor success metrics and iterate - **Continuous Improvement**

**Timeline Estimate:**
- Phase 1-2: 1 week (foundation + validation)
- Phase 3: 2 weeks (Facebook + Twitter amplifiers)
- Phase 4: 1 week (UI implementation)
- **Total:** 4 weeks to production-ready Facebook + Twitter amplification

---

**Dokumentasi dibuat:** 5 Januari 2026
**Status:** 📋 Planning Complete - Ready for Implementation
**Next Review:** After Phase 1 completion
