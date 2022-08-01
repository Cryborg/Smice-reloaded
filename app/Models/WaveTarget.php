<?php

namespace App\Models;

use App\Exceptions\SmiceException;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;



/**
 * App\Models\WaveTarget
 *
 * @property int $id
 * @property string $date_start
 * @property string $date_end
 * @property int|null $reviewer_id
 * @property int $wave_id
 * @property int $shop_id
 * @property int $mission_id
 * @property int|null $briefing_id
 * @property int|null $scenario_id
 * @property int|null $survey_id
 * @property int|null $quiz_id
 * @property int|null $user_id
 * @property int|null $reader_id
 * @property int|null $nb_quiz_error
 * @property string $name
 * @property string $type
 * @property string|null $hours
 * @property string $description
 * @property bool $has_briefing
 * @property bool $has_quiz
 * @property bool $answered_quiz
 * @property bool $answered_survey
 * @property bool $read_survey
 * @property mixed $filters
 * @property bool $ask_refund
 * @property bool $ask_proof
 * @property float|null $refund
 * @property int|null $compensation
 * @property string|null $category
 * @property bool $monday
 * @property bool $tuesday
 * @property bool $wednesday
 * @property bool $thursday
 * @property bool $friday
 * @property bool $saturday
 * @property bool $sunday
 * @property string $status
 * @property string|null $picture
 * @property string $date_status
 * @property string|null $visit_date
 * @property string|null $validation_mode
 * @property string|null $uuid
 * @property string|null $pdf_url
 * @property string|null $pdf_url_created_at
 * @property int|null $percentage_completeness
 * @property bool $is_paid
 * @property string $validation
 * @property bool $permanent_mission
 * @property float $frais_kms
 * @property float $score
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $comment
 * @property string|null $cause
 * @property int $payment_delay
 * @property int|null $autopilot_level
 * @property string|null $request_id
 * @property int|null $sign_template
 * @property int|null $sign_rate
 * @property mixed|null $scenario_bis
 * @property float|null $max_refund
 * @property bool $faceblur
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Answer[] $answers
 * @property-read \App\Models\User|null $boss
 * @property-read \App\Models\Briefing|null $briefing
 * @property-read \App\Models\Mission $mission
 * @property-read \App\Models\Survey|null $quiz
 * @property-read \App\Models\User|null $reviewer
 * @property-read \App\Models\Scenario|null $scenario
 * @property-read \App\Models\Shop $shop
 * @property-read \App\Models\Survey|null $survey
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tags[] $tag
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveTargetHistory[] $waveTargetHistory
 * @property-read \App\Models\Tags $tags
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \App\Models\Wave $wave
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PassageProof[] $passageProofs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveUser[] $waveUsers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveTargetConversation[] $waveTargetConversations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Claim[] $claim
 * @property-read \App\Models\Gain $gain
 * @property-read \App\Models\Signature|null $signature
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveTargetConversationGlobal[] $waveTargetConversationsGlobal
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereAnsweredQuiz($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereAnsweredSurvey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereAskProof($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereAskRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereAutomaticAssignation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereBriefingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereCompensation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereDateStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereFraisKms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereFriday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereHasBriefing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereHasQuiz($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereHasUserUuid($uuid)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereMonday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereNbQuizError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget wherePdfUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget wherePdfUrlCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget wherePercentageCompleteness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereQuizId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereReadSurvey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereReaderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereReviewerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereSaturday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereSunday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereThursday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereTuesday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereValidation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereValidationMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereVisitDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereWaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereWednesday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereAutopilotLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget wherePaymentDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget wherePermanentMission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereMaxRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereScenarioBis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereSignRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereSignTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTarget whereFaceblur($value)
 * @mixin \Eloquent
 */
class WaveTarget extends SmiceModel implements iREST, iProtected
{

    use SoftDeletes;

    protected $table                = 'wave_target';

    protected $list_table           = 'show_targets';

    protected $primaryKey           = 'id';

