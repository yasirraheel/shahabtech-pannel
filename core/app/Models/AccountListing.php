<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AccountListing extends Model
{

    protected $casts = [
        'account_info' => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function images()
    {
        return $this->hasMany(AccountListingImage::class);
    }

    public function socialMedia()
    {
        return $this->belongsTo(SocialMedia::class);
    }

    public function accountCredential()
    {
        return $this->hasOne(AccountCredential::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function accountBidding()
    {
        return $this->hasMany(BiddingListing::class);
    }

    public function report()
    {
        return $this->hasMany(ListingReport::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', Status::LISTING_PENDING);
    }
    public function scopeActive($query)
    {
        return $query->where('status', Status::LISTING_ACTIVE);
    }
    public function scopeInactive($query)
    {
        return $query->where('status', Status::LISTING_INACTIVE);
    }
    public function scopeRejected($query)
    {
        return $query->where('status', Status::LISTING_REJECTED);
    }
    public function scopeDraft($query)
    {
        return $query->where('status', Status::LISTING_DRAFT);
    }
    public function scopeSold($query)
    {
        return $query->where('status', Status::LISTING_SOLD);
    }

    public function scopePricingModelAuction($query)
    {
        return $query->where('pricing_model', Status::AUCTION);
    }
    
    public function scopeActiveSocialMedia($query)
    {
        return $query->whereHas('socialMedia', function ($q) {
            $q->active();
        });
    }
    public function scopeActiveCategory($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->active();
        });
    }
    public function scopeCheckPreviousDate($query)
    {
        return $query->where(function ($q) {
            $q->where('pricing_model', Status::FIXED)->orWhere(function ($q) {
                $q->where('pricing_model', Status::AUCTION)->whereDate('auction_deadline', '>=', today());
            });
        });
    }

    public function scopeMyBidCount($query)
    {
        return $query->withCount(['accountBidding as my_bid_count' => function($query){
            $query->where('user_id',auth()->id());
        }]);
    }
    public function scopeMyBid($query)
    {
        return $query->with(['accountBidding'=>function($q){
            $q->where('user_id',auth()->id());
        }]);
    }

    public function statusBadge(): Attribute
    {
        return new Attribute(function () {
            $html = '';
            if ($this->status == Status::LISTING_ACTIVE) {
                $html = '<span class="badge badge--success">' . trans("Active") . '</span>';
            } elseif ($this->status == Status::LISTING_SOLD) {
                $html = '<span class="badge badge--success">' . trans("Sold") . '</span>';
            } elseif ($this->status == Status::LISTING_PENDING) {
                $html = '<span class="badge badge--warning">' . trans("Pending") . '</span>';
            } elseif ($this->status == Status::LISTING_INACTIVE) {
                $html = '<span class="badge badge--primary">' . trans("Inactive") . '</span>';
            } elseif ($this->status == Status::LISTING_REJECTED) {
                $html = '<div class="d-flex flex-wrap gap-1"><span class="badge badge--danger">' . trans("Rejected") . '</span>
                        <button data-bs-toggle="modal" data-bs-target="#rejectReasonModal" data-reason="' . $this->reason . '" class="badge badge--danger reasonBtn"> <i class="las la-info-circle"></i> </button></div>';
            } elseif ($this->status == Status::LISTING_DRAFT) {
                $html = '<span class="badge badge--danger">' . trans("Draft") . '</span>';
            }
            return $html;
        });
    }

    public function auctionDeadlineFormate(): Attribute
    {
        return new Attribute(function () {
            return showDateTime($this->auction_deadline,'Y-m-d ')."23:59:59";
        });
    }
}
