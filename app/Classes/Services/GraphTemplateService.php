<?php

namespace App\Classes\Services;

use App\Classes\Helpers\ActivityHelper;
use App\Classes\Helpers\ArrayHelper;
use App\Classes\Helpers\DateHelper;
use App\Classes\Helpers\FusionHelper;
use App\Classes\Helpers\GraphHelper;
use App\Http\Controllers\GraphFilterController;
use App\Http\Controllers\ScoreController;
use App\Http\Shops\Models\Shop;
use App\Models\Alias;
use App\Models\Dashboard;
use App\Models\Graph;
use App\Models\GraphTemplate;
use App\Models\Question;
use App\Models\Scenario;
use App\Models\User;
use App\Models\Wave;
use App\Models\WaveGroupWave;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use Webpatser\Uuid\Uuid;

class GraphTemplateService
{
    const TABLE_FILTERS = ['missions', 'program', 'scenarios', 'themes', 'uo_filter', 'q_tag', 'criteria_a', 'criteria_b'],
        VIEW_GLOBAL = ['box_global', 'list_shop', 'global_wave', 'global_shop', 'list_shop_missions'],
        VIEW_SEQUENCE = [
            'sequence_graph_wave',
            'sequence_graph_evo',
            'sequence_graph_periode',
            'sequence_list_score',
            'sequence_list',
            'sequence_evo',
            'sequence_wave',
            'box_sequence',
            'sequence_periode',
            'sequence_radar'
        ],
        VIEW_THEME = ['theme_graph_evo', 'theme_graph_radar', 'theme_graph_wave', 'theme_list'],
        VIEW_JOB = ['job_list', 'job_graph_evo', 'job_graph_wave', 'job_graph_radar'],
        VIEW_CRITERIA_A = ['criteria_a_list', 'criteria_a_graph_evo', 'criteria_a_graph_radar', 'criteria_a_graph_wave'],
        VIEW_CRITERIA_B = ['criteria_b_list', 'criteria_b_graph_evo', 'criteria_b_graph_radar', 'criteria_b_graph_wave'],
        VIEW_TAG = ['tag_wave', 'tag_evo', 'tag_periode'],
        VIEW_MATRIX = [
            'matrice_sequence',
            'matrice_theme',
            'matrice_sequence',
            'matrice_job',
            'matrice_ca',
            'matrice_cb',
            'matrice_st',
            'matrice_sc',
            'matrice_tc',
            'matrice_tt',
            'matrice_ts'
        ],
        VIEW_CRITERIA = ['criteria_scoring', 'criteria_missions', 'criteria_mission'],
        VIEW_QUESTION = ['box_questions'],
        VIEW_IMAGES = ['picture_wall'],
        VIEW_COMPARE = ['shop_list_graph', 'shop_group_graph'],
        VIEW_SCENARIO = ['scenario_graph'],
        VIEW_ACTIVITY = [
            'participation',
            'user_number',
            'user_number_graph',
            'percent_mission_shops',
            'mission_shops',
            'mission_shops_graph'
        ];

    private

        $answers_targets_ids = null,

        $box_sequence = null,

        $comparison = [],

        $criteria_a_graph_evo = null,
        $criteria_a_graph_id = null,
        $criteria_a_graph_radar = null,
        $criteria_a_graph_wave = null,
        $criteria_a_id = null,
        $criteria_a_list_score = null,
        $criteria_a_score_box = null,

        $criteria_b_graph_id = null,
        $criteria_b_id = null,
        $criteria_b_list_score = null,
        $criteria_b_score_box = null,

        $criteria_graph_id = null,
        $criteria_id = null,
        $criteria_mission_score = [],
        $criteria_td = null,

        $dashboard_id = null,

        $filters = null,
        $filters_compare = null,

        $general_graph = null,

        $global_score = 0,
        $globalScoreDate = null,

        $graph = null,
        $graph_legend = [],
        $graph_id = null,
        $graph_name = null,
        $graph_question = null,
        $graph_type = null,
        $graph_template_sequence = [],

        $job_graph_evo = null,
        $job_graph_id = null,
        $job_graph_radar = null,
        $job_graph_wave = null,
        $job_id = null,
        $job_list_score = null,
        $job_score_box = null,

        $matrice_ca_shop = [],
        $matrice_cb_shop = [],
        $matrice_j_shop = [],
        $matrice_s_shop = [],
        $matrice_st = [],
        $matrice_sc = [],
        $matrice_t_shop = [],
        $matrice_tc = [],
        $matrice_ts = [],
        $matrice_tt = [],

        $path = '',

        $params = [],

        $picture_wall = [],

        $position = null,

        $question_graph_id = null,
        $question_id = null,
        $question_possible_answers = null,
        $question_user_answer = null,

        $questions_answers_gr = [],

        $scenario_table_score = [],

        $seq = [],

        $sequence_box_name = null,
        $sequence_box_score = null,
        $sequence_graph_evo = null,
        $sequence_graph_id = null,
        $sequence_graph_periode = null,
        $sequence_graph_radar = null,
        $sequence_graph_wave = null,
        $sequence_id = null,
        $sequence_list_score = null,

        $shop_graph = null,
        $shop_group_graph = [],
        $shop_score = [],
        $shop_score_by_wave_table = [],
        $shops_missions_table = [],
        $shops_achieved_graph = [],

        $smicers_achieved_graph = [],

        $society_id = null,

        $template_id = null,

        $tag_graph_evo = null,
        $tag_graph_periode = null,
        $tag_graph_wave = null,

        $theme_graph_evo = null,
        $theme_graph_id = null,
        $theme_graph_radar = null,
        $theme_graph_wave = null,
        $theme_id = null,
        $theme_list_score = null,
        $theme_score_box = null,

        $user_number = 0,
        $user_number_total = 0,
        $user_number_graph = null,

        $updateFromModel = null,

        $wave_name = [],
        $wave_name1 = null,
        $wave_name2 = null,
        $wave_period = null,

        $view = null,
        $shops_in_waves = [],
        $previous_period = [];

    /** @var User */
    private $user = null;


