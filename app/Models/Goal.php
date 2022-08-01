<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\Goal
 *
 * @property int $id
 * @property int $society_id
 * @property int $survey_id
 * @property int $program_id
 * @property int|null $scenario_id
 * @property int|null $axe_id
 * @property int|null $shop_id
 * @property int|null $theme_id
 * @property int|null $sequence_id
 * @property int|null $criteria_id
 * @property int|null $question_id
 * @property float|null $score
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereAxeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereCriteriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereSequenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Goal whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Goal extends SmiceModel implements iREST, iProtected
{
    protected $table = 'goal';

    protected $list_table = 'show_goals';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'society_id',
        'survey_id',
        'program_id',
        'scenario_id',
        'axe_id',
        'shop_id',
        'theme_id',
        'sequence_id',
        'criteria_id',
        'question_id',
        'score',
        'survey',
        'program',
        'scenario',
        'axe',
        'shop',
        'theme',
        'sequence',
        'criteria'
    ];

    protected $jsons = [
        'survey',
        'program',
        'scenario',
        'axe',
        'shop',
        'theme',
        'sequence',
        'criteria',
    ];

    public static function getURI()
    {
        return 'goals';
    }

    public static function getName()
    {
        return 'goal';
    }

    public function getModuleName()
    {
        return 'goal';
    }
}