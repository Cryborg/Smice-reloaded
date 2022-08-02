<?php

namespace App\Models;

/**
 * App\Models\LogGain
 *
 * @property int $id
 * @property int $user_id
 * @property int $wave_target_id
 * @property float|null $refund
 * @property float|null $compensation
 * @property int|null $frais_km
 * @property string $action
 * @property string $date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\WaveTarget $waveTarget
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogGain whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogGain whereCompensation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogGain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogGain whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogGain whereFraisKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogGain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogGain whereRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogGain whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogGain whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogGain whereWaveTargetId($value)
 * @mixin \Eloquent
 */
class LogGain extends SmiceModel
{
    protected $table 	  = 'log_gain';

	protected $primaryKey = 'id';

	public $timestamps    = true;

	protected $fillable	  = [
		'user_id',
		'wave_target_id',
		'refund',
		'compensation',
		'frais_km',
		'date',
		'action'
	];

	protected array $rules = [
		'user_id' 		 => 'integer|required',
		'wave_target_id' => 'integer|required',
		'refund' 		 => 'integer|required',
		'compensation'   => 'integer|required',
		'action' 		 => 'in:create,update,delete',
		'date' 			 => 'date|required'
	];

    public static $action = [
		'create',
		'update',
		'delete'
	];

	protected $hidden = [];

	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}

	public function waveTarget()
	{
		return $this->belongsTo('App\Models\WaveTarget');
	}
}
