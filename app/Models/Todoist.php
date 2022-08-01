<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Todoist
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $society_id
 * @property int $contact_id
 * @property int $affect_to
 * @property int $wave_id
 * @property int $survey_id
 * @property int $criteria_id
 * @property int $question_id
 * @property string $status
 * @property string $created_at
 * @property string $close_at
 * @property-read \App\Models\User $affectTo
 * @property-read \App\Models\User $contact
 * @property-read \App\Models\Criteria $criteria
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\Survey $survey
 * @property-read \App\Models\Wave $wave
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereAffectTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereCloseAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereCriteriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todoist whereWaveId($value)
 * @mixin \Eloquent
 */
class Todoist extends SmiceModel implements iREST, iProtected
{
	protected $table            = 'todoist';

	protected $primaryKey       = 'id';

	public $timestamps          = false;

	protected $fillable         = [
		'name',
        'description',
        'society_id',
        'contact_id',
        'affect_to',
        'wave_id',
        'survey_id',
        'criteria_id',
        'question_id',
        'status',
        'close_at'
	];

	protected $rules            = [
        'name'                  => 'string|required|unique_with:mission,society_id,{id}',
        'description'           => 'string',
        'society_id'            => 'integer|required|read:societies',
        'contact_id'            => 'integer|required|read:users',
        'affect_to'             => 'integer|required|read:users',
        'wave_id'               => 'integer|required|read:waves',
        'survey_id'             => 'integer|required|read:surveys',
        'criteria_id'           => 'integer|required|read:criterion',
        'question_id'           => 'integer|required|read:questions',
        'status'                => 'string|required|in:pending,done',
        'close_at'              => 'string|required|date_format:Y-m-d H:i:s'
	];

    public static function getURI()
    {
        return 'todoists';
    }

    public static function getName()
    {
        return 'todoist';
    }

    public function getModuleName()
    {
        return 'todoists';
    }

    public function society()
    {
    	return $this->belongsTo('App\Models\Society');
    }

    public function contact()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function affectTo()
    {
        return $this->belongsTo('App\Models\User', 'affect_to');
    }

    public function wave()
    {
        return $this->belongsTo('App\Models\Wave');
    }

    public function survey()
    {
        return $this->belongsTo('App\Models\Survey');
    }

    public function criteria()
    {
        return $this->belongsTo('App\Models\Criteria');
    }

    public function question()
    {
        return $this->belongsTo('App\Models\Question');
    }
}