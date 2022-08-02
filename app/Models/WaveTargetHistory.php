<?php

namespace App\Models;

/**
 * App\Models\WaveTargetHistory
 *
 * @property int $id
 * @property int $wave_target_id
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property string|null $message
 * @property int|null $created_by
 * @property string|null $action
 * @property-read \App\Models\WaveTarget $waveTarget
 * @property-read \App\Models\User|null $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetHistory whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetHistory whereInvalidatedReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetHistory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetHistory whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetHistory whereMessage($value)
 * @mixin \Eloquent
 */
class WaveTargetHistory extends SmiceModel
{
    protected $table                = 'wave_target_history';

    protected $primaryKey           = 'id';

    public $timestamps              =  false;

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

    protected $fillable             = [
        'wave_target_id',
        'status',
        'message',
    ];

    protected $hidden           = [
        'created_by'
    ];

    protected array $rules                = [
        'wave_target_id'            => 'required|integer|exists:wave_target,id',
        'status'                    => 'required|in:accepted,answered,confirmed,doodle,invalidated,off,proposed,read,selected,debriefed,not debriefed,not compliant,pending validation,rejected',
        'message'                   => 'string',
        'created_by'                => 'integer|read:createdBy',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    public function waveTarget()
    {
        return $this->belongsTo('App\Models\WaveTarget', 'wave_target_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
}
