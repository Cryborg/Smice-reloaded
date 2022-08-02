<?php

namespace App\Models;

/**
 * App\Models\AnswerCommentDeleted
 *
 * @property int $id
 * @property int $answer_id
 * @property int $question_row_comment_id
 * @property string|null $comment
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerCommentDeleted whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerCommentDeleted whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerCommentDeleted whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerCommentDeleted whereQuestionRowCommentId($value)
 * @mixin \Eloquent
 */
class AnswerCommentDeleted extends SmiceModel
{
    protected $table            = 'answer_comments_deleted';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $fillable         = [
        'id',
        'answer_id',
        'question_row_comment_id',
        'comment'
    ];

    protected $hidden           = [];

    protected array $rules            = [
        'answer_id'                 => 'integer|required',
        'question_row_comment_id'   => 'integer|required',
        'comment'                   => 'string'
    ];
}
