<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\Skill
 *
 * @property int $id
 * @property mixed $name
 * @property mixed $description
 * @property bool $visible
 * @property int $society_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $created_by
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Skill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Skill whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Skill whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Skill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Skill whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Skill whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Skill whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Skill whereVisible($value)
 * @mixin \Eloquent
 */
class Skill extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table                = 'skill';

    protected $primaryKey           = 'id';

    protected $jsons                = [
        'name',
        'description'
    ];

    protected array $translatables        = [
        'name',
        'description'
    ];

    protected $fillable             = [
        'name',
        'description',
        'visible',
        'society_id',
        'created_by',
    ];

    protected $hidden               = [
        'society_id',
        'created_by'
    ];

    protected $list_rows            = [
        'name',
        'description',
        'visible',
    ];

    protected $rules                = [
        'name'                          => 'required|json',
        'description'                   => 'required|json',
        'visible'                       => 'required|boolean',
        'society_id'                    => 'required|integer|exists:society,id',
        'created_by'                    => 'required|integer|exists:user,id',
    ];

    public static function getURI()
    {
        return 'skills';
    }

    public static function getName()
    {
        return 'skill';
    }

    public function getModuleName()
    {
        return 'skills';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_skill');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
}
