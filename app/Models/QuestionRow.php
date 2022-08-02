<?php

namespace App\Models;

use App\Exceptions\SmiceException;
use Illuminate\Support\Facades\DB;
use App\Models\QuestionRowComment;

/**
 * App\Models\QuestionRow
 *
 * @property int $id
 * @property mixed|null $name
 * @property string|null $image
 * @property string|null $image_report
 * @property bool $expected_row
 * @property bool $comment
 * @property bool $comment_required
 * @property string|null $comment_model
 * @property bool $allow_images
 * @property int $order
 * @property int|null $value
 * @property int $question_id
 * @property bool $na
 * @property-read \App\Models\Question $question
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Color[] $colors
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionRowComment[] $comments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereAllowImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereCommentModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereCommentRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereExpectedRow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereImageReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuestionRow whereNa($value)
 * @mixin \Eloquent
 */
class QuestionRow extends SmiceModel
{
    protected $table = 'question_row';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected array $translatable = [
        'name'
    ];

    protected $jsons = [
        'name'
    ];

    protected $fillable = [
        'name',
        'image',
        'image_report',
        'value',
        'comment',
        'comment_required',
        'comments',
        'expected_row',
        'allow_images',
        'question_id',
        'order',
        'na'
    ];

    protected $children = [
        'comments'
    ];

    protected $hidden = [
        'question_id',
        'comment_model'
    ];

    protected array $rules = [
        'name' => 'array|required_if:image,""',
        'question_id' => 'integer|required',
        'image' => 'string|required_if:name,""',
        'image_report' => 'string',
        'value' => 'integer',
        'order' => 'integer|min:0|required',
        'comments' => 'array',
        'comment' => 'boolean',
        'comment_required' => 'boolean',
        'expected_row' => 'boolean',
        'allow_images' => 'boolean',
    ];

    protected $files = [
        'image',
        'image_report'
    ];

