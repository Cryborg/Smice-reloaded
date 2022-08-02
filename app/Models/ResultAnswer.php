<?php

namespace App\Models;

class ResultAnswer extends SmiceModel
{
    protected $table        = 'result_answer';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $fillable     = [];

    protected $hidden       = [];

    protected array $rules        = [];


}
