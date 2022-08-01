<?php

namespace App\Models;

/**
 * App\Models\Answer
 *
 * @property int $id
 * @property string|null $value
 * @property string|null $comment
 * @property int $survey_id
 * @property int $user_id
 * @property int $question_id
 * @property int|null $question_row_id
 * @property int|null $question_col_id
 * @property int|null $wave_target_id
 * @property string|null $uuid
 * @property int|null $tag
 * @property-read \App\Models\QuestionRow|null $question_tag
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AnswerImage[] $images
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\QuestionCol|null $question_col
 * @property-read \App\Models\QuestionRow|null $question_row
 * @property-read \App\Models\Survey $survey
 * @property-read \App\Models\User $user
 * @property-read \App\Models\WaveTarget|null $waveTarget
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AnswerComment[] $comments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AnswerFile[] $files
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereQuestionColId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereQuestionRowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereTag($value)
 * @mixin \Eloquent
 */
class Answer extends SmiceModel
{
    protected $table            = 'answer';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $fillable         = [
        'user_id',
        'survey_id',
        'question_id',
        'value',
        'comment',
        'question_row_id',
        'question_col_id',
        'wave_target_id',
        'uuid',
        'tag'
    ];

    protected $hidden           = [];

    protected $rules            = [
        'user_id'           => 'integer|required',
        'question_id'       => 'integer|required',
        'survey_id'         => 'integer|required',
        'comment'           => 'string',
        'value'             => 'string',
        'question_row_id'   => 'integer|required_with:question_col_id',
        'question_col_id'   => 'integer',
        'wave_target_id'    => 'integer',
        'uuid'              => 'string'
    ];

    public static function getURI()
    {
        return 'answers';
    }

    public static function getName()
    {
        return 'answer';
    }

    public function getModuleName()
    {
        return 'answers';
    }

    public function question()
    {
        return $this->belongsTo('App\Models\Question');
    }

    public function question_row()
    {
        return $this->belongsTo('App\Models\QuestionRow');
    }

    public function question_col()
    {
        return $this->belongsTo('App\Models\QuestionCol');
    }

    public function survey()
    {
        return $this->belongsTo('App\Models\Survey');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function waveTarget()
    {
        return $this->belongsTo('App\Models\WaveTarget');
    }

    public function images()
    {
        return $this->hasMany('App\Models\AnswerImage');
    }

    public function files()
    {
        return $this->hasMany('App\Models\AnswerFile');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\AnswerComment')->with('questionrowcomment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question_tag()
    {
        return $this->belongsTo(QuestionRow::class, 'tag');
    }

    public function check($target = null)
    {
        $this->wave_target = $target;
        if ($this->survey->quiz) {
            $this->checkErrors();
        }
        //check question answer_max answer_min

        /**
         * @todo Check if the answer that's about to get saved is correct, depending on it's question
         * quesiton_row and question_col.
         */
    }

    public function checkErrors()
    {
        if ($this->question->type == Question::TYPE_RADIO || $this->question->type == Question::TYPE_SELECT) {
            $this->handleRadioSelect();
        } else if ($this->question->type == Question::TYPE_CHECKBOX) {
            $this->handleCheck();
        }
    }

    public function handleRadioSelect()
    {
        $expected = false;
        foreach ($this->question->answers->toArray() as $possible_answers)
            if ($possible_answers['expected_row']) {
                $expected = true;
            }

        if (!$this->question_row->expected_row && $expected) {
            $this->wave_target->nb_quiz_error++;
            $this->wave_target->save();
        }
    }

    public function handleCheck()
    {
        $expected = false;
        foreach ($this->question->answers->toArray() as $possible_answers) {
            if ($possible_answers['expected_row']) {
                $expected = true;
            }
        }

        $value_to_compare = $this->value == 'false' ? false : true;

        if ($this->question_row->expected_row !== $value_to_compare && $expected) {
            $this->wave_target->nb_quiz_error++;
            $this->wave_target->save();
        }
    }
}