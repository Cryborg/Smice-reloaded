<?php

namespace App\Models;

use App\Exceptions\SmiceException;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\QuestionRowComment
 *
 * @property int $id
 * @property mixed $name
 * @property bool $comment
 * @property bool $comment_required
 * @property int $order
 * @property int|null $question_row_id
 * @property-read \App\Models\QuestionRow $answer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRowComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRowComment whereCommentRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRowComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRowComment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRowComment whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRowComment whereQuestionRowId($value)
 * @mixin \Eloquent
 */
class QuestionRowComment extends SmiceModel
{
    protected $table = 'question_row_comment';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public $jsons   = [
        'name'
    ];

    public array $translatables   = [
        'name'
    ];

    protected $fillable = [
        'name',
        'comment',
        'comment_required',
        'order',
        'question_row_id'
    ];

    public function answer()
    {
        return $this->belongsTo('App\Models\QuestionRow');
    }

    public static function createManyCom(Question $question, $rows)
    {
        $new_rows = [];
        $order = 0;

        foreach ($rows as $row_attributes) {
            if (!is_array($row_attributes)) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The row is not valid.'
                );
            }

            $row = new self();

            $row->order = $order;
            $row->fill($row_attributes);
            $row->question()->associate($question);
            $row->validate();

            array_push($new_rows, $row->getAttributes());
            $order++;
        }
        DB::table('question_row_comment')->insert($new_rows);
    }
}
