<?php

namespace App\Models;

class ResultScenario extends SmiceModel
{
    protected $table        = 'result_scenario';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $fillable     = [];

    protected $hidden       = [];

    protected array $rules        = [];


}
