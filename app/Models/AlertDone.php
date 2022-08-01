<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AlertDone
 *
 * @property int $id
 * @property string|null $scheduled_alert
 * @property string|null $scheduled_for
 * @property int $alert_id
 * @property int|null $option
 * @property string|null $score_of
 * @property string|null $operand
 * @property float $score
 * @property float $score_result
 * @property bool $condition_ok
 * @property string|null $message_type
 * @property int $message_id
 * @property bool $zip
 * @property int $wave_target_id
 * @property int $shop_id
 * @property int $contact_id
 * @property int $created_by
 * @property string $sent_at
 * @property-read \App\Models\Alert $alert
 * @property-read \App\Models\User $contact
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\Message $message
 * @property-read \App\Models\Shop $shop
 * @property-read \App\Models\WaveTarget $waveTarget
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereAlertId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereConditionOk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereMessageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereOperand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereScheduledAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereScheduledFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereScoreOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereScoreResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertDone whereZip($value)
 * @mixin \Eloquent
 */
class AlertDone extends Model
{
	protected $table            = 'alert_done';

	protected $primaryKey       = 'id';

	public $timestamps          = false;

	protected $fillable         = [
		'scheduled_alert',
		'scheduled_for',
        'alert_id',
        'option',
        'score_of',
        'operand',
        'score',
        'score_result',
        'condition_ok',
        'message_type',
        'message_id',
        'zip',
        'wave_target_id',
        'shop_id',
        'contact_id',
        'created_by',
        'sent_at',
	];

	protected $hidden           = [
	    'created_by'
    ];

	protected $rules            = [
		'scheduled_alert'       => 'nullable|string',
        'scheduled_for'         => 'nullable|date',
        'alert_id'              => 'integer|required|read:alert|exists:alert,id',
        'option'                => 'nullable|integer',
        'score_of'              => 'nullable|string',
        'operand'               => 'nullable|string',
        'score'                 => 'numeric|required|min:0|max:100',
        'score_result'          => 'numeric|required|min:0|max:100',
        'condition_ok'          => 'boolean|required',
        'message_type'          => 'nullable|string',
        'message_id'            => 'integer|required|read:message|exists:message,id',
        'zip'                   => 'boolean|required',
        'wave_target_id'        => 'integer|required|read:waveTarget|exists:wave_target,id',
        'shop_id'               => 'integer|required|read:shop|exists:shop,id',
        'contact_id'            => 'integer|required|read:contact|exists:user,id',
        'created_by'            => 'integer|required|read:createdBy|exists:user,id',
        'sent_at'               => 'date|required',
	];

    public function alert()
    {
    	return $this->belongsTo('App\Models\Alert');
    }

    public function message()
    {
        return $this->belongsTo('App\Models\Message');
    }

    public function waveTarget()
    {
        return $this->belongsTo('App\Models\WaveTarget');
    }

    public function shop()
    {
        return $this->belongsTo('App\Models\Shop');
    }

    public function contact()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User');
    }
}