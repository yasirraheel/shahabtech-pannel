<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use GlobalStatus;

    protected $guarded = ['id'];

    protected $casts = [
        'features' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getIncludedResourcesAttribute()
    {
        return \App\Models\AccountListing::where('plan_id', $this->id)
            ->where('status', \App\Constants\Status::LISTING_ACTIVE)
            ->whereHas('socialMedia', function($q){ 
                $q->active(); 
            })
            ->with('socialMedia')
            ->get()
            ->pluck('socialMedia.name')
            ->unique();
    }
}
