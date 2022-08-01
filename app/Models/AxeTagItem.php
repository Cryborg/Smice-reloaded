<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\AxeTagItem
 *
 * @property int $axe_tag_id
 * @property string $axe_tag_item_type
 * @property int $axe_tag_item_id
 * @property-read \App\Models\AxeTag $axeTag
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeTagItem minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Axe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeTagItem whereAxeTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeTagItem whereAxeTagItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AxeTagItem whereAxeTagItemType($value)
 * @mixin \Eloquent
 */
class AxeTagItem extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'axe_tag_items';

    public $incrementing = false;

    public $timestamps              = false;

    protected $fillable             = [
    	'axe_tag_id',
        'axe_tag_item_type',
        'axe_tag_item_id',
    ];

    protected $rules                = [
        'axe_tag_id' 		=> 'integer|required|exists:axe_tag,id',
        'axe_tag_item_type' => 'string|required|in:'.Axe::class.','.AxeDirectory::class,
        'axe_tag_item_id'   => 'integer|required',
    ];

    public static function getURI()
    {
        return 'axe-tags';
    }

    public static function getName()
    {
        return 'axeTag';
    }

    public function getModuleName()
    {
        return 'axe-tags';
    }

    public function axeTag()
    {
        return $this->belongsTo('App\Models\AxeTag');
    }
}