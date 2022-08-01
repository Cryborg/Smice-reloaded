<?php

namespace App\Models;

use App\Interfaces\iREST;

/**
 * App\Models\ActionplanStatus
 *
 * @property int $id
 * @property mixed $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanStatus whereName($value)
 * @mixin \Eloquent
 */
class ActionplanStatus extends SmiceModel implements iREST
{
    protected $table = 'actionplan_status';

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

    protected $list_rows = [];

    protected $rules = [
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
