<?php

namespace App\Models;

/**
 * App\Models\DashboardUser
 *
 * @property int $id
 * @property int $dashboard_id
 * @property int|null $user_id
 * @property int|null $group_id
 * @property-read \App\Models\Dashboard $dashboard
 * @property-read \App\Models\Group|null $group
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DashboardUser whereDashboardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DashboardUser whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DashboardUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DashboardUser whereUserId($value)
 * @mixin \Eloquent
 */
class DashboardUser extends SmiceModel
{
    protected $table            = 'dashboard_user';

    protected $primaryKey       = 'id';

    protected $fillable         = [
        'dashboard_id',
        'user_id',
        'group_id',
    ];

    protected array $rules            = [
        'dashboard_id' => 'integer|required',
        'user_id' => 'integer|required',
        'group_id' => 'integer',
    ];

    protected $hidden           = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function dashboard()
    {
        return $this->belongsTo('App\Models\Dashboard');
    }

    public function group()
    {
        return $this->belongsTo('App\Models\Group');
    }
}
