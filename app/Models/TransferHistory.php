<?php

namespace App\Models;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;


/**
 * App\Models\TransferHistory
 *
 * @property int $id
 * @property string $filename
 * @property int $operations
 * @property float $amount
 * @property int $created_by
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PaymentDemand[] $payments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferHistory whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferHistory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferHistory whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferHistory whereOperations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TransferHistory extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table            = 'transfer_history';

    protected $primaryKey       = 'id';

    public $timestamps          = true;

    protected $fillable         = [
        'filename',
        'operations',
        'amount',
        'status',
        'created_at',
        'updated_at'
    ];

    public static $status = [
        'En attente',
        'annulee',
        'traitee'
    ];

    protected $hidden           = [];

    protected $rules            = [
        'filename'      => 'string|required',
        'operations'    => 'integer|required',
        'amount'        => 'decimal|required',
        'status'        => 'integer|required'
    ];

    public static function getURI()
    {
        return 'transfer_history';
    }

    public static function getName()
    {
        return 'transfer_history';
    }

    public function 	   getModuleName()
    {
        return 'transfer_history';
    }

    public function        payments()
    {
        return $this->hasMany('App\Models\Payment', 'sepa_file_id', 'id');
    }
}
