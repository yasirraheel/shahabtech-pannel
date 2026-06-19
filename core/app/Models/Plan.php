<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use GlobalStatus;

    protected $guarded = ['id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
