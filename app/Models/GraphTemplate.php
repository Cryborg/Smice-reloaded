<?php

namespace App\Models;


/**
 * App\Models\GraphTemplate
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $template_html
 * @property mixed $template
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Graph $graph
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphTemplate whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphTemplate whereTemplateHtml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphTemplate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GraphTemplate extends SmiceModel
{
    protected $table            = 'graph_template';

    protected $primaryKey       = 'id';

    public $timestamps          = true;

    protected $fillable         = [
        'template',
        'name',
        'type',
        'template_html',
    ];

    protected $hidden           = [];

    protected $rules            = [];

    public function graph()
    {
        return $this->belongsTo('App\Models\Graph');
    }

    public static function getURI()
    {
        return 'graph_templates';
    }

    public static function getName()
    {
        return 'graph_template';
    }

    public function 	   getModuleName()
    {
        return 'graph_templates';
    }
}
