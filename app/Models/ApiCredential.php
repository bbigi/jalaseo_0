<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ApiCredential extends Model
{
    protected $table = 'ane_api_credentials';

    protected $fillable = ['category', 'provider', 'api_key', 'api_secret', 'display_name', 'description', 'is_active'];

    protected $hidden = ['api_key', 'api_secret']; // Don't expose raw key in JSON responses

    /**
     * Automatically encrypt the API key when setting it.
     */
    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = Crypt::encryptString($value);
    }

    /**
     * Automatically decrypt the API key when retrieving it.
     */
    public function getApiKeyAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            \Log::warning("Failed to decrypt API key for provider: {$this->provider}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Automatically encrypt the API secret when setting it.
     */
    public function setApiSecretAttribute($value)
    {
        if ($value) {
            $this->attributes['api_secret'] = Crypt::encryptString($value);
        }
    }

    /**
     * Automatically decrypt the API secret when retrieving it.
     */
    public function getApiSecretAttribute($value)
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get a masked version of the API key for display purposes.
     * Shows only first 4 and last 4 characters.
     */
    public function getMaskedApiKey()
    {
        if (!$this->api_key) {
            return '●●●●●●●●';
        }

        $key = $this->api_key;
        $length = strlen($key);

        if ($length <= 8) {
            return '●●●●●●●●';
        }

        $start = substr($key, 0, 4);
        $end = substr($key, -4);

        return "{$start}●●●●●●●●{$end}";
    }

    /**
     * Scope to get credentials by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get a credential by category and provider name.
     */
    public static function getByProvider($category, $provider)
    {
        return self::where('category', $category)->where('provider', $provider)->first();
    }

    /**
     * Check if a provider credential exists and is active in a category.
     */
    public static function isConfigured($category, $provider)
    {
        return self::where('category', $category)->where('provider', $provider)->where('is_active', true)->exists();
    }

    /**
     * Get all active providers in a category.
     */
    public static function getActiveProvidersInCategory($category)
    {
        return self::where('category', $category)->where('is_active', true)->pluck('provider')->toArray();
    }
}
