<?php

namespace App\Models;

class ResultQuestionAsFilter extends SmiceModel
{
    protected $table        = 'result_question_as_filter';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $fillable     = [];

    protected $hidden       = [];

    protected array $rules        = [];


}
