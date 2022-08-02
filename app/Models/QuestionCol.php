<?php

namespace App\Models;

use App\Exceptions\SmiceException;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\QuestionCol
 *
 * @property int $id
 * @property mixed $name
 * @property string|null $image
 * @property bool $comment
 * @property bool $comment_required
 * @property int $order
 * @property int $question_id
 * @property int|null $question_row_id
 * @property-read \App\Models\Question $question
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionCol whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionCol whereCommentRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionCol whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionCol whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionCol whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionCol whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionCol whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionCol whereQuestionRowId($value)
 * @mixin \Eloquent
 */
class QuestionCol extends SmiceModel
{
    protected $table        = 'question_col';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $jsons = [
        'name'
    ];

    protected array $translatable = [
        'name'
    ];

    protected $fillable     = [
        'name',
        'image',
        'comment',
        'comment_required',
        'question_row_id'
    ];

    protected $hidden       = [
        'question_id',
    ];

    protected array $rules        = [
        'name'              => 'array|required',
        'question_id'       => 'integer|required',
        'image'             => 'string|required_if:name,""',
        'comment'           => 'boolean',
        'comment_required'  => 'boolean',
        'question_row_id'   => 'integer'
    ];

    protected $files        = [
        'image'
    ];

    public function         question()
    {
        return $this->belongsTo('App\Models\Question');
    }

    public static function createManyCols(Question $question, $cols)
    {
        $new_cols           = [];
        $order              = 0;

        foreach ($cols as $col_attributes) {
            if (!is_array($col_attributes)) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The column is not valid.'
                );
            }

            $col = new self();

            $col->order = $order;
            $col->fill($col_attributes);
            $col->question()->associate($question);
            $col->validate();

            array_push($new_cols, $col->getAttributes());
            $order++;
        }

        if ($question->type === Question::TYPE_MATRIX_CHECKBOX || $question->type === Question::TYPE_MATRIX_RADIO) {
            if (count($new_cols) < 1) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The question requires at least 1 column.'
                );
            }

            DB::table('question_col')->insert($new_cols);
        }
    }

    public static function updateManyCols(Question $question, $cols)
    {
        $update_cols = $new_cols = [];
        $order              = 0;
        $old_col_id         = array_flatten(DB::table('question_col')
            ->select('id')
            ->where('question_id', $question->id)
            ->get());

        foreach ($cols as $col_attributes) {
            if (!is_array($col_attributes)) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The column is not valid.'
                );
            }

            if (isset($col_attributes['id']) && is_numeric($col_attributes['id'])) {
                $col = $question->cols()->where('id', $col_attributes['id'])->first();

                if (!$col) {
                    throw new SmiceException(
                        SmiceException::HTTP_BAD_REQUEST,
                        SmiceException::E_VARIABLE,
                        'The column is not related to the question.'
                    );
                }

                unset($old_col_id[array_search($col_attributes['id'], $old_col_id)]);
            } else {
                $col = new self();
                $col->question()->associate($question);
            }

            $col->order = $order;
            $col->fill($col_attributes);
            $col->validate();

            if ($col->exists) {
                array_push($update_cols, $col->getAttributes());
            } else {
                array_push($new_cols, $col->getAttributes());
            }

            $order++;
        }

        if ($question->type === Question::TYPE_MATRIX_CHECKBOX || $question->type === Question::TYPE_MATRIX_RADIO)
        {
            if (count($new_cols) + count($update_cols) < 1) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The question requires at least 1 column.'
                );
            }

            DB::table('question_col')->whereIn('id', $old_col_id)->delete();
            DB::table('question_col')->insert($new_cols);

            foreach ($update_cols as $col) {
                DB::table('question_col')->where('id', $col['id'])->update($col);
            }
        } else {
            DB::table('question_col')->where('question_id', $question->getKey())->delete();
        }
    }
}