    const STATUS_ACCEPTED = 'accepted';
    const STATUS_ANSWERED = 'answered';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_DOODLE = 'doodle';
    const STATUS_INVALIDATED = 'invalidated';
    const STATUS_OFF = 'off';
    const STATUS_PROPOSED = 'proposed';
    const STATUS_READ = 'read';
    const STATUS_SELECTED = 'selected';
    const STATUS_DEBRIEFED = 'debriefed';
    const STATUS_NOT_DEBRIEFED = 'not debriefed';
    const STATUS_NOT_COMPLIANT = 'not compliant';
    const STATUS_PENDING_VALIDATION = 'pending validation';
    const STATUS_REJECTED = 'rejected';

    const READ_ONLY = ['answered', 'off', 'read', 'debriefed', 'not debriefed', 'not compliant', 'pending validation', 'rejected'];

    const VALIDATION_AUTOMATIC = 'automatic';
    const VALIDATION_MANUAL = 'manual';

    protected $jsons                = [
        'filters',
        'program',
        'scenario'
    ];

    protected $fillable             = [
        'mission_id',
        'wave_id',
        'program_id',
        'shop_id',
        'survey_id',
        'quiz_id',
        'user_id',
        'reader_id',
        'name',
        'type',
        'hours',
        'description',
        'accroche',
        'filters',
        'category',
        'refund',
        'compensation',
        'has_quiz',
        'answered_quiz',
        'answered_survey',
        'has_briefing',
        'briefing_id',
        'scenario_id',
        'scenario_bis',
        'monday',
        'tuesday',
        'wednesday',
        'read_survey',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'date_status',
        'date_start',
        'date_end',
        'status',
        'visit_date',
        'reviewer_id',
        'nb_quiz_error',
        'picture',
        'ask_proof',
        'ask_refund',
        'payment_delay',
        'uuid',
        'pdf_url',
        'pdf_url_created_at',
        'percentage_completeness',
        'is_paid',
        'validation_mode',
        'validation',
        'permanent_mission',
        'frais_kms',
        'score',
        'comment',
        'cause',
        'autopilot_level',
        'reject_reason',
        'request_id',
        'sign_template',
        'sign_rate',
        'max_refund',
        'questions',
        'link',
        'date_exclusion'
    ];

    protected $mass_fillable = [
        'name',
        'description',
        'accroche',
        'type',
        'date_start',
        'date_end',
        'monday',
        'tuesday',
        'wednesday',
        'filters',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'category',
        'refund',
        'compensation',
        'wave_id',
        'shop_id',
        'nb_quiz_error',
        'hours',
        'uuid',
        'is_paid',
        'validation',
        'validation_mode',
        'permanent_mission',
        'frais_kms',
        'score',
        'comment',
        'sign_template',
        'sign_rate',
        'max_refund',
        'link',
        'questions'
    ];

    protected $hidden               = [];

    protected $rules                = [
        'mission_id'                => 'required|integer|read:missions',
        'shop_id'                   => 'required|integer|read:shops',
        'wave_id'                   => 'required|integer|read:waves',
        'program_id'                => 'required|integer|read:programs',
        'name'                      => 'required',
        'type'                      => 'required|in:visit,audit',
        'hours'                     => 'string',
        'accroche'                  => 'string',
        'description'               => 'required',
        'filters'                   => 'required|array',
        'category'                  => 'string',
        'refund'                    => 'numeric',
        'payment_delay'             => 'numeric',
        'compensation'              => 'integer',
        'has_briefing'              => 'boolean',
        'has_quiz'                  => 'boolean',
        'briefing_id'               => 'integer|read:briefings',
        'monday'                    => 'required|boolean',
        'tuesday'                   => 'required|boolean',
        'wednesday'                 => 'required|boolean',
        'thursday'                  => 'required|boolean',
        'friday'                    => 'required|boolean',
        'saturday'                  => 'required|boolean',
        'sunday'                    => 'required|boolean',
        'date_status'               => 'required|date',
        'visit_date'                => 'date',
        'scenario_id'               => 'integer|read:scenarios',
        'survey_id'                 => 'integer|read:surveys',
        'quiz_id'                   => 'integer|read:surveys',
        'user_id'                   => 'integer|read:users',
        'reader_id'                 => 'integer|read:users',
        'reviewer_id'               => 'integer|read:users',
        'date_start'                => 'required|date',
        'date_end'                  => 'required|date|after:date_start',
        'status'                    => 'required|in:accepted,answered,confirmed,doodle,invalidated,off,proposed,read,selected,debriefed,not debriefed,not compliant,pending validation,rejected',
        'nb_quiz_error'             => 'integer',
        'ask_proof'                 => 'boolean',
        'ask_refund'                => 'boolean',
        'uuid'                      => 'string',
        'pdf_url'                   => 'date',
        'pdf_url_created_at'        => 'date',
        'percentage_completeness'   => 'required|integer',
        'is_paid'                   => 'boolean',
        'validation'                => 'in:manual,automatic',
        'permanent_mission'         => 'boolean',
        'frais_kms'                 => 'numeric',
        'score'                     => 'numeric|min:0|max:100',
        'comment'                   => 'string',
        'autopilot_level'           => 'numeric',
        'questions'                 => 'numeric',
        'reject_reason'             => 'string',
        'link'                      => 'json',
    ];

