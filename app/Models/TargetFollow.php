<?php

namespace App\Models;

use Illuminate\Support\Facades\Validator;

/**
 * App\Models\TargetFollow
 *
 * @property int|null $program_id
 * @property string|null $program
 * @property int|null $wave_id
 * @property string|null $wave
 * @property bool|null $ended
 * @property int|null $society_id
 * @property string|null $society
 * @property int|null $shop_id
 * @property string|null $shop
 * @property float|null $shop_lat
 * @property float|null $shop_lon
 * @property string|null $shop_place
 * @property string|null $city
 * @property string|null $pc
 * @property int|null $mission_id
 * @property string|null $mission
 * @property int|null $scenario_id
 * @property string|null $scenario
 * @property int|null $user_id
 * @property int|null $score
 * @property string|null $user
 * @property int|null $reader_id
 * @property string|null $boss
 * @property int|null $id
 * @property int|null $selected_user
 * @property string|null $date_start
 * @property bool|null $answered_quiz
 * @property int|null $quiz_id
 * @property string|null $date_end
 * @property int|null $survey_id
 * @property string|null $status
 * @property string|null $visit_date
 * @property string|null $date_status
 * @property string|null $hours
 * @property string|null $description
 * @property string|null $uuid
 * @property int|null $propositions
 * @property int|null $accepted
 * @property int|null $answered_survey
 * @property int|null $invalidated
 * @property int|null $validated
 * @property mixed|null $name
 * @property string|null $image
 * @property string|null $validation_mode
 * @property int|null $percentage_completeness
 * @property bool|null $is_paid
 * @property string|null $validation
 * @property string|null $survey_answered_at
 * @property float|null $global_score
 * @property bool|null $anonymous_mode
 * @property int|null $reviewer_id
 * @property string|null $mission_name
 * @property mixed|null $scenario_bis
 * @property string|null $type
 * @property float|null $autopilot_level
 * @property string|null $request_id
 * @property string|null $request_status
 * @property int|null $claim
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow filter($params)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereAnsweredQuiz($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereAnsweredSurvey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereBoss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereDateStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereEnded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereInvalidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereMission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow wherePc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow wherePercentageCompleteness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereProgram($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow wherePropositions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereQuizId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereReaderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereScenario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereSelectedUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereShop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereShopLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereShopLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereShopPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereSociety($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereSurveyAnsweredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereValidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereValidation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereValidationMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereVisitDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereWave($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereWaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereGlobalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereAnonymousMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereAutopilotLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereClaim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereMissionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereRequestStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereReviewerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereScenarioBis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetFollow whereType($value)
 * @mixin \Eloquent
 */
class TargetFollow extends SmiceModel
{
    protected $table                = 'show_targets';

    public $timestamps              = false;

    protected $fillable             = [];

    protected $hidden               = [];

    protected $rules                = [];

    protected $list_rows            = [
        'society',
        'program',
        'mission',
        'scenario',
        'shop',
        'wave',
        'date_start',
        'date_end',
        'user',
        'boss',
        'status',
        'date_status',
        'propositions'
    ];

    public function     scopeFilter($query, $params)
    {
        $mission        = array_get($params, 'mission_id');
        $program        = array_get($params, 'program_id');
        $society        = array_get($params, 'society_id');
        $scenario       = array_get($params, 'scenario_id');
        $shop           = array_get($params, 'shop_id');
        $wave           = array_get($params, 'wave_id');
        $boss           = array_get($params, 'reader_id');
        $date_start     = array_get($params, 'date_start');
        $date_end       = array_get($params, 'date_end');
        $date_status    = array_get($params, 'date_status');
        $validator      = Validator::make(
            [
                'mission'       => $mission,
                'program'       => $program,
                'society'       => $society,
                'scenario'      => $scenario,
                'shop'          => $shop,
                'wave'          => $wave,
                'boss'          => $boss,
                'date_start'    => $date_start,
                'date_end'      => $date_end,
                'date_status'   => $date_status
            ],
            [
                'mission'       => 'integer|read:missions',
                'program'       => 'integer|read:programs',
                'society'       => 'integer|read:societies',
                'scenario'      => 'integer|read:scenarios',
                'shop'          => 'integer|read:shops',
                'wave'          => 'integer|read:waves',
                'boss'          => 'integer|read:users',
                'date_start'    => 'date',
                'date_end'      => 'date',
                'date_status'   => 'date'
            ]
        );

        $validator->passOrDie();
        if ($mission)
            $query->where('mission_id', $mission);
        if ($program)
            $query->where('program_id', $program);
        if ($society)
            $query->where('society_id', $society);
        if ($scenario)
            $query->where('scenario_id', $scenario);
        if ($shop)
            $query->where('shop_id', $shop);
        if ($wave)
            $query->where('wave_id', $wave);
        if ($boss)
            $query->where('reader_id', $boss);
        if ($date_start)
            $query->where('date_start', $date_start);
        if ($date_end)
            $query->where('date_end', $date_end);
        if ($date_status)
            $query->where('date_status', $date_status);

        return $query;
    }
}