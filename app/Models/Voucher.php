<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * App\Models\PaymentDemand
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
 * @property string|null $ask_voucher_date
 * @property string|null $scheduled_date_voucher
 * @property string|null $voucher_done_date
 * @property string|null $voucher_status_date
 * @property int|null $voucher_status_id
 * @property int|null $voucher_file_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gain[] $AmountGain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gain[] $gains
 * @property-read \App\Models\TransferStatus|null $status
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VoucherFile $VoucherFile
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand whereAskPaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand wherePaymentDoneDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand whereScheduledDatePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand whereSepaFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand whereTransferStatusDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand whereTransferStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PaymentDemand whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Voucher whereAskVoucherDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Voucher whereScheduledDateVoucher($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Voucher whereVoucherDoneDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Voucher whereVoucherFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Voucher whereVoucherStatusDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Voucher whereVoucherStatusId($value)
 * @mixin \Eloquent
 */
class Voucher extends SmiceModel implements iRest, iProtected
{
    protected $table = 'voucher';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'ask_voucher_date',
        'scheduled_date_voucher',
        'voucher_done_date',
        'created_at',
        'updated_at',
        'created_at',
        'voucher_status_id',
        'voucher_file_id',
    ];

    protected array $rules = [
        'user_id' => 'integer|required',
        'ask_voucher_date' => 'date|required',
        'voucher_status_id' => 'integer|required',
        'scheduled_date_voucher' => 'string|required',
    ];

    public static $types = [
        'Cheque cadeaux'
    ];

    protected $hidden = [];

    protected $with = ['status'];


    public static function getURI()
    {
        return 'vouchers';
    }

    public static function getName()
    {
        return 'voucher';
    }

    public function getModuleName()
    {
        return 'vouchers';
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
        return $this->belongsTo('App\Models\VoucherStatus', 'voucher_status_id', 'id');
    }

    public function VoucherFile()
    {
        return $this->belongsTo('App\Models\VoucherFile', 'voucher_files_id', 'id');
    }


}
