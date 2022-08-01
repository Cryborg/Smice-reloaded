<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Payslip
 *
 * @property int $id
 * @property int $request_id
 * @property int $user_id
 * @property string|null $payslip_done_date
 * @property int $payslip_file_id
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @property float|null $salary
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payslip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payslip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payslip wherePayslipDoneDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payslip wherePayslipFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payslip whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payslip whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payslip whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Payslip whereUserId($value)
 * @mixin \Eloquent
 */
class Payslip extends Model
{
    protected $table = 'payslip';

    protected $primaryKey = 'id';

    public $timestamps = true;
}
