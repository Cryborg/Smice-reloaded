<?php

namespace App\Models;


/**
 * App\Models\WaveTargetScoring
 *
 * @property int $id
 * @property string $name
 * @property string $selection
 * @property int $wave_target_id
 * @property int|null $job_id
 * @property int|null $criteria_id
 * @property int|null $criteria_b_id
 * @property int|null $criteria_a_id
 * @property int|null $sequence_id
 * @property int|null $theme_id
 * @property int|null $question_id
 * @property-read \App\Models\Criteria|null $criteria
 * @property-read \App\Models\CriteriaA|null $criteria_a
 * @property-read \App\Models\CriteriaB|null $criteria_b
 * @property-read \App\Models\Job|null $job
 * @property-read \App\Models\Sequence|null $sequence
 * @property-read \App\Models\Theme|null $theme
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereCriteriaAId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereCriteriaBId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereCriteriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereSelection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereSequenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WaveTargetScoring whereWaveTargetId($value)
 * @mixin \Eloquent
 */
class WaveTargetScoring extends SmiceModel
{
    protected $table = 'wave_target_scoring';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'wave_target_id',
        'type',
        'job_id',
        'criteria_id',
        'criteria_a_id',
        'criteria_b_id',
        'sequence_id',
        'theme_id',
        'score'
    ];

    public static function getURI()
    {
        return 'waveTargetScorings';
    }

    public static function getName()
    {
        return 'waveTargetScoring';
    }

    public function sequence()
    {
        return $this->belongsTo('App\Models\Sequence');
    }

    public function criteria()
    {
        return $this->belongsTo('App\Models\Criteria');
    }

    public function theme()
    {
        return $this->belongsTo('App\Models\Theme');
    }

    public function criteria_a()
    {
        return $this->belongsTo('App\Models\CriteriaA');
    }

    public function criteria_b()
    {
        return $this->belongsTo('App\Models\CriteriaB');
    }

    public function job()
    {
        return $this->belongsTo('App\Models\Job');
    }
}
