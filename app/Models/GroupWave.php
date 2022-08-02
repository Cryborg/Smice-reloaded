<?php

namespace App\Models;

use App\Interfaces\iREST;

/**
 * App\Models\GroupWave
 *
 * @property int $id
 * @property string $name
 * @property int $society_id
 * @property int|null $parent_id
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GroupWave[] $children
 * @property-read \App\Models\GroupWave|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Wave[] $waves
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GroupWave minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GroupWave relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GroupWave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GroupWave whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GroupWave whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GroupWave whereParentId($value)
 * @mixin \Eloquent
 */
class GroupWave extends SmiceModel implements iREST
{
    protected $table                = 'group_wave';

    protected $primarykey           = 'id';

    public $timestamps              = false;

    protected $fillable             = [
        'society_id',
        'name',
        'parent_id'
    ];

    protected array $rules                = [
        'society_id'    => 'integer|required',
        'name' 		    => 'string|required',
        'parent_id'     => 'integer'
    ];

    protected array $list_rows            = [
        'name'
    ];

    public static function getURI()
    {
        return 'group-waves';
    }

    public static function getName()
    {
        return 'groupWave';
    }

    public function getModuleName()
    {
        return 'group-waves';
    }

    public function waves()
    {
        return $this->belongsToMany('App\Models\Wave', 'wave_group_wave');
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\GroupWave', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Models\GroupWave', 'parent_id')->with('children', 'waves')->orderBy('name');
    }

    public function scopeRelations($query)
    {
        return $query->with('children', 'waves')->orderBy('name');
    }

    public function scopeMinimum($query)
    {
        $query->select('id', 'name');
    }
}
