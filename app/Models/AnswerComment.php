<?php

namespace App\Models;

/**
 * App\Models\AnswerComment
 *
 * @property int $id
 * @property int $answer_id
 * @property int $question_row_comment_id
 * @property string|null $comment
 * @property-read \App\Models\Answer $answer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionRowComment[] $questionrowcomment
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerComment whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerComment whereQuestionRowCommentId($value)
 * @mixin \Eloquent
 */
class AnswerComment extends SmiceModel
{
    protected $table = 'answer_comments';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'answer_id',
        'question_row_comment_id',
        'comment'
    ];

    public function questionrowcomment()
    {
        return $this->hasMany('App\Models\QuestionRowComment', 'id', 'question_row_comment_id');
    }

    public function answer()
    {
        return $this->belongsTo('App\Models\Answer');
    }
}