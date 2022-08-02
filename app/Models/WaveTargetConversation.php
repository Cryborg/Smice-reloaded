<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Carbon\Carbon;

/**
 * App\Models\WaveTargetConversation
 *
 * @property int $id
 * @property string $message
 * @property int $wave_target_id
 * @property int $question_id
 * @property string $status
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property bool $private
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\WaveTarget $waveTarget
 * @property-read \App\Models\Society $society
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetConversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetConversation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetConversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetConversation whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetConversation whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetConversation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetConversation whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetConversation wherePrivate($value)
 * @mixin \Eloquent
 */
class WaveTargetConversation extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'wave_target_conversation';

    protected $primaryKey           = 'id';

    public $timestamps = false;

    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_CLOSE = 'close';

    protected $fillable             = [
        'message',
        'wave_target_id',
        'question_id',
        'status',
        'created_by'
    ];

    protected $hidden               = [
        'created_by'
    ];

    protected array $rules                = [
        'message'                  => 'required|string',
        'wave_target_id'           => 'required|integer|read:targets',
        'question_id'              => 'integer|read:questions',
        'status'                   => 'in:' . self::STATUS_IN_PROGRESS . ',' . self::STATUS_CLOSE,
        'created_by'               => 'required|integer|read:users'
    ];
    protected array $list_rows = [
        'private',
        'message',
        'wave_target_id',
        'question_id',
        'status',
        'created_by',
    ];

    public static function getURI()
    {
        return 'conversations';
    }

    public static function getName()
    {
        return 'conversation';
    }

    public function getModuleName()
    {
        return 'conversations';
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function (self $waveTargetConversation) {
            $waveTargetConversation->created_at = Carbon::now();
        });
    }

    public function scopeRelations($query)
    {
        $query->with('createdBy');
    }

    public function waveTarget()
    {
        return $this->belongsTo('App\Models\WaveTarget');
    }

    public function question()
    {
        return $this->belongsTo('App\Models\Question');
    }

    public function society()
    {
        return $this->belongsTo(Society::class, ($this->waveTarget) ? $this->waveTarget->mission->society_id : null);
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
}
