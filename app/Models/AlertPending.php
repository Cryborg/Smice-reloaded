<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AlertPending
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
 * @property string|null $message_type
 * @property int $message_id
 * @property bool $zip
 * @property int $wave_target_id
 * @property int $shop_id
 * @property int $created_by
 * @property string $checked_at
 * @property-read \App\Models\Alert $alert
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\Message $message
 * @property-read \App\Models\Shop $shop
 * @property-read \App\Models\WaveTarget $waveTarget
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereAlertId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereMessageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereOperand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereScheduledAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereScheduledFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereScoreOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereScoreResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertPending whereZip($value)
 * @mixin \Eloquent
 */
class AlertPending extends Model
{
	protected $table            = 'alert_pending';

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
        'message_type',
        'message_id',
        'zip',
        'wave_target_id',
        'shop_id',
        'created_by',
        'checked_at',
        'disable'
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
        'message_type'          => 'nullable|string',
        'message_id'            => 'integer|required|read:message|exists:message,id',
        'zip'                   => 'boolean|required',
        'wave_target_id'        => 'integer|required|read:waveTarget|exists:wave_target,id',
        'shop_id'               => 'integer|required|read:shop|exists:shop,id',
        'created_by'            => 'integer|required|read:createdBy|exists:user,id',
        'checked_at'            => 'date|required',
        'disable'               => 'boolean|required',
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

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User');
    }
}