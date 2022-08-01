<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\CriteriaA
 *
 * @property int $id
 * @property mixed $name
 * @property int|null $created_by
 * @property int $society_id
 * @property int|null $order
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SurveyItem[] $surveyItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CriteriaA whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CriteriaA whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CriteriaA whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CriteriaA whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CriteriaA whereOrder($value)
 * @mixin \Eloquent
 */
class CriteriaA extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table = 'criteria_a';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $jsons = [
        'name'
    ];

//    protected $translatables = [
//        'name'
//    ];

    protected $fillable = [
        'name',
        'society_id',
        'created_by'
    ];

    protected $hidden = [
        'society_id',
        'created_by'
    ];

    protected $rules = [
        'name' => 'array|required',
        'society_id' => 'integer|required',
        'created_by' => 'integer|required'
    ];

    protected $list_rows = [
        'society_id',
        'name',
        'created_by'
    ];

    public static function getURI()
    {
        return 'criterionA';
    }

    public static function getName()
    {
        return 'criteria_A';
    }

    public function getModuleName()
    {
        return 'criterionA';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function surveyItem()
    {
        return $this->belongsToMany(SurveyItem::class, 'survey_item_criterion_a', 'criteria_a_id', 'survey_item_id');
    }
}