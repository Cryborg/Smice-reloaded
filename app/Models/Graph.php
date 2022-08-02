<?php

namespace App\Models;


/**
 * App\Models\Graph
 *
 * @property int $id
 * @property string $name
 * @property int $dashboard_id
 * @property int $graph_template_id
 * @property int $society_id
 * @property mixed $filters
 * @property int $position
 * @property string $type
 * @property string $case
 * @property mixed $graph
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int|null $question_id
 * @property int|null $theme_id
 * @property int|null $criteria_id
 * @property int|null $sequence_id
 * @property string|null $subtitle
 * @property string|null $wave_name1
 * @property string|null $wave_name2
 * @property mixed|null $filters_compare
 * @property-read \App\Models\Dashboard $dashboard
 * @property-read \App\Models\GraphTemplate $graph_template
 * @property-read \App\Models\Society $society
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereCase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereCriteriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereDashboardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereFiltersCompare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereGraph($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereGraphTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereSequenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereWaveName1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Graph whereWaveName2($value)
 * @mixin \Eloquent
 */
class Graph extends SmiceModel
{
    protected $table            = 'graph';

    protected $primaryKey       = 'id';

    public $timestamps          = true;

    protected $fillable         = [
        'name',
        'dashboard_id',
        'graph_template_id',
        'society_id',
        'filters',
        'filters_compare',
        'position',
        'type',
        'case',
        'graph',
        'question_id',
        'theme_id',
        'criteria_id',
        'sequence_id',
        'wave_name1',
        'wave_name2'
    ];

    protected $hidden           = [];

    protected array $rules            = [];

    public function dashboard()
    {
        return $this->belongsTo('App\Models\Dashboard');
    }

    public function graph_template()
    {
        return $this->belongsTo('App\Models\GraphTemplate');
    }

    public static function getURI()
    {
        return 'graphs';
    }

    public static function getName()
    {
        return 'graph';
    }

    public function 	   getModuleName()
    {
        return 'graphs';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }
}
