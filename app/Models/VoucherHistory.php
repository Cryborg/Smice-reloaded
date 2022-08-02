<?php

namespace App\Models;


/**
 * App\Models\VoucherHistory
 *
 * @property int $id
 * @property string $filename
 * @property int $operations
 * @property float $amount
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PaymentDemand[] $payments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherHistory whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherHistory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherHistory whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherHistory whereOperations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VoucherHistory extends SmiceModel
{
    protected $table = 'voucher_history';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'filename',
        'operations',
        'amount',
        'created_by',
        'status',
        'created_at',
        'updated_at'
    ];

    public static $status = [
        'En attente',
        'annulee',
        'traitee'
    ];

    protected $hidden = [];

    protected array $rules = [
        'filename' => 'string|required',
        'operations' => 'integer|required',
        'amount' => 'decimal|required',
        'created_by' => 'integer|required',
        'status' => 'integer|required'
    ];

    public static function getURI()
    {
        return 'voucher_history';
    }

    public static function getName()
    {
        return 'voucher_history';
    }

    public function getModuleName()
    {
        return 'voucher_history';
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Payment', 'voucher_file_id', 'id');
    }
}
