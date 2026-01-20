<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OAuth\MetaOAuthController;
use App\Http\Controllers\Webhook\MetaWebhookController;
use App\Http\Controllers\SocialConnectionController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\OAuthServiceController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\WebsiteAnalyticsController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\AiGenerateTemplateController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteSpeedInsightsController;
use App\Http\Controllers\WebsiteHealthController;
use App\Http\Controllers\UnsplashController;

// ============================================
// Public Routes
// ============================================

// Home
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Privacy Policy (public)
Route::view('/privacy-policy', 'privacy-policy')->name('privacy.policy');

// Meta Webhook (public)
Route::match(['GET', 'POST'], '/webhooks/meta', [MetaWebhookController::class, 'handle']);

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Logout (for authenticated users only)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email-verification-notice', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::post('/email-verification-send', [EmailVerificationController::class, 'resend'])
        ->name('verification.send');

    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
});

// ============================================
// Protected Routes (Authenticated Users Only)
// ============================================

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (user preferences)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Content generation (Template-based AI Article Generator)
    Route::get('/generate', [GenerateController::class, 'index'])->name('generate.index');
    Route::post('/generate/article', [GenerateController::class, 'generateArticle'])->name('generate.article');
    Route::get('/generate/progress/{jobId}', [GenerateController::class, 'getProgress'])->name('generate.progress');
    Route::get('/generate/progress-page', [GenerateController::class, 'progressPage'])->name('generate.progress-page');
    Route::get('/generate/keywords/{websiteId}', [GenerateController::class, 'getKeywordsForWebsite'])->name('generate.keywords');
    Route::get('/generate/autocomplete', [GenerateController::class, 'autocompleteKeywords'])->name('generate.autocomplete');
    Route::get('/generate/references/search', [GenerateController::class, 'searchReferences'])->name('generate.references.search');
    Route::post('/generate/references/select', [GenerateController::class, 'selectReference'])->name('generate.references.select');

    // Image generation
    Route::get('/image-generation', [\App\Http\Controllers\ImageGenerationController::class, 'create'])->name('image-generation.create');
    Route::post('/image-generation', [\App\Http\Controllers\ImageGenerationController::class, 'store'])->name('image-generation.store');
    Route::get('/image-generation/progress', [\App\Http\Controllers\ImageGenerationController::class, 'progress'])->name('image-generation.progress');
    Route::post('/image-generation/run', [\App\Http\Controllers\ImageGenerationController::class, 'generate'])->name('image-generation.run');
    Route::post('/image-generation/generate-prompt', [\App\Http\Controllers\ImageGenerationController::class, 'generatePrompt'])->name('image-generation.generate-prompt');
    Route::get('/image-generation-modal/create', [\App\Http\Controllers\ImageGenerationController::class, 'createModal'])->name('image-generation.create-modal');

    // Websites
    Route::get('/websites', [WebsiteController::class, 'index'])->name('websites.index');
    Route::get('/websites/create', [WebsiteController::class, 'create'])->name('websites.create');
    Route::post('/websites', [WebsiteController::class, 'store'])->name('websites.store');
    Route::get('/websites/fetch-categories/{id}', [WebsiteController::class, 'fetchCategories'])->name('websites.fetch-categories');
    Route::get('/websites/{website}', [WebsiteController::class, 'show'])->name('websites.show');
    Route::get('/websites/{website}/edit', [WebsiteController::class, 'edit'])->name('websites.edit');
    Route::put('/websites/{website}', [WebsiteController::class, 'update'])->name('websites.update');
    Route::delete('/websites/{website}', [WebsiteController::class, 'destroy'])->name('websites.destroy');
    Route::post('/websites/{website}/test-connection', [WebsiteController::class, 'testConnection'])->name('websites.test-connection');
    Route::get('/websites/{website}/tags', [WebsiteController::class, 'getWordPressTags'])->name('websites.tags');

    // Competitor Intelligence
    Route::get('/competitors', [\App\Http\Controllers\CompetitorController::class, 'index'])->name('competitors.index');
    Route::post('/competitors', [\App\Http\Controllers\CompetitorController::class, 'store'])->name('competitors.store');
    Route::get('/competitors/view/{competitor}', [\App\Http\Controllers\CompetitorController::class, 'show'])->name('competitors.show');
    Route::get('/competitors/detail/{competitor}', [\App\Http\Controllers\CompetitorController::class, 'show'])->name('competitors.detail');
    Route::delete('/competitors/delete/{competitor}', [\App\Http\Controllers\CompetitorController::class, 'destroy'])->name('competitors.destroy');
    Route::post('/competitors/analyze/start', [\App\Http\Controllers\CompetitorController::class, 'analyzeStart'])->name('competitors.analyze.start');
    Route::post('/competitors/analyze/poll/{sessionId}', [\App\Http\Controllers\CompetitorController::class, 'analyzePoll'])->name('competitors.analyze.poll');
    Route::post('/competitors/{competitor}/set-primary', [\App\Http\Controllers\CompetitorAdvancedAnalysisController::class, 'setPrimary'])->name('competitors.set-primary');
    Route::post('/competitors/{competitor}/advanced-ranked-keywords/run', [\App\Http\Controllers\CompetitorAdvancedAnalysisController::class, 'run'])->name('competitors.advanced.run');
    Route::get('/competitors/{competitor}/advanced-ranked-keywords/status', [\App\Http\Controllers\CompetitorAdvancedAnalysisController::class, 'status'])->name('competitors.advanced.status');

    // Website Analytics (GSC & GA4)
    Route::prefix('websites/{website}/analytics')->name('website-analytics.')->group(function () {
        Route::get('/', [WebsiteAnalyticsController::class, 'index'])->name('index');

        // GSC OAuth
        Route::get('/gsc/connect', [WebsiteAnalyticsController::class, 'connectGSC'])->name('gsc.connect');
        Route::post('/gsc/sync', [WebsiteAnalyticsController::class, 'syncGSC'])->name('gsc.sync');
        Route::delete('/gsc/disconnect', [WebsiteAnalyticsController::class, 'disconnectGSC'])->name('gsc.disconnect');

        // GA4 OAuth
        Route::get('/ga4/connect', [WebsiteAnalyticsController::class, 'connectGA4'])->name('ga4.connect');
        Route::get('/ga4/setup-property', [WebsiteAnalyticsController::class, 'setupGA4Property'])->name('ga4.setup-property');
        Route::post('/ga4/save-property', [WebsiteAnalyticsController::class, 'saveGA4Property'])->name('ga4.save-property');
        Route::post('/ga4/sync', [WebsiteAnalyticsController::class, 'syncGA4'])->name('ga4.sync');
        Route::delete('/ga4/disconnect', [WebsiteAnalyticsController::class, 'disconnectGA4'])->name('ga4.disconnect');

        // Keywords
        Route::post('/keywords/save', [WebsiteAnalyticsController::class, 'saveKeyword'])->name('keywords.save');

        // Sync Status Check (for polling)
        Route::get('/sync-status', [WebsiteAnalyticsController::class, 'getSyncStatus'])->name('sync-status');
    });

    // Competitive Gap Analysis page
    Route::get('/websites/{website}/analytics-gaps', [WebsiteAnalyticsController::class, 'analyticsGaps'])
        ->name('website-analytics.gaps');
    Route::post('/websites/{website}/analytics-gaps/recommendations', [WebsiteAnalyticsController::class, 'generateGapRecommendations'])
        ->name('website-analytics.gaps.recommendations');

    // Speed Intelligence (PageSpeed)
    Route::get('/websites/{website}/speed-insights', [WebsiteSpeedInsightsController::class, 'show'])
        ->name('website-analytics.speed-insights');
    Route::post('/websites/{website}/pagespeed/run', [WebsiteSpeedInsightsController::class, 'run'])
        ->name('website-analytics.pagespeed.run');
    Route::get('/websites/{website}/pagespeed/status', [WebsiteSpeedInsightsController::class, 'status'])
        ->name('website-analytics.pagespeed.status');
    Route::delete('/websites/{website}/pagespeed/{run}', [WebsiteSpeedInsightsController::class, 'destroy'])
        ->name('website-analytics.pagespeed.destroy');
    Route::delete('/websites/{website}/pagespeed/{run}', [WebsiteSpeedInsightsController::class, 'destroy'])
        ->name('website-analytics.pagespeed.destroy');

    // Health Score
    Route::post('/websites/{website}/health/run', [WebsiteHealthController::class, 'run'])
        ->name('websites.health.run');
    Route::get('/websites/{website}/health/latest', [WebsiteHealthController::class, 'latest'])
        ->name('websites.health.latest');
    Route::get('/health-sessions/{session}/status', [WebsiteHealthController::class, 'status'])
        ->name('health-sessions.status');

    // OAuth Callbacks (outside website prefix)
    Route::get('/oauth/google/gsc/callback', [WebsiteAnalyticsController::class, 'callbackGSC'])->name('oauth.gsc.callback');
    Route::get('/oauth/google/ga4/callback', [WebsiteAnalyticsController::class, 'callbackGA4'])->name('oauth.ga4.callback');

    // Meta (Facebook/Instagram/Threads) OAuth
    Route::get('/oauth/meta/connect', [MetaOAuthController::class, 'redirect'])->name('oauth.meta.connect');
    Route::get('/oauth/meta/callback', [MetaOAuthController::class, 'callback'])->name('oauth.meta.callback');

    // Social Connections
    Route::get('/social/connections', [SocialConnectionController::class, 'index'])->name('social.connections');

    // News Insight (submenu of Insights) - MUST BE BEFORE resource route to avoid conflict
    Route::prefix('insights/news')->name('insights.news.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NewsInsightController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\NewsInsightController::class, 'create'])->name('create');
        Route::post('/search', [\App\Http\Controllers\NewsInsightController::class, 'search'])->name('search');
        Route::post('/sources', [\App\Http\Controllers\NewsInsightController::class, 'storeSource'])->name('sources.store');
        Route::get('/results/{id}', [\App\Http\Controllers\NewsInsightController::class, 'results'])->name('results');
        Route::post('/save-as-insights', [\App\Http\Controllers\NewsInsightController::class, 'saveAsInsights'])->name('save-as-insights');
        Route::get('/generate-progress', [\App\Http\Controllers\NewsInsightController::class, 'generateProgress'])->name('generate-progress');
        Route::post('/generate-article', [\App\Http\Controllers\NewsInsightController::class, 'generateArticle'])->name('generate-article');
        Route::delete('/{id}', [\App\Http\Controllers\NewsInsightController::class, 'destroy'])->name('destroy');
    });

    // Social Media Insight (submenu of Insights) - MUST BE BEFORE resource route to avoid conflict
    Route::prefix('insights/social')->name('insights.social.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SocialInsightController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SocialInsightController::class, 'create'])->name('create');
        Route::post('/search', [\App\Http\Controllers\SocialInsightController::class, 'search'])->name('search');
        Route::post('/search-news-from-trend', [\App\Http\Controllers\SocialInsightController::class, 'searchNewsFromTrend'])->name('search-news-from-trend');
        Route::post('/generate-batch-news', [\App\Http\Controllers\SocialInsightController::class, 'generateBatchNews'])->name('generate-batch-news');
        Route::get('/results/{id}', [\App\Http\Controllers\SocialInsightController::class, 'results'])->name('results');
        Route::get('/choose-template', [\App\Http\Controllers\SocialInsightController::class, 'chooseTemplate'])->name('choose-template');
        Route::get('/generate-progress', [\App\Http\Controllers\SocialInsightController::class, 'generateProgress'])->name('generate-progress');
        Route::post('/generate-content', [\App\Http\Controllers\SocialInsightController::class, 'generateContent'])->name('generate-content');
        Route::delete('/{id}', [\App\Http\Controllers\SocialInsightController::class, 'destroy'])->name('destroy');
    });

    // Deep Research (Keyword Research with AI)
    Route::prefix('deep-research')->name('deep-research.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DeepResearchController::class, 'index'])->name('index');
        Route::post('/start', [\App\Http\Controllers\DeepResearchController::class, 'start'])->name('start');
        Route::get('/status/{sessionId}', [\App\Http\Controllers\DeepResearchController::class, 'status'])->name('status');
        Route::post('/autocomplete', [\App\Http\Controllers\DeepResearchController::class, 'autocomplete'])->name('autocomplete');
        Route::get('/{sessionId}', [\App\Http\Controllers\DeepResearchController::class, 'show'])->name('show');
        Route::post('/{sessionId}/save-keywords', [\App\Http\Controllers\DeepResearchController::class, 'saveKeywords'])->name('save-keywords');
        Route::post('/{sessionId}/re-enrich', [\App\Http\Controllers\DeepResearchController::class, 'reEnrich'])->name('re-enrich');
        Route::post('/{sessionId}/regenerate-ai', [\App\Http\Controllers\DeepResearchController::class, 'regenerateAI'])->name('regenerate-ai');
        Route::post('/{sessionId}/prepare-generate', [\App\Http\Controllers\DeepResearchController::class, 'prepareGenerate'])->name('prepare-generate');
        Route::get('/{sessionId}/generate-progress', [\App\Http\Controllers\DeepResearchController::class, 'generateProgress'])->name('generate-progress');
        Route::post('/generate-article', [\App\Http\Controllers\DeepResearchController::class, 'generateArticle'])->name('generate-article');
        Route::delete('/{sessionId}', [\App\Http\Controllers\DeepResearchController::class, 'destroy'])->name('destroy');
    });

    // API endpoints for progress polling
    Route::get('/api/content-strategy-status/{id}', [\App\Http\Controllers\SocialInsightController::class, 'getStrategyStatus']);
    Route::get('/api/content-strategy-posts/{id}', [\App\Http\Controllers\SocialInsightController::class, 'getStrategyPosts']);

    // Insights
    Route::post('/insights/bulk-delete', [InsightController::class, 'bulkDelete'])->name('insights.bulk-delete');
    Route::resource('insights', InsightController::class);

    // Keywords Management
    Route::get('/keywords', [\App\Http\Controllers\KeywordController::class, 'index'])->name('keywords.index');
    Route::patch('/keywords/{keyword}/priority', [\App\Http\Controllers\KeywordController::class, 'updatePriority'])->name('keywords.update-priority');
    Route::patch('/keywords/{keyword}/tracking', [\App\Http\Controllers\KeywordController::class, 'updateTrackingStatus'])->name('keywords.update-tracking');
    Route::patch('/keywords/{keyword}/target', [\App\Http\Controllers\KeywordController::class, 'updateTargetPosition'])->name('keywords.update-target');
    Route::delete('/keywords/{keyword}', [\App\Http\Controllers\KeywordController::class, 'destroy'])->name('keywords.destroy');
    Route::post('/keywords/save-from-deep-research', [\App\Http\Controllers\KeywordController::class, 'saveFromDeepResearch'])->name('keywords.save-from-deep-research');

    // Posts
    Route::get('/posts/bulk-review', [PostController::class, 'bulkReview'])->name('posts.bulk-review');
    Route::post('/posts/update-field/{id}', [PostController::class, 'updateField'])->name('posts.update-field');
    Route::post('/posts/{id}/save-target', [PostController::class, 'saveTarget'])->name('posts.save-target');
    Route::delete('/posts/delete-ajax/{id}', [PostController::class, 'deleteAjax'])->name('posts.delete-ajax');
    Route::post('/posts/remove-image/{id}', [PostController::class, 'removeImage'])->name('posts.remove-image');
    Route::post('/posts/bulk-publish', [PostController::class, 'bulkPublish'])->name('posts.bulk-publish');
    Route::post('/posts/bulk-delete', [PostController::class, 'bulkDelete'])->name('posts.bulk-delete');
    Route::delete('/posts/targets/{target}', [PostController::class, 'destroyTarget'])->name('posts.targets.destroy');
    Route::resource('posts', PostController::class);

    // Media
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media/upload', [MediaController::class, 'uploadFile'])->name('media.upload');
    Route::get('/media/details/{id}', [MediaController::class, 'show'])->name('media.show');
    Route::post('/media/update/{id}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('/media/{id}', [MediaController::class, 'apiDestroy'])->name('media.destroy');

    // Media Library Component APIs
    Route::get('/api/media/list', [MediaController::class, 'apiList'])->name('api.media.list');
    Route::get('/api/media/search', [MediaController::class, 'apiSearch'])->name('api.media.search');
    Route::patch('/api/media/{id}/metadata', [MediaController::class, 'updateMetadata'])->name('api.media.metadata');

    // Unsplash Stock Photos Integration
    Route::post('/unsplash/search', [UnsplashController::class, 'search'])->name('unsplash.search');
    Route::post('/unsplash/download', [UnsplashController::class, 'download'])->name('unsplash.download');
    Route::post('/unsplash/save-to-library', [UnsplashController::class, 'saveToLibrary'])->name('unsplash.saveToLibrary');

    // AI Generate Templates
    Route::resource('ai-templates', AiGenerateTemplateController::class);
    Route::get('/ai-templates-modal/create', [AiGenerateTemplateController::class, 'createModal'])->name('ai-templates.create-modal');

    // Templates Management
    Route::resource('templates', \App\Http\Controllers\TemplateController::class);
});

