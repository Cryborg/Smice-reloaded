<?php

namespace App\Models;

use App\Interfaces\iREST;

/**
 * App\Models\TodoistStatus
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @mixin \Eloquent
 */
class TodoistStatus extends SmiceModel implements iREST
{
    protected $table = 'todoist_status';

    protected $primaryKey = 'id';

    public $timestamps = false;

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
        return 'todoist-status';
    }

    public static function getName()
    {
        return 'todoist_status';
    }

    public function getModuleName()
    {
        return 'todoist_status';
    }
}
