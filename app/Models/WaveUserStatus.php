<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\WaveUserStatus
 *
 * @property int $id
 * @property mixed $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveUser[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUserStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUserStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUserStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveUserStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WaveUserStatus extends Model
{
    protected $table                = 'wave_user_status';

    protected $primaryKey           = 'id';

    protected $fillable             = [
        'status'
    ];

    protected $jsons                = [
        'status'
    ];

    protected $hidden               = [];

    protected array $rules                = [
        'status'                => 'array|required',
    ];

    public function users()
    {
        return $this->hasMany('App\Models\WaveUser');
    }
}