    public function question()
    {
        return $this->belongsTo('App\Models\Question');
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'selected_colors', 'answer_id', 'color_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\QuestionRowComment')->orderBy('order', 'asc');
    }

    public static function createManyRows(Question $question, $rows)
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

       /*  if (count($new_rows) < $question->answer_min) {
            throw new SmiceException(
                SmiceException::HTTP_BAD_REQUEST,
                SmiceException::E_VARIABLE,
                $question->name['fr'] . ' : The question requires at least ' . $question->answer_min . ' answer(s).'
            );
        } */

        if ($question->answer_min > 0) {
            DB::table('question_row')->insert($new_rows);
        }
    }

    public static function updateManyRows(Question $question, $rows)
    {
        $update_rows = $new_rows = $colorRowsNew = $colorRowsUpdate = $update_comments = $new_comments = [];
        $order = 0;
        $old_row_id = $question->answers()->lists('id')->toArray();
        foreach ($rows as $key => $row_attributes) {
            if (!is_array($row_attributes)) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The row is not valid.'
                );
            }

            if (isset($row_attributes['id']) && is_numeric($row_attributes['id'])) {
                $row = $question->answers()->where('id', $row_attributes['id'])->first();
                if (!$row) {
                    throw new SmiceException(
                        SmiceException::HTTP_BAD_REQUEST,
                        SmiceException::E_VARIABLE,
                        'The row is not related to the question.'
                    );
                }

                unset($old_row_id[array_search($row_attributes['id'], $old_row_id)]);
            } else {
                $row = new self();
                $row->question()->associate($question);
            }
            // $row_attributes['name'] = json_encode($row_attributes['name'], true);
            $row->fill($row_attributes);
            $row->order = $order;
            $row->validate();

            if ($row->exists) {
                array_push($update_rows, $row->getAttributes());
                if (isset($row_attributes['colorId'])) {
                    $colorRowsUpdate[$row->id] = [
                        'answer_id' => $row->id,
                        'color_id' => $row_attributes['colorId']
                    ];
                }
            } else {
                $new_rows[$key] = $row->getAttributes();
                if(isset($row_attributes['colorId'])) {
                    $colorRowsNew[$key] = $row_attributes['colorId'];
                }
            }

             //sub answers
            //  if(isset($row->comments)) {
            //     foreach($row->comments as $k => $comment_attributes) {
            //         if (isset($comment_attributes['id']) && is_numeric($comment_attributes['id'])) {
            //             $comment = $row->comments()->where('id', $comment_attributes['id'])->first();
            //             // $comment = DB::table('question_row_comment')->where('id', $comment_attributes['id'])->first();
            //             if (!$comment) {
            //                 throw new SmiceException(
            //                     SmiceException::HTTP_BAD_REQUEST,
            //                     SmiceException::E_VARIABLE,
            //                     'The row is not related to the question.'
            //                 );
            //             }
            //         } else {
            //             $comment = new QuestionRowComment();
            //             $comment->answer()->associate($row);
            //         }

            //         $comment->fill($comment_attributes);
            //         $comment->validate();

            //         if ($comment->exists) {
            //             array_push($update_comments, $comment->getAttributes());
            //             if (isset($comment_attributes['id'])) {
            //                 $commentsUpdate[$comment->id] = [
            //                     'question_row_id' => $comment->id
            //                 ];
            //             }
            //         } else {
            //             if(isset($comment_attributes['id'])) {
            //                 $commentsNew[$key] = $comment_attributes['id'];
            //             }
            //         }

            //         // if (isset($comment_attributes['id'])) {
            //         //     if(!is_numeric($comment_attributes['id'])) {
            //         //         $newComment = new QuestionRowComment();
            //         //         $newComment->create($comment_attributes);
            //         //     } else {
            //         //         $comment = DB::table('question_row_comment')->where('id', $comment_attributes['id'])->first();
            //         //         if($comment === null) {
            //         //             $newComment = new QuestionRowComment();
            //         //             $newComment->create($comment_attributes);
            //         //         } else {
            //         //             // DB::table('question_row_comment')->where('id', $comment_attributes['id'])->update($comment_attributes);
            //         //         }
            //         //     }
            //         // }
            //     }
            // }
            $order++;
        }

        if (count($update_rows) + count($new_rows) < $question->answer_min) {
            throw new SmiceException(
                SmiceException::HTTP_BAD_REQUEST,
                SmiceException::E_VARIABLE,
                'The question requires at least ' . $question->answer_min . ' answer(s).'
            );
        }

        if ($question->answer_min === 0) {
            DB::table('question_row')->where('question_id', $question->getKey())->delete();
            SelectedColor::whereIn('answer_id', $old_row_id)->delete();
        } else {
            DB::table('question_row')->whereIn('id', $old_row_id)->delete();
            SelectedColor::whereIn('answer_id', $old_row_id)->delete();

            foreach ($new_rows as $key => $row) {
                $row['name'] = json_decode($row['name'], true);
                $model = self::create($row);
                if (isset($row['comments'])) {
                    foreach ($row['comments'] as $comment) {
                        $QuestionRowComment = new QuestionRowComment();
                        $QuestionRowComment->fill($comment);
                        $QuestionRowComment->question_row_id= $model->id;
                        $QuestionRowComment->validate();
                        $QuestionRowComment->save();
                    }
                }

                if (isset($colorRowsNew[$key])) {
                    SelectedColor::insert([
                        'answer_id' => $model->id,
                        'color_id' => $colorRowsNew[$key]['id']
                    ]);
                }
                // if (isset($commentsNew[$key])) {
                //     QuestionRowComment::insert([
                //         'question_row_id' => $model->id
                //     ]);
                // }
            }
            foreach ($update_rows as $row) {
                $row_comments = null;
                $update_comment_id = array();
                //remove comments
                if (isset($row['comments'])) {
                    $row_comments = $row['comments'];
                }
                foreach ($row_comments as $rc) {
                    if (is_numeric($rc['id'])) {
                        array_push($update_comment_id, $rc['id']);
                    }
                }
                unset($row['comments']);
                DB::table('question_row_comment')->where('question_row_id', $row['id'])->wherenotin('id', $update_comment_id)->delete();
                foreach ($row_comments as $comment) {
                    if (is_numeric($comment['id'])) {
                        //id numeric update comments
                        $QuestionRowComment = QuestionRowComment::find($comment['id']);
                        if ($QuestionRowComment) {
                            $QuestionRowComment->fill($comment);
                            $QuestionRowComment->validate();
                            $QuestionRowComment->save();
                        }
                    }
                    else {
                        //id is uuid new comments
                        $QuestionRowComment = new QuestionRowComment();
                        $QuestionRowComment->fill($comment);
                        $QuestionRowComment->validate();
                        $QuestionRowComment->save();
                    }

                }
                DB::table('question_row')->where('id', $row['id'])->update($row);
                if (isset($colorRowsUpdate[$row['id']])) {
                    SelectedColor::updateOrCreate([
                        'answer_id' => $row['id']
                    ], $colorRowsUpdate[$row['id']]);
                }
                // if (isset($commentsUpdate[$row['id']])) {
                //     QuestionRowComment::updateOrCreate([
                //         'question_row_id' => $row['id']
                //     ], $commentsUpdate[$row['id']]);
                // }
            }
        }
    }

    /**
     * @param integer $id
     * @return string|null
     */
    public static function getReportImageById($id)
    {
        $result = self::where('id', $id)->select('image_report')->first()->toArray();
        return $result['image_report'];
    }

    public function updatedEvent()
    {
        if ($this->getChildren('comments')) {
            QuestionRowComment::updateManyRows($this, $this->getChildren('comments'));
        }
    }
}
