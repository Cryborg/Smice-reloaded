<?php

namespace App\Models;

use Illuminate\Support\Facades\Validator;

/**
 * App\Models\WaveFollow
 *
 * @property int|null $id
 * @property int|null $wave_id
 * @property int|null $society_id
 * @property string|null $society
 * @property string|null $program
 * @property string|null $wave
 * @property int|null $reader_id
 * @property int|null $missions
 * @property int|null $doodle
 * @property int|null $proposed
 * @property int|null $accepted
 * @property int|null $selected
 * @property int|null $confirmed
 * @property int|null $answered
 * @property int|null $done
 * @property int|null $invalidated
 * @property int|null $validated
 * @property int|null $read
 * @property int|null $boss_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow filter($params)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereAnswered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereBossId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereDoodle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereInvalidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereMissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereProgram($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereProposed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereReaderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereSelected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereSociety($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereValidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereWave($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveFollow whereWaveId($value)
 * @mixin \Eloquent
 */
class WaveFollow extends SmiceModel
{
    protected $table                = 'show_current_waves';

    protected $jsons = [
        'program',
        'name',
    ];

    public $timestamps              = false;

    protected $fillable             = [];

    protected $hidden               = [];

    protected array $rules                = [];

    protected array $list_rows            = [
        'society',
        'program',
        'missions',
        'doodle',
        'proposed',
        'accepted',
        'selected',
        'confirmed',
        'read'
    ];

    public static function  current()
    {
        $self               = new self;

        $self->setTable('show_current_waves');

        return $self;
    }

    public static function incoming()
    {
        $self               = new self;

        $self->setTable('show_incoming_waves');

        return $self;
    }

    public static function finished()
    {
        $self               = new self;

        $self->setTable('show_finished_waves');

        return $self;
    }

    public static function scopeFilter($query, $params)
    {
        $mission        = array_get($params, 'mission_id');
        $program        = array_get($params, 'program_id');
        $society        = array_get($params, 'society_id');
        $scenario       = array_get($params, 'scenario_id');
        $shop           = array_get($params, 'shop_id');
        $wave           = array_get($params, 'wave_id');
        $boss           = array_get($params, 'reader_id');
        $date_start     = array_get($params, 'date_start');
        $date_end       = array_get($params, 'date_end');
        $date_status    = array_get($params, 'date_status');
        $validator      = Validator::make(
            [
                'mission'       => $mission,
                'program'       => $program,
                'society'       => $society,
                'scenario'      => $scenario,
                'shop'          => $shop,
                'wave'          => $wave,
                'boss'          => $boss,
                'date_start'    => $date_start,
                'date_end'      => $date_end,
                'date_status'   => $date_status
            ],
            [
                'mission'       => 'integer|read:missions',
                'program'       => 'integer|read:programs',
                'society'       => 'integer|read:societies',
                'scenario'      => 'integer|read:scenarios',
                'shop'          => 'integer|read:shops',
                'wave'          => 'integer|read:waves',
                'boss'          => 'integer|read:users',
                'date_start'    => 'date',
                'date_end'      => 'date',
                'date_status'   => 'date'
            ]
        );

        $validator->passOrDie();
        if ($society)
            $query = $query->where('society_id', $society);
        if ($mission)
            $query = $query->where('mission_id', $mission);
        if ($program)
            $query = $query->where('id', $program);
        if ($scenario)
            $query = $query->where('scenario_id', $scenario);
        if ($shop)
            $query = $query->where('shop_id', $shop);
        if ($wave)
            $query = $query->where('wave_id', $wave);
        if ($boss)
            $query = $query->where('boss_id', $boss);
        if ($date_start)
            $query = $query->where('date_start', $date_start);
        if ($date_end)
            $query = $query->where('date_end', $date_end);
        if ($date_status)
            $query = $query->where('date_status', $date_status);
        return $query;
    }
}
