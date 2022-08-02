<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Alert
 *
 * @property int $id
 * @property int $society_id
 * @property string $name
 * @property int $option
 * @property string $event
 * @property int|null $survey_id
 * @property int|null $sequence_id
 * @property int|null $question_id
 * @property int|null $job_id
 * @property int|null $theme_id
 * @property int|null $criteria_a_id
 * @property int|null $criteria_b_id
 * @property string $operand
 * @property float $score
 * @property string $scheduled_for
 * @property int $hours
 * @property string|null $date
 * @property string $send_by
 * @property bool $report_attachment
 * @property int|null $mail_template_id
 * @property string|null $text_message
 * @property mixed $recipient
 * @property bool $send_to_auditor
 * @property bool $limit_user_with_right
 * @property int $created_by
 * @property string $created_at
 * @property string $type
 * @property string|null $hook
 * @property bool $send_to_shop
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\CriteriaA|null $criteriaA
 * @property-read \App\Models\CriteriaB|null $criteriaB
 * @property-read \App\Models\Job|null $job
 * @property-read \App\Models\MailTemplate|null $mailTemplate
 * @property-read \App\Models\Question|null $question
 * @property-read \App\Models\Sequence|null $sequence
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\Survey|null $survey
 * @property-read \App\Models\Theme|null $theme
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AlertVariable[] $alertVariables
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereCriteriaAId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereCriteriaBId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereLimitUserWithRight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereMailTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereOperand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereRecipient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereReportAttachment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereScheduledFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereSendBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereSendToAuditor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereSequenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereTextMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereHook($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alert whereSendToShop($value)
 * @mixin \Eloquent
 */
class Alert extends SmiceModel implements iREST, iProtected
{

    const SCHEDULED_FOR_NOW = 'now';
    const SCHEDULED_FOR_HOURS = 'hours';
    const SCHEDULED_FOR_END_WAVE_BY_SHOP = 'end_wave_by_shop';
    const SCHEDULED_FOR_END_WAVE_ALL_SHOPS = 'end_wave_all_shops';
    const SCHEDULED_FOR_DATE = 'date';

    const TYPE_LAUNCH = 'launch'; // Nouvelle visite disponible
    const TYPE_ASSIGN_MISSION = 'assign_mission';
    const TYPE_BEFORE_VISIT = 'before_visit'; // Rappel avant la visite
    const TYPE_SURVEY_LOCK = 'survey_answered'; // Questionnaire répondu
    const TYPE_SURVEY_READ = 'survey_read'; // Questionnaire relu
    const TYPE_SURVEY_EMPTY = 'survey_empty'; // Questionnaire non répondu
    const TYPE_SURVEY_INVALIDATED = 'survey_invalidated'; // Questionnaire non répondu
    const TYPE_SURVEY_INVALIDATED_WAITING = 'survey_invalidated_waiting'; // Questionnaire invalidé sans retour
    const TYPE_SURVEY_CANCEL = 'survey_cancel'; // Mission abandonnée
    const TYPE_WAVE = 'wave';
    const TYPE_MISSION = 'mission';
    const TYPE_QUESTION = 'question';
    const TYPE_THEME = 'theme';
    const TYPE_JOB = 'job';
    const TYPE_CRITERION_A = 'criteria_a';
    const TYPE_CRITERION_B = 'criteria_b';

    // Options :
    // 1. Pilotage de mon programme
    // 2. Animation de mon programme
    // 3. Envoi périodique

	protected $table            = 'alert';

	protected $primaryKey       = 'id';

	protected $jsons            = ['recipient'];

	public $timestamps          = false;

	protected $fillable         = [
		'society_id',
		'name',
        'option',
        'event',
        'survey_id',
        'sequence_id',
        'question_id',
        'criteria_a_id',
        'criteria_b_id',
        'job_id',
        'theme_id',
        'operand',
        'score',
        'scheduled_for',
        'hours',
        'date',
        'send_by',
        'report_attachment',
        'mail_template_id',
        'text_message',
        'recipient',
        'send_to_auditor',
        'limit_user_with_right',
        'created_by',
        'type',
        'hook',
        'send_to_shop',
        'reports_type',
        'mail_subject'
	];

	protected $hidden           = [
        'society_id',
	    'created_by'
    ];

	protected array $rules            = [
		'society_id'            => 'integer|required|read:societies',
        'name'                  => 'string|required|unique_with:mission,society_id,{id}',
        'option'                => 'integer|required|min:1|in:1,2,3',
        'event'                 => 'string|required|in:launch,before_visit,survey_answered,survey_read,survey_empty,survey_invalidated,survey_invalidated_waiting,survey_cancel,wave,mission,question,theme,job,criterion_a,criterion_b',
        'survey_id'             => 'integer|required|read:surveys',
        'sequence_id'           => 'integer|required|read:sequences',
        'question_id'           => 'integer|required|read:questions',
        'criteria_a_id'         => 'integer|required|read:criterionA',
        'criteria_b_id'         => 'integer|required|read:criterionB',
        'job_id'                => 'integer|required|read:jobs',
        'theme_id'              => 'integer|required|read:themes',
        'operand'               => 'string|required|in:superior,inferior',
        'score'                 => 'numeric|required|min:0',
        'scheduled_for'         => 'string|required|in:now,hours,end_wave_by_shop,end_wave_all_shop,date',
        'hours'                 => 'integer|required|min:0',
        'date'                  => 'string|required',
        'send_by'               => 'string|required|in:mail,text',
        'report_attachment'     => 'boolean|required',
        'mail_template_id'      => 'integer|required|read:mail_templates',
        'text_message'          => 'string|required',
        'recipient'             => 'array|required',
        'send_to_auditor'       => 'boolean|required',
        'limit_user_with_right' => 'boolean|required',
        'created_by'            => 'integer|required|read:users',
        'type'                  => 'string|required',
        'hook'                  => 'string',
        'mail_subject'          => 'string',
        'send_to_shop'          => 'boolean|required',
	];

    public static function getURI()
    {
        return 'alerts';
    }

    public static function getName()
    {
        return 'alert';
    }

    public function getModuleName()
    {
        return 'alerts';
    }

    public function scopeRelations($query)
    {
        $query->with('alertVariables');
    }

    public function society()
    {
    	return $this->belongsTo('App\Models\Society');
    }

    public function survey()
    {
        return $this->belongsTo('App\Models\Survey');
    }

    public function sequence()
    {
        return $this->belongsTo('App\Models\Sequence');
    }

    public function question()
    {
        return $this->belongsTo('App\Models\Question');
    }

    public function criteriaA()
    {
        return $this->belongsTo('App\Models\CriteriaA');
    }

    public function criteriaB()
    {
        return $this->belongsTo('App\Models\CriteriaB');
    }

    public function job()
    {
        return $this->belongsTo('App\Models\Job');
    }

    public function theme()
    {
        return $this->belongsTo('App\Models\Theme');
    }

    public function mailTemplate()
    {
        return $this->belongsTo('App\Models\MailTemplate');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function alertVariables()
    {
        return $this->hasMany('App\Models\AlertVariable');
    }
}
