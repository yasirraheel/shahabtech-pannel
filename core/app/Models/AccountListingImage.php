<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountListingImage extends Model
{

    public function accountListing() {
        return $this->hasMany(AccountListing::class);
    }
}
