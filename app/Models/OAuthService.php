<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OAuthService extends Model
{
    protected $table = 'ane_oauth_services';

    protected $fillable = [
        'service_name',
        'display_name',
        'client_id',
        'client_secret',
        'redirect_uri',
        'scopes',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'client_id' => 'encrypted',
        'client_secret' => 'encrypted',
        'scopes' => 'array',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByService($query, $serviceName)
    {
        return $query->where('service_name', $serviceName);
    }

    // Helpers
    public function getMaskedClientId()
    {
        if (!$this->client_id) {
            return 'Not set';
        }

        $decrypted = $this->client_id;
        $length = strlen($decrypted);

        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($decrypted, 0, 4) . str_repeat('*', $length - 8) . substr($decrypted, -4);
    }

    public function getMaskedClientSecret()
    {
        if (!$this->client_secret) {
            return 'Not set';
        }

        $decrypted = $this->client_secret;
        $length = strlen($decrypted);

        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($decrypted, 0, 4) . str_repeat('*', $length - 8) . substr($decrypted, -4);
    }

    /**
     * Get OAuth config for a service (with cache)
     */
    public static function getConfig(string $serviceName): ?array
    {
        return Cache::remember("oauth_service_{$serviceName}", 3600, function () use ($serviceName) {
            $service = self::active()->byService($serviceName)->first();

            if (!$service) {
                // Fallback to .env
                return [
                    'client_id' => config("services.{$serviceName}.client_id"),
                    'client_secret' => config("services.{$serviceName}.client_secret"),
                    'redirect_uri' => config("services.{$serviceName}.redirect_uri"),
                ];
            }

            return [
                'client_id' => $service->client_id,
                'client_secret' => $service->client_secret,
                'redirect_uri' => $service->redirect_uri ?: config("services.{$serviceName}.redirect_uri"),
            ];
        });
    }

    /**
     * Clear cache when updated
     */
    protected static function booted()
    {
        static::saved(function ($service) {
            Cache::forget("oauth_service_{$service->service_name}");
        });

        static::deleted(function ($service) {
            Cache::forget("oauth_service_{$service->service_name}");
        });
    }
}
