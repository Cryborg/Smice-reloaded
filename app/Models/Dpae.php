<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Dpae
 *
 * @property int $id
 * @property int $user_id
 * @property string $request_status
 * @property string $nir
 * @property string $nir_key
 * @property string $birthday
 * @property string $birthday_place
 * @property string $birthday_departement
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $sex_code
 * @property string $startcontrat_date
 * @property string $startcontrat_time
 * @property string $endcontract_date
 * @property string $nature_code
 * @property string $health_service
 * @property string $trial_time
 * @property string $created_at
 * @property string $status
 * @property string $file
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereBirthdayDepartement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereBirthdayPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereEndcontractDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereHealthService($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereNatureCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereNir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereNirKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereRequestStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereSexCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereStartcontratDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereStartcontratTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereTrialTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Dpae whereUserId($value)
 * @mixin \Eloquent
 */
class Dpae extends Model
{
    protected $table = 'dpae';

    protected $primaryKey = 'id';

    public $timestamps = false;
}
