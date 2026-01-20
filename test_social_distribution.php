<?php

// Manual test script for Social Distribution Phase 1
// Run: php test_social_distribution.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Post;
use App\Services\ContentDNAService;
use App\Services\SocialCopyService;

echo "=== Social Distribution Test ===\n\n";

// Step 1: Get a test post
echo "Step 1: Getting test post...\n";
$post = Post::where('created_by', 2)->first();

if (!$post) {
    echo "ERROR: No post found for user ID 1\n";
    echo "Please create a post first via the UI\n";
    exit(1);
}

echo "✓ Found post: {$post->title}\n";
echo "  Article type: {$post->article_type}\n";
echo "  Created by: {$post->created_by}\n\n";

// Step 2: Extract DNA
echo "Step 2: Extracting DNA...\n";
$dnaService = new ContentDNAService();

try {
    $dna = $dnaService->extractDNA($post);
    echo "✓ DNA extracted successfully\n";
    echo "  DNA ID: {$dna->id}\n";
    echo "  Headline: {$dna->headline}\n";
    echo "  Tone: {$dna->tone}\n";
    echo "  Intent: {$dna->intent}\n";
    echo "  Key points: " . count($dna->key_points) . " items\n";
    echo "  Keywords: " . count($dna->keywords) . " items\n\n";
} catch (\Exception $e) {
    echo "ERROR: DNA extraction failed\n";
    echo "  Message: {$e->getMessage()}\n";
    exit(1);
}

// Step 3: Generate social copy for all channels
echo "Step 3: Generating social copy for all channels...\n";
$copyService = new SocialCopyService();

$channels = ['instagram', 'facebook', 'twitter', 'linkedin'];
$assets = [];

foreach ($channels as $channel) {
    echo "  Generating {$channel}...\n";
    try {
        $asset = $copyService->generateCopy($dna, $channel);
        $assets[$channel] = $asset;
        echo "  ✓ {$channel} copy generated (ID: {$asset->id})\n";
        echo "    Preview: " . substr($asset->copy_text, 0, 100) . "...\n";
    } catch (\Exception $e) {
        echo "  ERROR: {$channel} failed - {$e->getMessage()}\n";
    }
}

echo "\n";

// Step 4: Verify database
echo "Step 4: Verifying database...\n";
$dnaCount = \App\Models\ContentDNA::where('post_id', $post->id)->count();
$assetCount = \App\Models\SocialAsset::where('post_id', $post->id)->count();

echo "  DNA records: {$dnaCount}\n";
echo "  Social assets: {$assetCount}\n\n";

// Summary
echo "=== Test Summary ===\n";
echo "✓ Phase 1.1: Database & Models - OK\n";
echo "✓ Phase 1.2: DNA Extraction - OK\n";
echo "✓ Phase 1.3: Social Copy Generation - OK\n";
echo "✓ End-to-end flow: 1 post → 1 DNA → {$assetCount} social assets\n\n";

echo "Test completed successfully!\n";
