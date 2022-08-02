<?php

namespace App\Http\Country\Models;

use App\Http\User\Models\User;
use App\Models\SmiceModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends SmiceModel
{
    protected $table = 'country';

    public $timestamps = false;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
