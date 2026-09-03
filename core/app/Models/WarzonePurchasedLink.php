<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarzonePurchasedLink extends Model
{
    protected $table = 'warzone_purchased_links';

    protected $guarded = ['id'];

    const STATUS_AVAILABLE = 1;
    const STATUS_ACTIVE    = 2;
    const STATUS_USED      = 3;
    const STATUS_EXPIRED   = 0;

    protected $casts = [
        'purchased_at' => 'datetime',
        'status'       => 'integer',
    ];

    public function statusBadge(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return new \Illuminate\Database\Eloquent\Casts\Attribute(
            get: fn () => $this->badgeData(),
        );
    }

    public function badgeData()
    {
        switch ((int) $this->status) {
            case self::STATUS_AVAILABLE:
                return '<span class="badge badge--success"><i class="las la-check-circle"></i> Available</span>';
            case self::STATUS_ACTIVE:
                return '<span class="badge badge--primary"><i class="las la-clock"></i> Active</span>';
            case self::STATUS_USED:
                return '<span class="badge badge--warning"><i class="las la-check-double"></i> Used</span>';
            case self::STATUS_EXPIRED:
                return '<span class="badge badge--danger"><i class="las la-times-circle"></i> Expired</span>';
            default:
                return '<span class="badge badge--dark">Unknown</span>';
        }
    }

    public function getStatusBadgeAttribute()
    {
        return $this->badgeData();
    }

    public function sourceBadge(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return new \Illuminate\Database\Eloquent\Casts\Attribute(
            get: function () {
                if ($this->source === 'bot' || $this->source === 'manual_bot') {
                    return '<span class="badge badge--info"><i class="lab la-telegram"></i> Bot Purchase</span>';
                }
                return '<span class="badge badge--dark"><i class="las la-user-edit"></i> Manual Entry</span>';
            }
        );
    }

    public function getSourceBadgeAttribute()
    {
        if ($this->source === 'bot' || $this->source === 'manual_bot') {
            return '<span class="badge badge--info"><i class="lab la-telegram"></i> Bot Purchase</span>';
        }
        return '<span class="badge badge--dark"><i class="las la-user-edit"></i> Manual Entry</span>';
    }


    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeUsed($query)
    {
        return $query->where('status', self::STATUS_USED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }
}
