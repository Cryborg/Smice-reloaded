<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


/**
 * App\Models\Mission
 *
 * @property int $id
 * @property string $name
 * @property string|null $hours
 * @property string $description
 * @property bool $has_briefing
 * @property bool $has_quiz
 * @property int|null $reviewer_id
 * @property int|null $briefing_id
 * @property int|null $survey_id
 * @property int|null $quiz_id
 * @property int|null $scenario_id
 * @property int $society_id
 * @property int|null $created_by
 * @property int|null $nb_quiz_error
 * @property string|null $picture
 * @property string|null $accroche
 * @property string|null $scope
 * @property mixed|null $filters
 * @property int|null $refund
 * @property int|null $compensation
 * @property bool $ask_refund
 * @property bool $ask_proof
 * @property bool $monday
 * @property bool $tuesday
 * @property bool $wednesday
 * @property bool $thursday
 * @property bool $friday
 * @property bool $saturday
 * @property bool $sunday
 * @property int $quantity
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property bool $report_auto
 * @property bool $is_paid
 * @property string $attribution
 * @property string $selection
 * @property int $confirm_days_before_start
 * @property string|null $automatic_selection_type
 * @property int $random_start
 * @property string $validation
 * @property bool $permanent_mission
 * @property bool $report_limit
 * @property bool $report_author
 * @property bool $disabled
 * @property bool $standard
 * @property bool $noncompliance
 * @property bool $experience
 * @property bool $synthetic
 * @property bool $show_legend
 * @property bool $smicer_name
 * @property bool $date_visit
 * @property bool $wave_name
 * @property string|null $type
 * @property string|null $category
 * @property string|null $moment
 * @property string|null $document
 * @property bool $show_score
 * @property int $payment_delay
 * @property string $internal_name
 * @property bool $report_mail
 * @property string|null $report_mail_name
 * @property int|null $sign_template
 * @property int|null $sign_rate
 * @property bool $show_score_2
 * @property string $report_type
 * @property bool $hide_score
 * @property bool $faceblur
 * @property-read \App\Models\Briefing|null $briefing
 * @property-read \App\Models\Survey|null $quiz
 * @property-read \App\Models\User|null $reviewer
 * @property-read \App\Models\Scenario|null $scenario
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\Survey|null $survey
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Checklist[] $checklists
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Wave[] $waves
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Alert[] $alerts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereAccroche($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereAskProof($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereAskRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereAttribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereAutomaticAssignation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereAutomaticSelectionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereBriefingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereCompensation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereConfirmDaysBeforeStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereFriday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereHasBriefing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereHasQuiz($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereMonday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereNbQuizError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereQuizId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereRandomStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereReportAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereReportAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereReportLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereReviewerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereSaturday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereSelection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereSunday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereThursday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereTuesday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereValidation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereWednesday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereDateVisit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereNoncompliance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereShowLegend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereSmicerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereStandard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereSynthetic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereWaveName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereMoment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereShowScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission wherePaymentDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission wherePermanentMission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereInternalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereReportMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereReportMailName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereReportType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereShowScore2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereSignRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereSignTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereHideScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mission whereFaceblur($value)
 * @mixin \Eloquent
 */
class Mission extends SmiceModel implements iREST, iProtected, iTranslatable
{
    const MINIMUM_TARGET        = 20;

    const DISABLED_YES          = true;
    const DISABLED_NO           = false;

    const SCOPE_TO_SMICERS           = 'to_smicers';
    const SCOPE_TO_CONTRIBUTORS      = 'to_contributors';
    const SCOPE_TO_LIMIT_SMICER      = 'to_limit_smicer';
    const SCOPE_TO_AUTO_CONTRIBUTORS = 'to_auto_contributors';

    const SELECTION_AUTOMATIC = 'automatic';
    const SELECTION_MANUAL = 'manual';

    const MOMENT_MORNING           = 'morning';
    const MOMENT_MIDDAY            = 'midday';
    const MOMENT_AFTERNOON         = 'afternoon';
    const MOMENT_EVENING           = 'evening';