    protected $list_rows = [
        'mission_id',
        'wave_id',
        'program_id',
        'shop_id',
        'survey_id',
        'quiz_id',
        'user_id',
        'reader_id',
        'name',
        'type',
        'hours',
        'description',
        'accroche',
        'filters',
        'category',
        'refund',
        'compensation',
        'has_quiz',
        'answered_quiz',
        'answered_survey',
        'has_briefing',
        'briefing_id',
        'scenario_id',
        'scenario_bis',
        'monday',
        'tuesday',
        'wednesday',
        'read_survey',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'date_status',
        'date_start',
        'date_end',
        'status',
        'visit_date',
        'reviewer_id',
        'nb_quiz_error',
        'picture',
        'ask_proof',
        'ask_refund',
        'payment_delay',
        'uuid',
        'pdf_url',
        'pdf_url_created_at',
        'percentage_completeness',
        'is_paid',
        'validation_mode',
        'validation',
        'permanent_mission',
        'frais_kms',
        'score',
        'comment',
        'cause',
        'autopilot_level',
        'request_id',
        'sign_template',
        'sign_rate',
        'max_refund',
        'questions',
        'link'
    ];

    protected static function boot()
    {
        parent::boot();

        self::updating(function (self $waveTarget) {
            if ($waveTarget->isDirty('status')) {
                $waveTargetHistory = new WaveTargetHistory();
                $waveTargetHistory->wave_target_id = $waveTarget->id;
                $waveTargetHistory->status = $waveTarget->status;
                $waveTargetHistory->action = 'status';
                if ($waveTarget->status == self::STATUS_INVALIDATED) {
                    $waveuser = new WaveUser();
                    $waveuser = $waveuser::select('survey_explanation')->where('user_id', $waveTarget->user_id)->where('wave_target_id', $waveTarget->id)->first();
                    $waveTargetHistory->message = (!empty($waveuser)) ? $waveuser->survey_explanation : NULL;
                }
                if (isset(request()->user)) {
                    $waveTargetHistory->created_by = request()->user->getKey();
                } else {
                    $waveTargetHistory->created_by = 1; //autopilot
                }
                $waveTargetHistory->save();
                //Add passage proof in timeline
                if ($waveTarget->status == self::STATUS_READ) {
                    //clean cause
                    $waveTarget->cause = null;
                    $check_existence = PassageProof::where([
                        'user_id' => request()->user->getKey(),
                        'wave_target_id' => $waveTarget->id,
                        'survey_id' => $waveTarget->survey_id,
                    ])->first();
                    if ($check_existence) {
                        if ($check_existence->url || $check_existence->url2 || $check_existence->url3) {
                            $waveTargetHistory = new WaveTargetHistory();
                            $waveTargetHistory->wave_target_id = $waveTarget->id;
                            $waveTargetHistory->status = $waveTarget->status;
                            $waveTargetHistory->created_by = request()->user->getKey();
                            $waveTargetHistory->action = "PassageProofUrl";
                            $waveTargetHistory->message = $check_existence->url . ";" . $check_existence->url2 . ";" . $check_existence->url3;
                            $waveTargetHistory->save();
                        }
                        if ($check_existence->position) {
                            $waveTargetHistory = new WaveTargetHistory();
                            $waveTargetHistory->wave_target_id = $waveTarget->id;
                            $waveTargetHistory->status = $waveTarget->status;
                            $waveTargetHistory->created_by = request()->user->getKey();
                            $waveTargetHistory->action = "PassageProofPosition";
                            $waveTargetHistory->message = $check_existence->position;
                            $waveTargetHistory->save();
                        }
                        if ($check_existence->signature) {
                            $waveTargetHistory = new WaveTargetHistory();
                            $waveTargetHistory->wave_target_id = $waveTarget->id;
                            $waveTargetHistory->status = $waveTarget->status;
                            $waveTargetHistory->created_by = request()->user->getKey();
                            $waveTargetHistory->action = "PassageProofSignature";
                            $waveTargetHistory->message = $check_existence->signature;
                            $waveTargetHistory->save();
                        }
                    }
                    }
                    
            }
        });
    }

