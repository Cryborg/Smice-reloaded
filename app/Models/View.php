<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Webpatser\Uuid\Uuid;

/**
 * App\Models\View
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property mixed $filters
 * @property int $society_id
 * @property int|null $created_by
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @property string|null $uuid
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ViewShare[] $viewShares
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\View minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\View whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\View whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\View whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\View whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\View whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\View whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\View whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\View whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\View whereUuid($value)
 * @mixin \Eloquent
 */
class View extends SmiceModel implements iREST, iProtected
{
    const TYPE_DASHBOARD = 'DASHBOARD';
    const TYPE_CRITERIA = 'CRITERIA';
    const TYPE_RANKING = 'RANKING';
    const TYPE_ACTIONPLAN = 'ACTIONPLAN';
    const TYPE_REPORT = 'REPORT';
    const TYPE_IMAGE = 'IMAGE';
    const TYPE_VERBATIM = 'VERBATIM';
    const TYPE_TRACKING = 'TRACKING';

    const TYPES = [
        self::TYPE_DASHBOARD,
        self::TYPE_CRITERIA,
        self::TYPE_RANKING,
        self::TYPE_ACTIONPLAN,
        self::TYPE_REPORT,
        self::TYPE_IMAGE,
        self::TYPE_VERBATIM,
        self::TYPE_TRACKING,

    ];

    protected $table                = 'view';

    protected $primaryKey           = 'id';

    public $timestamps              = true;

    public static function getURI()
    {
        return 'views';
    }

    public static function getName()
    {
        return 'view';
    }

    public function getModuleName()
    {
        return 'views';
    }

    protected $jsons = [
        'filters'
    ];

    protected $fillable             = [
        'name',
        'type',
        'filters',
        'society_id',
        'created_by',
        'uuid',
    ];

    protected $hidden = [
    ];

    protected array $list_rows = [
        'name',
        'type',
        'filters',
        'created_by',
    ];

    protected array $rules        = [
        'name'              => 'required|string|required',
        'type'              => 'string|required,in:'.self::TYPE_DASHBOARD.','.self::TYPE_CRITERIA.','.self::TYPE_RANKING.','.self::TYPE_ACTIONPLAN.','.self::TYPE_REPORT,
        'filters'           => 'required|array',
        'society_id'        => 'integer|required|read:society',
        'created_by'        => 'integer|required|read:createdBy',
        'uuid'              => 'string|required',
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function(self $view) {
            $view->uuid = Uuid::generate()->string;
        });
    }

    public function scopeRelations($query)
    {
        $query->with('viewShares');
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\User', 'created_by')->select(array('id', 'first_name', 'last_name', 'email'));
    }

    public function viewShares()
    {
        return $this->hasMany('App\Models\ViewShare');
    }

    public function scopeMinimum($query)
    {
        return $query->select('id', 'name', 'filters');
    }
}
