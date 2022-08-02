<?php

namespace App\Models;

use App\Classes\MockUp;
use App\Exceptions\SmiceException;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;
use Illuminate\Support\Facades\DB;
use App\Models\LogModel;
use Carbon\Carbon;

/**
 * App\Models\Question
 *
 * @property int $id
 * @property mixed $name
 * @property mixed|null $info
 * @property mixed|null $answer_explanation
 * @property string $type
 * @property string|null $image
 * @property string|null $role_name
 * @property bool $library
 * @property int $answer_min
 * @property int $answer_max
 * @property int $society_id
 * @property int $created_by
 * @property bool $allow_images
 * @property mixed|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionRow[] $answers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionCol[] $cols
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SurveyItem[] $items
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Survey[] $surveys
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Answer[] $userAnswers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveTargetConversation[] $waveTargetConversations
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereAllowImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereAnswerExplanation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereAnswerMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereAnswerMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereLibrary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereRoleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Question whereType($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionRowComment[] $answers_comment
 * @mixin \Eloquent
 */
class Question extends SmiceModel implements iREST, iProtected, iTranslatable
{
    const TYPE_MATRIX_RADIO = 'matrix_radio';
    const TYPE_NET_PROMOTER_SCORE = 'net_promoter_score';
    const TYPE_FILE = 'file';
    const TYPE_TEXT = 'text';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_TEXT_AREA = 'text_area';
    const TYPE_SELECT = 'select';
    const TYPE_DATE = 'date';
    const TYPE_MATRIX_CHECKBOX = 'matrix_checkbox';
    const TYPE_HOUR = 'hour';
    const TYPE_NUMBER = 'number';
    const TYPE_SATISFACTION = 'satisfaction';
    const TYPE_RADIO = 'radio';

    protected $table        = 'question';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    public $jsons   = [
        'name',
        'info',
        'description',
        'answer_explanation',
        'comment'
    ];

    public array $translatables   = [
        'name',
        'info',
        'description',
        'answer_explanation',
        'comment'
    ];

    protected $fillable     = [
        'name',
        'info',
        'answer_explanation',
        'type',
        'society_id',
        'image',
        'library',
        'required',
        'answer_max',
        'answers',
        'cols',
        'created_by',
        'description',
        'role_name',
        'allow_images',
        'answer_min',
    ];

    protected $hidden       = [
        'society_id',
        'created_by'
    ];

    protected array $list_rows            = [
        'name',
        'type',
        'description',
        'library'
    ];

    protected array $rules        = [
        'name'               => 'array|required',
        'answer_explanation' => 'array',
        'info'               => 'array',
        'description'        => 'array',
    	'type'               => 'string|required',
        'image'              => 'string',
        'society_id'         => 'integer|required',
        'library'            => 'boolean',
        'required'           => 'boolean',
        'answer_max'         => 'integer|min:0',
        'answers'            => 'array',
        'cols'               => 'array',
        'created_by'         => 'integer|required',
        'allow_images'       => 'boolean',
        'answer_min'         => 'integer',
    ];

    protected $children     = [
        'answers',
        'cols',
        'answers_comment'
    ];

    protected $files        = [
        'image'
    ];

    public static $history = [
        'name',
        'info',
        'description'
    ];

    public static function getURI()
    {
        return 'questions';
    }

    public static function getName()
    {
        return 'question';
    }

    public function getModuleName()
    {
        return 'questions';
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function(self $question)
        {
            self::_checkType($question);
        });

        self::created(function(self $question)
        {
            QuestionRow::createManyRows($question, $question->getChildren('answers'));
            QuestionCol::createManyCols($question, $question->getChildren('cols'));
            QuestionRowComment::createManyCom($question, $question->getChildren('answers_comment'));
        });

