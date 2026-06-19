<?php

namespace App\Models;


use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    use GlobalStatus;

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function accountListing()
    {
        return $this->hasMany(AccountListing::class);
    }

    public function scopeHasAccountListing($query)
    {
        return $query->whereHas('accountListing', function ($query) {
            $query->active();
        });
    }
}
