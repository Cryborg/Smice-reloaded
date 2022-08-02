<?php

namespace App\Models;

/**
 * App\Models\ShopCache
 *
 * @property string|null $uuid
 * @property int $shopid
 * @property-read \App\Models\Shop $shop
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShopCache whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShopCache whereShopid($value)
 * @mixin \Eloquent
 */
class ShopCache extends SmiceModel
{
    protected $table        = 'shop_cache';

    public $timestamps      = false;

    protected array $rules = [
        'shopid'             => 'integer|required',
        'uuid'                => 'string|required'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
