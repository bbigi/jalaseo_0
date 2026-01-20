<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\WordPressPublishService;
use App\Models\Website;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WordPressPublishServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WordPressPublishService $service;
    protected User $user;
    protected Website $website;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WordPressPublishService();

        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        // Create test website
        $this->website = Website::factory()->create([
            'name' => 'Test WordPress Site',
            'url' => 'https://example.com',
            'username' => 'admin',
            'app_password' => 'test_password_123',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    /**
     * Test checkDuplicateByTitle returns null when no duplicate exists
     */
    public function test_check_duplicate_returns_null_when_no_duplicate(): void
    {
        // This will fail if WordPress API is not accessible
        // For unit testing, we would need to mock the cURL calls
        $this->markTestSkipped('Requires mocking WordPress API');
    }

    /**
     * Test checkDuplicateByTitle returns array when duplicate exists
     */
    public function test_check_duplicate_returns_data_when_duplicate_exists(): void
    {
        $this->markTestSkipped('Requires mocking WordPress API');
    }

    /**
     * Test checkDuplicateByTitle handles API timeout
     */
    public function test_check_duplicate_handles_timeout(): void
    {
        $this->markTestSkipped('Requires mocking WordPress API');
    }

    /**
     * Test checkDuplicateByTitle handles API error
     */
    public function test_check_duplicate_handles_api_error(): void
    {
        $this->markTestSkipped('Requires mocking WordPress API');
    }

    /**
     * Test exact title matching (case-sensitive)
     */
    public function test_check_duplicate_exact_title_match(): void
    {
        $this->markTestSkipped('Requires mocking WordPress API');
    }

    /**
     * Test getWpStatus mapping
     */
    public function test_get_wp_status_mapping(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getWpStatus');
        $method->setAccessible(true);

        $this->assertEquals('publish', $method->invoke($this->service, 'publish_now'));
        $this->assertEquals('draft', $method->invoke($this->service, 'send_draft'));
        $this->assertEquals('future', $method->invoke($this->service, 'schedule'));
        $this->assertEquals('draft', $method->invoke($this->service, 'unknown_action'));
    }
}
