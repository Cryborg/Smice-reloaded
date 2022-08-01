<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\SepaFile
 *
 * @property int $id
 * @property int $transaction
 * @property float $amount
 * @property string|null $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $name
 * @property int|null $created_by
 * @property string|null $status
 * @property string|null $filename
 * @property int|null $transactions
 * @property string|null $url
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereTransaction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherFile whereTransactions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherFile whereUrl($value)
 * @mixin \Eloquent
 */
class VoucherFile extends SmiceModel implements iRest, iProtected
{
    protected $table = 'voucher_file';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'transactions',
        'amount',
        'url',
        'filename',
        'created_by',
        'created_at',
        'status',
    ];

    public static $status = [
        'En attente',
        'Annulée',
        'Traitée'
    ];

    protected $rules = [
        'transactions' => 'integer|required',
        'amount' => 'decimal|required',
    ];

    public static function getURI()
    {
        return 'voucher_file';
    }

    public static function getName()
    {
        return 'voucherFile';
    }

    public function getModuleName()
    {
        return 'voucher_file';
    }
}