<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\MailLog
 *
 * @property int $id
 * @property string|null $to
 * @property string|null $from
 * @property string|null $status
 * @property string|null $reject_reason
 * @property string|null $subject
 * @property string|null $html
 * @property mixed|null $attachments
 * @property string|null $mandrill_id
 * @property string|null $date_send
 * @property int|null $user_id
 * @property int|null $created_by
 * @property int|null $society_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \App\Models\Society|null $society
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereDateSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereHtml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereMandrillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereRejectReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailLog whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User|null $createdBy
 */
class MailLog extends SmiceModel implements iREST, iProtected
{
    protected $table            = 'mail_log';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $fillable         = [];

    protected array $list_rows        = [
        'to',
        'from',
        'subject',
        'status',
        'date_send'
    ];

    protected array $rules            = [];

    public static function getURI()
    {
        return 'mails';
    }

    public static function getName()
    {
        return 'mail';
    }

    public function getModuleName()
    {
        return 'mails';
    }

    public function     user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function     society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function createdBy()
    {
        return$this->belongsTo('App\Models\User', 'created_by');
    }

    public function     scopeRelations($query)
    {
        $query->with([
            'user' => function($query)
            {
                $query->minimum();
            }
        ]);
    }
}
