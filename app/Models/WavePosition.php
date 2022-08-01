<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\WavePosition
 *
 * @property int $id
 * @property int $wave_user_id
 * @property string $position_date
 * @property-read \App\Models\WaveUser $waveUser
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WavePosition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WavePosition wherePositionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WavePosition whereWaveUserId($value)
 * @mixin \Eloquent
 */
class WavePosition extends Model
{
    protected $table                = 'wave_user_date';

    protected $primaryKey           = 'id';

    public $timestamps              = false;

    protected $fillable             = [
        'wave_user_id',
        'position_date'
    ];

    protected $hidden               = [
        'id'
    ];

    protected $rules                = [
        'wave_user_id'     => 'required',
        'position_date'    => 'date|required'
    ];

    public function waveUser()
    {
        return $this->belongsTo('App\Models\WaveUser', 'wave_user_id', 'uuid');
    }
}