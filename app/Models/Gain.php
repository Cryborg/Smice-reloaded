<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Gain
 *
 * @property int $id
 * @property int $user_id
 * @property int $wave_target_id
 * @property string $survey_validate_at
 * @property float|null $amount
 * @property string|null $frais_kms
 * @property int $society_id
 * @property string|null $visit_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property float|null $compensation
 * @property int|null $payment_id
 * @property int|null $voucher_id
 * @property float|null $salary
 * @property int|null $payslip_id
 * @property-read \App\Models\Payment|null $payment
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User $user
 * @property-read \App\Models\WaveTarget $waveTarget
 * @property-read \App\Models\Voucher|null $voucher
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereCompensation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereFraisKms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereSurveyValidateAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereVisitDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereVoucherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain wherePayslipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Gain whereSalary($value)
 * @mixin \Eloquent
 */
class Gain extends SmiceModel implements iRest, iProtected
{
    protected $table = 'gain';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'wave_target_id',
        'survey_validate_at',
        'amount',
        'compensation',
        'salary',
        'frais_kms',
        'society_id',
        'payment_id',
        'visit_date',
    ];

    protected array $rules = [

    	'user_id'			 => 'integer|required',
    	'wave_target_id'	 => 'integer|required',
    	'survey_validate_at' => 'date|required',
    	'amount'			 => 'integer|required',
        'compensation'       => 'integer',
    	'society_id'    	 => 'integer|required',
        'visit_date'         => 'date|required'
    ];

    protected $hidden = [];

    public static function getURI()
    {
        return 'gains';
    }

    public static function getName()
    {
        return 'gain';
    }

    public function getModuleName()
    {
        return 'gains';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }

    public function voucher()
    {
        return $this->belongsTo('App\Models\Voucher');
    }

    public function payslip()
    {
        return $this->belongsTo('App\Models\Payslip');
    }

    public function waveTarget()
    {
        return $this->belongsTo('App\Models\WaveTarget');
    }
}
