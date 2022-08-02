<?php

namespace App\Models;

/**
 * App\Models\ClaimCustomField
 *
 * @property int $id
 * @property string $name
 * @property string $iso
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClaimCustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClaimCustomField whereIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClaimCustomField whereName($value)
 * @mixin \Eloquent
 */
class ClaimCustomField extends SmiceModel
{
    protected $table        = 'claim_custom_field';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $fillable     = [];

    protected $hidden       = [];

    protected array $rules        = [];

}
