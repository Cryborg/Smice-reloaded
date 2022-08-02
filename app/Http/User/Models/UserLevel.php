<?php

namespace App\Http\User\Models;

use App\Models\SmiceModel;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Spatie\Translatable\HasTranslations;

/**
 * App\Models\UserLevel
 *
 * @property int $id
 * @property mixed $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLevel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserLevel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserLevel extends SmiceModel
{
    use DispatchesJobs;

    protected $table = 'user_level';

    protected $primaryKey = 'id';

    protected $jsons = [
        'status'
    ];

    protected array $translatable = [
        'status'
    ];

    protected $fillable = [
        'status',
    ];

    protected $hidden = [];

    protected array $list_rows = [];

    protected array $rules = [
        'status' => 'required|array',
    ];
}
