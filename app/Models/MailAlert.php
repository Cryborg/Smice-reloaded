<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\MailAlert
 *
 * @property int $id
 * @property int $alert_id
 * @property int $user_id
 * @property bool $sent
 * @property string $start
 * @property string $end
 * @property string $created_at
 * @property string $updated_at
 * @property-read \App\Models\Alert $alert
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailAlert whereAlertId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailAlert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailAlert whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailAlert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailAlert whereSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailAlert whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailAlert whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailAlert whereUserId($value)
 * @mixin \Eloquent
 */
class MailAlert extends SmiceModel implements iREST, iProtected
{
	protected $table            = 'mail_alert';

	protected $primaryKey       = 'id';

	public $timestamps          = false;

	protected $fillable         = [
        'id',
        'alert_id',
        'sent',
        'user_id',
        'start',
        'end',
        'created_at'
	];


	protected $rules            = [
        'alert_id'    => 'integer|required|read:alert'
	];

    public static function getURI()
    {
        return 'mailalerts';
    }

    public static function getName()
    {
        return 'mailalert';
    }

    public function getModuleName()
    {
        return 'mailalerts';
    }

    public function alert()
    {
        return $this->belongsTo('App\Models\alert');
    }
}