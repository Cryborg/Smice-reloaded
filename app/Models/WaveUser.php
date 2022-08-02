<?php

namespace App\Models;

use App\Exceptions\SmiceException;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\iREST;

/**
 * App\Models\WaveUser
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int $wave_target_id
 * @property int $status_id
 * @property string|null $reason
 * @property string|null $explanation
 * @property bool $refused
 * @property bool $retried
 * @property bool $invitation_email
 * @property bool $confirmation_email
 * @property bool $reconfirmation_email
 * @property bool $confirmed
 * @property bool $winner
 * @property bool $started_answer_survey
 * @property string|null $refused_at
 * @property string|null $positioned_at
 * @property string|null $selected_at
 * @property string|null $confirmed_at
 * @property string|null $quizz_answered_at
 * @property string|null $survey_answered_at
 * @property string|null $invalidated_at
 * @property string|null $validated_at
 * @property string|null $read_at
 * @property mixed|null $errors
 * @property mixed|null $score
 * @property string|null $survey_explanation
 * @property bool $offline
 * @property string|null $offline_date
 * @property string|null $online_date
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $invalidation_reason_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WavePosition[] $positions
 * @property-read \App\Models\WaveUserStatus $status
 * @property-read \App\Models\WaveTarget $target
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereConfirmationEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereErrors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereExplanation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereInvalidatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereInvitationEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereOffline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereOfflineDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereOnlineDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser wherePositionedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereQuizzAnsweredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereReconfirmationEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereRefused($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereRefusedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereRetried($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereSelectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereStartedAnswerSurvey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereSurveyAnsweredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereSurveyExplanation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereValidatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereWinner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUser whereInvalidationReasonId($value)
 * @mixin \Eloquent
 */
class WaveUser extends SmiceModel implements iREST
{
    protected $table                = 'wave_user';

    protected $list_table           = 'show_wave_users';

    protected $primaryKey           = 'id';

    protected $fillable             = [
        'user_id',
        'id',
        'wave_target_id',
        'uuid',
        'invitation_email',
        'status_id',
        'score',
        'offline'
    ];

    protected $jsons                = [
        'errors',
        'score'
    ];

    protected $hidden               = [];

    protected array $rules                = [
        'uuid'                  => 'string|required',
        'user_id'               => 'integer|required',
        'status_id'             => 'integer|required',
        'wave_target_id'        => 'integer|required',
        'reason'                => 'string',
        'explanation'           => 'string',
        'invitation_email'      => 'boolean',
        'confirmation_email'    => 'boolean',
        'reconfirmation_email'  => 'boolean',
        'confirmed'             => 'boolean',
        'winner'                => 'boolean',
        'retried'               => 'boolean',
        'started_answer_survey' => 'boolean',
        'changed_visit'         => 'boolean',
        'positioned_at'         => 'date',
        'selected_at'           => 'date',
        'confirmed_at'          => 'date',
        'quizz_answered_at'     => 'date',
        'survey_answered_at'    => 'date',
        'errors'                => 'array',
        'survey_explanation'    => 'string',
        'score'                 => 'array',
        'offline'               => 'boolean',
        'offline_date'          => 'date',
        'online_date'           => 'date'
    ];

    protected array $list_rows            = [
        '*'
    ];

    public static function getURI()
    {
        return 'waveUsers';
    }

    public static function getName()
    {
        return 'waveUser';
    }

    public function target()
    {
        return $this->belongsTo('App\Models\WaveTarget', 'wave_target_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function positions()
    {
        return $this->hasMany('App\Models\WavePosition', 'wave_user_id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\WaveUserStatus');
    }

    public function scopeRelations($query)
    {
        return $query->with([
            'positions',
            'user' => function ($query) {
                $query->minimum();
            }
        ]);
    }

    public function getPositions()
    {
        $positions  = $this->positions->map(function(WavePosition $item)
        {
            return $item->position_date;
        });

        return $positions;
    }

    public function deletePositions()
    {
        $this->positions()->delete();
    }

    public function savePositions($positions, $dates)
    {
        $validator  = Validator::make(['positions' => $positions], ['positions' => 'string_array']);
        $dates      = array_fill_keys(array_values($dates), true);
        $good_dates = [];

        $validator->passOrDie();
        $positions = array_unique($positions);
        foreach ($positions as $position) {
            //if (isset($dates[$position]))
                array_push($good_dates, [
                    'wave_user_id' => $this->getKey(),
                    'position_date' => $position
                ]);
            //else
            //    throw new SmiceException(
            //        SmiceException::HTTP_BAD_REQUEST,
            //        SmiceException::E_VALIDATION,
            //        "The date $position is incorrect."
            //    );
        }

        $this->positions()->delete();

        $this->positions()->createMany($good_dates);
    }
}
