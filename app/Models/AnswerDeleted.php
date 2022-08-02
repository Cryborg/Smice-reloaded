<?php

namespace App\Models;

/**
 * App\Models\AnswerDeleted
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereQuestionColId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereQuestionRowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerDeleted whereTag($value)
 * @mixin \Eloquent
 */
class AnswerDeleted extends SmiceModel
{
    protected $table            = 'answer_deleted';

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

    protected array $rules            = [
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
}
