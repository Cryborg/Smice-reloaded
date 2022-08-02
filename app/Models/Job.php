<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\Job
 *
 * @property int $id
 * @property mixed $name
 * @property int|null $created_by
 * @property int $society_id
 * @property int|null $order
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User|null $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Job whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Job whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Job whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Job whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Job whereOrder($value)
 * @mixin \Eloquent
 */
class Job extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table = 'job';

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
        'created_by'
    ];

    protected $hidden = [
        'society_id',
        'created_by'
    ];

    protected array $rules = [
        'name' => 'array|required',
        'society_id' => 'integer|required',
        'created_by' => 'integer|required'
    ];

    protected array $list_rows = [
        'society_id',
        'name',
        'created_by'
    ];

    public static function getURI()
    {
        return 'jobs';
    }

    public static function getName()
    {
        return 'job';
    }

    public function getModuleName()
    {
        return 'jobs';
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
