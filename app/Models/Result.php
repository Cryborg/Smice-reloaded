<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

class Result extends SmiceModel implements iRest, iProtected
{
    protected $table = 'result';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'filters',
        'data',
        'type',
        'society_id',
        'view_id',
        'static',
        'isgraph',
        'last_refresh_data',
        'updated_at',
        'base-min',
        'top_5',
        'flop_5',
        'hide_bases',
        'hide_goals'
    ];

    protected $hidden = [];

    public static function getURI()
    {
        return 'results';
    }

    public static function getName()
    {
        return 'result';
    }

    public function getModuleName()
    {
        return 'results';
    }
}
