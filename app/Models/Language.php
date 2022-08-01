<?php

namespace App\Models;

/**
 * App\Models\Language
 *
 * @property int $id
 * @property string $code
 * @property string|null $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Language whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Language whereName($value)
 * @mixin \Eloquent
 */
class Language extends SmiceModel
{
    protected $table        = 'language';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $fillable     = [
        'code',
        'name'
    ];

    protected $hidden       = [];

    protected $rules        = [
        'code' => 'unique:language,code,{id}'
    ];
}