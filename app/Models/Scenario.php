<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\Scenario
 *
 * @property int $id
 * @property string $name
 * @property int $society_id
 * @property int $created_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SurveyItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mission[] $missions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Shop[] $shops
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Scenario minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Scenario whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Scenario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Scenario whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Scenario whereSocietyId($value)
 * @mixin \Eloquent
 */
class Scenario extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table                = 'scenario';

    protected $primaryKey           = 'id';

    public $timestamps              = false;

    protected $jsons = [
        'name'
    ];

    protected array $translatable = [
        'name'
    ];

    protected $fillable             = [
        'society_id',
        'name',
        'created_by'
    ];

    protected $hidden               = [
        'created_by',
        'pivot'
    ];

    protected $list_rows            = [
        'society_id',
        'name',
        'created_by'
    ];

    protected $rules                = [
        'society_id'    => "integer|required",
        'name' 		    => 'array|required', // "string|required|unique_with:axe,society_id,{id}",
        'created_by'    => 'integer|required'
    ];

    protected $exportable      = [
        'name'
    ];

    public static function getURI()
    {
        return 'scenarios';
    }

    public static function getName()
    {
        return 'scenario';
    }

    public function getModuleName()
    {
        return 'scenarios';
    }

    public function missions()
    {
        return $this->belongsToMany('\App\Models\Mission');
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function shops()
    {
        return $this->belongsToMany('App\Models\Shop', 'shop_axe');
    }

    public function items()
    {
        return $this->belongsToMany('App\Models\SurveyItem', 'survey_item_scenario');
    }

    public function scopeMinimum($query)
    {
        $query->select('id', 'name');
    }
}
