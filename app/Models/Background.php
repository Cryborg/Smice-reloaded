<?php

namespace App\Models;

/**
 * App\Models\Background
 *
 * @property int $id
 * @property string $url
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Background whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Background whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Background whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Background whereUrl($value)
 * @mixin \Eloquent
 */
class Background extends SmiceModel
{
    protected $table            = 'background';

    protected $primaryKey       = 'id';

    public $timestamps          = true;

    protected $fillable         = [
        'url'
    ];

    protected $hidden           = [];

    protected $rules            = [];

   
}
