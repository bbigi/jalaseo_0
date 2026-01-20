<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use App\Models\HealthSession;

class Website extends Model
{
    protected $table = 'ane_websites';
    protected $fillable = ['name', 'url', 'username', 'app_password', 'category', 'status', 'created_by'];

    /**
     * Encrypt app_password attribute on assignment
     */
    public function setAppPasswordAttribute($value): void
    {
        $this->attributes['app_password'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt app_password attribute on retrieval
     */
    public function getAppPasswordAttribute($value): string
    {
        return Crypt::decryptString($value);
    }

    /**
     * Get masked app_password for display (first 8 + dots + last 8)
     */
    public function getMaskedAppPassword(): string
    {
        $value = $this->getRawOriginal('app_password');
        if (strlen($value) < 16) {
            return str_repeat('•', strlen($value));
        }
        return substr($value, 0, 8) . str_repeat('•', strlen($value) - 16) . substr($value, -8);
    }

    /**
     * Relationship: belongs to a user
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: has many website analyses
     */
    public function analyses(): HasMany
    {
        return $this->hasMany(WebsiteAnalysis::class);
    }

    /**
     * Relationship: has many keywords
     */
    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    /**
     * Check if website name already exists (excluding current record)
     */
    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        $query = static::where('name', $name);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    /**
     * Get website with post count and analytics status
     */
    public function getWithStats()
    {
        return static::where('id', $this->id)
            ->withCount('analyses')
            ->withCount('keywords')
            ->first();
    }
    /**
     * Relationship: has many health sessions
     */
    public function healthSessions()
    {
        return $this->hasMany(HealthSession::class);
    }
}
