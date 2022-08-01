<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Signature
 *
 * @property int $id
 * @property string|null $request_id
 * @property int|null $wave_target_id
 * @property int|null $user_id
 * @property string|null $request_status
 * @property string|null $action_time
 * @property string|null $modified_time
 * @property bool|null $is_delete
 * @property int|null $expiration_day
 * @property string|null $sign_submitted_time
 * @property string|null $owner_first_name
 * @property string|null $expire_by
 * @property string|null $owner_email
 * @property string|null $action_type
 * @property string|null $recipient_email
 * @property string|null $recipient_phonenumber
 * @property string|null $recipient_name
 * @property string|null $action_status
 * @property string|null $recipient_countrycode
 * @property string|null $created_time
 * @property bool|null $email_reminders
 * @property int|null $document_id
 * @property float|null $sign_percentage
 * @property bool $read_info_after_completed
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SignatureHistory[] $signatureHistory
 * @property-read \App\Models\WaveTarget|null $waveTarget
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereActionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereActionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereActionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereEmailReminders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereExpirationDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereExpireBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereIsDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereModifiedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereOwnerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereOwnerFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereReadInfoAfterCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereRecipientCountrycode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereRecipientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereRecipientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereRecipientPhonenumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereRequestStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereSignPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereSignSubmittedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Signature whereWaveTargetId($value)
 * @mixin \Eloquent
 */
class Signature extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'signature';

    protected $primarykey           = 'id';

    public $timestamps              = false;

    protected $fillable             = [
        'id',
        'wave_target_id',
        'request_id',
        'request_status',
        'action_time',
        'modified_time',
        'is_delete',
        'expiration_day',
        'sign_submitted_time',
        'owner_first_name',
        'sign_percentage',
        'expire_by',
        'owner_email',
        'action_type',
        'recipient_email',
        'recipient_phonenumber',
        'recipient_name',
        'action_status',
        'recipient_countrycode',
        'created_time',
        'email_reminders'
    ];

    protected $hidden               = [];

    protected $list_rows            = [
    ];

    protected $rules                = [
    ];

    public static function getURI()
    {
        return 'signature';
    }

    public static function getName()
    {
        return 'signature';
    }

    public function getModuleName()
    {
        return 'signatures';
    }

    public function waveTarget()
    {
        return $this->belongsTo('App\Models\WaveTarget');
    }

    public function signatureHistory()
    {
        return $this->belongsToMany('App\Models\SignatureHistory', 'SignatureHistory');
    }
}
