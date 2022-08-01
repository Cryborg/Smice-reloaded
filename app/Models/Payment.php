<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Payment
 *
 * @property int $id
 * @property int $user_id
 * @property string $ask_payment_date
 * @property string|null $payment_done_date
 * @property string $notes
 * @property int|null $sepa_file_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int|null $transfer_status_id
 * @property string|null $scheduled_date_payment
 * @property string|null $transfer_status_date
 * @property int|null $society_id
 * @property int|null $voucher_status_id
 * @property string|null $voucher_status_date
 * @property string|null $voucher_send_date
 * @property int|null $voucher_file_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gain[] $AmountGain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gain[] $gains
 * @property-read \App\Models\TransferStatus|null $status
 * @property-read \App\Models\User $user
 * @property-read \App\Models\SepaFile $SepaFile
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereAskPaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment wherePaymentDoneDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereScheduledDatePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereSepaFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereTransferStatusDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereTransferStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereVoucherFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereVoucherSendDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereVoucherStatusDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payment whereVoucherStatusId($value)
 * @mixin \Eloquent
 */
class Payment extends SmiceModel implements iRest, iProtected
{
    protected $table = 'payment';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'ask_payment_date',
        'payment_done_date',
        'transfer_status_id',
        'notes',
        'sepa_file_id',
        'created_at',
        'scheduled_date_payment',
        'transfer_status_date',
    ];

    protected $rules = [
        'user_id' => 'integer|required',
        'ask_payment_date' => 'date|required',
        'payment_done_date' => 'date|required',
        'transfer_status_id' => 'integer|required',
        'notes' => 'string|required',
        'scheduled_date_payment' => 'string|required',
    ];

    public static $types = [
        'Remboursement',
        'Cheque cadeaux'
    ];

    protected $hidden = [];

    public static function getURI()
    {
        return 'payments';
    }

    public static function getName()
    {
        return 'payment';
    }

    public function getModuleName()
    {
        return 'payments';
    }

    public function AmountGain()
    {
        return $this->hasMany('App\Models\Gain')->select(['id']);
    }

    public function gains()
    {
        return $this->hasMany('App\Models\Gain');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\TransferStatus', 'transfer_status_id', 'id');
    }

    public function SepaFile()
    {
        return $this->belongsTo('App\Models\SepaFile', 'sepa_files_id', 'id');
    }
}