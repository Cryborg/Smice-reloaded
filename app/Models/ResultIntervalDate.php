<?php

namespace App\Models;

class ResultIntervalDate extends SmiceModel
{
    protected $table        = 'result_interval-date';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $fillable     = [];

    protected $hidden       = [];

    protected array $rules        = [];


}
