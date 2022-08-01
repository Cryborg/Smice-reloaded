<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Reprocessing
 *
 * @property int $id
 * @property string|null $link
 * @property int $society_id
 * @property int $survey_id
 * @property int|null $scenario_id
 * @property int|null $axe_id
 * @property int|null $shop_id
 * @property string $action
 * @property int|null $question_id
 * @property int|null $question_row_id
 * @property int|null $question_row_comment_id
 * @property int|null $value
 * @property int|null $target_sequence_id
 * @property int|null $target_question_id
 * @property int|null $target_question_row_id
 * @property int|null $target_question_row_comment_id
 * @property string|null $target_value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Axe|null $axe
 * @property-read \App\Models\Question|null $question
 * @property-read \App\Models\QuestionRow|null $questionrow
 * @property-read \App\Models\QuestionRowComment|null $questionrowcomment
 * @property-read \App\Models\Scenario|null $scenario
 * @property-read \App\Models\Sequence|null $sequence
 * @property-read \App\Models\Shop|null $shop
 * @property-read \App\Models\Survey $survey
 * @property-read \App\Models\Question|null $targetquestion
 * @property-read \App\Models\QuestionRow|null $targetquestionrow
 * @property-read \App\Models\QuestionRowComment|null $targetquestionrowcomment
 * @property-read \App\Models\Society $targetsociety
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereAxeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereQuestionRowCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereQuestionRowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereScenarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereTargetQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereTargetQuestionRowCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereTargetQuestionRowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereTargetSequenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereTargetValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reprocessing whereValue($value)
 * @mixin \Eloquent
 */
class Reprocessing extends SmiceModel implements iREST, iProtected
{
    protected $table = 'reprocessing';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'society_id',
        'survey_id',
        'scenario_id',
        'shop_id',
        'action',
        'question_id',
        'question_row_id',
        'question_row_comment_id',
        'value',
        'target_question_id',
        'target_question_row_id',
        'target_question_row_comment_id',
        'target_value',
        'created_at',
        'updated_at',
        'link'
    ];

    public static function getURI()
    {
        return 'reprocessing';
    }

    public static function getName()
    {
        return 'reprocessing';
    }

    public function getModuleName()
    {
        return 'reprocessing';
    }

    public function survey()
    {
        return $this->belongsTo('App\Models\Survey');
    }

    public function scenario()
    {
        return $this->belongsTo('App\Models\Scenario');
    }

    public function shop()
    {
        return $this->belongsTo('App\Models\Shop');
    }

    public function axe()
    {
        return $this->belongsTo('App\Models\Axe');
    }

    public function sequence()
    {
        return $this->belongsTo('App\Models\Sequence', 'target_sequence_id');
    }
   
    public function question()
    {
        return $this->belongsTo('App\Models\Question');
    }

    public function questionrow()
    {
        return $this->belongsTo('App\Models\QuestionRow', 'question_row_id');
    }

    public function questionrowcomment()
    {
        return $this->belongsTo('App\Models\QuestionRowComment', 'question_row_comment_id');
    }

    public function targetquestion()
    {
        return $this->belongsTo('App\Models\Question', 'target_question_id');
    }

    public function targetquestionrow()
    {
        return $this->belongsTo('App\Models\QuestionRow', 'target_question_row_id');
    }

    public function targetquestionrowcomment()
    {
        return $this->belongsTo('App\Models\QuestionRowComment', 'target_question_row_comment_id');
    }

    public function targetsociety()
    {
        return $this->belongsTo('App\Models\Society');
    }
}