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
 * @property string $filename
 * @property int $transactions
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SepaFile whereTransactions($value)
 * @mixin \Eloquent
 */
class SepaFile extends SmiceModel implements iRest, iProtected
{
    protected $table = 'sepa_file';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'transactions',
        'filename',
        'amount',
        'data',
        'name',
        'created_at',
        'created_by',
        'status',
    ];

    protected array $rules = [
        'transaction' => 'integer|required',
        'amount' => 'decimal|required',
    ];

    public static function getURI()
    {
        return 'sepa_file';
    }

    public static function getName()
    {
        return 'sepaFile';
    }

    public function getModuleName()
    {
        return 'sepa_file';
    }
}
