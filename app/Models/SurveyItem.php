<?php

namespace App\Models;

use App\Classes\Helpers\ArrayHelper;
use App\Classes\MockUp;
use App\Exceptions\SmiceException;
use App\Interfaces\iREST;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\LogModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Models\SurveyItem
 *
 * @property int $id
 * @property mixed|null $conditions
 * @property int $survey_id
 * @property int|null $parent_id
 * @property string $type
 * @property bool $required
 * @property int $item_id
 * @property int $order
 * @property int|null $weight
 * @property bool|null $scoring
 * @property bool|null $comment_flag
 * @property string|null $comment_select
 * @property bool $display_report
 * @property int|null $criteria_weight
 * @property int|null $theme_id
 * @property int|null $criteria_id
 * @property int|null $criteria_a_id
 * @property int|null $criteria_b_id
 * @property int|null $job_id
 * @property string|null $exclusion_filter
 * @property bool $tag
 * @property bool $is_hide_for_smicer
 * @property bool $bonus_question
 * @property bool $show_verbatim
 * @property bool $is_visible_on_top_report
 * @property bool $is_visible_on_split_by_question
 * @property bool $date_range
 * @property string|null $date_start
 * @property string|null $date_end
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Axe[] $axes
 * @property-read \App\Models\Criteria|null $criteria
 * @property-read \App\Models\CriteriaA|null $criteriaA
 * @property-read \App\Models\CriteriaB|null $criteriaB
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CriteriaA[] $criterionA
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CriteriaB[] $criterionB
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SurveyItem[] $items
 * @property-read \App\Models\Job|null $job
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Job[] $jobs
 * @property-read \App\Models\SurveyItem|null $parent
 * @property-read \App\Models\Question $question
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Scenario[] $scenarios
 * @property-read \App\Models\Sequence $sequence
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Shop[] $shops
 * @property-read \App\Models\Survey $survey
 * @property-read \App\Models\Theme|null $theme
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Group[] $groups
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Theme[] $themes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SurveyItem[] $children
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereCommentFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereCommentSelect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereCriteriaAId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereCriteriaBId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereCriteriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereCriteriaWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereDisplayReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereExclusionFilter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereScoring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem withInfo()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem withItems()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereIsHideForSmicer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereBonusQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereIsVisibleOnSplitByQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereIsVisibleOnTopReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereShowVerbatim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereDateRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SurveyItem whereDateStart($value)
 * @mixin \Eloquent
 */
class SurveyItem extends SmiceModel implements iREST
{
    const ITEM_SEQUENCE = 'sequence';
    const ITEM_QUESTION = 'question';
    const ITEM_MOCK_UP = 'mockup';

    const DATE_RANGE_NO = false;
    const DATE_RANGE_YES = true;

    const IS_HIDE_FOR_SMICER_YES = true;
    const IS_HIDE_FOR_SMICE_NO = false;

