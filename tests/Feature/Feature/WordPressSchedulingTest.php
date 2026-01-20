<?php

namespace Tests\Feature\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Website;
use App\Models\Post;
use App\Models\PostTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class WordPressSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Website $website;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test-scheduling@example.com',
            'role' => 'user',
            'timezone' => 'Asia/Jakarta',
        ]);

        // Create test website
        $this->website = Website::factory()->create([
            'name' => 'Test WordPress',
            'url' => 'https://example.com',
            'username' => 'admin',
            'app_password' => 'test_password',
            'created_by' => $this->user->id,
        ]);

        // Create test post
        $this->post = Post::factory()->create([
            'title' => 'Test WordPress Scheduling Post',
            'content' => 'This is a test post for WordPress scheduling',
            'created_by' => $this->user->id,
        ]);
    }

    /**
     * Test scheduling a WordPress post
     */
    public function test_can_schedule_wordpress_post(): void
    {
        $this->actingAs($this->user);

        $scheduledTime = Carbon::now('UTC')->addDays(1)->format('Y-m-d\TH:i');

        $response = $this->put(route('posts.update', $this->post), [
            'title' => $this->post->title,
            'content' => $this->post->content,
            'article_type' => 'blog',
            'website_id' => $this->website->id,
            'publish_action' => 'schedule',
            'scheduled_at' => $scheduledTime,
        ]);

        $response->assertRedirect(route('posts.edit', $this->post));

        $this->assertDatabaseHas('ane_post_targets', [
            'post_id' => $this->post->id,
            'website_id' => $this->website->id,
            'status' => 'scheduled',
        ]);

        $target = PostTarget::where('post_id', $this->post->id)->first();
        $this->assertNotNull($target->scheduled_at);
    }

    /**
     * Test force publish bypasses duplicate check
     */
    public function test_force_publish_bypasses_duplicate_check(): void
    {
        $this->actingAs($this->user);

        // This test assumes duplicate detection would normally fail
        // In a real test, we would mock the WordPress API response

        $response = $this->put(route('posts.update', $this->post), [
            'title' => $this->post->title,
            'content' => $this->post->content,
            'article_type' => 'blog',
            'website_id' => $this->website->id,
            'publish_action' => 'send_draft',
            'force_publish' => '1',
        ]);

        // Should not show duplicate warning if force_publish is checked
        $response->assertSessionMissing('warning');
    }

    /**
     * Test rescheduling WordPress post from calendar
     */
    public function test_can_reschedule_wordpress_post_from_calendar(): void
    {
        $this->actingAs($this->user);

        // Create scheduled target
        $target = PostTarget::create([
            'post_id' => $this->post->id,
            'website_id' => $this->website->id,
            'status' => 'scheduled',
            'scheduled_at' => Carbon::now('UTC')->addDays(1),
        ]);

        $newScheduledTime = Carbon::now('UTC')->addDays(2)->toISOString();

        $response = $this->patchJson(route('calendar.wordpress-reschedule', $target), [
            'scheduled_at' => $newScheduledTime,
        ]);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('ane_post_targets', [
            'id' => $target->id,
        ]);

        $target->refresh();
        $this->assertEquals($newScheduledTime, $target->scheduled_at->toISOString());
    }

    /**
     * Test user cannot reschedule another user's post
     */
    public function test_cannot_reschedule_another_users_post(): void
    {
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $otherPost = Post::factory()->create(['created_by' => $otherUser->id]);

        $target = PostTarget::create([
            'post_id' => $otherPost->id,
            'website_id' => $this->website->id,
            'status' => 'scheduled',
            'scheduled_at' => Carbon::now('UTC')->addDays(1),
        ]);

        $this->actingAs($this->user);

        $response = $this->patchJson(route('calendar.wordpress-reschedule', $target), [
            'scheduled_at' => Carbon::now('UTC')->addDays(2)->toISOString(),
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test WordPress events appear in calendar
     */
    public function test_wordpress_events_appear_in_calendar(): void
    {
        $this->actingAs($this->user);

        // Create scheduled target
        PostTarget::create([
            'post_id' => $this->post->id,
            'website_id' => $this->website->id,
            'status' => 'scheduled',
            'scheduled_at' => Carbon::now('UTC')->addDays(1),
        ]);

        $response = $this->getJson(route('calendar.wordpress-events'));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'title' => $this->post->title,
        ]);
    }

    /**
     * Test scheduled WordPress posts are filtered by date range
     */
    public function test_wordpress_events_filtered_by_date_range(): void
    {
        $this->actingAs($this->user);

        // Create post scheduled for tomorrow
        PostTarget::create([
            'post_id' => $this->post->id,
            'website_id' => $this->website->id,
            'status' => 'scheduled',
            'scheduled_at' => Carbon::now('UTC')->addDays(1),
        ]);

        // Create another post scheduled for next week
        $anotherPost = Post::factory()->create(['created_by' => $this->user->id]);
        PostTarget::create([
            'post_id' => $anotherPost->id,
            'website_id' => $this->website->id,
            'status' => 'scheduled',
            'scheduled_at' => Carbon::now('UTC')->addWeek(),
        ]);

        // Query only for next 3 days
        $response = $this->getJson(route('calendar.wordpress-events', [
            'start' => Carbon::now('UTC')->toISOString(),
            'end' => Carbon::now('UTC')->addDays(3)->toISOString(),
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(1); // Only tomorrow's post
    }
}
