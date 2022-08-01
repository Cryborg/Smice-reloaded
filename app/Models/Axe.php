<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Axe
 *
 * @property int $id
 * @property string $name
 * @property int $society_id
 * @property int $axe_directory_id
 * @property-read \App\Models\AxeDirectory $axeDirectory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SurveyItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Shop[] $shops
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AxeTag[] $axeTags
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Axe minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Axe whereAxeDirectoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Axe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Axe whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Axe whereSocietyId($value)
 * @mixin \Eloquent
 */
class Axe extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'axe';

    protected $primarykey           = 'id';

    public $timestamps              = false;

    protected $fillable             = [
    	'name',
        'axe_directory_id',
        'society_id'
    ];

    protected $hidden               = [
        'pivot',
        'society_id'
    ];

    protected $rules                = [
        'axe_directory_id'  => 'integer|required',/*|read:axes*/
        'name' 		        => 'string|required|unique_with:axe,axe_directory_id,{id}',
        'society_id'        => 'integer|required'
    ];

    protected $exportable       = [
        'name'
    ];

    public static function getURI()
    {
        return 'axes';
    }

    public static function getName()
    {
        return 'axe';
    }

    public function getModuleName()
    {
        return 'axes';
    }

    public function axeDirectory()
    {
        return $this->belongsTo('App\Models\AxeDirectory');
    }

    public function shops()
    {
        return $this->belongsToMany('App\Models\Shop', 'shop_axe');
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function items()
    {
        return $this->belongsToMany('App\Models\SurveyItem', 'survey_item_axe');
    }

    public function axeTags()
    {
        return $this->morphMany('App\Models\AxeTag', 'axe_tag_item');
    }

    public function scopeMinimum($query)
    {
        $query->select('id', 'name');
    }

}