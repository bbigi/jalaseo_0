<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SocialAccount extends Model
{
    protected $table = 'ane_social_accounts';

    protected $fillable = [
        'user_id',
        'provider',
        'account_id',
        'page_id',
        'page_name',
        'ig_business_account_id',
        'username',
        'access_token_enc',
        'refresh_token_enc',
        'expires_at',
        'scopes',
        'status',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'scopes' => 'array',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Decrypt access token
     */
    public function decryptedToken(): ?string
    {
        if (!$this->access_token_enc) {
            return null;
        }

        try {
            return Crypt::decryptString($this->access_token_enc);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Check if account is primary for this provider
     */
    public function isPrimary(): bool
    {
        return ($this->metadata['is_primary'] ?? false) === true;
    }
}
