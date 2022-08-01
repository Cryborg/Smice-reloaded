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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gain[] $AmountGain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gain[] $gains
 * @property-read \App\Models\TransferStatus|null $status
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VoucherFile $SepaFile
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
 * @mixin \Eloquent
 */
class VoucherDemand extends SmiceModel implements iRest, iProtected
{
    protected $table = 'voucher_demand';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'ask_voucher_date',
        'scheduled_date_voucher',
        'voucher_done_date',
        'voucher_status_id',
        'voucher_file_id',
        'created_at',
        'updated_at',
    ];

    protected $rules = [
        'user_id' => 'integer|required',
        'ask_voucher_date' => 'date|required',
        'voucher_done_date' => 'date|required',
        'voucher_status_id' => 'integer|required',
        'scheduled_date_voucher' => 'date|required',
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
        return $this->belongsTo('App\Models\VoucherStatus', 'voucher_status_id', 'id');
    }

    public function SepaFile()
    {
        return $this->belongsTo('App\Models\VoucherFile', 'voucher_file_id', 'id');
    }


}