    public static function getURI()
    {
        return 'targets';
    }

    public static function getName()
    {
        return 'target';
    }

    public function getModuleName()
    {
        return 'targets';
    }

    public function wave()
    {
        return $this->belongsTo('App\Models\Wave');
    }

    public function mission()
    {
        return $this->belongsTo('App\Models\Mission');
    }

    public function scenario()
    {
        return $this->belongsTo('App\Models\Scenario');
    }

    public function survey()
    {
        return $this->belongsTo('App\Models\Survey');
    }

    public function quiz()
    {
        return $this->belongsTo('App\Models\Survey', 'quiz_id');
    }

    public function shop()
    {
        return $this->belongsTo('App\Models\Shop');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function briefing()
    {
        return $this->belongsTo('App\Models\Briefing');
    }

    public function program()
    {
        return $this->belongsTo('App\Models\Program');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'wave_user', 'wave_target_id', 'user_id')->withPivot('*');
    }

    public function reader()
    {
        return $this->belongsTo('App\Models\User', 'reader_id')->select('email', 'phone')->minimum();
    }

    public function supervisor()
    {
        return $this->belongsTo('App\Models\User', 'reviewer_id')->select('email', 'phone')->minimum();
    }

    public function gain()
    {
        return $this->belongsTo('App\Models\Gain', 'id', 'wave_target_id');
    }

    public function waveUsers()
    {
        return $this->hasMany('App\Models\WaveUser', 'wave_target_id');
    }

    public function waveTargetConversations()
    {
        return $this->hasMany('App\Models\WaveTargetConversation');
    }

    public function waveTargetConversationsGlobal()
    {
        return $this->hasMany('App\Models\WaveTargetConversationGlobal');
    }

    public function claim()
    {
        return $this->hasMany('App\Models\Claim');
    }

    public function waveTargetHistory()
    {
        return $this->hasMany('App\Models\WaveTargetHistory')->orderBy('created_at');
    }

    public function tags()
    {
        return $this->belongsTo('App\Models\Tags');
    }

    public function tag()
    {
        return $this->belongsToMany('App\Models\Tags', 'wave_target_tags', 'wave_target_id', 'tag_id');
    }

    public function answers()
    {
        return $this->hasMany('App\Models\Answer');
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function passageProofs()
    {
        return $this->hasMany('App\Models\PassageProof');
    }

    public function signature()
    {
        return $this->belongsTo('App\Models\Signature', 'request_id', 'request_id');
    }

    public function scopeRelations($query)
    {
        return $query->with(
            'wave',
            'scenario',
            'briefing',
            'mission.checklists',
            'survey',
            'shop',
            'user',
            'supervisor',
            'waveTargetConversations.createdBy',
            'waveTargetHistory.createdBy',
            'waveTargetConversationsGlobal.createdBy',
            'claim',
            'signature'
        );
    }

    public function scopeWhereHasUserUuid($query, $uuid)
    {
        //get wave_target_id
        $waveUser = new WaveUser();

        $waveUser = $waveUser->where('uuid', $uuid)->first();

        if (!$waveUser) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_FOUND,
                SmiceException::E_RESOURCE,
                'Wave user not found'
            );
        }

