<?php

namespace App\Http\User\Models;

use App\Models\SmiceModel;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * App\Models\UserActivity
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $validated_mission
 * @property int $invalidated_mission
 * @property int $user_level_id
 * @property-read \App\Models\User $user
 * @property-read \App\Http\User\Models\UserLevel $userLevel
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivity whereInvalidatedMission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivity whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivity whereUserLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserActivity whereValidatedMission($value)
 */
class UserActivity extends SmiceModel
{
    use DispatchesJobs;

    protected $table = 'user_activity';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $jsons = [];

    protected $fillable = [
        'validated_mission',
        'invalidated_mission',
        'user_level_id',
    ];

    protected $hidden = [];

    protected array $list_rows = [
        'user_level_id'
    ];

    protected array $rules = [
        'user_id' => 'required|integer|read:user',
        'validated_mission' => 'required|integer',
        'invalidated_mission' => 'required|integer',
        'user_level_id' =>  'required|integer',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function userLevel()
    {
        return $this->belongsTo('App\Http\User\Models\UserLevel');
    }
}
