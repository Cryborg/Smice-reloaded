<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\AxeDirectory
 *
 * @property int $id
 * @property string $name
 * @property int $society_id
 * @property int|null $created_by
 * @property int|null $parent_id
 * @property bool $hide_to_client
 * @property bool $available_as_filter
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Axe[] $axes
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AxeDirectory[] $children
 * @property-read \App\Models\AxeDirectory|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AxeTag[] $axeTags
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeDirectory minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeDirectory relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeDirectory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeDirectory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeDirectory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeDirectory whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeDirectory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeDirectory whereAvailableAsFilter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeDirectory whereHideToClient($value)
 * @mixin \Eloquent
 */
class AxeDirectory extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'axe_directory';

    protected $primarykey           = 'id';

    public $timestamps              = false;

    protected $fillable             = [
        'society_id',
        'created_by',
        'name',
        'parent_id',
        'hide_to_client',
        'available_as_filter'
    ];

    protected $hidden               = [
        'created_by'
    ];

    protected $rules                = [
        'society_id'    => 'integer|required',
        'name' 		    => 'string|required|unique_with:axe_directory,society_id,{id}',
        'created_by'    => 'integer|required',
        'parent_id'     => 'integer'
    ];

    protected $list_rows            = [
        'name'
    ];

    public static function getURI()
    {
        return 'directories';
    }

    public static function getName()
    {
        return 'axeDirectory';
    }

    public function getModuleName()
    {
        return 'axeDirectories';
    }

    public function axes()
    {
        return $this->hasMany('App\Models\Axe')->orderBy('name');
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\AxeDirectory', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Models\AxeDirectory', 'parent_id')->with('children', 'axes')->orderBy('name');
    }

    public function axeTags()
    {
        return $this->morphMany('App\Models\AxeTag', 'axe_tag_item');
    }

    public function scopeRelations($query)
    {
        return $query->with('children', 'axes')->orderBy('name');
    }

    public function scopeMinimum($query)
    {
        $query->select('id', 'name');
    }

    /**
     * Recursive function to get all axes ids including subgroups
     * 
     * @param array $itemIds
     * @return array
     */
    public static function getChildAxes($axeDirectories)
    {
        $axes = [];

        foreach($axeDirectories as $axeDirectory) {
            foreach($axeDirectory['axes'] as $axe) {
                $axes[] = $axe['id'];
            }

            if(isset($axeDirectory['children']) && count($axeDirectory['children'])) {
                $axes = array_merge($axes, self::getChildAxes($axeDirectory['children']));
            }
        }

        return $axes;
    }

    /**
     * @param array $itemIds
     * @return array
     */
    public static function getAxesIds($itemIds)
    {
        $result = AxeDirectory::relations()->whereIn('axe_directory.id', $itemIds)
                                                    ->retrieveAll()
                                                    ->toArray();

        $ids = self::getChildAxes($result);

        return $ids;
    }
}