        $target     = $query->where('id', $waveUser->wave_target_id)->whereHas('waveUsers', function ($query) use ($uuid) {
            $query->where('wave_user.uuid', $uuid);
        })->with([
            'waveUsers' => function ($query) use ($uuid) {
                $query->where('wave_user.uuid', $uuid);
                $query->with('positions');
            }
        ])->first();

        if (!$target) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_FOUND,
                SmiceException::E_RESOURCE
            );
        }

        return $target;
    }

    protected function _getDateRange($start, $end)
    {
        $end = new Carbon($end);
        $end->addDay(1);

        $period = new \DatePeriod(new \DateTime($start), new \DateInterval('P1D'), new \DateTime($end));

        return $period;
    }

    protected function _getDay($date)
    {
        $day = date('l', $date);

        return strtolower($day);
    }

    /**
     * Check if the user can still position himself for this mission
     * @return bool
     */
    public function isAvailable()
    {
        return ($this->user_id) ? false : true;
    }

    /**
     *
     * Get the available dates for the target
     * @return array
     */
    public function getAvailableDates()
    {
        $range      = $this->_getDateRange($this->date_start, $this->date_end);
        $dates      = [];
        $date_exclusion = json_decode($this->date_exclusion, true);
        if ($date_exclusion === null)
            $date_exclusion = [];

        foreach ($range as $date) {
            $day = $this->_getDay($date->getTimestamp());

            if ($this->{$day} && $this->shop->{$day} && array_search($date->format('Y-m-d'), $date_exclusion) === false) {
                array_push($dates, $date->format('Y-m-d'));
            }
        }

        return $dates;
    }

    /**
     * Return the global score of the target.
     * @return mixed|null
     */
    public function getScore()
    {
        $result     = DB::table('show_wave_target_scoring')
            ->select('score')
            ->where('id', $this->getKey())
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the score for one theme of the target.
     * @param $theme_id
     * @return mixed|null
     */
    public function     getThemeScore($theme_id)
    {
        $result         = DB::table('show_scoring')
            ->selectRaw('SUM(question_score) / SUM(question_weight) as score')
            ->where([
                'wave_target_id' => $this->getKey(),
                'theme_id' => $theme_id
            ])
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the score for one job of the target.
     * @param $job_id
     * @return mixed|null
     */
    public function     getJobScore($job_id)
    {
        $result         = DB::table('show_scoring')
            ->selectRaw('SUM(question_score) / SUM(question_weight) as score')
            ->where([
                'wave_target_id' => $this->getKey(),
                'job_id' => $job_id
            ])
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the score for one criteria a of the target.
     * @param $criteria_a_id
     * @return mixed|null
     */
    public function     getCriteriaAScore($criteria_a_id)
    {
        $result         = DB::table('show_scoring')
            ->selectRaw('SUM(question_score) / SUM(question_weight) as score')
            ->where([
                'wave_target_id' => $this->getKey(),
                'criteria_a_id' => $criteria_a_id
            ])
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the score for one criteria b of the target.
     * @param $criteria_b_id
     * @return mixed|null
     */
    public function     getCriteriaBScore($criteria_b_id)
    {
        $result         = DB::table('show_scoring')
            ->selectRaw('SUM(question_score) / SUM(question_weight) as score')
            ->where([
                'wave_target_id' => $this->getKey(),
                'criteria_b_id' => $criteria_b_id
            ])
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the score for one criteria of the target.
     * @param $criteria_id
     * @return mixed|null
     */
    public function     getCriteriaScore($criteria_id)
    {
        $result         = DB::table('show_scoring')
            ->selectRaw('SUM(question_score) / SUM(question_weight) as score')
            ->where([
                'wave_target_id' => $this->getKey(),
                'criteria_id' => $criteria_id
            ])
            ->first();

        return ($result) ? $result['score'] : null;
    }
}