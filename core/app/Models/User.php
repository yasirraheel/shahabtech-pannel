<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\UserNotify;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, UserNotify, SoftDeletes;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','ver_code','balance','kyc_data'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'address' => 'object',
        'kyc_data' => 'object',
        'ver_code_send_at' => 'datetime',
        'account_ids' => 'array',
        'expires_at' => 'datetime',
        'last_seen' => 'datetime',
        'account_prices' => 'array',
    ];

    public function loginLogs()
    {
        return $this->hasMany(UserLogin::class);
    }

    public function userSocialMedia()
    {
        return $this->hasOne(UserSocialMedia::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderBy('id','desc');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class)->where('status','!=',Status::PAYMENT_INITIATE);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class)->where('status','!=',Status::PAYMENT_INITIATE);
    }

    public function accounts()
    {
        return $this->hasMany(AccountListing::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function notifyPermission()
    {
        return $this->hasOne(UserNotificationPermission::class,'user_id');
    }

    public function fullname(): Attribute
    {
        return new Attribute(
            get: fn () => $this->firstname . ' ' . $this->lastname,
        );
    }

    public function mobileNumber(): Attribute
    {
        return new Attribute(
            get: fn () => $this->dial_code . $this->mobile,
        );
    }

    // SCOPES
    public function scopeActive($query)
    {
        return $query->where('status', Status::USER_ACTIVE)->where('ev',Status::VERIFIED)->where('sv',Status::VERIFIED);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', Status::USER_BAN);
    }

    public function scopeEmailUnverified($query)
    {
        return $query->where('ev', Status::UNVERIFIED);
    }

    public function scopeMobileUnverified($query)
    {
        return $query->where('sv', Status::UNVERIFIED);
    }

    public function scopeKycUnverified($query)
    {
        return $query->where('kv', Status::KYC_UNVERIFIED);
    }

    public function scopeKycPending($query)
    {
        return $query->where('kv', Status::KYC_PENDING);
    }

    public function scopeEmailVerified($query)
    {
        return $query->where('ev', Status::VERIFIED);
    }

    public function scopeMobileVerified($query)
    {
        return $query->where('sv', Status::VERIFIED);
    }

    public function scopeWithBalance($query)
    {
        return $query->where('balance','>', 0);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function onlineTimeFormatted()
    {
        $seconds = (int) ($this->total_online_time ?? 0);
        if ($seconds <= 0) {
            return '0m';
        }

        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days}d";
        }
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }
        if (empty($parts) && $secs > 0) {
            $parts[] = "{$secs}s";
        }

        return implode(' ', $parts);
    }

    public function syncPlatformsWithLoadBalancing(array $platformIds)
    {
        $currentAccountIds = (array) ($this->account_ids ?? []);
        $existingAccounts = AccountListing::whereIn('id', $currentAccountIds)->get()->keyBy('social_media_id');

        $assignedAccountIds = [];

        foreach ($platformIds as $platformId) {
            $platformId = (int) $platformId;
            if (!$platformId) continue;

            if (isset($existingAccounts[$platformId]) && $existingAccounts[$platformId]->status == Status::LISTING_ACTIVE) {
                $assignedAccountIds[] = $existingAccounts[$platformId]->id;
                continue;
            }

            $listings = AccountListing::where('social_media_id', $platformId)
                ->where('status', Status::LISTING_ACTIVE)
                ->get();

            if ($listings->isEmpty()) {
                continue;
            }

            $bestListing = null;
            $minUserCount = PHP_INT_MAX;

            foreach ($listings as $listing) {
                $count = User::whereJsonContains('account_ids', (int) $listing->id)
                    ->orWhereJsonContains('account_ids', (string) $listing->id)
                    ->count();

                if ($count < $minUserCount) {
                    $minUserCount = $count;
                    $bestListing = $listing;
                }
            }

            if ($bestListing) {
                $assignedAccountIds[] = $bestListing->id;
            }
        }

        $this->account_ids = array_values(array_unique($assignedAccountIds));
        return $this->account_ids;
    }

    public function assignedAccountListings()
    {
        $accountIds = (array) ($this->account_ids ?? []);
        if (empty($accountIds)) {
            return collect();
        }
        return AccountListing::whereIn('id', $accountIds)->with('socialMedia')->get();
    }

}
