<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * App\Models\Log
 *
 * @property int $id
 * @property string $date
 * @property string $type
 * @property int $owner
 * @property int $user_id
 * @property string $action
 * @property string $text
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int|null $level
 * @property int|null $model_id
 * @property string|null $agent
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\log whereUserId($value)
 * @mixin \Eloquent
 */
class Log extends SmiceModel implements iRest, iProtected
{
	protected $table 	  = 'log';

	protected $primaryKey = 'id';

	public $timestamps    = true;

	protected $fillable	  = [
		'date',
		'type',
		'owner',
		'user_id',
		'action',
		'text',
	];

	protected $rules = [
		'date' 	  => 'date|required',
		'type' 	  => 'string|required',
		'owner'	  => 'integer|required',
		'user_id' => 'integer|required',
		'action'  => 'string|required',
		'text'	  => 'text|required',
	];

	protected $hidden = [];

	public static function getURI()
	{
		return 'logs';
	}

	public static function getName()
	{
		return 'log';
	}

	public function getModuleName()
	{
		return 'logs';
	}

	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}
}
