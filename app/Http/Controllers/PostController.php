<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Insight;
use App\Models\Website;
use App\Services\WordPressPublishService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $posts = Post::where('created_by', auth()->id())
            ->with(['insight', 'targets'])
            ->latest()
            ->paginate(20);

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        $insights = Insight::where('created_by', auth()->id())->get();
        $websites = \App\Models\Website::where('created_by', auth()->id())->get();

        return view('posts.create', compact('insights', 'websites'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'insight_id' => 'nullable|exists:ane_insights,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'article_type' => 'required|in:blog,news',
            'seo_focus_keyword' => 'nullable|string|max:150',
            'seo_meta_description' => 'nullable|string|max:255',
            'featured_media_id' => 'nullable|exists:ane_media,id',
            'website_id' => 'nullable|exists:ane_websites,id',
            'wp_category_id' => 'nullable|integer',
            'publish_action' => 'nullable|in:save_only,send_draft,publish_now,schedule',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $post = Post::create([
            'insight_id' => $validated['insight_id'] ?? null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'article_type' => $validated['article_type'],
            'seo_focus_keyword' => $validated['seo_focus_keyword'] ?? null,
            'seo_meta_description' => $validated['seo_meta_description'] ?? null,
            'featured_media_id' => $validated['featured_media_id'] ?? null,
            'source_type' => 'manual',
            'created_by' => auth()->id(),
        ]);

        if ($request->insight_id) {
            $insight = Insight::find($request->insight_id);
            $insight->posts()->attach($post->id, ['created_by' => auth()->id()]);
            $insight->updateArticleCount();
        }

        if ($request->website_id) {
            $action = $request->publish_action ?? 'save_only';

            // Convert comma-separated tag names to tag IDs
            $tagNames = [];
            if ($request->filled('wp_tags')) {
                $tagNames = array_map('trim', explode(',', $request->wp_tags));
                $tagNames = array_filter($tagNames);
            } elseif (!empty($post->ai_generated_tags)) {
                // Fallback to AI generated tags if wp_tags is empty
                $tagNames = $post->ai_generated_tags;
            }

            $tags = null;
            if (!empty($tagNames)) {
                // Try to find existing tag IDs in local database
                $tagIds = \App\Models\Tag::where('website_id', $request->website_id)
                    ->whereIn('name', $tagNames)
                    ->pluck('wp_tag_id')
                    ->toArray();

                // If found IDs, use them. Otherwise, use tag names (WordPress will auto-create)
                $tags = !empty($tagIds) ? $tagIds : $tagNames;
            }

            $target = $post->targets()->create([
                'website_id' => $request->website_id,
                'categories' => $request->wp_category_id ? [$request->wp_category_id] : null,
                'tags' => $tags, // Array of WordPress tag IDs or names
                'status' => $action === 'schedule' ? 'scheduled' : 'draft',
                'scheduled_at' => $request->scheduled_at
                    ? \Carbon\Carbon::parse($request->scheduled_at, auth()->user()->timezone ?? 'UTC')
                        ->setTimezone('UTC')
                    : null,
            ]);

            // Publish to WordPress for all actions (send_draft, publish_now, schedule)
            if (in_array($action, ['send_draft', 'publish_now', 'schedule'])) {
                try {
                    $wpService = new WordPressPublishService();
                    $website = Website::findOrFail($request->website_id);

                    // Check for duplicate before publishing (unless force_publish is checked)
                    if (!$request->has('force_publish')) {
                        $duplicate = $wpService->checkDuplicateByTitle($post->title, $website);

                        if ($duplicate) {
                            // Show warning with link to existing post
                            $warningMessage = sprintf(
                                'A post with the title "%s" already exists on WordPress (ID: %d, Status: %s). <a href="%s" target="_blank" class="underline font-semibold hover:text-yellow-300">View Post →</a>',
                                $post->title,
                                $duplicate['wp_post_id'],
                                ucfirst($duplicate['status']),
                                $duplicate['link']
                            );

                            return redirect()->route('posts.edit', $post)->with('warning', $warningMessage);
                        }
                    }

                    // Proceed with publish to WordPress
                    // For 'schedule' action, WordPress will receive status='future' and handle auto-publish
                    $wpService->publish($post, $target, $action);
                    $target->refresh();

                    $messages = [
                        'publish_now' => 'Post published to WordPress!',
                        'send_draft' => 'Post sent as draft to WordPress!',
                        'schedule' => 'Post scheduled in WordPress for ' .
                            ($target->scheduled_at
                                ? $target->scheduled_at->setTimezone(auth()->user()->timezone ?? 'UTC')->format('M d, Y H:i T')
                                : 'scheduled time') .
                            '. WordPress will auto-publish at scheduled time.',
                    ];

                    return redirect()->route('posts.index')->with('success', $messages[$action]);
                } catch (\Exception $e) {
                    return redirect()->route('posts.edit', $post)->with('error', 'Failed to publish: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    public function show(Post $post)
    {
        $this->authorize('view', $post);

        $post->load('insight', 'media', 'targets.website');

        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        // Check if AJAX request for bulk editor
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'content' => $post->content
            ]);
        }

        $post->load('featuredMedia', 'targets', 'insight');
        $insights = Insight::where('created_by', auth()->id())->get();
        $websites = Website::where('created_by', auth()->id())->get();
        $templates = \App\Models\AiGenerateTemplate::where('created_by', auth()->id())
            ->where('is_active', 1)
            ->get();

        return view('posts.edit', compact('post', 'insights', 'websites', 'templates'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'insight_id' => 'nullable|exists:ane_insights,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'article_type' => 'required|in:blog,news',
            'seo_focus_keyword' => 'nullable|string|max:150',
            'seo_meta_description' => 'nullable|string|max:255',
            'featured_media_id' => 'nullable|exists:ane_media,id',
            'website_id' => 'nullable|exists:ane_websites,id',
            'wp_category_id' => 'nullable|integer',
            'publish_action' => 'nullable|in:save_only,send_draft,publish_now,schedule',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $post->update([
            'insight_id' => $validated['insight_id'] ?? null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'article_type' => $validated['article_type'],
            'seo_focus_keyword' => $validated['seo_focus_keyword'] ?? null,
            'seo_meta_description' => $validated['seo_meta_description'] ?? null,
            'featured_media_id' => $validated['featured_media_id'] ?? null,
        ]);

        // Update targets
        if ($request->website_id) {
            $action = $request->publish_action ?? 'save_only';

            $post->targets()->delete();
            // Convert comma-separated tag names to tag IDs
            $tagNames = [];
            if ($request->filled('wp_tags')) {
                $tagNames = array_map('trim', explode(',', $request->wp_tags));
                $tagNames = array_filter($tagNames);
            } elseif (!empty($post->ai_generated_tags)) {
                // Fallback to AI generated tags if wp_tags is empty
                $tagNames = $post->ai_generated_tags;
            }

            $tags = null;
            if (!empty($tagNames)) {
                // Try to find existing tag IDs in local database
                $tagIds = \App\Models\Tag::where('website_id', $request->website_id)
                    ->whereIn('name', $tagNames)
                    ->pluck('wp_tag_id')
                    ->toArray();

                // If found IDs, use them. Otherwise, use tag names (WordPress will auto-create)
                $tags = !empty($tagIds) ? $tagIds : $tagNames;
            }

            $target = $post->targets()->create([
                'website_id' => $request->website_id,
                'categories' => $request->wp_category_id ? [$request->wp_category_id] : null,
                'tags' => $tags, // Array of WordPress tag IDs or names
                'status' => $action === 'schedule' ? 'scheduled' : 'draft',
                'scheduled_at' => $request->scheduled_at
                    ? \Carbon\Carbon::parse($request->scheduled_at, auth()->user()->timezone ?? 'UTC')
                        ->setTimezone('UTC')
                    : null,
            ]);

            // Publish to WordPress for all actions (send_draft, publish_now, schedule)
            if (in_array($action, ['send_draft', 'publish_now', 'schedule'])) {
                try {
                    $wpService = new WordPressPublishService();
                    $website = Website::findOrFail($request->website_id);

                    // Check for duplicate before publishing (unless force_publish is checked)
                    if (!$request->has('force_publish')) {
                        $duplicate = $wpService->checkDuplicateByTitle($post->title, $website);

                        if ($duplicate) {
                            // Show warning with link to existing post
                            $warningMessage = sprintf(
                                'A post with the title "%s" already exists on WordPress (ID: %d, Status: %s). <a href="%s" target="_blank" class="underline font-semibold hover:text-yellow-300">View Post →</a>',
                                $post->title,
                                $duplicate['wp_post_id'],
                                ucfirst($duplicate['status']),
                                $duplicate['link']
                            );

                            return redirect()->route('posts.edit', $post)->with('warning', $warningMessage);
                        }
                    }

                    // Proceed with publish to WordPress
                    // For 'schedule' action, WordPress will receive status='future' and handle auto-publish
                    $wpService->publish($post, $target, $action);
                    $target->refresh();

                    $messages = [
                        'publish_now' => 'Post published to WordPress!',
                        'send_draft' => 'Post sent as draft to WordPress!',
                        'schedule' => 'Post scheduled in WordPress for ' .
                            ($target->scheduled_at
                                ? $target->scheduled_at->setTimezone(auth()->user()->timezone ?? 'UTC')->format('M d, Y H:i T')
                                : 'scheduled time') .
                            '. WordPress will auto-publish at scheduled time.',
                    ];

                    return redirect()->route('posts.index')->with('success', $messages[$action]);
                } catch (\Exception $e) {
                    return redirect()->route('posts.edit', $post)->with('error', 'Failed to publish: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }

    /**
     * Bulk Review - Edit multiple posts at once
     * GET /posts/bulk-review?ids=1,2,3
     */
    public function bulkReview(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids) {
            return redirect()->route('posts.index')->with('error', 'No posts selected for bulk review.');
        }

        // Convert comma-separated string to array
        $postIds = explode(',', $ids);

        // Load posts with relationships
        $posts = Post::whereIn('id', $postIds)
            ->where('created_by', auth()->id()) // Security: only user's posts
            ->with(['featuredMedia', 'targets.website', 'insight'])
            ->get();

        if ($posts->isEmpty()) {
            return redirect()->route('posts.index')->with('error', 'No posts found.');
        }

        // Load user's websites for target selection
        $websites = Website::where('created_by', auth()->id())->get();

        // Get available image AI providers for media modal
        $providers = \App\Models\ApiCredential::where('category', 'image_generation')
            ->where('is_active', true)
            ->get(['provider', 'display_name'])
            ->map(function ($credential) {
                return [
                    'value' => $credential->provider,
                    'label' => $credential->display_name ?? ucfirst($credential->provider),
                ];
            })
            ->toArray();

        return view('posts.bulk-review', compact('posts', 'websites', 'providers'));
    }

    /**
     * Update single field via AJAX (for inline editing)
     * POST /posts/update-field/{id}
     */
    public function updateField(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Security check
        if ($post->created_by !== auth()->id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'field' => 'required|string|in:title,content,seo_meta_description,seo_focus_keyword,featured_media_id',
            'value' => 'nullable'
        ]);

        $field = $validated['field'];
        $value = $validated['value'];

        // Update the field
        $post->update([
            $field => $value
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Field updated successfully'
        ]);
    }

    /**
     * Save publishing target (website + category)
     * POST /posts/{id}/save-target
     */
    public function saveTarget(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Security check
        if ($post->created_by !== auth()->id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'website_id' => 'nullable|exists:ane_websites,id',
            'category_id' => 'nullable|integer',
        ]);

        // Get or create target
        $target = $post->targets()->first();

        if ($request->has('website_id')) {
            if ($target) {
                $target->update(['website_id' => $validated['website_id']]);
            } else {
                $post->targets()->create([
                    'website_id' => $validated['website_id'],
                    'status' => 'draft',
                ]);
            }
        }

        if ($request->has('category_id')) {
            if ($target) {
                $target->update(['categories' => [$validated['category_id']]]);
            } else {
                // Website must be set first
                return response()->json(['success' => false, 'error' => 'Please select website first'], 400);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Target saved successfully'
        ]);
    }

    /**
     * Delete article via AJAX
     * DELETE /posts/delete-ajax/{id}
     */
    public function deleteAjax($id)
    {
        $post = Post::findOrFail($id);

        // Security check
        if ($post->created_by !== auth()->id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article deleted successfully'
        ]);
    }

    /**
     * Bulk delete posts (JSON)
     * POST /posts/bulk-delete
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:ane_posts,id',
        ]);

        $userId = auth()->id();
        $posts = Post::whereIn('id', $validated['ids'])->get();

        $deleted = 0;
        foreach ($posts as $post) {
            if ($post->created_by === $userId) {
                $post->delete();
                $deleted++;
            }
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }

    /**
     * Remove featured image from post
     * POST /posts/remove-image/{id}
     */
    public function removeImage($id)
    {
        $post = Post::findOrFail($id);

        // Security check
        if ($post->created_by !== auth()->id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $post->update(['featured_media_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Featured image removed successfully'
        ]);
    }

    /**
     * Bulk publish to WordPress
     * POST /posts/bulk-publish
     */
    public function bulkPublish(Request $request)
    {
        $validated = $request->validate([
            'articles' => 'required|array',
            'articles.*.post_id' => 'required|exists:ane_posts,id',
            'articles.*.website_id' => 'required|exists:ane_websites,id',
            'articles.*.wp_category_id' => 'nullable|integer',
            'articles.*.wp_tags' => 'nullable|string',
            'articles.*.status' => 'required|in:draft,publish,scheduled',
            'articles.*.scheduled_at' => 'nullable|date',
        ]);

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($validated['articles'] as $articleData) {
            try {
                $post = Post::findOrFail($articleData['post_id']);

                // Security check
                if ($post->created_by !== auth()->id()) {
                    throw new \Exception('Unauthorized');
                }

                // Handle tags (same logic as store/update)
                $tagNames = [];
                if (!empty($articleData['wp_tags'])) {
                    $tagNames = array_map('trim', explode(',', $articleData['wp_tags']));
                    $tagNames = array_filter($tagNames);
                } elseif (!empty($post->ai_generated_tags)) {
                    $tagNames = $post->ai_generated_tags;
                }

                $tags = null;
                if (!empty($tagNames)) {
                    $tagIds = \App\Models\Tag::where('website_id', $articleData['website_id'])
                        ->whereIn('name', $tagNames)
                        ->pluck('wp_tag_id')
                        ->toArray();
                    $tags = !empty($tagIds) ? $tagIds : $tagNames;
                }

                // Create or update post target
                $target = $post->targets()->updateOrCreate(
                    ['website_id' => $articleData['website_id']],
                    [
                        'status' => $articleData['status'],
                        'categories' => isset($articleData['wp_category_id']) ? [$articleData['wp_category_id']] : null,
                        'tags' => $tags,
                        'scheduled_at' => $articleData['scheduled_at'] ?? null,
                    ]
                );

                // Publish to WordPress
                if (in_array($articleData['status'], ['publish', 'scheduled'])) {
                    $website = Website::findOrFail($articleData['website_id']);
                    $wpService = new WordPressPublishService();

                    $action = $articleData['status'] === 'publish' ? 'publish_now' : 'schedule';
                    $wpService->publish($post, $target, $action);
                    $target->refresh();
                } elseif ($articleData['status'] === 'draft') {
                    $website = Website::findOrFail($articleData['website_id']);
                    $wpService = new WordPressPublishService();

                    $wpService->publish($post, $target, 'send_draft');
                    $target->refresh();
                }

                $successCount++;

            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'post_id' => $articleData['post_id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$successCount} articles processed successfully" . ($failedCount > 0 ? ", {$failedCount} failed" : ""),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'errors' => $errors,
            'redirect_url' => route('posts.index')
        ]);
    }

    /**
     * Delete PostTarget (cancel WordPress schedule)
     * Used by calendar to cancel scheduled posts
     */
    public function destroyTarget(\App\Models\PostTarget $target)
    {
        // Security: Check ownership via post
        if ($target->post->created_by !== auth()->id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        \Illuminate\Support\Facades\Log::info('WordPress schedule cancelled via calendar', [
            'target_id' => $target->id,
            'post_id' => $target->post_id,
            'user_id' => auth()->id(),
        ]);

        $target->delete();

        return response()->json(['success' => true]);
    }
}
