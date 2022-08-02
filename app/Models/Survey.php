<?php

namespace App\Models;

use App\Classes\MockUp;
use App\Exceptions\SmiceException;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;
use Illuminate\Support\Facades\DB;
use WavetargetScenario;

/**
 * App\Models\Survey
 *
 * @property int $id
 * @property mixed $name
 * @property mixed|null $text_end
 * @property string|null $image
 * @property string|null $image_end
 * @property bool $quiz
 * @property bool $footer_survey
 * @property bool $user_survey
 * @property bool $display_sequence
 * @property int|null $allowed_errors
 * @property string $type
 * @property int $society_id
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property bool $ended
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\SurveyItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Answer[] $answers
 * @property-read \App\Models\PassageProof $passageProof
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mission[] $missions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey retrieve($quiz_mode = false, $scenario_id = null, $axes = [], $groups = [], $shop_id = null, $wave_target_id = null, $is_admin = false)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereAllowedErrors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereDisplaySequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereFooterSurvey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereImageEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereQuiz($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereTextEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereUserSurvey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Survey whereEnded($value)
 * @mixin \Eloquent
 */
class Survey extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table        = 'survey';

    protected $primaryKey   = 'id';

    public $timestamps      = true;

    const ENDED_YES = true;
    const ENDED_NO = false;

    const SURVEY_TYPES     = [
        'Quiz',
        'Mission',
        'Profile'
    ];

    protected $jsons = [
        'name',
        'text_end'
    ];

    protected array $translatable = [
        'name',
        'text_end'
    ];

    protected $fillable     = [
        'society_id',
        'user_survey',
        'name',
        'image',
        'image_end',
        'text_end',
        'quiz',
        'footer_survey',
        'type',
        'display_sequence',
        'created_by',
        'ended'
    ];

    protected $hidden       = [
        'created_by'
    ];

    protected array $rules        = [
        'name'              => 'array|required',
        'image'             => 'string',
        'image_end'         => 'string',
        'text_end'          => 'array',
        'society_id'        => 'integer|required',
        'quiz'              => 'boolean',
        'display_sequence'  => 'boolean',
        'user_survey'       => 'boolean',
        'footer_survey'     => 'boolean',
        'created_by'        => 'integer|required|read:createdBy',
        'ended'             => 'boolean',
    ];

    protected array $list_rows    = [
        'name',
        'quiz',
        'user_survey',
        'created_at',
        'updated_at',
        'footer_survey',
        'type',
        'ended'
    ];

    private $quiz_question_types = [
        'radio' => true,
        'checkbox' => true,
        'matrix_radio' => true,
        'matrix_checkbox' => true
    ];

    protected $files        = [
        'image',
        'image_end'
    ];

    public function creatingEvent(User $user, array $params = [])
    {
        $nb_sequences       = array_get($params, 'number_sequences');
        $nb_questions       = array_get($params, 'number_questions');
        $guarded_attributes = [];

        if (is_int($nb_sequences) && $nb_sequences > 0 && $nb_sequences <= 30) {
            $guarded_attributes['number_sequences'] = $nb_sequences;
            if (is_int($nb_questions) && $nb_questions > 0 && $nb_questions <= 30) {
                $guarded_attributes['number_questions'] = $nb_questions;
            }

            $this->setGuardedAttributes($guarded_attributes);
        }
    }

    public static function getSurveyTypes()
    {
        return self::SURVEY_TYPES;
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function (self $survey) {
            $user_survey = isset($survey->user_survey) ? $survey->user_survey : false;

            if (
                $user_survey === true &&
                DB::affectingStatement(
                    'SELECT id FROM survey WHERE user_survey = true AND society_id = :society_id',
                    [
                        'society_id' => $survey->society_id
                    ]
                )
            ) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The society can not have two user surveys.'
                );
            }

            return true;
        });

        self::created(function (self $survey) {
            $nb_sequences = array_get($survey->getGuardedAttributes(), 'number_sequences');
            $nb_questions = array_get($survey->getGuardedAttributes(), 'number_questions');

            if ($nb_sequences && $nb_sequences > 0) {
                $first_id_seq = $survey->saveManySequences($nb_sequences);
                if ($nb_questions && $nb_questions > 0)
                    $survey->saveManyQuestions($nb_questions, $nb_sequences, $first_id_seq);
            }
            /*
            else
                DB::statement('INSERT
                INTO survey_item ("order", "type", item_id, survey_id)
                VALUES (0, :item_type, (SELECT id FROM sequence WHERE default_sequence = true AND society_id = :society_id), :survey_id)',
                        [
                            'item_type' => SurveyItem::ITEM_SEQUENCE,
                            'society_id' => intval($survey->society->getKey()),
                            'survey_id' => intval($survey->getKey())
                        ]
                    );*/
        });

        self::updating(function (self $survey) {
            $user_survey = isset($survey->user_survey) ? $survey->user_survey : false;

            if (
                $user_survey === true &&
                DB::affectingStatement(
                    'SELECT id FROM survey WHERE user_survey = true
                  AND society_id = :society_id AND id <> :survey_id',
                    [
                        'society_id' => $survey->society_id,
                        'survey_id' => $survey->getKey()
                    ]
                )
            ) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'The society can not have two user surveys.'
                );
            }

            return true;
        });

        self::deleting(function (Survey $survey) {
            /* We have to check if answer is link to existing answers
                 */
            $answers = Answer::where('survey_id', $survey->survey_id);
            if ($answers->count() > 0) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'This survey has already been answered and cannot be deleted.'
                );
            }
            foreach ($survey->items as $survey_item)
                $survey_item->delete();

            return true;
        });
    }

    public static function getURI()
    {
        return 'surveys';
    }

    public static function getName()
    {
        return 'survey';
    }

    public function getModuleName()
    {
        return 'surveys';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function items()
    {
        return $this->hasMany('App\Models\SurveyItem');
    }

    public function answers()
    {
        return $this->hasMany('App\Models\Answer');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function passageProof()
    {
        return $this->hasOne('App\Models\PassageProof');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missions()
    {
        return $this->hasMany(Mission::class, 'survey_id');
    }

    public function scopeMinimum($query)
    {
        $query->select('id', 'name');
    }

    public function scopeRetrieve($query, $quiz_mode = false, $scenario_id = null, $axes = [], $groups = [], $shop_id = null, $wave_target_id = null, $is_admin = false)
    {
        $survey = $query->with(['items' => function ($query) {
            $query->whereNull('survey_item.parent_id')
                ->orderBy('survey_item.order', 'asc')
                ->withInfo()
                ->withItems();
        }])->find($this->getKey());
        $wt = Wavetarget::with('mission')->find($wave_target_id);
        if ($wt) {
            $nb_quest = $this->_clean($survey->items, $quiz_mode, $scenario_id, $axes, $groups, $shop_id, true, $is_admin, $wt->mission, $wt);
        } else {
            $nb_quest = $this->_clean($survey->items, $quiz_mode, $scenario_id, $axes, $groups, $shop_id, true, $is_admin, null, null);
        }

        $survey['number_questions'] = $nb_quest;

        //Reorganize survey to have good incrementation even clean function has remove some sequence or question
        $items = $this->numbering($survey['items'], $is_admin, $wave_target_id, '');

        unset($survey['items']);
        $survey['items'] = $items;
        return $survey;
    }

    private function numbering($items, $is_admin, $wave_target_id = NULL, $numbering = '')
    {
        $r_items = [];
        $key = 1;
        foreach ($items as $item) {
            if ($item->type == SurveyItem::ITEM_SEQUENCE) {
                $item->sequence['numbering'] = $numbering . $key . '.';
            } elseif ($item->type == SurveyItem::ITEM_QUESTION && !$is_admin && $item->is_hide_for_smicer === SurveyItem::IS_HIDE_FOR_SMICER_YES) {
                continue;
            } elseif ($item->type == SurveyItem::ITEM_QUESTION) {
                $item->question['numbering'] = $numbering . $key . '.';
                if ($wave_target_id) {
                    $item->question->wave_target_conversations = $item->question->waveTargetConversations($wave_target_id)->with('createdBy')->get();
                    $item->question->wave_target_private_conversations = $item->question->WaveTargetConversationPrivate($wave_target_id)->with('createdBy')->get();
                }
            }
            $childKey = 1;
            foreach ($item->items as $i => &$childItem) {
                if ($childItem->type == SurveyItem::ITEM_SEQUENCE) {
                    $childItem->sequence['numbering'] = $numbering . $key . '.' . $childKey . '.';
                    $this->numbering($childItem['items'], $is_admin, $wave_target_id, $numbering . $key . '.' . $childKey . '.');
                } elseif (!$is_admin && $childItem->is_hide_for_smicer === SurveyItem::IS_HIDE_FOR_SMICER_YES) {
                    unset($item->items[$i]);
                    continue;
                } else {
                    $childItem->question['numbering'] = $numbering . $key . '.' . $childKey . '.';
                    if ($wave_target_id) {
                        $childItem->question->wave_target_conversations = $childItem->question->waveTargetConversations($wave_target_id)->with('createdBy')->get();
                        $childItem->question->wave_target_private_conversations = $childItem->question->WaveTargetConversationPrivate($wave_target_id)->with('createdBy')->get();
                    }
                }
                $childKey++;
            }

            $r_items[] = $item;
            $key++;
        }

        return $r_items;
    }

    /**
     * Clean the survey by removing the sequence / question
     * from an item when needed.
     * Clean the survey depending on the scenario / axes or shops given.
     * Calculate the number of questions in the survey, by sequence and in total.
     * @param $items
     * @param $scenario_id
     * @param $axes
     * @param $groups
     * @param $shop_id
     * @param $first_loop
     * @param $quiz_mode
     * @return mixed
     */
    private function _clean(&$items, $quiz_mode, $scenario_id, $axes, $groups, $shop_id, $first_loop = true, $is_admin, $mission = null, $wt = null)
    {
        static $nb_total_quest  = 0;
        $nb_quest_in_seq        = 0;

        foreach ($items as $key => $item) {
            /* @var $item SurveyItem */
            //clean json structure transfor "[]" string to null
            if ($item->conditions == '[]') {
                $items[$key]['conditions'] = null;
            }

            $filter = false;


            /*
             * check options in mission model : set all question to mandatory & disable seq filter
             */
            if ($mission) {
                if ($mission->all_question_mandatory === true) {
                    $items[$key]['required'] = true;
                }
            }

            /*
             * filter on scenarion, axe, shop, groups
             */
            if (!$is_admin && $wt && !$wt->visit_date && $item->date_range === SurveyItem::DATE_RANGE_YES) {
                unset($items[$key]);
                $filter = true;
            }
            if (!$is_admin && $item->is_hide_for_smicer === SurveyItem::IS_HIDE_FOR_SMICER_YES) {
                unset($items[$key]);
                $filter = true;
            } else if ((!$is_admin || $is_admin === "2") && $wt && $wt->visit_date && $item->date_range === SurveyItem::DATE_RANGE_YES) {

                $startDate = strtotime($item->date_start);
                if ($startDate === false)
                    $startDate = strtotime('2000-01-01');

                $endDate = strtotime($item->date_end);
                if ($endDate === false)
                    $endDate = strtotime('2099-01-01');

                $userDate = strtotime($wt->visit_date);
                if ($userDate < $startDate || $userDate > $endDate) {
                    unset($items[$key]);
                    $filter = true;
                }
            }
            if ($mission && $mission->disable_seq_condition === true && $item->type === SurveyItem::ITEM_SEQUENCE) {
                //no filter on sequence
            } else {
                if ($item->exclusion_filter == 'exclusion') {
                    //Get all axes link to this item
                    $item_axes = $item->axes->modelKeys();
                    //Get all groups link to this item
                    $item_groups = $item->groups->modelKeys();
                    //filter on shop
                    if ($shop_id && !$item->shops->isEmpty() && !$item->shops->where('id', $shop_id)->isEmpty()) {
                        unset($items[$key]);
                        $filter = true;
                    }
                    //filter on scenario
                    if ($scenario_id && !$item->scenarios->isEmpty() && !$item->scenarios->where('id', $scenario_id)->isEmpty()) {
                        unset($items[$key]);
                        $filter = true;
                    }
                    //filter on axe
                    if ($axes && !$item->axes->isEmpty() && !empty(array_intersect($item_axes, $axes))) {
                        unset($items[$key]);
                        $filter = true;
                    }
                    //filter on group
                    if ($groups && !$item->groups->isEmpty() && !empty(array_intersect($item_groups, $groups))) {
                        unset($items[$key]);
                        $filter = true;
                    }
                } else if ($item->exclusion_filter == 'inclusion') {
                    //Get all axes link to this item
                    $item_axes = $item->axes->modelKeys();
                    //Get all groups link to this item
                    $item_groups = $item->groups->modelKeys();
                    //filter on shop
                    if ($shop_id && !$item->shops->isEmpty() && $item->shops->where('id', $shop_id)->isEmpty()) {
                        if ($axes) {
                            if ($item->axes->isEmpty() || empty(array_intersect($item_axes, $axes))) {
                                unset($items[$key]);
                                $filter = true;
                            }
                        } else {
                            unset($items[$key]);
                            $filter = true;
                        }
                    }
                    //filter on scenario
                    if ($scenario_id && !$item->scenarios->isEmpty() && $item->scenarios->where('id', $scenario_id)->isEmpty()) {
                        unset($items[$key]);
                        $filter = true;
                    }
                    if (!$axes && !$is_admin &&  !$item->axes->isEmpty()) { //no axe for this shop
                        unset($items[$key]);
                        $filter = true;
                    }
                    //filter on axe
                    if ($axes && !$item->axes->isEmpty() && empty(array_intersect($item_axes, $axes))) {
                        if ($shop_id) {
                            if ($item->shops->isEmpty() || $item->shops->where('id', $shop_id)->isEmpty()) {
                                unset($items[$key]);
                                $filter = true;
                            }
                        } else {
                            unset($items[$key]);
                            $filter = true;
                        }
                    }
                    //filter on group
                    if (!$groups && !$is_admin &&  !$item->groups->isEmpty()) { //no group for user
                        unset($items[$key]);
                        $filter = true;
                    }
                    if ($groups && !$item->groups->isEmpty() && empty(array_intersect($item_groups, $groups))) {
                        unset($items[$key]);
                        $filter = true;
                    }
                }
            }


            if (!$filter) {
                if ($item->type === SurveyItem::ITEM_SEQUENCE) {
                    unset($items[$key]['question']);
                    $nb_quest = $this->_clean($item->items, $quiz_mode, $scenario_id, $axes, $groups, $shop_id, false, $is_admin, $mission, $wt);
                    $items[$key]['number_questions'] = $nb_quest;
                    $items[$key]->setRelation('items', $item->items->values());
                    $nb_total_quest += $nb_quest;
                } else {
                    if ($quiz_mode && !isset($this->quiz_question_types[$item->question->type])) {
                        unset($items[$key]);
                    } else {
                        unset($items[$key]['sequence']);
                        unset($item->items);
                        $nb_quest_in_seq++;
                    }
                }
            }
        }
        //Organise array
        if ($first_loop) {
            return $nb_total_quest;
        }

        return $nb_quest_in_seq;
    }

    /**
     * Determines if a question is in a survey.
     * @param null $question_id
     * @param null $survey_id
     * @return mixed
     */
    public static function hasQuestion($question_id = null, $survey_id = null)
    {
        return DB::affectingStatement(
            'SELECT id FROM survey_item WHERE "type" = :item_type AND item_id = :question_id AND survey_id = :survey_id',
            [
                'item_type' => SurveyItem::ITEM_QUESTION,
                'question_id' => intval($question_id),
                'survey_id' => intval($survey_id)
            ]
        );
    }

    /**
     * This function insert many sequences in a survey when creating
     * a survey.
     * It returns the ID of the first survey_item created.
     * @param $number_sequences
     * @return int
     */
    private function        saveManySequences($number_sequences)
    {
        $mock_up            = MockUp::get(13);
        $sequences = $sequence_items = [];
        $nb_max_sequences   = $number_sequences;

        while ($number_sequences > 0) {
            array_push($sequences, [
                'society_id' => $this->society->getKey(),
                'name' => json_encode($mock_up['name']),
                'created_by' => $this->created_by
            ]);
            $number_sequences--;
        }
        DB::table('sequence')->insert($sequences);

        $first_id_seq = intval(DB::getPDO()->lastInsertId('sequence_id_seq')) - ($nb_max_sequences - 1);
        for ($i = 0; $i < $nb_max_sequences; $i++) {
            array_push($sequence_items, [
                'survey_id' => $this->getKey(),
                'item_id' => $first_id_seq,
                'type' => SurveyItem::ITEM_SEQUENCE,
                'order' => $i,
                'parent_id' => null
            ]);
            $first_id_seq++;
        }
        DB::table('survey_item')->insert($sequence_items);

        return (intval(DB::getPDO()->lastInsertId('survey_item_id_seq')) - ($nb_max_sequences - 1));
    }

    /**
     * This function insert X questions for Y sequences in the survey.
     * The mockup used is the one for the textarea.
     * The $first_parent_id is the ID of the first survey_item that contains the first
     * sequence to begin the insertion.
     * @param $quest_per_seq
     * @param $nb_seq
     * @param $first_parent_id
     * @return bool
     */
    private function        saveManyQuestions($quest_per_seq, $nb_seq, $first_parent_id)
    {
        $mockup             = MockUp::get(1);
        $questions = $question_items = [];
        $number_questions   = ($quest_per_seq * $nb_seq);
        $first_quest_id     = null;

        while ($number_questions > 0) {
            array_push($questions, [
                'type' => $mockup['type'],
                'answer_min' => $mockup['answer_min'],
                'image' => $mockup['image'],
                'society_id' => $this->society->getKey(),
                'name' => json_encode($mockup['name']),
                'created_by' => $this->created_by
            ]);
            $number_questions--;
        }
        DB::table('question')->insert($questions);
        $first_quest_id = (intval(DB::getPDO()->lastInsertId('question_id_seq')) - (($quest_per_seq * $nb_seq) - 1));

        while ($nb_seq > 0) {
            for ($i = 0; $i < $quest_per_seq; $i++) {
                array_push($question_items, [
                    'survey_id' => $this->getKey(),
                    'item_id' => $first_quest_id,
                    'type' => SurveyItem::ITEM_QUESTION,
                    'order' => $i,
                    'parent_id' => $first_parent_id
                ]);
                $first_quest_id++;
            }
            $first_parent_id++;
            $nb_seq--;
        }
        DB::table('survey_item')->insert($question_items);

        return true;
    }
}