        self::updating(function(self $question)
        {
            self::_checkType($question);
            $snapshot = [];
            foreach (Question::$history as $field) {
                if ($question->isDirty($field) && $question->getOriginal($field) !== $question->$field) {
                    $snapshot[$field] = $question->$field;
                }
            }
            LogModel::create([
                'user_id' => isset(request()->user) ? request()->user->getKey() : 1,
                'action' => 'update',
                'model' => 'question',
                'model_id' => $question->id,
                'date' => Carbon::now(),
                'snapshot' => $snapshot
            ]);
        });

        self::deleting(function(self $question)
        {
            foreach ($question->items as $survey_item) {
                $survey_item->delete();
            }

            return true;
        });
    }

    private static function _checkType(&$question)
    {
        if (!($type = MockUp::typeExists($question->type))) {
            throw new SmiceException(
                SmiceException::HTTP_BAD_REQUEST,
                SmiceException::E_RESOURCE,
                'Invalid question type : ' . $type . $question
            );
        } else {
            $question->answer_min = $type['answer_min'];
        }
    }

    public function society()
    {
    	return $this->belongsTo('App\Models\Society');
    }

    public function surveys()
    {
        return $this->belongsToMany('App\Models\Survey', 'survey_item', 'item_id', 'survey_id')
                    ->wherePivot('type', '=', SurveyItem::ITEM_QUESTION);
    }

    public function items()
    {
        return $this->hasMany('App\Models\SurveyItem', 'item_id')
            ->where('type', SurveyItem::ITEM_QUESTION);
    }
    /*
     * Actually this should be named rows but it was too complex for front developers
     */
    public function answers()
    {
        return $this->hasMany('App\Models\QuestionRow')->orderBy('order', 'asc');
    }

    public function cols()
    {
        return $this->hasMany('App\Models\QuestionCol')->orderBy('order', 'asc');
    }

    public function answers_comment()
    {
        return $this->hasMany('App\Models\QuestionRowComment')->orderBy('order', 'asc');
    }

    public function waveTargetConversations($wave_target_id) //, $private_comments)
    {
        return $this->hasMany('App\Models\WaveTargetConversation')->where('wave_target_id', $wave_target_id);
    }

    public function WaveTargetConversationPrivate($wave_target_id) //, $private_comments)
    {
        return $this->hasMany('App\Models\WaveTargetConversationPrivate')->where('wave_target_id', $wave_target_id);
    }

    /*
     * And this should be named answers...
     */
    public function userAnswers()
    {
        return $this->hasMany('App\Models\Answer');
    }

    public function scopeRelations($query)
    {
        $query->with('answers', 'cols');
    }

    public function scopeMinimum($query)
    {
        $query->select('id', 'name');
    }

    public function updatedEvent()
    {
        if ($this->getChildren('answers') || $this->answer_min === 0) {
            QuestionRow::updateManyRows($this, $this->getChildren('answers'));
        }
        QuestionCol::updateManyCols($this, $this->getChildren('cols'));
    }

    /**
     * Determines if a question is in the library or not
     * by running an SQL statement.
     * @param null $question_id
     * @param null $society_id
     */
    public static function inLibrary($question_id = null, $society_id = null)
    {
        return DB::affectingStatement('SELECT id FROM question WHERE society_id = :society_id AND library = true AND id = :id',
            [
                'society_id' => intval($society_id),
                'id' => intval($question_id)
            ]
        );
    }

    /**
     * Determines if a question has a row.
     * @param null $row_id
     * @param null $question_id
     * @return mixed
     */
    public static function hasRow($row_id = null, $question_id = null)
    {
        return DB::affectingStatement('SELECT id FROM question_row WHERE question_id = :question_id AND id = :row_id',
            [
                'question_id' => intval($question_id),
                'row_id' => intval($row_id)
            ]
        );
    }

    /**
     * Determines if a question has a column.
     * @param null $col_id
     * @param null $question_id
     * @return mixed
     */
    public static function hasCol($col_id = null, $question_id = null)
    {
        return DB::affectingStatement('SELECT id FROM question_col WHERE question_id = :question_id AND id = :col_id',
            [
                'question_id' => intval($question_id),
                'col_id' => intval($col_id)
            ]
        );
    }
}
