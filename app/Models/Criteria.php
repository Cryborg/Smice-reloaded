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
class Criteria extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table = 'criteria';

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
        'created_by',
        'criteria_group_id'
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
        'criteria_group_id',
        'created_by'
    ];

    public static $history = [
        'name'
    ];

    public static function getURI()
    {
        return 'criterion';
    }

    public static function getName()
    {
        return 'criteria';
    }

    public function getModuleName()
    {
        return 'criterion';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function group()
    {
        return $this->belongsTo('App\Models\CriteriaGroup');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    protected static function boot()
    {
        parent::boot();

        self::updating(function (self $criteria) {
            $snapshot = [];
            foreach (Criteria::$history as $field) {
                if ($criteria->isDirty($field)) {
                    $snapshot[$field] = $criteria->$field;
                }
            }
            if (empty($snapshot)) {
                return;
            }
            LogModel::create([
                'user_id' => isset(request()->user) ? request()->user->getKey() : 1,
                'action' => 'update',
                'model' => 'criteria',
                'model_id' => $criteria->id,
                'date' => Carbon::now(),
                'snapshot' => $snapshot
            ]);

            //link criteria to group
        });
    }

}