// ============================================
// Admin Routes (Super Admin Only)
// ============================================

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // API Keys Management
    Route::resource('api-keys', ApiKeyController::class);
    Route::patch('/api-keys/{apiKey}/toggle-active', [ApiKeyController::class, 'toggleActive'])->name('api-keys.toggle-active');

    // OAuth Services Management
    Route::resource('oauth-services', OAuthServiceController::class);
    Route::patch('/oauth-services/{oauthService}/toggle-active', [OAuthServiceController::class, 'toggleActive'])->name('oauth-services.toggle-active');

    // General Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\GeneralSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\GeneralSettingController::class, 'update'])->name('settings.update');

    // Template Categories Management
    Route::resource('template-categories', \App\Http\Controllers\Admin\TemplateCategoryController::class);
});

// Debug route removed - use normal /dashboard when logged in

    // Social Media Amplify (Content Distribution)
    Route::post('/posts/{post}/amplify', [\App\Http\Controllers\AmplifyController::class, 'generate'])->name('posts.amplify');
    Route::get('/posts/{post}/amplify/preview', [\App\Http\Controllers\AmplifyController::class, 'preview'])->name('posts.amplify.preview');

    // Social Asset Management
    Route::patch('/social-assets/{asset}', [\App\Http\Controllers\SocialAssetController::class, 'update'])->name('social-assets.update');
    Route::post('/social-assets/{asset}/regenerate', [\App\Http\Controllers\SocialAssetController::class, 'regenerate'])->name('social-assets.regenerate');

    // User Branding Management
    Route::get('/user/branding', [\App\Http\Controllers\UserBrandingController::class, 'show'])->name('user.branding.show');
    Route::put('/user/branding', [\App\Http\Controllers\UserBrandingController::class, 'update'])->name('user.branding.update');

    // Visual Template Management
    Route::get('/api/visual-templates', [\App\Http\Controllers\VisualController::class, 'getTemplateList'])->name('api.visual-templates');
    Route::get('/social-assets/{asset}/templates', [\App\Http\Controllers\VisualController::class, 'getTemplates'])->name('social-assets.templates');
    Route::post('/social-assets/{asset}/template-data', [\App\Http\Controllers\VisualController::class, 'getTemplateData'])->name('social-assets.template-data');
    Route::post('/social-assets/{asset}/upload-visual', [\App\Http\Controllers\VisualController::class, 'uploadVisual'])->name('social-assets.upload-visual');

    // Branding Presets
    Route::resource('brandings', \App\Http\Controllers\BrandingController::class);

    // Asset Library
    Route::get('/assets', [\App\Http\Controllers\AssetLibraryController::class, 'index'])->name('assets.index');
    Route::get('/api/assets/data', [\App\Http\Controllers\AssetLibraryController::class, 'getData'])->name('api.assets.data');
    Route::get('/assets/details/{asset}', [\App\Http\Controllers\AssetLibraryController::class, 'details'])->name('assets.details');
    Route::post('/assets/update/{asset}', [\App\Http\Controllers\AssetLibraryController::class, 'update'])->name('assets.update');
    Route::post('/assets/delete/{asset}', [\App\Http\Controllers\AssetLibraryController::class, 'delete'])->name('assets.delete');
    Route::post('/assets/{asset}/schedule', [\App\Http\Controllers\AssetLibraryController::class, 'schedule'])->name('assets.schedule');
    Route::get('/posts/{post}/export-pack', [\App\Http\Controllers\AssetLibraryController::class, 'exportPack'])->name('posts.export-pack');

    // Social Accounts Management
    Route::get('/social-accounts', [\App\Http\Controllers\SocialAccountController::class, 'index'])->name('social-accounts.index');
    Route::get('/api/social-accounts', [\App\Http\Controllers\SocialAccountController::class, 'getAccounts'])->name('api.social-accounts');
    Route::post('/social-accounts/{account}/set-primary', [\App\Http\Controllers\SocialAccountController::class, 'setPrimary'])->name('social-accounts.set-primary');
    Route::post('/social-accounts/{account}/refresh', [\App\Http\Controllers\SocialAccountController::class, 'refresh'])->name('social-accounts.refresh');
    Route::delete('/social-accounts/{account}/disconnect', [\App\Http\Controllers\SocialAccountController::class, 'disconnect'])->name('social-accounts.disconnect');

    // Calendar
    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [\App\Http\Controllers\CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::get('/calendar/wordpress-events', [\App\Http\Controllers\CalendarController::class, 'getWordPressEvents'])->name('calendar.wordpress-events');
    Route::patch('/calendar/wordpress/{target}/reschedule', [\App\Http\Controllers\CalendarController::class, 'rescheduleWordPress'])->name('calendar.wordpress-reschedule');
    Route::get('/calendar/stats', [\App\Http\Controllers\CalendarController::class, 'getStats'])->name('calendar.stats');
    Route::patch('/calendar/events/{schedule}/reschedule', [\App\Http\Controllers\CalendarController::class, 'reschedule'])->name('calendar.reschedule');
    Route::delete('/calendar/events/{schedule}', [\App\Http\Controllers\CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::post('/calendar/events/bulk-reschedule', [\App\Http\Controllers\CalendarController::class, 'bulkReschedule'])->name('calendar.bulk-reschedule');

    // Image Editor (Konva.js POC)
    Route::get('/image-editor', [\App\Http\Controllers\ImageEditorController::class, 'index'])->name('image-editor.index');
    Route::post('/image-editor/upload', [\App\Http\Controllers\ImageEditorController::class, 'uploadImage'])->name('image-editor.upload');
    Route::post('/image-editor/remove-background', [\App\Http\Controllers\ImageEditorController::class, 'removeBackground'])->name('image-editor.remove-background');
    Route::post('/image-editor/save', [\App\Http\Controllers\ImageEditorController::class, 'saveDesign'])->name('image-editor.save');