    protected $table = 'survey_item';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'required',
        'conditions',
        'weight',
        'scoring',
        'criteria_weight',
        'theme_id',
        'criteria_id',
        'criteria_a_id',
        'criteria_b_id',
        'job_id',
        'question',
        'sequence',
        'scenarios',
        'groups',
        'shops',
        'axes',
        'themes',
        'jobs',
        'criterion_a',
        'criterion_b',
        'exclusion_filter',
        'comment_select',
        'comment_flag',
        'display_report',
        'survey_id',
        'item_id',
        'order',
        'type',
        'parent_id',
        'tag',
        'bonus_question',
        'is_hide_for_smicer',
        'show_verbatim',
        'is_visible_on_top_report',
        'is_visible_on_first_page_report',
        'is_visible_on_split_by_question',
        'date_range',
        'date_start',
        'date_end',
        'available_as_filter'
    ];

    protected $hidden = [
        //        'survey_id',
        //        'item_id',
    ];

    protected $jsons = [
        'conditions'
    ];

    protected $rules = [
        'survey_id'         => 'integer|required|read:surveys',
        'parent_id'         => 'integer',
        'required'          => 'boolean',
        'item_id'           => 'integer|required|unique_with:survey_item,survey_id,type,{id}',
        'weight'            => 'integer|between:0,100',
        'scoring'           => 'boolean',
        'criteria_weight'   => 'integer|between:0,100|required_with:criteria_id',
        'order'             => 'integer|required|min:0',
        'type'              => 'required|in:' . self::ITEM_SEQUENCE . ',' . self::ITEM_QUESTION,
        'theme_id'          => 'integer|read:themes',
        'criteria_id'       => 'integer|read:criterion',
        'criteria_a_id'     => 'integer|read:criterionA',
        'criteria_b_id'     => 'integer|read:criterionB',
        'job_id'            => 'integer|read:jobs',
        'question'          => 'array',
        'sequence'          => 'array',
        'conditions'        => 'array',
        'scenarios'         => 'array',
        'groups'            => 'array',
        'shops'             => 'array',
        'axes'              => 'array',
        'themes'            => 'array',
        'jobs'              => 'array',
        'criterion_a'       => 'array',
        'criterion_b'       => 'array',
        'comment_select'    => 'string',
        'comment_flag'      => 'boolean',
        'exclusion_filter'  => 'string',
        'display_report'    => 'boolean',
        'bonus_question'    => 'boolean',
        'is_hide_for_smicer' => 'boolean',
        'shop_verbatim'     => 'boolean',
        'date_range'           => 'boolean',
        'date_start'        => 'date',
        'date_end'          => 'date|after:date_start',
        'available_as_filter' => 'boolean'
    ];

    protected $children = [
        'question',
        'sequence',
        'scenarios',
        'groups',
        'axes',
        'shops',
        'themes',
        'jobs',
        'criterion_a',
        'criterion_b'
    ];

    public static $history = [
        'required',
        'conditions',
        'weight',
        'scoring',
        'criteria_weight',
        'theme_id',
        'criteria_id',
        'criteria_a_id',
        'criteria_b_id',
        'job_id',
        'question',
        'sequence',
        'scenarios',
        'groups',
        'shops',
        'axes',
        'themes',
        'jobs',
        'criterion_a',
        'criterion_b',
        'exclusion_filter',
        'comment_select',
        'comment_flag',
        'display_report',
        'survey_id',
        'item_id',
        'order',
        'type',
        'parent_id',
        'tag',
        'bonus_question',
        'is_hide_for_smicer',
        'show_verbatim',
        'is_visible_on_top_report',
        'is_visible_on_first_page_report',
        'is_visible_on_split_by_question',
        'available_as_filter'
    ];

    public static function getURI()
    {
        return 'items';
    }

    public static function getName()
    {
        return 'item';
    }

    protected static function boot()
    {
        parent::boot();

        self::created(function (self $surveyItem) {
            $snapshot = [
                'conditions' => $surveyItem->conditions,
                'survey_id' => $surveyItem->survey_id,
                'parent_id' => $surveyItem->parent_id,
                'type' => $surveyItem->type,
                'required' => $surveyItem->required,
                'item_id' => $surveyItem->item_id,
                'order' => $surveyItem->order,
                'weight' => $surveyItem->weight,
                'scoring' => $surveyItem->scoring,
                'comment_flag' => $surveyItem->comment_flag,
                'comment_select' => $surveyItem->comment_select,
                'display_report' => $surveyItem->display_report,
                'criteria_weight' => $surveyItem->criteria_weight,
                'theme_id' => $surveyItem->theme_id,
                'criteria_id' => $surveyItem->criteria_id,
                'criteria_a_id' => $surveyItem->criteria_a_id,
                'criteria_b_id' => $surveyItem->criteria_b_id,
                'job_id' => $surveyItem->job_id,
                'exclusion_filter' => $surveyItem->exclusion_filter,
                'tag' => $surveyItem->tag,
                'is_hide_for_smicer' => $surveyItem->is_hide_for_smicer,
                'bonus_question' => $surveyItem->bonus_question,
                'show_verbatim' => $surveyItem->show_verbatim,
                'is_visible_on_report' => $surveyItem->is_visible_on_report,
                'is_visible_on_split_by_question' => $surveyItem->is_visible_on_split_by_question
            ];
            LogModel::create([
                'user_id' => isset(request()->user) ? request()->user->getKey() : 1,
                'action' => 'create',
                'model' => 'survey_item',
                'model_id' => $surveyItem->id,
                'date' => Carbon::now(),
                'snapshot' => $snapshot
            ]);
        });
        self::updating(function (self $surveyItem) {
            $snapshot = [];
            foreach (SurveyItem::$history as $field) {
                if ($surveyItem->isDirty($field)) {
                    $snapshot[$field] = $surveyItem->$field;
                }
            }
            if (empty($snapshot)) {
                return;
            }
            LogModel::create([
                'user_id' => isset(request()->user) ? request()->user->getKey() : 1,
                'action' => 'update',
                'model' => 'survey_item',
                'model_id' => $surveyItem->id,
                'date' => Carbon::now(),
                'snapshot' => $snapshot
            ]);
        });
        self::deleting(function (self $surveyItem) {
            $snapshot = [
                'conditions' => $surveyItem->conditions,
                'survey_id' => $surveyItem->survey_id,
                'parent_id' => $surveyItem->parent_id,
                'type' => $surveyItem->type,
                'required' => $surveyItem->required,
                'item_id' => $surveyItem->item_id,
                'order' => $surveyItem->order,
                'weight' => $surveyItem->weight,
                'scoring' => $surveyItem->scoring,
                'comment_flag' => $surveyItem->comment_flag,
                'comment_select' => $surveyItem->comment_select,
                'display_report' => $surveyItem->display_report,
                'criteria_weight' => $surveyItem->criteria_weight,
                'theme_id' => $surveyItem->theme_id,
                'criteria_id' => $surveyItem->criteria_id,
                'criteria_a_id' => $surveyItem->criteria_a_id,
                'criteria_b_id' => $surveyItem->criteria_b_id,
                'job_id' => $surveyItem->job_id,
                'exclusion_filter' => $surveyItem->exclusion_filter,
                'tag' => $surveyItem->tag,
                'is_hide_for_smicer' => $surveyItem->is_hide_for_smicer,
                'bonus_question' => $surveyItem->bonus_question,
                'show_verbatim' => $surveyItem->show_verbatim,
                'is_visible_on_report' => $surveyItem->is_visible_on_report,
                'is_visible_on_split_by_question' => $surveyItem->is_visible_on_split_by_question
            ];
            LogModel::create([
                'user_id' => isset(request()->user) ? request()->user->getKey() : 1,
                'action' => 'delete',
                'model' => 'survey_item',
                'model_id' => $surveyItem->id,
                'date' => Carbon::now(),
                'snapshot' => $snapshot
            ]);
            if ($surveyItem->type === self::ITEM_QUESTION) {

                /* We have to check if answer is link to existing answers
                 */
                $answers = Answer::where('question_id', $surveyItem->item_id)->where('survey_id', $surveyItem->survey_id);
                if ($answers->count() > 0) {
                    throw new SmiceException(
                        SmiceException::HTTP_BAD_REQUEST,
                        SmiceException::E_VARIABLE,
                        'This question has already been answered and cannot be deleted.'
                    );
                }
            }

            return true;
        });
    }

    public static function getItemTypes()
    {
        return [self::ITEM_SEQUENCE, self::ITEM_QUESTION];
    }

    public function survey()
    {
        return $this->belongsTo('App\Models\Survey');
    }

    public function question()
    {
        return $this->belongsTo('App\Models\Question', 'item_id');
    }

    public function sequence()
    {
        return $this->belongsTo('App\Models\Sequence', 'item_id');
    }

    public function scenarios()
    {
        return $this->belongsToMany('App\Models\Scenario', 'survey_item_scenario');
    }

    public function axes()
    {
        return $this->belongsToMany('App\Models\Axe', 'survey_item_axe');
    }

    public function themes()
    {
        return $this->belongsToMany('App\Models\Theme', 'survey_item_theme');
    }

    public function jobs()
    {
        return $this->belongsToMany('App\Models\Job', 'survey_item_job');
    }

    public function shops()
    {
        return $this->belongsToMany('App\Models\Shop', 'survey_item_shop');
    }

    public function groups()
    {
        return $this->belongsToMany('App\Models\Group', 'survey_item_group');
    }

    public function criterionA()
    {
        return $this->belongsToMany('App\Models\CriteriaA', 'survey_item_criterion_a');
    }

    public function criterionB()
    {
        return $this->belongsToMany('App\Models\CriteriaB', 'survey_item_criterion_b');
    }

    public function theme()
    {
        return $this->belongsTo('App\Models\Theme');
    }

    public function criteria()
    {
        return $this->belongsTo('App\Models\Criteria');
    }

    public function criteriaA()
    {
        return $this->belongsTo('App\Models\CriteriaA');
    }

    public function criteriaB()
    {
        return $this->belongsTo('App\Models\CriteriaB');
    }

    public function job()
    {
        return $this->belongsTo('App\Models\Job');
    }

    public function items()
    {
        return $this->hasMany('App\Models\SurveyItem', 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\SurveyItem', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Models\SurveyItem', 'parent_id', 'id')->with('children')->where('type', 'sequence')->orderBy('order');
    }

    public function surveyItemHistory()
    {
        return $this->hasMany('App\Models\SurveyItemHistory')->orderBy('id');
    }

    /**
     * Join all the info to an item.
     * @param $query
     * @return mixed
     */
    public function scopeWithInfo($query)
    {
        return $query->with([
            'shops' => function ($query) {
                $query->select('shop.id', 'shop.name');
            },
            'axes',
            'scenarios',
            'criteria',
            'sequence',
            'themes',
            'jobs',
            'groups',
            'criterionA',
            'criterionB',
            'question.answers.colors',
            'question.answers.comments',
            'question.cols',
            //            'question.waveTargetConversations.createdBy',
        ]);
    }

    /**
     * Join all the items of an item.
     * @param $query
     * @return mixed
     */
    public function scopeWithItems($query)
    {
        return $query->with(
            [
                'items' => function ($query) {
                    $query->withInfo()
                        ->withItems()
                        ->orderBy('survey_item.order', 'asc');
                }
            ]
        );
    }

    public function scopeRetrieve($query)
    {
        $item = $query->withInfo()->withItems()->find($this->getKey());
        if ($item->type === SurveyItem::ITEM_SEQUENCE) {
            unset($item['question']);
            $nb_quest = $this->_clean($item->items);
            $item['number_questions'] = $nb_quest;
        } else {
            unset($item['sequence'], $item->items);
        }
        return $item;
    }

    /**
     * Clean the items of an item by removing the sequence / question
     * from an item when needed
     * @param $items
     * @pâram $first_loop
     * @return mixed
     */
    private function _clean(&$items)
    {
        $nb_quest_in_seq = 0;

        foreach ($items as $key => $item) {
            if ($item->type === SurveyItem::ITEM_SEQUENCE) {
                unset($items[$key]['question']);
                $nb_quest = $this->_clean($item->items);
                $items[$key]['number_questions'] = $nb_quest;
            } else {
                $nb_quest_in_seq++;
                unset($items[$key]['sequence'], $item->items);
            }
        }
        return $nb_quest_in_seq;
    }

    /**
     * This function is called before creating an item.
     * It links the new item to a question or a sequence.
     * Then it moves the item in the survey.
     * @param array $params
     * @param User $user
     * @throws SmiceException
     * @return null
     */
    public function creatingEvent(User $user, array $params = [])
    {
        $item_id = intval(array_get($params, 'id'));

        switch (array_get($params, 'type')) {
            case self::ITEM_MOCK_UP:
                $this->_createFromMockUp($item_id, $user);
                break;
            case self::ITEM_QUESTION:
                if (!Question::inLibrary($item_id, $user->society['id']))
                    throw new SmiceException(
                        SmiceException::HTTP_BAD_REQUEST,
                        SmiceException::E_RESOURCE,
                        'Survey: question not found.'
                    );
                $this->item_id = $item_id;
                $this->type = self::ITEM_QUESTION;
                break;
            case self::ITEM_SEQUENCE;
                if (!Sequence::inLibrary($item_id, $user->society['id']))
                    throw new SmiceException(
                        SmiceException::HTTP_BAD_REQUEST,
                        SmiceException::E_RESOURCE,
                        'Survey: sequence not found.'
                    );
                $this->item_id = $item_id;
                $this->type = self::ITEM_SEQUENCE;
                break;
            default:
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'Survey: unrecognized item type.'
                );
        }

        if ($this->_shouldMove(array_get($params, 'order'), array_get($params, 'parent_id'))) {
            $this->_move();
        }
    }

    /**
     * This function is called before updating a item.
     * It moves the item if it was moved.
     * @param array $params
     * @param User $user
     * @throws SmiceException
     * @return null
     */
    public function updatingEvent(User $user, array $params = [])
    {
        $current = SurveyItem::find($this->getKey());
        if ($this->_shouldMove(array_get($params, 'order'), array_get($params, 'parent_id'), $current)) {
            $this->_move($current);
            //$this->exists = false;
            unset($this->old_order);
            unset($this->old_parent_id);
        }
        // Remove useless fields
        if ($this->type === self::ITEM_SEQUENCE) {
            $this->attributes['criteria_id'] = null;
            $this->attributes['criteria_weight'] = null;
        } elseif (!$this->criteria_id) {
            $this->attributes['criteria_weight'] = null;
        }
    }

    /**
     * This function is called after an item was updated.
     * It updates the related sequence or question, then synchronise the scenarios and axes id.
     */
    protected function updatedEvent()
    {
        if (
            $this->type === self::ITEM_SEQUENCE && !$this->sequence->default_sequence &&
            $this->getChildren('sequence')
        ) {
            $this->sequence->update($this->getChildren('sequence'));
        } elseif ($this->type === self::ITEM_QUESTION && $this->getChildren('question')) {
            $this->question->update($this->getChildren('question'));
        }

        $this->_syncScenarios();
        $this->_syncGroups();
        $this->_syncShops();
        $this->_syncAxes();
        $this->_syncThemes();
        $this->_syncJobs();
        $this->_syncCriterionA();
        $this->_syncCriterionB();
    }

    /**
     * Synchronise the scenarios id when updating the survey item
     */
    private function _syncScenarios()
    {
        $scenarios = $this->getChildren('scenarios');
        $scenarios_id = [];

        Validator::make(['scenarios' => $scenarios], ['scenarios' => 'array_array:id'])->passOrDie();
        foreach ($scenarios as $scenario) {
            $scenarios_id[] = $scenario['id'];
        }
        Validator::make(['scenarios' => $scenarios_id], ['scenarios' => 'int_array|read:scenarios'])->passOrDie();
        $this->scenarios()->sync($scenarios_id);
    }

    /**
     * Synchronise the groups id when updating the survey item
     */
    private function _syncGroups()
    {
        $groups = $this->getChildren('groups');
        $groups_id = [];

        Validator::make(['groups' => $groups], ['groups' => 'array_array:id'])->passOrDie();
        foreach ($groups as $group) {
            $groups_id[] = $group['id'];
        }
        Validator::make(['groups' => $groups_id], ['groups' => 'int_array|read:groups'])->passOrDie();
        $this->groups()->sync($groups_id);
    }

    /**
     * Synchronise the shops id when updating the survey item
     */

    private function _syncShops()
    {
        $shops = $this->getChildren('shops');
        $shops_id = [];

        Validator::make(['shops' => $shops], ['shops' => 'array_array:id'])->passOrDie();
        foreach ($shops as $shop)
            $shops_id[] = $shop['id'];
        Validator::make(['shops' => $shops_id], ['shops' => 'int_array|read:shops'])->passOrDie();

        $this->shops()->sync($shops_id);
    }

    /**
     * Synchronise the themes id when updating the survey item
     */
    private function _syncThemes()
    {
        $themes = $this->getChildren('themes');
        $themes_id = [];

        Validator::make(['themes' => $themes], ['themes' => 'array_array:id'])->passOrDie();
        foreach ($themes as $theme) {
            $themes_id[] = $theme['id'];
        }
        Validator::make(['themes' => $themes_id], ['themes' => 'int_array|read:themes'])->passOrDie();
        $this->themes()->sync($themes_id);
    }

    /**
     * Synchronise the jobs id when updating the survey item
     */
    private function _syncJobs()
    {
        $jobs = $this->getChildren('jobs');
        $jobs_id = [];

        Validator::make(['jobs' => $jobs], ['jobs' => 'array_array:id'])->passOrDie();
        foreach ($jobs as $job) {
            $jobs_id[] = $job['id'];
        }
        Validator::make(['jobs' => $jobs_id], ['jobs' => 'int_array|read:jobs'])->passOrDie();
        $this->jobs()->sync($jobs_id);
    }

    /**
     * Synchronise the criterion_a id when updating the survey item
     */
    private function _syncCriterionA()
    {
        $criterion_a = $this->getChildren('criterion_a');
        $criterion_a_id = [];

        Validator::make(['criterion_a' => $criterion_a], ['criterion_a' => 'array_array:id'])->passOrDie();
        foreach ($criterion_a as $criteria_a) {
            $criterion_a_id[] = $criteria_a['id'];
        }
        Validator::make(['criterion_a' => $criterion_a_id], ['criterion_a' => 'int_array|read:criterionA'])->passOrDie();
        $this->criterionA()->sync($criterion_a_id);
    }

    /**
     * Synchronise the criterion_b id when updating the survey item
     */
    private function _syncCriterionB()
    {
        $criterion_b = $this->getChildren('criterion_b');
        $criterion_b_id = [];

        Validator::make(['criterion_b' => $criterion_b], ['criterion_b' => 'array_array:id'])->passOrDie();
        foreach ($criterion_b as $criteria_b) {
            $criterion_b_id[] = $criteria_b['id'];
        }
        Validator::make(['criterion_b' => $criterion_b_id], ['criterion_b' => 'int_array|read:criterionB'])->passOrDie();
        $this->criterionB()->sync($criterion_b_id);
    }

    /**
     * Synchronise the axes id when updating the survey item
     */
    private function _syncAxes()
    {
        $axes = $this->getChildren('axes');
        $axes_id = [];

        Validator::make(['axes' => $axes], ['axes' => 'array_array:id'])->passOrDie();
        foreach ($axes as $axe) {
            $axes_id[] = $axe['id'];
        }
        $this->axes()->sync($axes_id);
    }

    /**
     * This function creates a question / sequence from a mock up
     * and associate it to the item.
     * @param $mock_up_id
     * @param User $user
     * @return bool
     * @throws SmiceException
     */
    private function _createFromMockUp($mock_up_id, User $user)
    {
        $mock_up = MockUp::get($mock_up_id);

        if (!$mock_up)
            throw new SmiceException(
                SmiceException::HTTP_BAD_REQUEST,
                SmiceException::E_RESOURCE,
                'Mock up not found: invalid id.'
            );

        /*
         * Insert a new sequence
         */
        if ($mock_up['type'] === self::ITEM_SEQUENCE) {
            $id = DB::table('sequence')->insertGetId([
                'society_id' => $user->society['id'],
                'name' => json_encode($mock_up['name']),
                'created_by' => $user->getKey()
            ]);

            $this->item_id = $id;
            $this->type = self::ITEM_SEQUENCE;

            return true;
        }
        /*
         * Insert the question
         */
        $id = DB::table('question')->insertGetId([
            'type' => $mock_up['type'],
            'answer_min' => $mock_up['answer_min'],
            'image' => $mock_up['image'],
            'society_id' => $user->society->getKey(),
            'name' => json_encode($mock_up['name']),
            'created_by' => $user->getKey()
        ]);

        /*
         * Insert the answers
         */
        if (isset($mock_up['answers'])) {
            $answers = [];
            foreach ($mock_up['answers'] as $mock_up_answer) {
                array_push($answers, [
                    'name' => json_encode($mock_up_answer['name']),
                    'order' => $mock_up_answer['order'],
                    'value' => $mock_up_answer['value'],
                    'image' => $mock_up_answer['image'],
                    'question_id' => $id
                ]);
            }
            DB::table('question_row')->insert($answers);
        }

        /*
         * Insert the columns
         */
        if (isset($mock_up['cols'])) {
            $cols = [];
            foreach ($mock_up['cols'] as $mock_up_cols) {
                array_push($cols, [
                    'name' => json_encode($mock_up_cols['name']),
                    'order' => $mock_up_cols['order'],
                    'question_id' => $id
                ]);
            }
            DB::table('question_col')->insert($cols);
        }

        $this->item_id = $id;
        $this->type = self::ITEM_QUESTION;

        return true;
    }

    /**
     * This function determines if a item's position was changed.
     * The order and parent_id of the item are set here.
     * It compares the old_parent_id and the parent_id, and the old_order and order.
     * @param $order
     * @param $parent_id
     * @return bool
     * @throws SmiceException
     */
    private function _shouldMove($order, $parent_id, $current = null)
    {
        $move = false;
        //2, null, 5896
        if ($this->type === SurveyItem::ITEM_QUESTION && !$parent_id)
            throw new SmiceException(
                SmiceException::HTTP_BAD_REQUEST,
                SmiceException::E_RESOURCE,
                'Survey: the question must be in a sequence.'
            );
        if (
            $this->type === SurveyItem::ITEM_QUESTION ||
            ($this->type === SurveyItem::ITEM_SEQUENCE &&
                ($this->parent_id != $parent_id) && $parent_id !== null)
        ) {
            if (!DB::table('survey_item')->where('survey_id', $this->survey->getKey())->find($parent_id))
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_RESOURCE,
                    'Survey: the question must be in a sequence.'
                );
        }
        if (!is_numeric($order) || $order < 0)
            throw new SmiceException(
                SmiceException::HTTP_BAD_REQUEST,
                SmiceException::E_RESOURCE,
                'Survey: the order is required and must be equal or greater than 0.'
            );

        if ($this->getKey() && ($order != $current->order)) {
            $this->old_order = $current->order;
            $this->order = $order;
            $move = true;
        } elseif ($this->getKey()) {
            $this->old_order = $current->order;
        } else {
            $this->old_order = $order;
            $this->order = $order;
            $move = true;
        }

        if ($this->getKey() && ($parent_id != $current->parent_id)) {
            $this->old_parent_id = $current->parent_id;
            $this->parent_id = $parent_id;
            $move = true;
        } elseif ($this->getKey()) {
            $this->old_parent_id = $current->parent_id;
        } else {
            $this->old_parent_id = $parent_id;
            $this->parent_id = $parent_id;
            $move = true;
        }

        if (!$move) {
            unset($this->old_order);
            unset($this->old_parent_id);
        }
        return $move;
    }

    /**
     * This function moves an item in a survey.
     * This function must be called only if _shouldMove() returns true.
     * It handles the items' order for the old and new sequence.
     */
    private function _move($current = null)
    {
        //$id_constraint  = null;
        //list all item of one sequence or all sequence of one survey if parent_id is null
        //$items          = collect(DB::table('survey_item')
        //    ->where('parent_id', $this->parent_id)
        //    ->where('survey_id', $this->survey->getKey())
        //    ->get());

        //$order_max = $items->count();

        //if ($this->getKey())
        //    DB::table('survey_item')
        //        ->where('id', $this->getKey())
        //        ->delete();

        //move item in same sequence
        if ($this->parent_id == $this->old_parent_id) {
            //add new ite in survey
            if ($this->order == $this->old_order) {
                //if ($this->order > $order_max)
                //    $this->order = $order_max - 1;

                //$items->filter(function($item)
                //{
                //    return ($item['order'] <= $this->order && $item['order'] > $this->old_order);
                //})->each(function($item) use (&$id_constraint)
                //{
                //    if (!$id_constraint)
                //        $id_constraint .= $item['id'];
                //    else
                //        $id_constraint .= ",". $item['id'];
                //});

                //if (!$id_constraint)
                //    $id_constraint = 'null';
                $query = DB::table('survey_item')
                    ->where('parent_id', $this->parent_id)
                    ->where('survey_id', $this->survey->getKey())
                    ->where('order', '>=', $this->order);
                $query->increment('order');
                //$this->order--;
            } //move item down
            elseif ($this->order > $this->old_order) {
                //if ($this->order > $order_max)
                //    $this->order = $order_max - 1;

                //$items->filter(function($item)
                //{
                //    return ($item['order'] <= $this->order && $item['order'] > $this->old_order);
                //})->each(function($item) use (&$id_constraint)
                //{
                //    if (!$id_constraint)
                //        $id_constraint .= $item['id'];
                //    else
                //        $id_constraint .= ",". $item['id'];
                //});

                //if (!$id_constraint)
                //    $id_constraint = 'null';
                //var_dump('UPDATE survey_item SET "order" = "order" - 1 WHERE parent_id =  '. $this->parent_id . ' AND survey_id = ' . $this->survey->getKey() . ' AND "order" > ' . $current->order .' AND "order" <  ' . $this->order . '+1');
                $query = DB::table('survey_item')
                    ->where('parent_id', $this->parent_id)
                    ->where('survey_id', $this->survey->getKey())
                    ->where('order', '>', $current->order)
                    ->where('order', '!=', $this->order);
                $query->decrement('order');
                $this->order--;

                /*if($this->parent_id == "")
                    DB::update('UPDATE survey_item SET "order" = "order" - 1 WHERE parent_id is null AND survey_id = ' . $this->survey->getKey() . ' AND "order" > ' . $current->order .' AND "order" <  ' . $this->order . '+1');
                else
                    DB::update('UPDATE survey_item SET "order" = "order" - 1 WHERE parent_id =  '. $this->parent_id . ' AND survey_id = ' . $this->survey->getKey() . ' AND "order" > ' . $current->order .' AND "order" <  ' . $this->order . '+1');
                    */
            } //move item up
            else {
                /*
                $items->filter(function($item)
                {
                    return ($item['order'] >= $this->order && $item['order'] < $this->old_order);
                })->each(function($item) use (&$id_constraint)
                {
                    if (!$id_constraint)
                        $id_constraint .= $item['id'];
                    else
                        $id_constraint .= ",". $item['id'];
                });

                if (!$id_constraint)
                    $id_constraint = 'null';
                */
                $query = DB::table('survey_item')
                    ->where('parent_id', $this->parent_id)
                    ->where('survey_id', $this->survey->getKey())
                    ->where('order', '>=', $this->order)
                    ->where('order', '<', $current->order);
                $query->increment('order');
            }
        } //move item in different sequence
        else {
            /*
            if ($this->order > $order_max)
                $this->order = $order_max;

            $items->filter(function($item)
            {
                return ($item['order'] >= $this->order);
            })->each(function($item) use (&$id_constraint)
            {
                if (!$id_constraint)
                    $id_constraint .= $item['id'];
                else
                    $id_constraint .= ",". $item['id'];
            });

            if (!$id_constraint)
                $id_constraint = 'null';
            */
            $query = DB::table('survey_item')
                ->where('parent_id', $this->parent_id)
                ->where('survey_id', $this->survey->getKey())
                ->where('order', '>=', $this->order);
            $query->increment('order');

            // Update old sequence
            /*$id_constraint  = null;
            $items          = collect(DB::table('survey_item')
                ->where('parent_id', $this->old_parent_id)
                ->where('survey_id', $this->survey->getKey())
                ->get());

            $items->filter(function($item)
            {
                return ($item['order'] > $this->old_order);
            })->each(function($item) use (&$id_constraint)
            {
                if (!$id_constraint)
                    $id_constraint .= $item['id'];
                else
                    $id_constraint .= ",". $item['id'];
            });

            if (!$id_constraint)
                $id_constraint = 'null';
            */
            $query = DB::table('survey_item')
                ->where('parent_id', $current->parent_id)
                ->where('survey_id', $this->survey->getKey())
                ->where('order', '>=', $current->order);
            $query->decrement('order');
        }

        unset($this->old_order);
        unset($this->old_parent_id);
    }

    /**
     * @param integer $surveyId
     * @return array
     */
    public static function selectTags($surveyId)
    {
        $result = self::select(['parent_id', 'item_id'])->where('survey_id', $surveyId)->where('tag', true)->get();
        return $result;
    }

    /**
     * @param integer $surveyId
     * @return array
     */
    public static function selectConditions($surveyId)
    {
        $result = self::select(['parent_id', 'item_id', 'type', 'conditions'])->where('survey_id', $surveyId)->whereNotNull('conditions')->get();
        return $result;
    }

    /**
     * Recursive function to get all sequence ids including subsequences
     * 
     * @param array $itemIds
     * @return array
     */
    public static function getChild($surveyItems)
    {
        $ids = [];

        foreach ($surveyItems as $surveyItem) {
            $ids[] = $surveyItem['item_id'];

            if (isset($surveyItem['children']) && count($surveyItem['children'])) {
                $ids = array_merge($ids, self::getChild($surveyItem['children']));
            }
        }

        return $ids;
    }

    /**
     * @param array $itemIds
     * @return array
     */
    public static function getSequencesIds($itemIds)
    {
        $itemIds = ArrayHelper::getIds($itemIds);

        $result = self::with('children')
            ->whereIn('item_id', $itemIds)->orderBy('order')
            ->retrieveAll()
            ->toArray();

        $ids = self::getChild($result);

        return $ids;
    }

    /**
     * @param array $itemIds
     * @return array
     */
    public static function getSequencesParent($surveyItemId)
    {
        $result = self::with('parent')
            ->where('id', $surveyItemId)
            ->first()
            ->toArray();
        if (isset($result['parent']))
            return $result['parent'];
        else
            return null;
    }
}
