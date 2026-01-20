<?php

namespace App\Providers;

use App\Services\SocialPublishers\MetaPublisher;
use App\Services\SocialPublishers\SocialPublisherManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MetaPublisher::class);

        $this->app->singleton(SocialPublisherManager::class, function ($app) {
            return new SocialPublisherManager([
                $app->make(MetaPublisher::class),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS when APP_URL is HTTPS (Cloudflare Tunnel)
        if (str_starts_with(config('app.url'), 'https://')) {
            \URL::forceScheme('https');
        }
    }
}
