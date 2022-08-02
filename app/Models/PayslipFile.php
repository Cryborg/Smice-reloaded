<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\PayslipFile
 *
 * @property int $id
 * @property string|null $filename
 * @property int|null $transactions
 * @property float|null $amount
 * @property string|null $url
 * @property string|null $status
 * @property int|null $created_by
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PayslipFile whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PayslipFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PayslipFile whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PayslipFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PayslipFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PayslipFile whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PayslipFile whereTransactions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PayslipFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PayslipFile whereUrl($value)
 * @mixin \Eloquent
 */
class PayslipFile extends SmiceModel implements iRest, iProtected
{
    protected $table = 'payslip_file';

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

    protected array $rules = [
        'transactions' => 'integer|required',
        'amount' => 'decimal|required',
    ];

    public static function getURI()
    {
        return 'payslip_file';
    }

    public static function getName()
    {
        return 'payslipFile';
    }

    public function getModuleName()
    {
        return 'payslip_file';
    }
}
