<?php

namespace App\Models;

/**
 * App\Models\LogTarget
 *
 * @property int $id
 * @property int $user_id
 * @property int $shop_id
 * @property int $program_id
 * @property int $wave_id
 * @property string $date
 * @property string $action
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Program $program
 * @property-read \App\Models\Shop $shop
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Wave $wave
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogTarget whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogTarget whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogTarget whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogTarget whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogTarget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogTarget whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogTarget whereWaveId($value)
 * @mixin \Eloquent
 */
class LogTarget extends SmiceModel
{
    protected $table 	  = 'log_target';

	protected $primaryKey = 'id';

	public $timestamps    = true;

	protected $fillable	  = [
		'user_id',
		'shop_id',
		'program_id',
		'wave_id',
		'date',
		'action',
	];

	protected $rules = [
		'user_id' 	 => 'integer|required',
		'shop_id' 	 => 'integer|required',
		'program_id' => 'integer|required',
		'wave_id'    => 'integer|required',
		'date' 		 => 'date|required',
		'action' 	 => 'required|in:proposed,accepted,confirmed'
	];

	protected $hidden = [];

    public static $action = [
		'proposed',
		'accepted',
		'confirmed'
	];

	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}

	public function shop()
	{
		return $this->belongsTo('App\Models\Shop');
	}

	public function program()
	{
		return $this->belongsTo('App\Models\Program');
	}

	public function wave()
	{
		return $this->belongsTo('App\Models\Wave');
	}
}
