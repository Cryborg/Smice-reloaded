<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Webpatser\Uuid\Uuid;

/**
 * App\Models\Dashboard
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property int $society_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $uuid
 * @property string|null $share_option
 * @property int|null $program_id
 * @property mixed|null $options
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Graph[] $graphs
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DashboardUser[] $dashboardUsers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dashboard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dashboard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dashboard whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dashboard whereShareOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dashboard whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dashboard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dashboard whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dashboard whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dashboard whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dashboard whereProgramId($value)
 * @mixin \Eloquent
 */
class Dashboard extends SmiceModel implements iREST, iProtected
{
    protected $table            = 'dashboard';

    protected $primaryKey       = 'id';

    const SHARE_OPTION_AUTHPRIZE_CONSULT_ONLY = 'authorize_consult_only';
    const SHARE_OPTION_DEFINE_AS_MODEL = 'define_as_model';
    const SHARE_OPTION_NO_SHARE = 'no_share';

    protected $jsons = [
        'name',
    ];

    protected array $translatable = [
        'name',
    ];

    protected $fillable         = [
        'name',
        'user_id',
        'program_id',
        'uuid',
        'society_id',
        'share_option'
    ];

    protected array $rules            = [
        'name' => 'array|required',
        'user_id' => 'integer|required',
        'society_id' => 'integer|required',
        'uuid' => 'string|required',
        'share_option' => 'string|in:authorize_consult_only,define_as_model,no_share'
    ];

    protected $hidden           = [];

    protected static function boot()
    {
        parent::boot();

        self::creating(function(self $dashboard) {
            $dashboard->uuid = Uuid::generate();
        });

        self::updating(function (self $dashboard) {
            if ($dashboard->share_option == self::SHARE_OPTION_NO_SHARE) {
                $dashboard->dashboardUsers()->delete();
            }
        });
    }

    public static function getURI()
    {
        return 'dashboards';
    }

    public static function getName()
    {
        return 'dashboard';
    }

    public function 	   getModuleName()
    {
        return 'dashboards';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function graphs()
    {
        return $this->hasMany('App\Models\Graph');
    }

    public function dashboardUsers()
    {
        return $this->hasMany('App\Models\DashboardUser');
    }
}
