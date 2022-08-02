<?php

namespace App\Models;

class ResultWave extends SmiceModel
{
    protected $table        = 'result_wave';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $fillable     = [];

    protected $hidden       = [];

    protected array $rules        = [];


}
