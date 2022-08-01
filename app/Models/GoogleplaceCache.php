<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\GoogleplaceCache
 *
 * @property int $id
 * @property int $shop_id
 * @property float|null $lat
 * @property float|null $lon
 * @property string|null $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleplaceCache whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleplaceCache whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleplaceCache whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleplaceCache whereLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleplaceCache whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleplaceCache whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleplaceCache whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GoogleplaceCache extends SmiceModel implements iREST, iProtected
{
    protected $table = 'googleplace_cache';

    protected $primaryKey = 'id';

    public static function getURI()
    {
        return 'googleplace_cache';
    }

    public static function getName()
    {
        return 'googleplace_cache';
    }

    public function getModuleName()
    {
        return 'googleplace_cache';
    }
}