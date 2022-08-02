<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\CriteriaB
 *
 * @property int $id
 * @property mixed $name
 * @property int|null $created_by
 * @property int $society_id
 * @property int|null $order
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SurveyItem[] $surveyItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CriteriaB whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CriteriaB whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CriteriaB whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CriteriaB whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CriteriaB whereOrder($value)
 * @mixin \Eloquent
 */
class CriteriaB extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table = 'criteria_b';

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

    protected array $rules = [
        'name' => 'array|required',
        'society_id' => 'integer|required',
        'created_by' => 'integer|required'
    ];

    protected array $list_rows = [
        'society_id',
        'name',
        'created_by'
    ];

    public static function getURI()
    {
        return 'criterionB';
    }

    public static function getName()
    {
        return 'criteria_B';
    }

    public function getModuleName()
    {
        return 'criterionB';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function surveyItem()
    {
        return $this->belongsToMany(SurveyItem::class, 'survey_item_criterion_b', 'criteria_b_id', 'survey_item_id');
    }
}
