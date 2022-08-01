<?php

namespace App\Models;

use App\Exceptions\SmiceException;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\WaveTargetDate
 *
 * @property int $id
 * @property int $wave_target_id
 * @property string|null $date
 * @property-read \App\Models\WaveTarget $waveTarget
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetDate relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 */
class WaveTargetDate extends SmiceModel
{
    protected $table = 'wave_target_date';

    protected $primaryKey = 'id';

    protected $fillable = [
        'wave_target_id',
        'date'
    ];

    protected $hidden = [];

    protected $list_rows = [];

    protected $rules = [
        'wave_target_id' => 'required|integer',
        'date'           => 'date'
    ];

    public function targets()
    {
        return $this->hasMany('App\Models\WaveTarget');
    }
}