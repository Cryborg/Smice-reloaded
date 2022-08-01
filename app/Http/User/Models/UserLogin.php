<?php

namespace App\Http\User\Models;

use App\Models\SmiceModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UserLogin extends SmiceModel
{
    use DispatchesJobs;

    protected $table = 'user_login';

    public $timestamps = false;

    protected array $jsons = [];

    protected $fillable = [
        'google_user_id',
        'facebook_user_id',
    ];

    protected $hidden = [];

    protected array $list_rows = [];

    protected array $rules = [
        'user_id' => 'required|integer|read:user',
        'google_user_id' => 'string',
        'facebook_user_id' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }
}