    /**
     * GraphTemplateService constructor.
     * @param array $params
     * @param User $user
     * @param string $path
     * @param boolean $updateFromModel
     */
    public function __construct($params, $user, $path, $updateFromModel = false)
    {
        $this->updateFromModel = $updateFromModel;
        $this->path = $path;
        $this->user = $user;
        $this->society_id = $user->current_society_id;
        $this->setParams($params);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            if (is_array($this->$name)) {
                array_push($this->$name, $value);
            } else {
                $this->$name = $value;
            }
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    /**
     * @param array $params
     */
    private function setParams($params)
    {
        $params['page'] = array_get($params, 'page', 'dashboard');
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'name':
                    $this->graph_name = $value;
                    break;
                case 'graph_template_id':
                    $this->template_id = $value;
                    break;
                case 'type':
                    $this->graph_type = $value;
                    break;
                case 'question_id':
                    $this->question_graph_id = $value;
                    break;
                case 'criteria_id':
                    $this->criteria_graph_id = $value;
                    break;
                case 'sequence_id':
                    $this->sequence_graph_id = $value;
                    break;
                case 'theme_id':
                    $this->theme_graph_id = $value;
                    break;
                case 'job_id':
                    $this->job_graph_id = $value;
                    break;
                case 'criteria_a_id':
                    $this->criteria_a_graph_id = $value;
                    break;
                case 'criteria_b_id':
                    $this->criteria_b_graph_id = $value;
                    break;
                case 'case':
                    $this->graph = $value;
                    break;
                case 'sequence_box':
                    $this->sequence_box_name = $value;
                    break;
                case 'page':
                    $this->view = $value;
                    break;
                case 'data_question':
                    $this->graph_question = $value;
                    break;
                case 'filters_compare':
                    if (isset($value['general'])) {
                        if (!isset($value['general']['axes']) && !isset($value['general']['scenarios']) && !isset($value['general']['shop']))
                            $this->filters_compare = null;
                        else
                            $this->filters_compare = $value;
                    }
                    break;
                default:
                    $this->__set($key, $value);
            }
        }
    }

    /**
     * @param array $filter
     * @param array $targetIds
     * @param array $graphTemplateSeqPeriod
     */
    private function setViewActivity($filter, $targetIds, $graphTemplateSeqPeriod)
    {
        if (($this->view === 'activity') || (false !== array_search($this->graph, self::VIEW_ACTIVITY))) {
            //order wave
            $this->participation = $this->user_number = $this->mission_shops = $this->percent_mission_shops = 0;
            $this->user_number_graph = $this->mission_shops_graph = $this->users_missions_table =
                $this->shops_missions_table = $this->smicers_achieved_graph = $this->shops_achieved_graph = [];
            $this->shops_missions_table = ActivityHelper::getShopsMissionsTable($this->society_id, $filter);
            $this->users_missions_table = ActivityHelper::getUsersMissionsTable($this->society_id, $filter);

            $this->user_number = ActivityHelper::getUsersActivityBySociety($this->society_id, $filter);
            $this->user_number_total = ActivityHelper::getUsersBySociety($this->society_id, $filter);
            //$usersInSociety = ActivityHelper::getUsersWithMissionCount($this->society_id, $filter);
            $this->participation = ($this->user_number_total)
                ? round($this->user_number / $this->user_number_total * 100, 1) : 0;
            //$this->user_number = $usersInSociety;
            $this->user_number_graph = $this->_fusionFilter(
                ActivityHelper::getUsersActivityByMissionsCountOnSociety($this->society_id, $filter),
                [],
                $graphTemplateSeqPeriod,
                'user_number_graph'
            );

            $this->mission_shops_graph = $this->_fusionFilter(
                ActivityHelper::getShopsMissionsCount($this->society_id, $targetIds, $filter),
                [],
                $graphTemplateSeqPeriod,
                'mission_shops_graph'
            );
            $this->mission_shops = ActivityHelper::getShopsCountByMissionsOnSociety($this->society_id, $filter);
            $this->mission_shops_total = ActivityHelper::getShopsOnSociety($this->society_id, $filter);
            if ($this->mission_shops > 0) {
                $this->percent_mission_shops = round(
                    $this->mission_shops
                        / $this->mission_shops_total * 100,
                    1
                );
            } else {
                $this->percent_mission_shops = 0;
            }
            $this->smicers_achieved_graph = $this->_fusionFilter([
                'users' => $this->user_number_total,
                'periods' => ActivityHelper::getUsersAchieved($this->society_id, $filter)
            ], [], $this->graph_template_sequence, 'smicers_achieved_graph');

            $this->shops_achieved_graph = $this->_fusionFilter([
                'max_shops' => $this->mission_shops_total,
                'periods' => ActivityHelper::getShopsAchieved($this->society_id, $filter)
            ], [], $this->graph_template_sequence, 'shops_achieved_graph');
        }
    }

    /**
     * @return Shop|array
     */
    public function getRestrictedShops()
    {
        # check if dashboard is on consult only, bypass restricted_shop
        if ($this->dashboard_id > 0) {
            $dashboard = Dashboard::find($this->dashboard_id);
            if ($dashboard['share_option'] == Dashboard::SHARE_OPTION_AUTHPRIZE_CONSULT_ONLY) {
                /** @var Builder|Collection $restricted_shop */
                $restricted_shop = Shop::listQuery()->where('society_id', $dashboard['society_id'])->orderBy('name');
                return ArrayHelper::getIds($restricted_shop->get()->toArray());
            }
        }

        /** @var Builder|Collection $restricted_shop */
        $restricted_shop = Shop::getRestrictedshop(
            $this->user->getKey(),
            $this->user->society_id,
            $this->user->current_society_id
        );
        $restricted_shops = ArrayHelper::getIds($restricted_shop->get()->toArray());

        $restricted_shop_id = [];
        foreach ($restricted_shops as $shop) {
            array_push($restricted_shop_id, $shop);
        }

        return $restricted_shop_id;
    }

    /**
     * @return array|bool|Response|mixed|string
     */
    public function prepareMakeFilter()
    {
        ScoreController::$society_id = $this->society_id;
        $this->validateMakeFilter();
        $filters = $this->validateFilter();
        $this->graph_question = array_get($this->params, 'data_question');
        $graphTemplateBuild = json_decode(GraphTemplate::find($this->template_id)->template, true);
        $graphTemplateSeqPeriod = json_decode(GraphTemplate::find(3)->template, true);
        $graphTemplateSeqRadar = json_decode(GraphTemplate::find(4)->template, true);
        $graphTemplateQuestions = json_decode(GraphTemplate::find(6)->template, true);
        $graphTemplateSequence = json_decode(GraphTemplate::find(2)->template, true);
        $graphTemplateScoreByAxeBar = json_decode(GraphTemplate::where('name', 'score_by_group')->first()->template, true);
        $restrictedShopId = $this->getRestrictedShops();

        $axesId = ArrayHelper::getIds($filters['axes']);
        $filters['shops'] = $this->_getShops($filters['shops'], $axesId, $restrictedShopId);

        if (!$filters['shops']) {
            return new Response([]);
        }
        $shops = ArrayHelper::getIds($filters['shops']);
        $dataSet = [];
        $uuid = Uuid::generate(4)->string;
        foreach ($shops as $id) {
            $dataSet[] = [
                'shopid' => $id,
                'uuid' => $uuid
            ];
        }
        \DB::table('shop_cache')->insert($dataSet);
        $filters['uuid'] = $uuid;
        # if comparison
        # RecupÃ¨re les points de vente d'un groupe pour comparaison
        # Collect sales points from a group for comparison
        $this->setCompareShops($restrictedShopId);

        #get scenarios to compare
        $this->setCompareScenarios();

        # a revoir
        # to review
        if (isset($filters['one_seq'])) {
            if (array_key_exists('id', $filters['one_seq']) || isset($filters['one_seq'][0]['id'])) {
                $filters['one_seq'] = ArrayHelper::getIds($filters['one_seq']);
            }
        }

        # Ajoute l'ensemble des filtres dans un tableau
        # Add all filters in a table
        $filters['missions'] = ArrayHelper::getIds($filters['missions']);
        $filters['survey'] = ArrayHelper::getIds($filters['survey']);
        $filters['program'] = ArrayHelper::getIds($filters['program']);
        $filters['themes'] = ArrayHelper::getIds($filters['themes']);
        $filters['uo_filter'] = ArrayHelper::getIds($filters['uo_filter']);
        $filters['q_tag'] = ArrayHelper::getIds($filters['q_tag']);
        # user choose period
        $waveIds = $this->getWaves($filters);
        $filters['waves'] = $waveIds;
        if (!count($filters['waves'])) {
            # Pas de vague concernÃ© par la selection donc pas de missions
            # No wave concerned by the selection so no missions
            return new Response([]);
        }


        # set name of period
        $this->setWaveNames($filters);
        # set name of period
        $this->setWavePeriod($filters);
        # get previous period
        $this->getPreviousPeriod($filters);
        $questionIds = null;
        switch ($this->graph) {
            case 'global_shop':
                $response = $this->getGlobalShop($filters, $graphTemplateBuild);
                break;
            case 'global_wave':
                $response = $this->getGlobalWave($filters, $graphTemplateBuild);
                break;
            case 'box_global':
                $response = ScoreController::_getGlobalScore($filters, $questionIds, $this->previous_period);
                break;
            case 'list_shop':
                $response = $this->getShopsList($filters);
                break;
            case 'list_shop_missions':
                $response = $this->getShopsListMissions($filters);
                break;
            case 'shop_groups_graph':
                $response = $this->getShopsGroupsGraph($filters, $graphTemplateBuild);
                break;
            case 'shops_score_graph':
                $response = $this->getShopsScoreGraph($filters, $graphTemplateScoreByAxeBar);
                break;
            case 'shop_group_graph':
                $response = $this->getShopGroupGraph($filters, $graphTemplateScoreByAxeBar);
                break;
            case 'shop_score_by_wave_table':
                $response = $this->getShopScoreByWaveTable($filters);
                break;
            case 'sequence_wave':
                $response = $this->getSequenceGraph($filters, $graphTemplateSequence, 'sequence_wave');
                break;
            case 'sequence_evo':
                $response = $this->getSequenceGraph($filters, $graphTemplateSequence, 'sequence_evo');
                break;
            case 'sequence_periode':
                $response = $this->getSequenceAlterGraph(
                    $filters,
                    $graphTemplateSeqPeriod,
                    'sequence_periode'
                );
                break;
            case 'sequence_radar':
                $response = $this->getSequenceAlterGraph($filters, $graphTemplateSeqRadar, 'sequence_radar');
                break;
            case 'sequence_graph_radar':
                $response = $this->getSequenceAlterGraph(
                    $filters,
                    $graphTemplateSeqRadar,
                    'sequence_graph_radar'
                );
                break;
            case 'sequences_list':
                $response = $this->getSequenceListScore($filters, $graphTemplateBuild);
                break;
            case 'sequence_list':
                $response = $this->getSequenceListScore($filters, $graphTemplateBuild);
                break;
            case 'theme_wave':
                $response = $this->getThemeWaveGraph($filters, $this->theme_graph_id, $graphTemplateSequence);
                break;
            case 'theme_graph_wave':
                $response = $this->getThemeWaveGraph($filters, $this->theme_graph_id, $graphTemplateSequence);
                break;
            case 'theme_graph_evo':
                $response = $this->getThemeGraph($filters, $this->theme_graph_id, $graphTemplateSequence, 'theme_evo');
                break;
            case 'theme_evo':
                $response = $this->getThemeGraph($filters, $this->theme_graph_id, $graphTemplateSequence, 'theme_evo');
                break;
            case 'theme_graph_radar':
                $response = $this->getThemeGraph($filters, $this->theme_graph_id, $graphTemplateSeqRadar, 'theme_radar');
                break;
            case 'theme_radar':
                $response = $this->getThemeGraph($filters, $this->theme_graph_id, $graphTemplateSeqRadar, 'theme_radar');
                break;
            case 'theme_list':
                $response = $this->getThemeListScore($filters, $this->theme_graph_id, $graphTemplateSequence);
                break;
            case 'theme_box':
                $response = $this->getThemeScoreBox($filters, $this->theme_graph_id, $graphTemplateSequence);
                break;
            case 'sequence_axes_list':
                $response = $this->getSequenceAxesList($filters);
                break;
            case 'sequence_axes_list_reverse':
                $response = $this->getSequenceAxesList($filters, true);
                break;
            case 'job_graph_wave':
                $response = $this->getJobGraphWave($filters, $questionIds, $graphTemplateSequence);
                break;
            case 'job_graph_evo':
                $response = $this->getJobGraph($filters, $questionIds, $graphTemplateSequence, 'job_evo');
                break;
            case 'job_graph_radar':
                $response = $this->getJobGraph($filters, $questionIds, $graphTemplateSeqRadar, 'job_radar');
                break;
            case 'job_list':
                $response = $this->getJobListScore($filters, $questionIds, $graphTemplateSequence);
                break;
            case 'criteria_a_graph_wave':
                $response = $this->getCriteriaAGraphWave($filters, $questionIds, $graphTemplateSequence);
                break;
            case 'criteria_a_graph_evo':
                $response = $this->getCriteriaAGraph($filters, $questionIds, $graphTemplateSequence, 'criteria_a_evo');
                break;
            case 'criteria_a_graph_radar':
                $response = $this->getCriteriaAGraph($filters, $questionIds, $graphTemplateSeqRadar, 'criteria_a_radar');
                break;
            case 'criteria_a_list_score':
                $response = $this->getCriteriaAListScore($filters, $questionIds, $graphTemplateSequence);
                break;
            case 'criteria_b_graph_wave':
                $response = $this->getCriteriaBGraphWave($filters, $questionIds, $graphTemplateSequence);
                break;
            case 'criteria_b_graph_evo':
                $response = $this->getCriteriaBGraph($filters, $questionIds, $graphTemplateSequence, 'criteria_b_evo');
                break;
            case 'criteria_b_graph_radar':
                $response = $this->getCriteriaBGraph($filters, $questionIds, $graphTemplateSeqRadar, 'criteria_b_radar');
                break;
            case 'criteria_b_list_score':
                $response = $this->getCriteriaBListScore($filters, $questionIds, $graphTemplateSequence);
                break;
            case 'shop_list_graph':
                $response = $this->getShopListGraph($filters, $graphTemplateScoreByAxeBar);
                break;
            case 'tag_graph_wave':
                $response = $this->getTagGraphWave($filters, $graphTemplateSequence);
                break;
            case 'tag_graph_evo':
                $response = $this->getTagGraph($filters, $graphTemplateSequence, 'tag_evo');
                break;
            case 'tag_graph_period':
                $response = $this->getTagGraph($filters, $graphTemplateSeqPeriod, 'tag_period');
                break;
            case 'sequence_name':
                $response = $this->getMatrixSShop($filters);
                break;
            case 'matrice_sc':
                $response = $this->getMatrixSC($filters);
                break;
            case 'theme_name':
                $response = self::_getMatriceThemeShopScore($filters, $this->user->language->code, $this->society_id);
                break;
            case 'job_name':
                $response = self::_getMatriceJobShopScore($filters, $this->user->language->code, $this->society_id);
                break;
            case 'ca_name':
                $response = self::_getMatriceCritereAShopScore($filters, $this->user->language->code, $this->society_id);
                break;
            case 'cb_name':
                $response = self::_getMatriceCritereBShopScore($filters, $this->user->language->code, $this->society_id);
                break;
            case 'sequence_theme':
                $response = self::_getMatriceThemeSequenceScore($filters, $this->user->language->code, $this->society_id);
                break;
            case 'criteria_mission_score':
                $response = $this->_getCriteriaMissionShop(
                    $filters,
                    $questionIds,
                    ArrayHelper::getIds($filters['waves'])
                );
                break;
            case 'criteria_mission':
                $response = $this->_getCriteriaMissionShop(
                    $filters,
                    $questionIds,
                    ArrayHelper::getIds($filters['waves'])
                );
                break;
            case 'questions_box':
                $response = $this->getQuestionsAnswers($filters, $graphTemplateQuestions);
                break;
            case 'scenario_graph':
                $response = $this->getScenarioGraph($filters, $graphTemplateBuild);
                break;
            case 'scenario_score_by_shop':
                $response = ArrayHelper::getTableFromScenariosByShop(
                    $this->getScenarioScoresByShops($filters, $this->society_id)
                );
                break;
            case 'shops_missions_table':
                $response = ActivityHelper::getShopsMissionsTable($this->society_id, $filters);
                break;
            case 'users_missions_table':
                $response = ActivityHelper::getUsersMissionsTable($this->society_id, $filters);
                break;
            case 'user_number':
                $response = ActivityHelper::getUsersActivityBySociety($this->society_id, $filters);
                break;
            case 'user_number_total':
                $response = ActivityHelper::getUsersBySociety($this->society_id, $filters);
                break;
            case 'participation':
                $user_number = ActivityHelper::getUsersActivityBySociety($this->society_id, $filters);
                $user_number_total = ActivityHelper::getUsersBySociety($this->society_id, $filters);
                $response = ($user_number_total)
                    ? round($user_number / $user_number_total * 100, 1) : 0;
                break;
            case 'user_number_graph':
                $response = $this->_fusionFilter(
                    ActivityHelper::getUsersActivityByMissionsCountOnSociety($this->society_id, $filters),
                    [],
                    $graphTemplateSeqPeriod,
                    'user_number_graph'
                );
                break;
            case 'mission_shops_graph':
                $targetIds = $this->getTargetIds($filters);
                $response = $this->_fusionFilter(
                    ActivityHelper::getShopsMissionsCount($this->society_id, $targetIds, $filters),
                    [],
                    $graphTemplateSeqPeriod,
                    'mission_shops_graph'
                );
                break;
            case 'mission_shops':
                $response = ActivityHelper::getShopsCountByMissionsOnSociety($this->society_id, $filters);
                break;
            case 'mission_shops_total':
                $response = ActivityHelper::getShopsOnSociety($this->society_id, $filters);
                break;
            case 'percent_mission_shops':
                $mission_shops = ActivityHelper::getShopsCountByMissionsOnSociety($this->society_id, $filters);
                $mission_shops_total = ActivityHelper::getShopsOnSociety($this->society_id, $filters);
                if ($mission_shops > 0) {
                    $percent_mission_shops = round(
                        $mission_shops
                            / $mission_shops_total * 100,
                        1
                    );
                } else {
                    $percent_mission_shops = 0;
                }
                $response = $percent_mission_shops;
                break;
            case 'smicers_achieved_graph':
                $response = $this->getSmicersAchievedGraph($filters, $graphTemplateSequence);
                break;
            case 'shops_achieved_graph':
                $response = $this->getShopsAchievedGraph($filters, $graphTemplateSequence);
                break;
            case 'matrice_ts':
                $response = $this->getMatrixTS();
                break;
            case 'matrice_tt':
                $response = $this->getMatrixTT();
                break;
            case 'matrice_tc':
                $response = $this->getMatrixTC();
                break;
            case 'job_box':
                $response = $this->getJobScoreBox($filters, $questionIds, $graphTemplateSequence);
                break;
            case 'image_table':
                $response = $this->getPictureWall($filters, $questionIds);
                break;
            case 'picture_wall':
                $response = $this->getPictureWall($filters, $questionIds);
                break;
            default:
                $response = [];
        }
        if ($this->path === 'graphs') {
            $this->_saveGraph($this->graph_question, 'graphs');
        }
        return $response;
    }

    /**
     * @param array $restrictedShopId
     */
    private function setCompareShops($restrictedShopId)
    {
        if ($this->filters_compare) {
            // compare shop groups
            $axe_id = array_get($this->filters_compare, 'general.axes.id', null);
            if ($axe_id) {
                $this->comparison['name'] = array_get($this->filters_compare, 'general.axes.name', []);
                $shops_to_compare = Shop::whereHas('axes', function ($query) use ($axe_id, $restrictedShopId) {
                    $query->where('id', $axe_id)->whereIn('shop_id', $restrictedShopId);
                });
                # add in comparison array
                $this->comparison['shops_to_compare'] = $shops_to_compare->get()->toArray();
            }

            // compare shop
            $shop_id = array_get($this->filters_compare, 'general.shops.id', null);
            if ($shop_id) {
                $this->comparison['name_shop'] = array_get($this->filters_compare, 'general.shops.name', '');
                $this->comparison['compare_shop'] = Shop::where('id', $shop_id)->get()->toArray();
            }
        }
    }

    private function setCompareScenarios()
    {
        if ($this->filters_compare) {
            $scenarios = array_get($this->filters_compare, 'general.scenarios', []);
            $scenario_ids = ArrayHelper::getIds($scenarios);
            if (count($scenario_ids)) {
                $this->comparison['name'] = null;
                foreach ($this->filters_compare['general']['scenarios'] as $value) {
                    $this->comparison['name'] .= ' ' . $value['name'];
                }
                $this->comparison['scenarios'] = Scenario::whereIn('id', $scenario_ids)->get()->toArray();
            }
        }
    }


    /**
     * @param array $filters
     * @return array
     */
    private function getWaves($filters)
    {
        $waves = $filters['waves'];
        if ($filters['period']) {
            if ($filters['period']['id'] !== 'perso') {
                # user not select specific month but choose last month or x lats month
                $waves = self::_getWaveFromPeriod($filters['period'], $filters['client']['id'], $filters);
            }
        }
        # Pas de vague ni periode on prend les 3 derniÃ¨res vagues
        # No wave nor period we take the last 3 waves
        if (!$waves) {
            $res = \DB::table('show_wave_with_missions_' . $this->society_id)
                ->select('wave_id')
                ->groupBy('wave_id');

            GraphFilterController::_addFilters($res, $filters);

            $waves_use = $res->get();
            $waves = Wave::where('society_id', $filters['client']['id'])
                ->whereIn('id', $waves_use)
                ->orderBy('date_start', 'DESC')
                ->limit(3)
                ->get()
                ->toArray();
        }

        return array_reverse($waves);
    }

    /**
     * @param array $filters
     */
    private function setWaveNames($filters)
    {
        foreach ($filters['waves'] as $wave) {
            $this->wave_name[$wave['id']] = $wave['name'];
        }
        $this->wave_name1 = ($filters['waves'][count($filters['waves']) - 1]['name']);
        if (count($filters['waves']) > 1) {
            $this->wave_name2 = ($filters['waves'][count($filters['waves']) - 2]['name']);
        }
    }

    /**
     * @param array $filters
     */
    private function setWavePeriod($filters)
    {
        $nb_wave = count($filters['waves']);
        if ($nb_wave > 1) {
            $wave_period = $filters['waves'][0]['name'] . ' -> ' . $filters['waves'][$nb_wave - 1]['name'];
        } else {
            $wave_period = $filters['waves'][0]['name'];
        }
        $this->globalScoreDate = DateHelper::getDateFromWave($filters['waves']);
        $this->wave_period = $wave_period;
    }

    /**
     * @param array $filters
     */
    private function getPreviousPeriod($filters)
    {
        $previous_period = null;
        $nb_wave = count($filters['waves']);
        $saved_wave = $filters['waves'];
        $filters['waves'] = null;
        $res = \DB::table('show_wave_with_missions_' . $this->society_id)
            ->select('wave_id')
            ->groupBy('wave_id');
        GraphFilterController::_addFilters($res, $filters);
        $waves_use = $res->get();
        if ($nb_wave == 1) {
            $previous_period = Wave::where('society_id', $filters['client']['id'])
                ->whereIn('id', $waves_use)
                ->where('id', '<', $saved_wave[0]['id'])
                ->orderBy('date_start', 'DESC')
                ->limit(1)
                ->get()
                ->toArray();
        } else if ($filters['period'] && ($filters['period']['id'] !== 'perso')) {
            $previous_period = Wave::where('society_id', $this->society_id)
                ->whereIn('id', $waves_use)
                ->orderBy('date_start', 'DESC')
                ->skip($filters['period']['id'])
                ->limit($filters['period']['id'])
                ->get()
                ->toArray();
        }
        if ($previous_period)
            $this->previous_period = $previous_period;
    }

    /**
     * @param array $filters
     * @return array|null|static[]
     */
    private function getTargetIds($filters)
    {
        if ($filters['uo_filter']) {
            $filters['uo_filter'] = ArrayHelper::getIds($filters['uo_filter']);
            $targetIds = self::_getFilters($filters, $this->society_id);
        } else {
            $targetIds = ArrayHelper::getIds(self::_getWaveTargetId($filters, $this->society_id));
        }

        return $targetIds;
    }

    /**
     * @return array
     */
    private function validateFilter()
    {
        $return['themes'] = array_get($this->filters, 'general.general_theme', []);
        \Validator::make(
            [
                'wave' => $return['waves'] = array_get($this->filters, 'general.vague', []),
                'shop' => $return['shops'] = array_get($this->filters, 'general.shop', []),
                'axes' => $return['axes'] = array_get($this->filters, 'general.axes', []),
                'q_tag' => $return['q_tag'] = array_get($this->filters, 'general.question_tag', []),
                'period' => $return['period'] = array_get($this->filters, 'general.period', []),
                'client' => $return['client'] = array_get($this->filters, 'general.client', []),
                'one_seq' => $return['one_seq'] = array_get($this->filters, 'general.general_sequence', []),
                'date_end' => $return['end'] = array_get($this->filters, 'general.date_end', []),
                'program' => $return['program'] = array_get($this->filters, 'general.program', []),
                'survey' => $return['survey'] = array_get($this->filters, 'general.survey', []),
                'missions' => $return['missions'] = array_get($this->filters, 'general.mission', []),
                'criteria' => $return['criteria'] = array_get($this->filters, 'criterions', []),
                'uo_filter' => $return['uo_filter'] = array_get($this->filters, 'general.uo_filter', []),
                'scenarios' => $return['scenarios'] = array_get($this->filters, 'general.scenarios', []),
                'sequences' => $return['sequences'] = array_get($this->filters, 'sequences', []),
                'questions' => $return['questions'] = array_get($this->filters, 'questions', []),
                'criteria_a' => $return['criteria_a'] = array_get($this->filters, 'general.criteria_a', []),
                'criteria_b' => $return['criteria_b'] = array_get($this->filters, 'general.criteria_b', []),
                'date_start' => $return['start'] = array_get($this->filters, 'general.date_start', []),
                'folder_axes' => $return['folder_axes'] = array_get($this->filters, 'general.folderAxes', []),
                'usersGroups' => $return['usersGroups'] = array_get($this->filters, 'general.usersGroups', []),
                'criterion_less_than_100' => $return['criterion_less_than_100'] = array_get(
                    $this->filters,
                    'general.criterion_less_than_100',
                    []
                ),
            ],
            [
                'wave' => 'array',
                'shop' => 'array',
                'axes' => 'array',
                'q_tag' => 'array',
                'client' => 'array',
                'period' => 'array',
                'one_seq' => 'array',
                'date_end' => 'date',
                'program' => 'array',
                'survey' => 'array',
                'missions' => 'array',
                'criteria' => 'array',
                'scenarios' => 'array',
                'sequences' => 'array',
                'questions' => 'array',
                'uo_filter' => 'array',
                'criteria_a' => 'array',
                'criteria_b' => 'array',
                'date_start' => 'date',
                'folder_axes' => 'array',
                'usersGroups' => 'array',
                'criterion_less_than_100' => 'boolean'
            ]
        )->passOrDie();

        return $return;
    }

    /**
     * return void
     */
    private function validateMakeFilter()
    {
        \Validator::make(
            [
                'filters' => $this->filters,
                'graph_template_id' => $this->template_id,
                'position' => $this->position,
                'society_id' => $this->society_id,
                'dashboard_id' => $this->dashboard_id,
                'type' => $this->graph_type,
                'graph' => $this->graph,
                'view' => $this->view,
            ],
            [
                'filters' => 'required|array',
                'graph_template_id' => 'required|int',
                'position' => 'int',
                'society_id' => 'int',
                'dashboard_id' => 'int',
                'type' => 'string',
                'graph' => 'string',
                'view' => 'string',
            ]
        )->passOrDie();
    }

    /**
     * @param $request
     * @return mixed
     */
    public static function init($request)
    {
        $r = new self($request, $request['user'], '');
        return $r->prepareMakeFilter();
    }

    /**
     * @return string
     */
    public function _language_code()
    {
        $user = $this->__get('user');
        return $user->language->code;
    }

    /**
     * @param $id
     * @return mixed|string
     */
    public function _updateGraph($id)
    {
        /** @var Graph $update */
        $update = Graph::find($id);
        if (json_encode($this->filters) !== null) {
            $update->filters = json_encode($this->filters);
        }

        switch ($this->graph) {
            case 'global_wave':
                $update->graph = json_encode($this->general_graph);
                break;
            case 'global_shop':
                $update->graph = json_encode($this->shop_graph);
                break;
            case 'list_shop':
                $update->graph = json_encode($this->shop_score);
                break;
            case 'list_shop_missions':
                $update->graph = json_encode($this->shop_score_by_wave_table);
                break;
            case 'box_global':
                $update->graph = json_encode($this->global_score);
                break;
            case 'sequence_wave':
                $update->graph = json_encode($this->sequence_graph_wave);
                break;
            case 'sequence_evo':
                $update->graph = json_encode($this->sequence_graph_evo);
                break;
            case 'box_sequence':
                $update->graph = json_encode($this->box_sequence);
                break;
            case 'sequence_periode':
                $update->graph = json_encode($this->sequence_graph_periode);
                break;
            case 'sequence_list':
                $update->graph = json_encode($this->sequence_list_score);
                break;
            case 'theme_list':
                $update->graph = json_encode($this->theme_list_score);
                break;
            case 'job_list':
                $update->graph = json_encode($this->job_list_score);
                break;
            case 'criteria_a_list':
                $update->graph = json_encode($this->criteria_a_list_score);
                break;
            case 'criteria_b_list':
                $update->graph = json_encode($this->criteria_b_list_score);
                break;
            case 'criteria_a_graph_evo':
                $update->graph = json_encode($this->criteria_a_graph_evo);
                break;
            case 'criteria_a_graph_radar':
                $update->graph = json_encode($this->criteria_a_graph_radar);
                break;
            case 'criteria_a_graph_wave':
                $update->graph = json_encode($this->criteria_a_graph_wave);
                break;
            case 'criteria_b_graph_evo':
                $update->graph = json_encode($this->criteria_a_graph_evo);
                break;
            case 'criteria_b_graph_radar':
                $update->graph = json_encode($this->criteria_a_graph_radar);
                break;
            case 'criteria_b_graph_wave':
                $update->graph = json_encode($this->criteria_a_graph_wave);
                break;
            case 'sequence_radar':
                $update->graph = json_encode($this->sequence_graph_radar);
                break;
            case 'matrice_sequence':
                $update->graph = json_encode($this->matrice_s_shop);
                break;
            case 'matrice_theme':
                $update->graph = json_encode($this->matrice_t_shop);
                break;
            case 'matrice_job':
                $update->graph = json_encode($this->matrice_j_shop);
                break;
            case 'matrice_ca':
                $update->graph = json_encode($this->matrice_ca_shop);
                break;
            case 'matrice_cb':
                $update->graph = json_encode($this->matrice_cb_shop);
                break;
            case 'criteria_scoring':
                $update->graph = json_encode($this->criteria_td);
                break;
            case 'criteria_mission':
                $update->graph = json_encode($this->criteria_mission_score);
                break;
            case 'criteria_missions':
                $update->graph = json_encode($this->criteria_mission_score);
                break;
            case 'matrice_st':
                $update->graph = json_encode($this->matrice_st);
                break;
            case 'matrice_sc':
                $update->graph = json_encode($this->matrice_sc);
                break;
            case 'matrice_tc':
                $update->graph = json_encode($this->matrice_tc);
                break;
            case 'picture_wall':
                $update->graph = json_encode($this->picture_wall);
                break;
            case 'theme_graph_evo':
                $update->graph = json_encode($this->theme_graph_evo);
                break;
            case 'theme_graph_wave':
                $update->graph = json_encode($this->theme_graph_wave);
                break;
            case 'theme_graph_radar':
                $update->graph = json_encode($this->theme_graph_radar);
                break;
            case 'tag_wave':
                $update->graph = json_encode($this->tag_graph_wave);
                break;
            case 'tag_evo':
                $update->graph = json_encode($this->tag_graph_evo);
                break;
            case 'tag_periode':
                $update->graph = json_encode($this->tag_graph_periode);
                break;
            case 'matrice_tt':
                $update->graph = json_encode($this->matrice_tt);
                break;
            case 'job_graph_evo':
                $update->graph = json_encode($this->job_graph_evo);
                break;
            case 'job_graph_radar':
                $update->graph = json_encode($this->job_graph_radar);
                break;
            case 'job_graph_wave':
                $update->graph = json_encode($this->job_graph_wave);
                break;
            case 'matrice_ts':
                $update->graph = json_encode($this->matrice_ts);
                break;
            case 'box_questions':
                if (isset($this->questions_answers_gr[0]))
                    $update->graph = json_encode($this->questions_answers_gr[0]);
                break;
        }

        if (true == $this->updateFromModel) {
            $update->save();
        }

        return $update->graph;
    }

    /**
     * @param $data
     * @param bool $update_from_model
     */
    private function _saveGraph($data, $update_from_model = false)
    {
        $last_pos = Graph::where('dashboard_id', $this->dashboard_id)->orderBy('position', 'DESC')->first();

        $graph = new Graph();
        $graph->name = $this->graph_name;
        $graph->dashboard_id = $this->dashboard_id;
        $graph->position = ($last_pos == NULL) ? 0 : $last_pos->position + 1;
        $graph->society_id = $this->society_id;
        $graph->graph_template_id = $this->template_id;
        $graph->subtitle = 1;
        $graph->filters = json_encode($this->filters);
        $graph->filters_compare = json_encode($this->filters_compare);
        $graph->type = $this->graph_type;
        $graph->case = $this->graph;
        $graph->question_id = $this->question_graph_id;
        $graph->criteria_id = $this->criteria_graph_id;
        $graph->sequence_id = $this->sequence_graph_id;
        $graph->theme_id = $this->theme_graph_id;
        $graph->wave_name1 = $this->wave_name1;
        $graph->wave_name2 = $this->wave_name2;

        if ($data) {
            $graph->graph = json_encode($data);
        }

        switch ($this->graph) {
            case 'score_per_group':
                if ($this->graph_id) {
                    $currentGraph = collect($this->shop_group_graph)->where('id', $this->graph_id)->first();
                    $graph->graph = json_encode($currentGraph['template']);
                }
                break;
            case 'scenario_table':
                $graph->graph = json_encode($this->scenario_table_score);
                break;
            case 'list_shop_missions':
                $graph->graph = json_encode($this->shop_score_by_wave_table);
                break;
            case 'global_wave':
                $graph->graph = json_encode($this->general_graph);
                break;
            case 'global_shop':
                $graph->graph = json_encode($this->shop_graph);
                break;
            case 'list_shop':
                $graph->graph = json_encode($this->shop_score);
                break;
            case 'box_global':
                $graph->graph = json_encode($this->global_score);
                break;
            case 'box_sequence':
                $graph->graph = json_encode($this->sequence_box_score[0][$this->sequence_box_name]);
                break;
            case 'sequence_wave':
                $graph->graph = json_encode($this->sequence_graph_wave);
                break;
            case 'sequence_evo':
                $graph->graph = json_encode($this->sequence_graph_evo);
                break;
            case 'sequence_periode':
                $graph->graph = json_encode($this->sequence_graph_periode);
                break;
            case 'sequence_list':
                $graph->graph = json_encode($this->sequence_list_score);
                break;
            case 'theme_list':
                $graph->graph = json_encode($this->theme_list_score);
                break;
            case 'job_list':
                $graph->graph = json_encode($this->job_list_score);
                break;
            case 'criteria_a_list':
                $graph->graph = json_encode($this->criteria_a_list_score);
                break;
            case 'criteria_b_list':
                $graph->graph = json_encode($this->criteria_b_list_score);
                break;
            case 'box_criteria_a':
                $graph->graph = json_encode(collect($this->criteria_a_score_box)->first());
                break;
            case 'box_criteria_b':
                $graph->graph = json_encode(collect($this->criteria_b_score_box)->first());
                break;
            case 'sequence_radar':
                $graph->graph = json_encode($this->sequence_graph_radar);
                break;
            case 'matrice_sequence':
                $graph->graph = json_encode($this->matrice_s_shop);
                break;
            case 'matrice_theme':
                $graph->graph = json_encode($this->matrice_t_shop);
                break;
            case 'matrice_job':
                $graph->graph = json_encode($this->matrice_j_shop);
                break;
            case 'matrice_ca':
                $graph->graph = json_encode($this->matrice_ca_shop);
                break;
            case 'matrice_cb':
                $graph->graph = json_encode($this->matrice_cb_shop);
                break;
            case 'criteria_scoring':
                $graph->graph = json_encode($this->criteria_td);
                break;
            case 'criteria_mission':
                $graph->graph = json_encode($this->criteria_mission_score);
                break;
            case 'tag_wave':
                $graph->graph = json_encode($this->tag_graph_wave);
                break;
            case 'tag_evo':
                $graph->graph = json_encode($this->tag_graph_evo);
                break;
            case 'tag_periode':
                $graph->graph = json_encode($this->tag_graph_periode);
                break;
            default:
                $graph->graph = json_encode($this->{$this->graph});
        }

        if ('graphs' === $update_from_model) {
            $graph->save();
        }
    }

    /**
     * @param $data
     * @param $comparison
     * @param $template
     * @param $graph
     * @return mixed
     */
    private function _fusionFilter($data, $comparison, $template, $graph)
    {
        //general.criteria_a
        //general.criteria_b
        //general.folderAxes
        //general.shop

        switch ($graph) {
            case 'shops_achieved_graph':
                $template = GraphHelper::formatAchievedShopsGraph($data, $template);
                break;
            case 'smicers_achieved_graph':
                $template = GraphHelper::formatAchievedUsersGraph($data, $template);
                break;
            case 'theme_evo':
                $template = GraphHelper::formatEvoGraph(
                    $data,
                    $template,
                    'theme_name',
                    'theme_id',
                    $this->user->language->code
                );
                $this->theme_score_box = $template['h'];
                $this->theme_id = $template['r'];
                break;
            case 'theme_score_box':
                $template = GraphHelper::formatEvoGraph(
                    $data,
                    $template,
                    'theme_name',
                    'theme_id',
                    $this->user->language->code
                );
                $template = [
                    'scores' => $template['h'],
                    'ids' => $template['r']
                ];
                break;
            case 'theme_list':
                $template = GraphHelper::formatEvoGraph(
                    $data,
                    $template,
                    'theme_name',
                    'theme_id',
                    $this->user->language->code
                );
                $template = $template['h'];
                break;
            case 'job_evo':
                $template = GraphHelper::formatEvoGraph(
                    $data,
                    $template,
                    'job_name',
                    'job_id',
                    $this->user->language->code
                );
                $this->job_score_box = $template['h'];
                $this->job_id = $template['r'];
                break;
            case 'job_score_box':
                $template = GraphHelper::formatEvoGraph(
                    $data,
                    $template,
                    'job_name',
                    'job_id',
                    $this->user->language->code
                );
                $template = [
                    'scores' => $template['h'],
                    'ids' => $template['r']
                ];
                break;
            case 'job_list_score':
                $template = GraphHelper::formatEvoGraph(
                    $data,
                    $template,
                    'job_name',
                    'job_id',
                    $this->user->language->code
                );
                $template = $template['h'];
                break;
            case 'criteria_a_evo':
                $template = GraphHelper::formatEvoGraph(
                    $data,
                    $template,
                    'criteria_a_name',
                    'criteria_a_id',
                    $this->user->language->code
                );
                $this->criteria_a_id = $template['r'];
                break;
            case 'criteria_a_score_box':
                $template = GraphHelper::formatEvoGraph(
                    $data,
                    $template,
                    'criteria_a_name',
                    'criteria_a_id',
                    $this->user->language->code
                );
                $template = $template['h'];
                break;
            case 'criteria_b_evo':
                $template = GraphHelper::formatEvoGraph(
                    $data,
                    $template,
                    'criteria_b_name',
                    'criteria_b_id',
                    $this->user->language->code
                );
                $this->criteria_b_id = $template['r'];
                break;
            case 'criteria_b_score_box':
                $template = GraphHelper::formatEvoGraph(
                    $data,
                    $template,
                    'criteria_b_name',
                    'criteria_b_id',
                    $this->user->language->code
                );
                $template = $template['h'];
                break;
            case 'theme_wave':
                $template = GraphHelper::formatWaveGraph($data, $template, 'theme_name', $this->user->language->code);
                break;
            case 'job_wave':
                $template = GraphHelper::formatWaveGraph($data, $template, 'job_name', $this->user->language->code);
                break;
            case 'criteria_a_wave':
                $template = GraphHelper::formatWaveGraph($data, $template, 'criteria_a_name', $this->user->language->code);
                break;
            case 'criteria_b_wave':
                $template = GraphHelper::formatWaveGraph($data, $template, 'criteria_b_name', $this->user->language->code);
                break;
            case 'theme_radar':
                $template = GraphHelper::formatRadarGraph($data, $template, 'theme_name', $this->user->language->code);
                break;
            case 'job_radar':
                $template = GraphHelper::formatRadarGraph($data, $template, 'job_name', $this->user->language->code);
                break;
            case 'criteria_a_radar':
                $template = GraphHelper::formatRadarGraph($data, $template, 'criteria_a_name', $this->user->language->code);
                break;
            case 'criteria_b_radar':
                $template = GraphHelper::formatRadarGraph($data, $template, 'criteria_b_name', $this->user->language->code);
                break;
            case 'wave':
                $template = GraphHelper::waveTemplate(
                    $comparison,
                    $data,
                    isset($this->comparison['name']) ?? $this->comparison['name']
                );
                break;
            case 'shops':
                $template = GraphHelper::shopsTemplate(
                    $data,
                    $comparison,
                    isset($this->comparison['name']) ?? $this->comparison['name']
                );
                break;
            case 'sequence_wave':
                $template = GraphHelper::sequenceWaveTemplate(
                    $data,
                    $comparison,
                    isset($this->comparison['name']) ?? $this->comparison['name'],
                    $this->user->language->code,
                    $this->wave_name
                );
                break;
            case 'sequence_evo':
                $data = GraphHelper::sequenceEvoTemplate($data, $this->user->language->code, $this->wave_name);
                $this->seq = $data['seq'];
                $template = $data['template'];
                break;
            case 'sequence_periode':

                $data = GraphHelper::sequencePeriodTemplate(
                    $data,
                    $comparison,
                    isset($this->comparison['name']) ?? $this->comparison['name'],
                    $this->wave_period,
                    $this->seq,
                    $this->user->language->code
                );
                $this->sequence_box_score = $data['sequence_box_score'];
                $this->sequence_id = $data['sequence_id'];
                $template = $data['template'];
                break;
            case 'sequence_box_score':
                $data = GraphHelper::sequencePeriodTemplate(
                    $data,
                    $comparison,
                    isset($this->comparison['name']) ?? $this->comparison['name'],
                    $this->wave_period,
                    $this->seq,
                    $this->user->language->code
                );
                $template = $data['sequence_box_score'];
                break;
            case 'sequence_radar':
                $template = GraphHelper::sequenceRadarTemplate($data, $this->user->language->code, $this->wave_period);
                break;
            case 'sequence_graph_radar':
                $template = GraphHelper::formatRadarGraph(
                    count($data) ? $data : [],
                    $template,
                    'sequence',
                    $this->user->language->code
                );
                break;
            case 'questions_answers':
                $data = GraphHelper::questionsAnswersTemplate(
                    $this->graph_template_sequence,
                    $this->answers_targets_ids,
                    $this->question_possible_answers,
                    $this->user->language->code
                );
                return [
                    'scores' => $data['array_template'],
                    'ids' => $data['question_template_ids']
                ];
                break;
            case 'tag_wave':
                $template = GraphHelper::tagWaveTemplate($data);
                break;
            case 'tag_evo':
                $template = GraphHelper::tagEvoTemplate($data);
                break;
            case 'tag_period':
                $template = GraphHelper::tagPeriodTemplate($data);
                break;
            case 'shop_group_graph':
                $template = GraphHelper::shopGroupGraphTemplate($data, $template);
                break;
            case 'shop_list_graph':
                //$template = GraphHelper::shopListGraphTemplate($data, $template);
                break;
            case 'scenario':
                $template = GraphHelper::scenarioGraphTemplate($data);
                break;
            case 'user_number_graph':
                $template = GraphHelper::activityUsersMissionsTemplate($data, $template);
                break;
            case 'mission_shops_graph':
                $template = GraphHelper::activityShopsMissionsTemplate($data, $template);
                break;
            case 'shops_score':
                $template = GraphHelper::shopScoreTemplate($data, $template);
                break;
            case 'shop_groups_graph':
                $template = GraphHelper::shopGroupsGraphTemplate($data, $template);
                break;
        }
        if (!strpos($graph, 'list') && !strpos($graph, 'radar')) {
            $template['chart'] = [
                'backgroundColor' =>  '#ffffff',
                'reflow' =>  true,
            ];
            $template['credits'] = [
                'enabled' =>  false
            ];
        }


        return $template;
    }

    /**
     * @param $period
     * @param $current_society_id
     * @param $array_filter
     * @return null
     */
    public static function _getWaveFromWaveGroup($group)
    {
        $waves = [];
        $gw = WaveGroupWave::wherein('group_wave_id', ArrayHelper::getIds($group))->get()->toArray();
        foreach ($gw as $w) {
            $waves[] = $w['wave_id'];
        }
        return $waves;

    }

    /**
     * @param $period
     * @param $current_society_id
     * @param $array_filter
     * @return null
     */
    public static function _getWaveFromPeriod($period, $current_society_id, $array_filter)
    {
        if ('perso' == $period['id']) {
            return null;
        } else {
            # show_wave_with_missions
            $res = \DB::table('show_wave_with_missions_' . $current_society_id)
                ->select('wave_id')
                ->groupBy('wave_id');


            $res = GraphFilterController::_addFilters($res, $array_filter);
            $waves_use = $res->get();
            # Utiliser show_wave_with_missions avec array_filter pour filtrer sur les vagues qui ont des rÃ©sultats
            # Use show_wave_with_missions with array_filter to filter on waves that have results
            $result = Wave::where('society_id', $current_society_id)
                ->whereIn('id', $waves_use)
                ->orderBy('date_start', 'DESC')
                ->limit($period['id'])
                ->get()
                ->toArray();
        }
        return $result;
    }

    /**
     * @param $uo
     * @return array|null
     */
    public static function _getUOTargets($uo)
    {
        $result = \DB::table('wave_target_tags')
            ->select('wave_target_id as id')
            ->whereIn('tag_id', $uo)
            ->get();

        return ArrayHelper::getIds($result);
    }

    /**
     * @param $target_id
     * @param $wave_id
     * @return array|static[]
     */
    public static function _getGlobalTagScoreFromWave($array_filter, $wave_id, $society_id)
    {
        $res = \DB::table('show_scoring_' . $society_id)
            ->select('wave_name', 'name')
            ->selectRaw('CASE WHEN SUM(score) > 0 THEN SUM(score) / SUM(CAST(weight AS FLOAT)) ELSE null END AS score')
            ->where('wave_id', $wave_id)
            ->where('scoring', true)
            ->whereNotNull('tag_id')
            ->whereNotNull('name')
            ->groupBy('wave_name', 'name')
            ->orderBy('name', 'ASC');
        GraphFilterController::_addFilters($res, $array_filter);

        return $res->get();
    }

    /**
     * @param $target_id
     * @return array|static[]
     */
    public static function _getGlobalTagScore($array_filter, $society_id)
    {

        $res = \DB::table('show_scoring_' . $society_id)
            ->select('wave_name', 'name')
            ->selectRaw('CASE WHEN SUM(score) > 0 THEN SUM(score) / SUM(CAST(weight AS FLOAT)) ELSE null END AS score')
            ->where('scoring', true)
            ->whereNotNull('tag_id')
            ->whereNotNull('name')
            ->groupBy('wave_name', 'name')
            ->orderBy('name', 'ASC');
        GraphFilterController::_addFilters($res, $array_filter);

        return $res->get();
    }

    /**
     * @param $array
     * @return array
     */
    public static function _makeSequenceScore($array)
    {
        $shops = [];
        foreach ($array as $key => $value) {
            array_push($shops, [
                'name' => $key,
                'score' => round($value, 1),
            ]);
        };
        return $shops;
    }

    /**
     * @param array $shops
     * @return array
     */
    public static function _removeNullShops($shops)
    {
        $data = collect($shops);
        $data->each(function ($item, $key) use ($data) {
            if ([] == $item || null) {
                $data->forget($key);
            }
        });

        return $data->toArray();
    }

    /**
     * get all shops from wave is $waves is passed as arg
     * if not, get the shops from 3 last waves
     *
     * @param $axes
     * @param $restricted_shop_id
     * @return mixed
     */
    public static function _getShops($shop_id, $axes, $restricted_shop_id)
    {
        $res = Shop::whereIn('id', $restricted_shop_id);
        if ($axes) {
            $res->whereHas('axes', function ($query) use ($axes) {
                $query->whereIn('id', $axes);
            });
        }
        if ($shop_id) {
            $res->wherein('id', ArrayHelper::getIds($shop_id));
        }
        return $res->get()->toArray();
    }

    /**
     * get all questions with answers
     * from targets_ids
     *
     * @param int array $target_ids
     * @param $question_id
     * @return array of questions with answers
     */
    public static function _getQuestionUsersAnswers($society_id, $target_ids, $question_id)
    {
        $res = \DB::table('show_scoring_' . $society_id)
            ->select('question_id as id')
            ->whereIn('wave_target_id', $target_ids)
            ->whereIn('question_id', $question_id)
            ->orderBy('order', 'ASC')
            ->groupBy('question_id', 'order')
            ->get();

        $questions = [];
        foreach ($res as $question_id) {
            $question = Question::with([
                'userAnswers' => function ($query) use ($target_ids) {
                    $query->whereIn('wave_target_id', $target_ids);
                }
            ])
                ->where('id', $question_id['id']);
            array_push($questions, $question->get()->toArray());
        }
        return $questions;
    }

    /**
     * NOT USED NOW
     * @param $target_ids
     * @param $questions_ids
     * @param $question_id
     * @return array
     */
    public static function _getQuestionAnswers($society_id, $target_ids, $questions_ids, $question_id)
    {
        $res = \DB::table('show_scoring_' . $society_id)
            ->select('question_id as id')
            ->whereIn('wave_target_id', $target_ids)
            ->whereIn('question_id', $question_id)
            ->orderBy('order', 'ASC')
            ->groupBy('question_id', 'order');

        if ($questions_ids) {
            $res = static::_withQuestions($res, $questions_ids, $society_id);
        }

        $res = $res->get();

        $questions = [];

        foreach ($res as $question_id) {
            $question = Question::with('answers')
                ->where('id', $question_id['id']);
            array_push($questions, $question->get()->toArray());
        }
        if (isset($questions[0])) #remove fist level of array
            $questions = array_map(function ($a) {
                return array_pop($a);
            }, $questions);

        return $questions;
    }

    /**
     * @param Builder $query
     * @param $questions
     * @return mixed
     */
    private static function _withQuestions($query, $questions, $society_id)
    {
        $answer_ids = ArrayHelper::getIds($questions);
        $result = [];
        foreach ($answer_ids as $id) {
            $result[] = \DB::table('show_scoring_' . $society_id)
                ->selectRaw('wave_target_id')
                ->where('question_row_id', $id)
                ->get();
        }

        return $query->whereIn('wave_target_id', ArrayHelper::sameArrayValue($result));
    }

    /**
     * @param $target_ids
     * @param $sequences
     * @param $language_code
     * @return array
     */
    public static function _getMatriceSequenceShopScore($array_filter, $sequences, $language_code, $society_id)
    {
        $query = \DB::table('show_scoring_' . $society_id)
            ->selectRaw("
                sequence_id as sequence_id,
                sequence_name as sequence_name,
                shop_name as shop_name,
                MAX(sequence_order) as sequence_order,
                CASE WHEN SUM(score) > 0 THEN
                SUM(score) / SUM(CAST(weight AS FLOAT))
                ELSE null
                END AS score
                ")
            ->where('scoring', true)
            ->where('question_weight', '>', '0')
            ->orderBy('sequence_order', 'ASC')
            ->groupBy('sequence_id', 'sequence_name', 'shop_name');
        GraphFilterController::_addFilters($query, $array_filter);

        $h = FusionHelper::fusionShopSequenceScore($query->get(), $sequences, $language_code);
        return $h;
    }

    /**
     * @param array $array_filter
     * @return array
     */
    private function _getAllSequences($array_filter)
    {
        $result = \DB::table('show_sequence_scoring')
            ->select('sequence_name')
            ->where('scoring', true);
        GraphFilterController::_addFilters($result, $array_filter);
        $final = $result->get();

        $sequence = [];
        foreach ($final as $key) {
            $sequence[] = json_decode($key['sequence_name'])->{$this->user->language->code};
        }
        $sequence = array_unique($sequence);

        return $sequence;
    }

    /**
     * @param array $array_filter
     * @return array
     */
    private function _getAllCriteria($array_filter)
    {
        $result = \DB::table('show_criteria_scoring')
            ->select('criteria_name');
        GraphFilterController::_addFilters($result, $array_filter);

        $final = $result->get();
        $criteria = [];
        foreach ($final as $key) {
            if ($key['criteria_name']) {
                $criteria[] = json_decode($key['criteria_name'])->{$this->user->language->code};
            }
        }
        $criteria = array_unique($criteria);
        return $criteria;
    }

    /**
     * @param $target_ids
     * @param $criteria
     * @param $language_code
     * @return array
     */
    public static function _getMatriceCriteriaShopScore($filters, $criteria, $language_code)
    {
        $f = $final = [];
        $result = \DB::table('show_criteria_scoring')
            ->select('score', 'shop_name', 'criteria_name');
        GraphFilterController::_addFilters($result, $filters);
        $h = FusionHelper::fusionShopCriteriaScore($result->get(), $language_code);
        foreach ($h as $key => $value) {
            $final['shop_name'] = $key;
            foreach ($criteria as $criterion) {
                if (isset($value[$criterion])) {
                    $final[$criterion] = $value[$criterion]['score'];
                } else {
                    $final[$criterion] = null;
                }
            }
            array_push($f, $final);
        }

        return $f;
    }

    /**
     * @param $target_ids
     * @param $language_code
     * @return array
     */
    public static function _getMatriceThemeShopScore($array_filter, $language_code, $society_id)
    {
        $result = \DB::table('show_scoring_multi_' . $society_id)
            ->selectRaw("
            shop_name,
            theme_name,
            CASE WHEN SUM(question_score) > 0 THEN
            SUM(question_score) / SUM(CAST(question_weight AS FLOAT))
            ELSE null
            END AS score
            ")
            ->where('scoring', true)
            ->whereNotNull('score')
            ->where('question_weight', '>', '0')
            ->whereNotNull('theme_name')
            ->groupBy('shop_name', 'theme_name');
        GraphFilterController::_addFilters($result, $array_filter);
        return FusionHelper::fusionShopScore($result->get(), 'theme_name', $language_code);
    }

    /**
     * @param $target_ids
     * @param $language_code
     * @return array
     */
    public static function _getMatriceJobShopScore($array_filter, $language_code, $society_id)
    {

        $result = \DB::table('show_scoring_multi_' . $society_id)
            ->selectRaw("
            shop_name,
            job_name,
            CASE WHEN SUM(question_score) > 0 THEN
            SUM(question_score) / SUM(CAST(question_weight AS FLOAT))
            ELSE null
            END AS score
            ")
            ->where('scoring', true)
            ->whereNotNull('score')
            ->where('question_weight', '>', '0')
            ->whereNotNull('job_name')
            ->groupBy('shop_name', 'job_name');
        GraphFilterController::_addFilters($result, $array_filter);
        return FusionHelper::fusionShopScore($result->get(), "job_name", $language_code);
    }

    /**
     * @param $target_ids
     * @param $language_code
     * @return array
     */
    public static function _getMatriceCritereAShopScore($array_filter, $language_code, $society_id)
    {
        $result = \DB::table('show_scoring_multi_' . $society_id)
            ->selectRaw("
            shop_name,
            criteria_a_name,
            CASE WHEN SUM(question_score) > 0 THEN
            SUM(question_score) / SUM(CAST(question_weight AS FLOAT))
            ELSE null
            END AS score
            ")
            ->where('scoring', true)
            ->where('question_weight', '>', '0')
            ->whereNotNull('criteria_a_name')
            ->groupBy('shop_name', 'criteria_a_name');
        GraphFilterController::_addFilters($result, $array_filter);
        return FusionHelper::fusionShopScore($result->get(), "criteria_a_name", $language_code);
    }

    /**
     * @param $target_ids
     * @param $language_code
     * @return array
     */
    public static function _getMatriceCritereBShopScore($array_filter, $language_code, $society_id)
    {
        $result = \DB::table('show_scoring_multi_' . $society_id)
            ->selectRaw("
            shop_name,
            criteria_b_name,
            CASE WHEN SUM(score) > 0 THEN
            SUM(score) / SUM(CAST(weight AS FLOAT))
            ELSE null
            END AS score
            ")
            ->where('scoring', true)
            ->whereNotNull('score')
            ->where('question_weight', '>', '0')
            ->whereNotNull('criteria_b_name')
            ->groupBy('shop_name', 'criteria_b_name');
        GraphFilterController::_addFilters($result, $array_filter);
        return FusionHelper::fusionShopScore($result->get(), "criteria_b_name", $language_code);
    }

    /**
     * @param $target_ids
     * @param $language_code
     * @return array
     */
    public static function _getMatriceThemeSequenceScore($array_filter, $language_code, $society_id)
    {
        $result = \DB::table('show_scoring_multi_' . $society_id)
            ->select('score', 'theme_name', 'sequence_name')
            ->where('scoring', true)
            ->whereNotNull('score')
            ->whereNotNull('theme_name');
        GraphFilterController::_addFilters($result, $array_filter);
        return FusionHelper::fusionThemeSequenceScore($result->get(), $language_code);
    }

    /**
     * @param array $array_filter
     * @param array $questionIds
     * @param array $wave_ids
     * @return array|mixed
     */
    private function _getCriteriaMissionShop($array_filter, $questionIds, $wave_ids)
    {
        $res = [];
        # classement des vagues
        arsort($wave_ids);
        # conserve les 2 derniere vagues seulement
        $wave_ids = array_slice($wave_ids, 0, 2);
        foreach ($wave_ids as $id) {
            $array_filter['waves'] = ['id' => $id];

            $td = $tb = $ta = $criterias = [];

            array_push($criterias, self::_getQuestionCriteriaScoring($array_filter, $questionIds, $this->society_id));

            foreach ($criterias as $criteria) {
                array_push($tb, FusionHelper::fusionCriteriaResponse($criteria, $this->user->language->code));
                array_push($td, FusionHelper::fusionCriteriaScore($criteria, $this->user->language->code));
                array_push($ta, FusionHelper::fusionCriteriaQuestionScoreId($criteria, $this->user->language->code));
            }

            array_push($res, FusionHelper::fusionFinalCriteriaMission($ta, $td, $tb));
        }

        if (count($wave_ids) > 1) {
            $res = FusionHelper::fusionPreviousWave($res);
        }

        $res_f = [];
        if (isset($res[0][0])) {
            $res_f = $res[0];
        } elseif (isset($res[0])) {
            $res_f[0] = $res;
        } else {
            $res_f[0] = $res;
        }

        return $res_f;
    }

    /**
     * @param $array_filter
     * @param $questionIds
     * @return array|static[]
     */
    private static function _getQuestionCriteriaScoring($array_filter, $questionIds = [], $society_id)
    {
        $result = \DB::table('show_question_criteria_scoring_' . $society_id)
            ->select('*');
        if ($questionIds)
            $result->whereIn('id_question', $questionIds);
        $result = GraphFilterController::_addFilters($result, $array_filter);
        return $result->get();
    }

    /**
     * @param $target_ids
     * @param $question_ids
     * @param $language_code
     * @param $filters
     * @return array
     */
    private static function _getQuestion($filters, $question_ids, $language_code)
    {
        $images_json_decode = [];
        $result = \DB::table('show_images')
            ->select('*');
        if ($question_ids)
            $result->whereIn('question_id', $question_ids);
        $result = GraphFilterController::_addFilters($result, $filters);
        $images = $result->get();
        foreach ($images as $img) {
            $img['question_name'] = json_decode($img['question_name'])->{$language_code};
            $img['answer'] = json_decode($img['answer'])->{$language_code};
            if (!$img['comment']) {
                $img['comment'] = '';
            }

            $images_json_decode[] = $img;
        }
        return $images_json_decode;
    }

    /**
     * @param $array_filter
     * @return mixed
     */
    private static function _getQuestionFilter($array_filter)
    {
        $result = \DB::table('show_scoring')
            ->select('question_id')
            ->groupBy('question_id');
        /** @var Builder $query */
        $query = GraphFilterController::_addFilters($result, $array_filter);
        return $query->get();
    }

    /**
     * @param $array_filter
     * @return array|static[]
     */
    private static function _getWaveTargetId($array_filter, $society_id)
    {
        $result = \DB::table('show_scoring_' . $society_id)
            ->select('wave_target_id as id')
            ->groupBy('wave_target_id');
        /** @var Builder $query */
        $query = GraphFilterController::_addFilters($result, $array_filter);
        return $query->get();
    }

    /**
     * @param $array_filter
     * @return array|static[]
     */
    private static function _getQuestionId($array_filter, $society_id)
    {
        $result = \DB::table('show_scoring_' . $society_id)
            ->select('question_id as id')
            ->groupBy('question_id');
        /** @var Builder $query */
        $query = GraphFilterController::_addFilters($result, $array_filter);
        return $query->get();
    }

    /**
     * @param $array_filter
     * @return array|static[]
     */
    private static function _getFilters($array_filter, $society_id)
    {
        $result = \DB::table('show_scoring_' . $society_id)
            ->select('wave_target_id as id');
        /** @var Builder $query */
        $query = GraphFilterController::_addFilters($result, $array_filter);
        return $query->get();
    }

    /**
     * @param array $targets
     * @return array
     */
    private function getShopMissionScoreFromWaveTargets($filters, $society_id)
    {
        $scores = [];
        $result = \DB::table('show_scoring_' . $society_id)
            ->select('shop_name', 'shop_id')
            ->selectRaw('
                CASE WHEN SUM(score) > 0
                THEN SUM(score) / SUM(CAST(weight AS FLOAT))
                ELSE null
                END AS score,
                COUNT(wave_target_id) as quantity')
            ->where('scoring', true)
            ->groupBy('shop_name', 'shop_id');
        GraphFilterController::_addFilters($result, $filters);
        $result = $result->get();
        foreach ($result as $r) {
            $scores[$r['shop_name']] = round($r['score'], 1);
        }
        return $scores;
    }

    /**
     * @param array $filters
     * @param array $targets
     * @return array
     */
    private function getScenarioScoresByShops($filters, $society_id)
    {
        $scores = [];

        $result = \DB::table('show_scoring_' . $society_id)
            ->select('shop_name', 'scenario_name')
            ->selectRaw('
                CASE WHEN SUM(score) > 0
                THEN SUM(score) / SUM(CAST(weight AS FLOAT))
                ELSE null
                END AS score')
            ->whereNotNull('scenario_name')
            ->where('scoring', true)
            ->groupBy('shop_name', 'scenario_name');
        GraphFilterController::_addFilters($result, $filters);
        $result = $result->get();
        foreach ($result as $row) {
            $scenario = trim(json_decode($row['scenario_name'])->{$this->_language_code()});
            $scores[$row['shop_name']][] = [
                'scenario' => $scenario,
                'score' => round($row['score'], 2)
            ];
        }
        foreach ($scores as $key => $score) {
            $scores[$key] = collect($score)->sortBy('scenario')->toArray();
        }
        return $scores;
    }

    /**
     * @param array $data
     * @return array
     */
    private function makeAxesSequenceTable($data)
    {
        $headers = [];
        foreach ($data as $key => $axe) {
            $sequences = [];
            foreach ($axe['sequences'] as $sequence) {
                $name = json_decode($sequence['name'])->{$this->_language_code()};
                $sequences[$name] = [
                    'name' => $name,
                    'score' => $sequence['score']
                ];
                $headers[$name] = $name;
            }
            $data[$key]['sequences'] = $sequences;
        }

        return [
            'headers' => $headers,
            'data' => $data
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    private function reverseAxesSequenceTable($data)
    {
        $headers = [];
        $rows = [];
        $sequences = [];
        $result = [];

        foreach ($data['data'] as $axe) {
            $headers[] = $axe['axe'];
            foreach ($axe['sequences'] as $row) {
                $rows[$row['name']] = $row['name'];
            }
        }

        foreach ($rows as $row) {
            foreach ($data['data'] as $axe) {
                $sequences[$axe['axe']] = [
                    'name' => $axe['axe'],
                    'score' => isset($axe['sequences'][$row]) ? $axe['sequences'][$row]['score'] : '',
                ];
            }
            $result[] = [
                'name' => $row,
                'axes' => $sequences
            ];
        }

        return [
            'headers' => $headers,
            'data' => $result
        ];
    }

    /**
     * @param array $filters
     * @param array $graphTemplateBuild
     * @return array
     */
    private function getGlobalShop($filters, $graphTemplateBuild)
    {
        $shop_score_by_wave = [];
        $shop_score_by_wave_comparison = [];
        foreach ($filters['waves'] as $wave) {
            $shop_score_by_wave[] = ScoreController::_getShopScoreByWave(
                $wave['id'],
                $wave['name'],
                $filters['shops'],
                $filters
            );

            if ($this->filters_compare) {
                $shop_score_by_wave_comparison[] = ScoreController::_getShopScoreByWave(
                    $wave['id'],
                    $wave['name'],
                    $filters['shops'],
                    $filters,
                    $this->comparison
                );
            }
        }

        $graph = $this->_fusionFilter(
            $shop_score_by_wave,
            $shop_score_by_wave_comparison,
            $graphTemplateBuild,
            'shops'
        );

        return $graph;
    }

    /**
     * @param array $filters
     * @param array $graphTemplateBuild
     * @return array
     */
    public function getGlobalWave($filters, $graphTemplateBuild, $question_level = false)
    {
        $wave_score = [];
        $wave_score_comparison = [];

        foreach ($filters['waves'] as $wave) {
            $wave_score[] = ScoreController::_getWaveScore($wave['id'], $wave['name'], $filters, false, $question_level);
            if ($this->filters_compare) {
                $wave_score_comparison[] = ScoreController::_getWaveScore(
                    $wave['id'],
                    $wave['name'],
                    $filters,
                    $this->comparison
                );
            }
        }

        $graph = $this->_fusionFilter(
            $wave_score,
            $wave_score_comparison,
            $graphTemplateBuild,
            'wave'
        );

        return $graph;
    }

    /**
     * @param array $filters
     * @return array
     */
    private function getShopsList($filters)
    {
        $result = self::_removeNullShops(
            ScoreController::_makeShopScore($filters)
        );

        return $result;
    }

    /**
     * @param array $filters
     * @param array $targetIds
     * @return array
     */
    private function getShopsListMissions($filters)
    {
        $shop_score_by_wave = [];
        foreach ($filters['waves'] as $wave) {
            $shop_score_by_wave[] = ScoreController::_getShopScoreByWave(
                $wave['id'],
                $wave['name'],
                $filters['shops'],
                $filters
            );
        }
        $missionsScoresForShop = $this->getShopMissionScoreFromWaveTargets($filters, $this->society_id);
        $result = ArrayHelper::getTableFromShopScoreByWave($shop_score_by_wave, $missionsScoresForShop);

        return $result;
    }

    /**
     * @param array $filters
     * @param array $graphTemplateBuild
     * @return array
     */
    private function getShopsGroupsGraph($filters, $graphTemplateBuild)
    {
        $shopGroupsScoreByWave = [];
        foreach ($filters['waves'] as $wave) {
            $shopGroupsScoreByWave[] = ScoreController::_getShopGroupScoreByWave($wave['id'], $wave['name'], $filters);
        }

        $result = $this->_fusionFilter(
            $shopGroupsScoreByWave,
            [],
            $graphTemplateBuild,
            'shop_groups_graph'
        );

        return $result;
    }

    /**
     * @param array $filters
     * @param array $graphTemplateScoreByAxeBar
     * @return array
     */
    private function getShopsScoreGraph($filters, $graphTemplateScoreByAxeBar)
    {
        $shop_score = self::_removeNullShops(
            ScoreController::_makeShopScore($filters)
        );

        $result = $this->_fusionFilter(
            $shop_score,
            [],
            $graphTemplateScoreByAxeBar,
            'shops_score'
        );

        return $result;
    }

    /**
     * @param array $filters
     * @param array $graphTemplateScoreByAxeBar
     * @return array
     */
    private function getShopGroupGraph($filters, $graphTemplateScoreByAxeBar)
    {
        $shopGroupGraph = [];
        $fa = $filters['folder_axes'];
        foreach ($fa as $key => $folderAxe) {
            if ($folderAxe || count($folderAxe)) {

                $score = ScoreController::_getShopGroupScore($folderAxe['id'], $filters);
                if ($score)
                    $shopGroupGraph[$folderAxe['axe_directory_id']][] = $score;
            }
        }

        $result = $this->_fusionFilter(
            $shopGroupGraph,
            [],
            $graphTemplateScoreByAxeBar,
            'shop_group_graph'
        );

        return $result;
    }

    /**
     * @param array $filters
     * @param array $targetIds
     * @return array
     */
    private function getShopScoreByWaveTable($filters)
    {
        $shop_score_by_wave = [];
        foreach ($filters['waves'] as $wave) {
            $shop_score_by_wave[] = ScoreController::_getShopScoreByWave(
                $wave['id'],
                $wave['name'],
                $filters['shops'],
                $filters
            );
        }

        $missionsScoresForShop = $this->getShopMissionScoreFromWaveTargets($filters, $this->society_id);
        $result = ArrayHelper::getTableFromShopScoreByWave(
            $shop_score_by_wave,
            $missionsScoresForShop
        );

        return $result;
    }

    /**
     * @param array $filters
     * @param array $graphTemplateSequence
     * @param string $type
     * @return array
     */
    private function getSequenceGraph($filters, $graphTemplateSequence, $type)
    {
        $wavesResult = [];
        foreach ($filters['waves'] as $wave) {
            $wavesResult[$wave['id']] = ScoreController::_getSequenceScoreFromWave($wave['id'], $filters);
        }


        $sequenceScoreByWaveComparison = [];
        if ($this->filters_compare && $this->filters_compare != 'tous') {
            foreach ($filters['waves'] as $wave) {
                $sequence_score_by_wave_comparison[] = ScoreController::_getSequenceScoreFromWave(
                    $wave['id'],
                    $filters,
                    $this->comparison
                );
            }
        }

        $result = $this->_fusionFilter($wavesResult, $sequenceScoreByWaveComparison, $graphTemplateSequence, $type);

        return $result;
    }

    /**
     * @param array $filters
     * @param array $targetIds
     * @param array $graphTemplate
     * @param string $type
     * @return array
     */
    private function getSequenceAlterGraph($filters, $graphTemplate, $type)
    {
        $sequenceScore = ScoreController::_getSequenceScore($filters);
        $sequenceScoreComparison = [];
        if ($this->filters_compare && $this->filters_compare != 'tous') {
            $sequenceScoreComparison = ScoreController::_getSequenceScore(
                $filters,
                $this->comparison,
                ArrayHelper::getIds($filters['waves']),
                $filters
            );
        }

        $wavesResult = [];
        foreach ($filters['waves'] as $wave) {
            $wavesResult[$wave['id']] = ScoreController::_getSequenceScoreFromWave($wave['id'], $filters);
        }
        $this->_fusionFilter($wavesResult, [], $graphTemplate, 'sequence_evo');

        $result = $this->_fusionFilter($sequenceScore, $sequenceScoreComparison, $graphTemplate, $type);

        return $result;
    }

    /**
     * @param array $filters
     * @param array $targetIds
     * @param array $graphTemplate
     * @return array
     */
    private function getSequenceListScore($filters, $graphTemplate)
    {
        $sequenceScore = ScoreController::_getSequenceScore($filters);
        $sequenceScoreComparison = [];
        if ($this->filters_compare && $this->filters_compare != 'tous') {
            $sequenceScoreComparison = ScoreController::_getSequenceScore(
                $filters,
                $this->comparison,
                ArrayHelper::getIds($filters['waves']),
                $filters
            );
        }

        $wavesResult = [];
        foreach ($filters['waves'] as $wave) {
            $wavesResult[$wave['id']] = ScoreController::_getSequenceScoreFromWave($wave['id'], $filters);
        }
        $this->_fusionFilter($wavesResult, [], $graphTemplate, 'sequence_evo');

        $sequenceBoxScore = $this->_fusionFilter(
            $sequenceScore,
            $sequenceScoreComparison,
            $graphTemplate,
            'sequence_box_score'
        );

        $result = self::_removeNullShops(self::_makeSequenceScore(count($sequenceBoxScore) > 2 ? $sequenceBoxScore[0] : []));

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplateSequence
     * @return array
     */
    private function getThemeWaveGraph($filters, $theme_id, $graphTemplateSequence)
    {
        $themeScoreWave = ScoreController::_getThemeScoreFromWave($filters, $theme_id);
        $result = $this->_fusionFilter($themeScoreWave, null, $graphTemplateSequence, 'theme_wave');

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplate
     * @param string $type
     * @return array
     */
    private function getThemeGraph($filters, $theme_id, $graphTemplate, $type)
    {
        $themeScore = ScoreController::_getThemeScore($filters, $theme_id);
        $result = $this->_fusionFilter($themeScore, null, $graphTemplate, $type);

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplate
     * @return array
     */
    private function getThemeListScore($filters, $theme_id, $graphTemplate)
    {
        $themeScore = ScoreController::_getThemeScore($filters, $theme_id);
        $result = $this->_fusionFilter($themeScore, null, $graphTemplate, 'theme_list');
        $result = self::_removeNullShops(self::_makeSequenceScore($result));

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $filters
     * @param bool $isReversed
     * @return array
     */
    private function getSequenceAxesList($filters, $isReversed = false)
    {
        $result = $this->makeAxesSequenceTable(ScoreController::_getSequenceAxesScore($filters));
        if ($isReversed) {
            $result = $this->reverseAxesSequenceTable($result);
        }

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplateSequence
     * @return array
     */
    private function getJobGraphWave($filters, $questionIds, $graphTemplateSequence)
    {
        $jobScoreByWave = ScoreController::_getJobScoreFromWave($filters, $questionIds);
        $result = $this->_fusionFilter($jobScoreByWave, null, $graphTemplateSequence, 'job_wave');

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplate
     * @param string $type
     * @return array
     */
    private function getJobGraph($filters, $questionIds, $graphTemplate, $type)
    {
        $jobScore = ScoreController::_getJobScore($filters, $questionIds);
        $result = $this->_fusionFilter($jobScore, null, $graphTemplate, $type);

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplate
     * @return array
     */
    private function getJobListScore($filters, $questionIds, $graphTemplate)
    {
        $jobScore = ScoreController::_getJobScore($filters, $questionIds);
        $result = $this->_fusionFilter($jobScore, null, $graphTemplate, 'job_list_score');
        $result = self::_removeNullShops(self::_makeSequenceScore($result));

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplateSequence
     * @return array
     */
    private function getCriteriaAGraphWave($filters, $questionIds, $graphTemplateSequence)
    {
        $criteriaAScoreByWave = ScoreController::_getcriteria_aScoreFromWave($filters, $questionIds);
        $result = $this->_fusionFilter($criteriaAScoreByWave, null, $graphTemplateSequence, 'criteria_a_wave');

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplate
     * @param string $type
     * @return array
     */
    private function getCriteriaAGraph($filters, $questionIds, $graphTemplate, $type)
    {
        $criteriaAScore = ScoreController::_getCriteria_aScore($filters, $questionIds);
        $result = $this->_fusionFilter($criteriaAScore, null, $graphTemplate, $type);

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplate
     * @return array
     */
    private function getCriteriaAListScore($filters, $questionIds, $graphTemplate)
    {
        $criteriaAScore = ScoreController::_getCriteria_aScore($filters, $questionIds);
        $result = $this->_fusionFilter($criteriaAScore, null, $graphTemplate, 'criteria_a_score_box');
        $result = self::_removeNullShops(self::_makeSequenceScore($result));

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplateSequence
     * @return array
     */
    private function getCriteriaBGraphWave($filters, $questionIds, $graphTemplateSequence)
    {
        $criteriaBScoreByWave = ScoreController::_getCriteria_bScoreFromWave($filters, $questionIds);
        $result = $this->_fusionFilter($criteriaBScoreByWave, null, $graphTemplateSequence, 'criteria_b_wave');

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplate
     * @param string $type
     * @return array
     */
    private function getCriteriaBGraph($filters, $questionIds, $graphTemplate, $type)
    {
        $criteriaBScore = ScoreController::_getCriteria_bScore($filters, $questionIds);
        $result = $this->_fusionFilter($criteriaBScore, null, $graphTemplate, $type);

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplate
     * @return array
     */
    private function getCriteriaBListScore($filters, $questionIds, $graphTemplate)
    {
        $criteriaBScore = ScoreController::_getCriteria_bScore($filters, $questionIds);
        $result = $this->_fusionFilter($criteriaBScore, null, $graphTemplate, 'criteria_b_score_box');
        $result = self::_removeNullShops(self::_makeSequenceScore($result));

        return $result;
    }

    /**
     * @param array $filters
     * @param array $graphTemplateScoreByAxeBar
     * @return array
     */
    private function getShopListGraph($filters, $graphTemplateScoreByAxeBar)
    {
        $result = $this->_fusionFilter($filters['folder_axes'], [], $graphTemplateScoreByAxeBar, 'shop_list_graph');

        return $result;
    }

    /**
     * @param array $filters
     * @param array $targetIds
     * @param array $graphTemplateSequence
     * @return array
     */
    private function getTagGraphWave($filters, $graphTemplateSequence)
    {
        $tagScoreByWave = [];
        foreach ($filters['waves'] as $wave) {
            $tagScoreByWave[] = self::_getGlobalTagScoreFromWave($filters, $wave['id'], $this->society_id);
        }

        $result = $this->_fusionFilter($tagScoreByWave, null, $graphTemplateSequence, 'tag_wave');

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $graphTemplate
     * @param array $type
     * @return array
     */
    private function getTagGraph($filters, $graphTemplate, $type)
    {
        $tagScore = self::_getGlobalTagScore($filters, $this->society_id);
        $result = $this->_fusionFilter($tagScore, null, $graphTemplate, $type);

        return $result;
    }

    /**
     * @param $filters
     * @param $targetIds
     * @return array
     */
    private function getMatrixSShop($filters)
    {
        $sequences = $this->_getAllSequences($filters);
        $result = self::_getMatriceSequenceShopScore($filters, $sequences, $this->user->language->code, $this->society_id);

        return $result;
    }

    /**
     * @param array $filters
     * @param array $targetIds
     * @return array
     */
    private function getMatrixSC($filters)
    {
        $criteria = $this->_getAllCriteria($filters);
        $result = self::_getMatriceCriteriaShopScore($filters, $criteria, $this->user->language->code);

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $filters
     * @param array $graphTemplateQuestions
     * @return array
     */
    private function getQuestionsAnswers($filters, $graphTemplateQuestions)
    {
        $targetIds = $this->getTargetIds($filters);
        $question_id = (true === $this->updateFromModel)
            ? [$this->question_graph_id]
            : self::_getQuestionFilter($filters);

        $this->question_user_answer = self::_getQuestionUsersAnswers($this->society_id, $targetIds, $question_id);
        $this->question_possible_answers = self::_getQuestionAnswers($this->society_id, $targetIds, $filters['questions'], $question_id);
        $this->answers_targets_ids = $targetIds;
        $result = $this->_fusionFilter(
            $this->question_user_answer,
            null,
            $graphTemplateQuestions,
            'questions_answers'
        );

        return $result;
    }

    /**
     * @param array $filters
     * @param array $graphTemplateBuild
     * @return array
     */
    private function getScenarioGraph($filters, $graphTemplateBuild)
    {
        $scenarioGraphScore = [];
        foreach ($filters['waves'] as $wave) {
            $scenarioGraphScore[] = ScoreController::_getScenarioScoreByWave(
                $wave['id'],
                $wave['name'],
                $filters,
                $this->_language_code()
            );
        }

        $result = $this->_fusionFilter($scenarioGraphScore, [], $graphTemplateBuild, 'scenario');

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $filter
     * @return float
     */
    private function getPercentMissionShops($targetIds, $filter)
    {
        $missionShops = ActivityHelper::getShopsCountOnSociety($this->society_id, $targetIds);
        if ($missionShops > 0) {
            return round($missionShops
                / ActivityHelper::getShopsCountByMissionsOnSociety($this->society_id, $filter) * 100, 1);
        }
        return 0;
    }

    /**
     * @param array $filter
     * @param array $graphTemplateSequence
     * @return array
     */
    private function getSmicersAchievedGraph($filter, $graphTemplateSequence)
    {
        $userNumberTotal = ActivityHelper::getUsersBySociety($this->society_id, $filter);
        $result = $this->_fusionFilter([
            'users' => $userNumberTotal,
            'periods' => ActivityHelper::getUsersAchieved($this->society_id, $filter)
        ], [], $graphTemplateSequence, 'smicers_achieved_graph');

        return $result;
    }

    /**
     * @param array $filter
     * @param array $graphTemplateSequence
     * @return array
     */
    private function getShopsAchievedGraph($filter, $graphTemplateSequence)
    {
        $missionShopsTotal = ActivityHelper::getShopsOnSociety($this->society_id, $filter);
        $result = $this->_fusionFilter([
            'max_shops' => $missionShopsTotal,
            'periods' => ActivityHelper::getShopsAchieved($this->society_id, $filter)
        ], [], $graphTemplateSequence, 'shops_achieved_graph');

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplate
     * @return array
     */
    private function getThemeScoreBox($filter, $questionIds, $graphTemplate)
    {
        $themeScore = ScoreController::_getThemeScore($filter, $questionIds);
        $result = $this->_fusionFilter($themeScore, null, $graphTemplate, 'theme_score_box');

        return $result;
    }


    private function getMatrixTS()
    {
        return [];
    }


    private function getMatrixTT()
    {
        return [];
    }


    private function getMatrixTC()
    {
        $result = FusionHelper::fusionRowsCriteriaTag($this->matrice_tc);
        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $graphTemplate
     * @return array
     */
    private function getJobScoreBox($filters, $questionIds, $graphTemplate)
    {
        $jobScore = ScoreController::_getJobScore($filters, $questionIds);
        $result = $this->_fusionFilter($jobScore, null, $graphTemplate, 'job_score_box');

        return $result;
    }

    /**
     * @param array $targetIds
     * @param array $questionIds
     * @param array $filters
     * @return array
     */
    private function getPictureWall($filters, $questionIds)
    {
        $response = self::_getQuestion($filters, $questionIds, $this->_language_code());
        if (($this->path === 'graphs') || ($this->updateFromModel === true)) {
            $this->_saveGraph($this->graph_question, $this->updateFromModel);
        }

        return $response;
    }

    public static function getFilter($filters, $society_id)
    {
        $filter = [
            'client' => null,
            'program' => null,
            'mission' => null,
            'wave' => null,
            'period' => null,
            'scenario' => null,
            'one_seq' => null,
            'criteria_a' => null,
            'criteria_b' => null,
            'society' => null,
            'theme' => null,
            'q_tag' => null,
            'job' => null,
            'shop' => null,
            'question' => null,
            'sequence' => null,
            'survey' => null,
            'criterion_less_than_100' => null,
            'top_5' => null,
            'flop_5' => null,
            'base-min' => null,
            'goal' => null,
            'answer' => null,
            'criteria' => null,
            'question_row' => null,
            'status' => null,
        ];

        //get all filter already set for this graph
        $multiple_id = ['one_seq', 'missions'];
        foreach ($filters['general'] as $key => $value) {
            $filter[$key] = in_array($key, $multiple_id)
                ? ArrayHelper::getIds($value)
                : $value;
        }

        //get wave if period selected
        $waves = $filter['wave'];



        //hot fix
        $filter = static::fixFilter($filter);
        $alias = Alias::select('filters')->where('society_id', $society_id)->first()->toArray();
        $alias = json_decode($alias['filters'], true);

        if (isset($filter['period']['id']) && $filter['period']['id'] !== 'perso' && $filter['period']['id'] !== 'cumulative') {
            # user not select specific month but choose last month or x lats month
            $waves = GraphTemplateService::_getWaveFromPeriod($filter['period'], $society_id, $filter);
            $filter['wave'] = !$waves ? ['id' => 9999999] : array_reverse($waves);
        }

        if (isset($filter['period']['id']) && $filter['period']['id'] === 'cumulative' && isset($filter['cumulative'])) {
            # user not select specific month but choose wave group
            $waves = GraphTemplateService::_getWaveFromWaveGroup($filter['cumulative']);
            $filter['wave'] = !$waves ? ['id' => 9999999] : array_reverse($waves);
        }

        # Pas de vague ni periode on prend les 3 derniÃ¨res vagues
        # No wave for period we take the last 3 waves
        if (!$waves && is_array($filter['wave']) && count($filter['wave']) == 0) {
            $res = \DB::table('show_wave_with_missions_' . $society_id)
                ->select('wave_id')
                ->groupBy('wave_id');

            GraphFilterController::_addFilters($res, $filter);

            $waves_use = $res->get();
            $waves = Wave::where('society_id', $filter['client']['id'])
                ->whereIn('id', $waves_use)
                ->orderBy('date_start', 'DESC')
                ->limit(3)
                ->get()
                ->toArray();

            $filter['wave'] = array_reverse($waves);
        }

        $filter['questions']  = array_get($filters, 'questions');
        $filter['sequences']  = array_get($filters, 'sequences');
        $filter['criterions'] = array_get($filters, 'criterions');
        $filter['criterion_less_than_100'] = array_get($filters, 'criterion_less_than_100');
        $filter['falling_critera'] = array_get($filters, 'falling_critera');
        $filter['top_10'] = array_get($filters['general'], 'top_10');
        $filter['flop_10'] = array_get($filters['general'], 'flop_10');
        $filter['with_active_action_plan'] = array_get($filters, 'with_active_action_plan');

        return $filter;
    }

    public static function fixFilter($filters)
    {
        $filters['questions'] = $filters['question'];
        $filters['scenarios'] = $filters['scenario'];
        $filters['sequences'] = $filters['sequence'];
        if (isset($filters['society']))
            $filters['client'] = $filters['society'];
        if (isset($filters['period']) && isset($filters['period']['id']) && $filters['period']['id'] !== 'perso')
            unset($filters['wave']);
        return $filters;
    }

    public static function getRestrictedshop(User $user, $filters)
    {
        //check restricted shop
        $restricted_shop = Shop::getRestrictedshop(
            $user->getKey(),
            $user->society_id,
            $user->current_society_id
        );

        $restrictedShops = ArrayHelper::getIds($restricted_shop->get()->toArray());
        $restrictedShopIds = [];
        $filter_shop = ArrayHelper::getIds($filters['shop']);
        foreach ($restrictedShops as $shop) {
            if ($filter_shop) {
                if (in_array($shop, $filter_shop)) {
                    array_push($restrictedShopIds, $shop);
                }
            } else {
                array_push($restrictedShopIds, $shop);
            }
        }
        return $restrictedShopIds;
    }
}
