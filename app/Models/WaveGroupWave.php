<?php

namespace App\Models;

/**
 * App\Models\WaveGroupWave
 *
 * @property int $id
 * @property int $wave_id
 * @property int $group_wave_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveGroupWave[] $children
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GroupWave[] $groupWaves
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GroupWave[] $waves
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveGroupWave minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveGroupWave relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveGroupWave whereGroupWaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveGroupWave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveGroupWave whereWaveId($value)
 * @mixin \Eloquent
 */
class WaveGroupWave extends SmiceModel
{
    protected $table                = 'wave_group_wave';
    protected $primarykey           = 'id';
    public $timestamps              = false;
    protected $fillable             = [
        'wave_id',
        'group_wave_id'
    ];

    protected $rules                = [
        'wave_id'    => 'integer|required',
        'group_wave_id'     => 'integer'
    ];

    public static function getURI()
    {
        return 'wave_group_wave';
    }

    public static function getName()
    {
        return 'WaveGroupWave';
    }

    public function getModuleName()
    {
        return 'wave_group_wave';
    }

    public function groupWaves()
    {
        return $this->hasMany('App\Models\GroupWave')->orderBy('name');
    }

    public function waves()
    {
        return $this->belongsToMany('App\Models\GroupWave', 'wave_group_wave')->select('id', 'name');
    }

    public function children()
    {
        return $this->hasMany('App\Models\WaveGroupWave', 'group_wave_id')->with('children', 'groupWaves')->orderBy('name');
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