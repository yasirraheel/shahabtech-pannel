<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingReport extends Model
{
    public function user()
    {
    	return $this->belongsTo(User::class);
    }
   
    public function accountListing()
    {
    	return $this->belongsTo(AccountListing::class);
    }
}
