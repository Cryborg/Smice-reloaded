<?php

namespace App\Models;

/**
 * App\Models\GraphFilterCache
 *
 * @property int $id
 * @property string $filters
 * @property mixed|null $restrictedShopId
 * @property mixed|null $targetIds
 * @property mixed|null $questionIds
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphFilterCache whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphFilterCache whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphFilterCache whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphFilterCache whereQuestionIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphFilterCache whereRestrictedShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphFilterCache whereTargetIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphFilterCache whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property mixed $waveIds
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GraphFilterCache whereWaveIds($value)
 */
class GraphFilterCache extends SmiceModel 
{
    protected $table        = 'graph_filter_cache';

    public $timestamps      = true;

   
    protected $rules = [
        'restrictedShopId'   => 'array',
        'targetIds'          => 'array',
        'questionIds'        => 'array',
        'waveIds'        => 'array'

    ];
}
