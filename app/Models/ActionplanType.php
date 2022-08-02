<?php

namespace App\Models;

use App\Interfaces\iREST;
use Response;

/**
 * App\Models\ActionplanType
 *
 * @property int $id
 * @property mixed $name
 * @property int $society_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanType whereSocietyId($value)
 * @mixin \Eloquent
 */
class ActionplanType extends SmiceModel implements iREST
{
    protected $table = 'actionplan_type';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public $jsons   = [
        'name'
    ];

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
    ];

    protected array $list_rows = [];

    protected array $rules = [
        'name' => 'json',
    ];

    public static function getURI()
    {
        return 'actionplan-status';
    }

    public static function getName()
    {
        return 'actionplan_status';
    }

    public function getModuleName()
    {
        return 'actionplan_status';
    }
}
