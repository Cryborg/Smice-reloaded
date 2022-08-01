<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\AlertVariable
 *
 * @property int $id
 * @property string $name
 * @property int $alert_id
 * @property int $society_id
 * @property string $type
 * @property mixed $filters
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property-read \App\Models\Alert $alert
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\Society $society
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertVariable minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertVariable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertVariable whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertVariable whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertVariable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertVariable whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertVariable whereAlertId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertVariable whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertVariable whereSocietyId($value)
 * @mixin \Eloquent
 */
class AlertVariable extends SmiceModel implements iREST, iProtected
{
    const UPDATED_AT = null;

    protected $table                = 'alert_variable';

    protected $primaryKey           = 'id';

    public $timestamps              = true;

    protected $fillable             = [
        'name',
        'alert_id',
        'society_id',
        'type',
        'filters',
        'created_by',
    ];

    protected $hidden = [
        'society_id',
        'created_by',
    ];

    protected $jsons = [
        'filters'
    ];

    protected $list_rows = [
        'name',
        'alert_id',
        'type',
        'created_by',
    ];

    public static function getURI()
    {
        return 'variables';
    }

    public static function getName()
    {
        return 'alertVariable';
    }

    public function getModuleName()
    {
        return 'variables';
    }

    protected $rules        = [
        'name'              => 'required|string|required|unique_with:alert,name,{id}',
        'alert_id'          => 'integer|required|read:alert',
        'society_id'        => 'integer|required|read:society',
        'type'              => 'string|required',
        'filters'           => 'required|array',
        'created_by'        => 'integer|required|read:createdBy',
    ];

    public function alert()
    {
        return $this->belongsTo('App\Models\Alert');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by')->select(['id', 'first_name', 'last_name', 'email']);
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function scopeMinimum($query)
    {
        return $query->select('id', 'name', 'type', 'filters');
    }
}