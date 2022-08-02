<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\Alias
 *
 * @property int $id
 * @property mixed|null $filters
 * @property bool|null $allow_proof_access
 * @property bool|null $result_in_percent
 * @property string|null $infos
 * @property int $society_id
 * @property int $created_by
 * @property mixed|null $sequence
 * @property mixed|null $theme
 * @property mixed|null $criteria_a
 * @property mixed|null $criteria_b
 * @property mixed|null $code_totem
 * @property mixed|null $shop
 * @property mixed|null $wave
 * @property mixed|null $brand
 * @property mixed|null $phone
 * @property mixed|null $open_days
 * @property mixed|null $open_hours
 * @property mixed|null $tag
 * @property mixed|null $job
 * @property mixed|null $custom_text1
 * @property mixed|null $custom_text2
 * @property mixed|null $custom_date1
 * @property mixed|null $custom_date2
 * @property mixed|null $custom_date3
 * @property mixed|null $custom_date4
 * @property mixed|null $custom_boolean1
 * @property mixed|null $custom_boolean2
 * @property bool $claim
 * @property mixed|null $criteria
 * @property mixed|null $answer
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereAllowProofAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCodeTotem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCriteria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCriteriaA($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCriteriaB($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereInfos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereOpenDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereOpenHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereResultInPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereShop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereWave($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCustomBoolean1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCustomBoolean2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCustomDate1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCustomDate2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCustomDate3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCustomDate4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCustomText1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCustomText2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereClaim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereAnswer($value)
 * @mixin \Eloquent
 * @property mixed|null $criteria
 * @property mixed|null $answer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Alias whereCriteria($value)
 */
class Alias extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table        = 'alias';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected array $translatable    = [
        'sequence',
        'theme',
        'criteria',
        'criteria_a',
        'criteria_b',
        'code_totem',
        'shop',
        'wave',
        'brand',
        'phone',
        'open_days',
        'open_hours',
        'tag',
        'job',
        'custom_text1',
        'custom_text2',
        'custom_date1',
        'custom_date2',
        'custom_date3',
        'custom_date4',
        'custom_boolean1',
        'custom_boolean2',
    ];

    protected $fillable     = [
        'filters',
        'allow_proof_access',
        'result_in_percent',
        'infos',
        'society_id',
        'created_by',
        'sequence',
        'theme',
        'criteria',
        'criteria_a',
        'criteria_b',
        'code_totem',
        'shop',
        'wave',
        'brand',
        'phone',
        'open_days',
        'open_hours',
        'tag',
        'job',
        'custom_text1',
        'custom_text2',
        'custom_date1',
        'custom_date2',
        'custom_date3',
        'custom_date4',
        'custom_boolean1',
        'custom_boolean2',
        'claim',
        'answer',
    ];

    protected $hidden       = [];

    protected array $list_rows = [
        'filters',
        'allow_proof_access',
        'result_in_percent',
        'infos',
        'society_id',
        'created_by',
        'sequence',
        'theme',
        'criteria',
        'criteria_a',
        'criteria_b',
        'code_totem',
        'shop',
        'wave',
        'brand',
        'phone',
        'open_days',
        'open_hours',
        'tag',
        'job',
        'custom_text1',
        'custom_text2',
        'custom_date1',
        'custom_date2',
        'custom_date3',
        'custom_date4',
        'custom_boolean1',
        'custom_boolean2',
        'claim',
        'answer'
    ];

    protected array $rules = [
        'allow_proof_access' => 'boolean',
        'result_in_percent'  => 'boolean',
        'filters'            => 'array',
        'infos'              => 'string',
        'sequence'           => 'array',
        'theme'              => 'array',
        'criteria'         => 'array',
        'criteria_a'         => 'array',
        'criteria_b'         => 'array',
        'code_totem'         => 'array',
        'shop'               => 'array',
        'wave'               => 'array',
        'brand'              => 'array',
        'phone'              => 'array',
        'open_days'          => 'array',
        'open_hours'         => 'array',
        'tag'                => 'array',
        'job'                => 'array',
        'custom_text1'       => 'string',
        'custom_text2'       => 'string',
        'custom_date1'       => 'string',
        'custom_date2'       => 'string',
        'custom_date3'       => 'string',
        'custom_date4'       => 'string',
        'custom_boolean1'    => 'string',
        'custom_boolean2'    => 'string',
        'answer'             => 'array',
    ];

    public static function getURI()
    {
        return 'alias';
    }

    public static function getName()
    {
        return 'alias';
    }

    public function getModuleName()
    {
        return 'alias';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
}
