<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Tags
 *
 * @property int $id
 * @property string $name
 * @property int $society_id
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveTarget[] $waveTarget
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tags whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tags whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tags whereSocietyId($value)
 * @mixin \Eloquent
 */
class Tags extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'tag';

    protected $primaryKey           = 'id';

    protected $fillable             = [
        'name',
        'society_id',
    ];

    /**
     * Returns the URI for the model
     * @return string
     */
    public static function  getURI()
    {
        return 'tags';
    }

    /**
     * Returns the name of the model (singular)
     * @return null|string
     */
    public static function  getName()
    {
        return 'tag';
    }

    /**
     * Returns the name of the module for which your model belongs to.
     * @return mixed
     */
    public function getModuleName()
    {
        return 'tags';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function waveTarget()
    {
        return $this->belongsToMany('App\Models\WaveTarget', 'shop_axe');
    }
}