    const TYPE_SMICER              = 'smicer';
    const TYPE_INVESTIGATOR        = 'investigator';

    protected $table            = 'mission';

    protected $list_table       = 'show_missions';

    protected $primaryKey       = 'id';

    public $timestamps          = true;

    protected array $translatables    = [
        'survey'
    ];

    protected $jsons            = [
        'filters',
        'scenario',
    ];

    protected $fillable         = [
        'name',
        'internal_name',
        'hours',
        'description',
        'has_briefing',
        'has_quiz',
        'briefing_id',
        'survey_id',
        'quiz_id',
        'scenario_id',
        'society_id',
        'filters',
        'refund',
        'compensation',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'reviewer_id',
        'quantity',
        'nb_quiz_error',
        'picture',
        'accroche',
        'ask_refund',
        'ask_proof',
        'scope',
        'report_auto',
        'is_paid',
        'attribution',
        'selection',
        'confirm_days_before_start',
        'automatic_selection_type',
        'random_start',
        'validation',
        'permanent_mission',
        'report_limit',
        'report_author',
        'disabled',
        'standard',
        'noncompliance',
        'experience',
        'synthetic',
        'show_legend',
        'smicer_name',
        'mission_name',
        'date_visit',
        'wave_name',
        'document',
        'moment',
        'type',
        'show_score',
        'payment_delay',
        'report_mail_name',
        'report_mail',
        'sign_template',
        'sign_rate',
        'report_type',
        'show_score_2',
        'show_mission_name',
        'hide_score',
        'all_question_mandatory',
        'disable_seq_condition',
        'exclusion_mail'
    ];

    protected $children = [
        'MissionReportExclusion'
    ];

    protected $hidden           = [
        'created_by',
    ];

    protected $list_rows        = [
        'name',
        'internal_name',
        'hours',
        'scenario',
        'survey',
        'society',
        'quantity',
        'society_id',
        'scenario_id',
        'filters',
        'survey_id',
        'scope',
        'report_auto',
        'all_question_mandatory',
        'disable_seq_condition'
    ];

    protected $rules            = [
        'name'                      => 'string|required|unique_with:mission,society_id,{id}',
        'type'                      => 'string|required|in:smicer,investigator',
        'internal_name'             => 'string|required',
        'hours'                     => 'string',
        'description'               => 'string|required',
        'reviewer_id'               => 'integer|read:users',
        'has_briefing'              => 'boolean',
        'has_quiz'                  => 'boolean',
        'briefing_id'               => 'required_if:briefing,1|integer|read:briefings',
        'survey_id'                 => 'integer|read:surveys',
        'quiz_id'                   => 'integer|read:surveys',
        'scenario_id'               => 'integer|read:scenarios',
        'refund'                    => 'integer',
        'compensation'              => 'integer',
        'monday'                    => 'boolean',
        'tuesday'                   => 'boolean',
        'wednesday'                 => 'boolean',
        'thursday'                  => 'boolean',
        'friday'                    => 'boolean',
        'saturday'                  => 'boolean',
        'sunday'                    => 'boolean',
        'quantity'                  => 'integer|min:0',
        'nb_quiz_error'             => 'integer|min:0',
        'accroche'                  => 'string',
        'ask_proof'                 => 'boolean',
        'ask_refund'                => 'boolean',
        'scope'                     => 'required|string|in:to_smicers,to_contributors,to_limit_smicer,to_auto_contributors',
        'report_auto'               => 'boolean',
        'is_paid'                   => 'boolean',
        'attribution'               => 'string|in:simple,complete',
        'selection'                 => 'string|in:manual,automatic',
        'confirm_days_before_start' => 'integer|required_if:attribution,complete',
        'automatic_selection_type'  => 'string|in:fifs,random|required_if:selection,automatic',
        'random_start'              => 'integer|required_if:selection,automatic',
        'validation'                => 'in:manual,automatic',
        'permanent_mission'         => 'boolean',
        'report_limit'              => 'boolean',
        'report_author'             => 'boolean',
        'disabled'                  => 'boolean',
        'standard'                  => 'boolean',
        'noncompliance'             => 'boolean',
        'experience'                => 'boolean',
        'synthetic'                 => 'boolean',
        'show_legend'               => 'boolean',
        'smicer_name'               => 'boolean',
        'mission_name'              => 'boolean',
        'date_visit'                => 'boolean',
        'wave_name'                 => 'boolean',
        'created_by'                => 'integer|read:users',
        'document'                  => 'string',
        'show_score'                => 'boolean',
        'show_score_2'              => 'boolean',
        'show_mission_name'         => 'boolean',
        'payment_delay'             => 'integer',
        'report_mail'               => 'boolean',
        'report_mail_name'          => 'string',
        'report_type'               => 'string',
        'hide_score'                => 'boolean',
        'all_question_mandatory'    => 'boolean',
        'disable_seq_condition'     => 'boolean',
        'exclusion_mail'            => 'boolean'
    ];

