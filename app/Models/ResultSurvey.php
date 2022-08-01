<?php

namespace App\Models;

class ResultSurvey extends SmiceModel
{
    protected $table        = 'result_survey';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $fillable     = [];

    protected $hidden       = [];

    protected $rules        = [];

    
}