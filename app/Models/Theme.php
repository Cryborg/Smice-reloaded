<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\Theme
 *
 * @property int $id
 * @property mixed $name
 * @property int|null $created_by
 * @property int $society_id
 * @property int|null $order
 * @property bool $is_visible_on_top_report
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SurveyItem[] $surveyItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Theme whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Theme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Theme whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Theme whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Theme whereIsVisibleOnTopReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Theme whereOrder($value)
 * @mixin \Eloquent
 */
class Theme extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table = 'theme';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $jsons = [
        'name'
    ];

    protected array $translatable = [
        'name'
    ];

    protected $fillable = [
        'order',
        'name',
        'is_visible_on_top_report',
        'society_id',
        'created_by'
    ];

    protected $hidden = [
        'society_id',
        'created_by'
    ];

    protected array $list_rows = [
        'name',
        'is_visible_on_top_report'
    ];

    protected array $rules = [
        'society_id' => 'integer|required',
        'name' => 'array|required',
        'created_by' => 'integer|required'
    ];

    public static function getURI()
    {
        return 'themes';
    }

    public static function getName()
    {
        return 'theme';
    }

    public function getModuleName()
    {
        return 'themes';
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
        return $this->belongsToMany(SurveyItem::class, 'survey_item_theme', 'theme_id', 'survey_item_id');
    }
}
