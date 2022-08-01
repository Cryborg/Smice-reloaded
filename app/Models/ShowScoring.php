<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\SepaFile
 *
 * @property int $id
 * @property int $transaction
 * @property float $amount
 * @property string|null $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $name
 * @property int|null $created_by
 * @property string|null $status
 * @property int|null $wave_target_id
 * @property int|null $user_id
 * @property int|null $survey_id
 * @property int|null $question_id
 * @property int|null $question_row_id
 * @property int|null $question_tag
 * @property int|null $response_value
 * @property int|null $question_weight
 * @property int|null $question_score
 * @property int|null $criteria_weight
 * @property int|null $criteria_score
 * @property int|null $weight
 * @property int|null $score
 * @property int|null $theme_id
 * @property int|null $job_id
 * @property int|null $criteria_id
 * @property int|null $criteria_a_id
 * @property int|null $criteria_b_id
 * @property int|null $sequence_id
 * @property int|null $scenario_id
 * @property int|null $shop_id
 * @property int|null $wave_id
 * @property int|null $mission_id
 * @property string|null $visit_date
 * @property string|null $uuid
 * @property int|null $program_id
 * @property int|null $society_id
 * @property string|null $date_start
 * @property string|null $date_end
 * @property mixed|null $program_name
 * @property string|null $wave_name
 * @property string|null $shop_name
 * @property mixed|null $scenario_name
 * @property string|null $mission_name
 * @property string|null $society_name
 * @property mixed|null $sequence_name
 * @property mixed|null $theme_name
 * @property mixed|null $job_name
 * @property mixed|null $criteria_name
 * @property mixed|null $criteria_a_name
 * @property mixed|null $criteria_b_name
 * @property mixed|null $question_name
 * @property int|null $order
 * @property int|null $sequence_order
 * @property int|null $tag_id
 * @property bool|null $scoring
 * @property mixed|null $question_info
 * @property bool|null $sequence_scoring
 * @property string|null $type
 * @property string|null $date_status
 * @property int|null $answer_id
 * @property string|null $comment
 * @property mixed|null $question_row_name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereTransaction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereCriteriaAId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereCriteriaAName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereCriteriaBId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereCriteriaBName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereCriteriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereCriteriaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereCriteriaScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereCriteriaWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereDateStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereJobName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereMissionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereProgramName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereQuestionInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereQuestionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereQuestionRowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereQuestionRowName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereQuestionScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereQuestionTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereQuestionWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereResponseValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereScenarioName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereScoring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereSequenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereSequenceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereSequenceOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereSequenceScoring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereShopName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereSocietyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereThemeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereVisitDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereWaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereWaveName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShowScoring whereWeight($value)
 * @mixin \Eloquent
 */
class ShowScoring extends SmiceModel implements iRest, iProtected
{
    protected $table = 'show_scoring';

    protected $primaryKey = 'wave_target_id';

    public static function getURI()
    {
        return 'show_scoring';
    }

    public static function getName()
    {
        return 'showScoring';
    }

    public function getModuleName()
    {
        return 'show_scoring';
    }
}