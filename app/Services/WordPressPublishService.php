<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostTarget;
use App\Models\Website;

class WordPressPublishService
{
    public function publish(Post $post, PostTarget $target, string $action)
    {
        $website = $target->website;

        // Convert tag names to tag IDs
        $tags = $target->tags ?? [];
        if (empty($tags) && !empty($post->ai_generated_tags)) {
            $tags = $post->ai_generated_tags;
        }

        // If tags are strings (names), convert to IDs by creating tags in WordPress
        $tagIds = [];
        if (!empty($tags) && is_string($tags[0] ?? null)) {
            foreach ($tags as $tagName) {
                $tagId = $this->getOrCreateTag($tagName, $website);
                if ($tagId) {
                    $tagIds[] = $tagId;
                }
            }
            $tags = $tagIds;
        }

        $wpData = [
            'title' => $post->title,
            'content' => $post->content,
            'excerpt' => $post->seo_meta_description ?? '',
            'status' => $this->getWpStatus($action),
            'categories' => $target->categories ?? [],
            'tags' => $tags, // Must be integer IDs
            'meta' => [
                '_yoast_wpseo_metadesc' => $post->seo_meta_description ?? '',
                '_yoast_wpseo_focuskw' => $post->seo_focus_keyword ?? '',
                'rank_math_description' => $post->seo_meta_description ?? '',
                'rank_math_focus_keyword' => $post->seo_focus_keyword ?? '',
            ],
        ];

        // Add scheduled date for future posts (use date_gmt for timezone-safe scheduling)
        // WordPress expects ISO 8601 format: YYYY-MM-DDTHH:MM:SS
        // $target->scheduled_at is already in UTC from database
        if ($action === 'schedule' && $target->scheduled_at) {
            $wpData['date_gmt'] = $target->scheduled_at->format('Y-m-d\TH:i:s');
        }

        if ($post->featuredMedia) {
            $mediaId = $this->uploadFeaturedImage($post->featuredMedia, $website);
            if ($mediaId) {
                $wpData['featured_media'] = $mediaId;
            }
        }

        $url = rtrim($website->url, '/') . '/wp-json/wp/v2/posts';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($wpData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            throw new \Exception('WordPress API error: ' . $response);
        }

        $result = json_decode($response, true);

        // Update SEO meta fields (Yoast & Rank Math) via separate API call
        if (!empty($post->seo_meta_description) || !empty($post->seo_focus_keyword)) {
            $this->updateSeoMeta($result['id'], $post, $website);
        }

        // Determine local status based on action
        $localStatus = match($action) {
            'publish_now' => 'published',
            'schedule' => 'scheduled',
            default => 'draft'
        };

        $target->update([
            'wp_post_id' => $result['id'],
            'status' => $localStatus,
            'published_at' => $action === 'publish_now' ? now() : null,
        ]);

        return $result;
    }

    private function getWpStatus(string $action): string
    {
        return match($action) {
            'publish_now' => 'publish',
            'send_draft' => 'draft',
            'schedule' => 'future',
            default => 'draft',
        };
    }

    public function checkPostStatus(PostTarget $target)
    {
        if (!$target->wp_post_id) {
            return null;
        }

        $website = $target->website;
        $url = rtrim($website->url, '/') . '/wp-json/wp/v2/posts/' . $target->wp_post_id;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        $result = json_decode($response, true);
        return $result['status'] ?? null;
    }

    /**
     * Check if post with same title already exists on WordPress
     * Returns duplicate post data if found, null otherwise
     */
    public function checkDuplicateByTitle(string $title, Website $website): ?array
    {
        $url = rtrim($website->url, '/') . '/wp-json/wp/v2/posts?search=' . urlencode($title) . '&per_page=20';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            \Log::warning('WordPress duplicate check failed', [
                'website' => $website->url,
                'http_code' => $httpCode,
                'title' => $title,
            ]);
            return null;
        }

        $results = json_decode($response, true);

        if (!is_array($results)) {
            return null;
        }

        // Check for exact title match (WordPress search is fuzzy, so we need exact match)
        foreach ($results as $post) {
            $wpTitle = $post['title']['rendered'] ?? '';

            if ($wpTitle === $title) {
                \Log::info('WordPress duplicate detected', [
                    'title' => $title,
                    'wp_post_id' => $post['id'],
                    'website' => $website->url,
                    'status' => $post['status'],
                ]);

                return [
                    'wp_post_id' => $post['id'],
                    'status' => $post['status'],
                    'link' => $post['link'],
                    'date' => $post['date'],
                ];
            }
        }

