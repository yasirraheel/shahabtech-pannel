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

    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case self::STATUS_AVAILABLE:
                return '<span class="badge badge--success">Available</span>';
            case self::STATUS_ACTIVE:
                return '<span class="badge badge--primary">Active</span>';
            case self::STATUS_USED:
                return '<span class="badge badge--secondary">Used</span>';
            case self::STATUS_EXPIRED:
                return '<span class="badge badge--danger">Expired</span>';
            default:
                return '<span class="badge badge--dark">Unknown</span>';
        }
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
