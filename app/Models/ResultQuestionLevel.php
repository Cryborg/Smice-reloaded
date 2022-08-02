<?php

namespace App\Models;

class ResultQuestionLevel extends SmiceModel
{
    protected $table        = 'result_question_level';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $fillable     = [];

    protected $hidden       = [];

    protected array $rules        = [];


}
