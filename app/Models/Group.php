<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\Group
 *
 * @property int $id
 * @property int $society_id
 * @property int $created_by
 * @property mixed $name
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Group whereSocietyId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User $createdBy
 */
class Group extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table            = 'group';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $jsons = [
        'name'
    ];

    protected array $translatables    = [
        'name'
    ];

    protected $fillable         = [
        'name',
        'society_id',
        'created_by'
    ];

    protected $hidden           = [
        'society_id',
        'created_by'
    ];

    protected $list_rows        = [
        'name'
    ];

    protected $rules            = [
        'society_id'    => 'integer|required',
        'name'          => 'array|required',
        'created_by'    => 'integer|required'
    ];

    public static function getURI()
    {
        return 'groups';
    }

    public static function getName()
    {
        return 'group';
    }

    public function getModuleName()
    {
        return 'groups';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'group_user');
    }
}
