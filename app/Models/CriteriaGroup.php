<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;
use App\Models\LogModel;
use Carbon\Carbon;

/**
 * App\Models\Criteria
 *
 * @property int $id
 * @property mixed $name
 * @property int|null $created_by
 * @property int $society_id
 * @property int|null $weight
 * @property-read \App\Models\Society $society
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Criteria whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Criteria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Criteria whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Criteria whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Criteria whereWeight($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User|null $createdBy
 */
class CriteriaGroup extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table = 'criteria_group';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $jsons = [
        'name'
    ];

    protected array $translatable = [
        'name'
    ];

    protected $fillable = [
        'name',
        'society_id',
    ];

    protected $hidden = [
        'society_id',
    ];

    protected $rules = [
        'society_id' => 'integer|required',
    ];

    protected $list_rows = [
        'id',
        'society_id',
        'name',
    ];



    public static function getURI()
    {
        return 'criterion_group';
    }

    public static function getName()
    {
        return 'criteria_group';
    }

    public function getModuleName()
    {
        return 'criterion_group';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }


}
