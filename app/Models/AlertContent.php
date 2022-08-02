<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\AlertContent
 *
 * @property int $id
 * @property string|null $uuid
 * @property int $mail_id
 * @property int $alert_variables_id
 * @property int $user_id
 * @property mixed $filters
 * @property string|null $result
 * @property string $start
 * @property string $end
 * @property string $created_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertContent whereAlertVariablesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertContent whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertContent whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertContent whereMailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertContent whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertContent whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertContent whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AlertContent whereUuid($value)
 * @mixin \Eloquent
 */
class AlertContent extends SmiceModel implements iREST, iProtected
{
	protected $table            = 'alert_content';

	protected $primaryKey       = 'id';

	protected $jsons            = ['filters'];

	public $timestamps          = false;

	protected $fillable         = [
		'uuid',
        'mail_id',
        'alert_variables_id',
        'filters',
        'result',
        'start',
        'end',
        'user_id',
        'created_at'
	];


	protected array $rules            = [
		'mail_id'                  => 'integer|required|read:mail_alert',
		'mail_alert'            => 'uuid|required',
        'alert_variables_id'    => 'integer|required|read:alert_variables',
	];

    public static function getURI()
    {
        return 'AlertContents';
    }

    public static function getName()
    {
        return 'AlertContent';
    }

    public function user()
    {
        return $this->belongsTo('App\Models\user');
    }

    public function getModuleName()
    {
        return 'AlertContents';
    }
}
