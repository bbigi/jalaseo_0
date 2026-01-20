<?php

namespace App\Models;

use App\Mail\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'ane_users';

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'subscription_plan',
        'subscription_status',
        'subscription_ends_at',
        'api_quota_used',
        'api_quota_limit',
        'locale',
        'timezone',
        'country_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ============================================
    // Relationships
    // ============================================

    public function websites()
    {
        return $this->hasMany(Website::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function insights()
    {
        return $this->hasMany(Insight::class, 'user_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    // ============================================
    // Scopes
    // ============================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeSubscribed($query)
    {
        return $query->where('subscription_status', 'active')
                     ->where('subscription_ends_at', '>', now());
    }

    // ============================================
    // Email Verification Methods
    // ============================================

    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function sendEmailVerificationNotification()
    {
        Mail::to($this->email)->send(new VerifyEmail($this));
    }

    // ============================================
    // Helper Methods
    // ============================================

    public function hasVerifiedEmail()
    {
        return $this->email_verified_at !== null;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function hasActiveSubscription()
    {
        return $this->subscription_status === 'active' &&
               $this->subscription_ends_at > now();
    }

    public function canUseAPI()
    {
        return $this->hasVerifiedEmail() && $this->isActive();
    }

    public function hasAPIQuota()
    {
        return $this->api_quota_used < $this->api_quota_limit;
    }

    public function incrementAPIQuota($amount = 1)
    {
        $this->increment('api_quota_used', $amount);
    }

    public function getSubscriptionDaysRemaining()
    {
        if (!$this->hasActiveSubscription()) {
            return 0;
        }
        return $this->subscription_ends_at->diffInDays(now());
    }
}
