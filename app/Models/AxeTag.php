<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\AxeTag
 *
 * @property int $id
 * @property string $name
 * @property int $society_id
 * @property string $created_at
 * @property string $updated_at
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AxeDirectory[] $directories
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Axe[] $axes
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeTag minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Axe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Axe whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Axe whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeTag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AxeTag extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'axe_tag';

    protected $primarykey           = 'id';

    public $timestamps              = true;

    protected $fillable             = [
    	'name',
        'society_id'
    ];

    protected $hidden               = [
        'society_id'
    ];

    protected array $rules                = [
        'name' 		        => 'string|required',
        'society_id'        => 'integer|required|exists:society,id'
    ];

    public static function getURI()
    {
        return 'axe-tags';
    }

    public static function getName()
    {
        return 'axeTag';
    }

    public function getModuleName()
    {
        return 'axeTags';
    }

    public function axes()
    {
        return $this->morphedByMany('App\Models\Axe', 'axe_tag_item');
    }

    public function directories()
    {
        return $this->morphedByMany('App\Models\AxeDirectory', 'axe_tag_item');
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function scopeMinimum($query)
    {
        $query->select('id', 'name');
    }

    public function scopeRelations($query)
    {
        return $query->with('directories', 'axes')->orderBy('name');
    }
}