    public static function  getURI()
    {
        return 'missions';
    }

    public static function  getName()
    {
        return 'mission';
    }

    public function  getModuleName()
    {
        return 'missions';
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function performDeleteOnModel()
    {
        $this->disabled = self::DISABLED_YES;
        $this->update(['disabled']);
    }

    public function         society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function         survey()
    {
        return $this->belongsTo('App\Models\Survey');
    }

    public function         quiz()
    {
        return $this->belongsTo('App\Models\Survey', 'quiz_id');
    }

    public function         scenario()
    {
        return $this->belongsTo('App\Models\Scenario');
    }

    public function         briefing()
    {
        return $this->belongsTo('App\Models\Briefing');
    }

    public function         waves()
    {
        return $this->belongsToMany('App\Models\Wave', 'wave_mission');
    }

    public function         alerts()
    {
        return $this->belongsToMany('App\Models\Alert', 'mission_alert');
    }

    public function         mission_report_exclusion()
    {
        return $this->belongsToMany('App\Models\Group', 'mission_report_exclusion');
    }

    public function checklists()
    {
        return $this->belongsToMany('App\Models\Checklist', 'mission_checklist')->orderBy('order');
    }

    public function reviewer()
    {
        return $this->belongsTo('App\Models\User', 'reviewer_id');
    }

    public function createdBy()
    {
        return$this->belongsTo('App\Models\User', 'created_by');
    }

    public function         scopeMinimum($query)
    {
        $query->select('id', 'name');
    }

    public function         scopeRelations($query)
    {
        $query->with([
            'survey',
            'scenario',
            'briefing',
            'waves',
            'alerts',
            'checklists',
            'mission_report_exclusion'
        ]);
    }

    public function         afterSync($changes, $id_parent)
    {
        $temps = [];
        $missions = DB::table('wave_mission')
            ->where('wave_mission.wave_id', $id_parent)
            ->whereIn('wave_mission.mission_id', $changes['attached'])
            ->join('wave', 'wave_mission.wave_id', '=', 'wave.id')
            ->join('mission', 'wave_mission.mission_id', '=', 'mission.id')
            ->select('wave_mission.*', 'mission.quantity', 'wave.date_start', 'wave.date_end')
            ->get();
        foreach ($missions as $value) {
            for ($i = 0; $i < $value['quantity']; $i++) {
                $temp = [];
                $temp['date_end'] = $value['date_end'];
                $temp['wave_mission_id'] = $value['id'];
                $temp['date_start'] = $value['date_start'];

                array_push($temps, $temp);
            }
        }
        DB::table('wave_mission_date')->insert($temps);
    }



    protected function updatedEvent()
    {
        $this->_syncGroups();
    }

    /**
     * Synchronise the exclusion groups for report  when updating the mission
     */
    private function _syncGroups()
    {
        $groups = request()->mission_report_exclusion;
        $groups_id = [];

        Validator::make(['groups' => $groups], ['groups' => 'array_array:id'])->passOrDie();
        foreach ($groups as $group) {
            $groups_id[] = $group['id'];
        }
        Validator::make(['groups' => $groups_id], ['groups' => 'int_array|read:groups'])->passOrDie();
        $this->mission_report_exclusion()->sync($groups_id);
    }
}
