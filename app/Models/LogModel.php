<?php

namespace App\Models;

/**
 * App\Models\LogModel
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property string $model
 * @property int $model_id
 * @property string $date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $agent
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogModel whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogModel whereAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogModel whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogModel whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogModel whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogModel whereUserId($value)
 * @mixin \Eloquent
 */
class LogModel extends SmiceModel
{
    protected $table 	  = 'log_model';

	protected $primaryKey = 'id';

	public $timestamps    = true;

    protected $jsons = ['snapshot'];

	protected $fillable	  = [
		'user_id',
		'date',
		'action',
		'model',
		'model_id',
		'agent',
		'snapshot'
	];

	protected array $rules = [
		'user_id'  => 'integer|required',
		'date' 	   => 'date|required',
		'action'   => 'required|in:create,update,delete,save_answer,send_answer,login',
		'model_id' => 'integer|required'
	];

    public static $action = [
		'create',
		'update',
		'delete'
	];

    public static $model = [
		'questionnaire',
		'user',
		'society',
		'axe',
		'survey_item',
		'criteria'
	];

	protected $hidden = [];

	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}
}
