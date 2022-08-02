<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyItemHistory extends Model
{
    protected $table = 'survey_item_history';

    protected $primaryKey = 'id';

    protected $jsons = ['snapshot'];

    public $timestamps = false;

    protected $fillable = [
        'action',
        'survey_item_id',
        'snapshot',
        'created_by',
        'created_at',
    ];

    protected $hidden = [];

    protected array $list_rows = [];

    protected array $rules = [];

    public function surveyItem()
    {
        return $this->belongsTo('App\Models\SurveyItem');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by')->select(array('id', 'first_name', 'last_name', 'email', 'picture'));
    }
}