        return null; // No duplicate found
    }

    private function uploadFeaturedImage($media, Website $website)
    {
        // Check if Unsplash (URL-based) or external URL
        if ($media->source_type === 'unsplash' || str_starts_with($media->file_path, 'http')) {
            return $this->uploadFromUrl($media, $website);
        }

        // Original code for local files
        $filePath = storage_path('app/public/' . $media->file_path);

        if (!file_exists($filePath)) {
            return null;
        }

        $url = rtrim($website->url, '/') . '/wp-json/wp/v2/media';

        // Prepare metadata for WordPress
        $metadata = [
            'title' => $media->title ?? pathinfo($media->file_name, PATHINFO_FILENAME),
            'alt_text' => $media->alt_text ?? '',
            'caption' => $media->caption ?? '',
            'description' => $media->description ?? '',
        ];

        // Build URL with query parameters for metadata
        $urlWithParams = $url . '?' . http_build_query($metadata);

        $ch = curl_init($urlWithParams);
        $fileData = file_get_contents($filePath);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: ' . mime_content_type($filePath),
            'Content-Disposition: attachment; filename="' . $media->file_name . '"',
            'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            return null;
        }

        $result = json_decode($response, true);
        return $result['id'] ?? null;
    }

    /**
     * Upload featured image from external URL (Unsplash, AI-generated, etc.)
     * Downloads temporarily then uploads to WordPress
     */
    private function uploadFromUrl($media, Website $website)
    {
        $tempFile = null;

        try {
            // 1. Download image to temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'anewp_');

            // Use cURL for better error handling and timeout control
            $ch = curl_init($media->file_path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For Unsplash HTTPS

            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$imageData) {
                \Log::error('Failed to download image from URL', [
                    'url' => $media->file_path,
                    'http_code' => $httpCode
                ]);
                return null;
            }

            file_put_contents($tempFile, $imageData);

            // 2. Upload to WordPress REST API with metadata
            $url = rtrim($website->url, '/') . '/wp-json/wp/v2/media';

            // Prepare metadata for WordPress
            $metadata = [
                'title' => $media->title ?? 'Unsplash Photo',
                'alt_text' => $media->alt_text ?? '',
                'caption' => $media->caption ?? '',
                'description' => $media->description ?? '',
            ];

            // Build URL with query parameters for metadata
            $urlWithParams = $url . '?' . http_build_query($metadata);

            $ch = curl_init($urlWithParams);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($tempFile));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: ' . ($media->mime_type ?? 'image/jpeg'),
                'Content-Disposition: attachment; filename="' . $media->file_name . '"',
                'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 201) {
                \Log::error('Failed to upload image to WordPress', [
                    'website' => $website->url,
                    'http_code' => $httpCode,
                    'response' => $response
                ]);
                return null;
            }

            $result = json_decode($response, true);

            \Log::info('Successfully uploaded Unsplash image to WordPress', [
                'media_id' => $media->id,
                'wp_media_id' => $result['id'] ?? null,
                'source_type' => $media->source_type
            ]);

            return $result['id'] ?? null;

        } catch (\Exception $e) {
            \Log::error('Exception during image upload from URL', [
                'error' => $e->getMessage(),
                'media_id' => $media->id
            ]);
            return null;

        } finally {
            // 3. Always clean up temporary file
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Get or create WordPress tag by name
     * Returns WordPress tag ID
     */
    private function getOrCreateTag(string $tagName, Website $website): ?int
    {
        $url = rtrim($website->url, '/') . '/wp-json/wp/v2/tags?search=' . urlencode($tagName);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $tags = json_decode($response, true);
            // Check for exact name match
            foreach ($tags as $tag) {
                if (strtolower($tag['name']) === strtolower($tagName)) {
                    return $tag['id'];
                }
            }
        }

        // Tag not found, create new
        $createUrl = rtrim($website->url, '/') . '/wp-json/wp/v2/tags';

        $ch = curl_init($createUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['name' => $tagName]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            $newTag = json_decode($response, true);
            return $newTag['id'];
        }

        return null;
    }

    /**
     * Update SEO meta fields (Yoast & Rank Math)
     * WordPress REST API doesn't allow meta in post creation, must update separately
     */
    private function updateSeoMeta(int $wpPostId, Post $post, Website $website): void
    {
        $metaData = [];

        if (!empty($post->seo_meta_description)) {
            $metaData['_yoast_wpseo_metadesc'] = $post->seo_meta_description;
            $metaData['rank_math_description'] = $post->seo_meta_description;
        }

        if (!empty($post->seo_focus_keyword)) {
            $metaData['_yoast_wpseo_focuskw'] = $post->seo_focus_keyword;
            $metaData['rank_math_focus_keyword'] = $post->seo_focus_keyword;
        }

        if (empty($metaData)) {
            return;
        }

        // WordPress REST API endpoint for updating post meta
        $url = rtrim($website->url, '/') . '/wp-json/wp/v2/posts/' . $wpPostId;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['meta' => $metaData]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($website->username . ':' . $website->app_password)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log if meta update failed (but don't throw exception, post is already created)
        if ($httpCode !== 200) {
            \Log::warning('WordPress SEO meta update failed', [
                'wp_post_id' => $wpPostId,
                'http_code' => $httpCode,
                'response' => $response,
            ]);
        }
    }
}
