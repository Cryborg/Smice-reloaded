<?php

namespace App\Classes\Results;

use App\Classes\Helpers\ArrayHelper;
use App\Classes\Helpers\CacheHelper;
use App\Classes\Services\GraphTemplateService;
use App\Http\Controllers\ExportImageController;
use App\Http\Controllers\GraphFilterController;
use App\Http\Shops\Models\Shop;
use App\Models\ActionPlan;
use App\Models\Alias;
use App\Models\AnswerComment;
use App\Models\AnswerFile;
use App\Models\AnswerImage;
use App\Models\Axe;
use App\Models\AxeDirectory;
use App\Models\AxeTagItem;
use App\Models\Criteria;
use App\Models\CriteriaGroup;
use App\Models\Goal;
use App\Models\LogModel;
use App\Models\Program;
use App\Models\Result;
use App\Models\ResultAnswer;
use App\Models\ResultAxe;
use App\Models\ResultAxeAsFilter;
use App\Models\ResultCompareToAxe;
use App\Models\ResultCompareToShop;
use App\Models\ResultCriteria;
use App\Models\ResultCriteriaA;
use App\Models\ResultCriteriaB;
use App\Models\ResultCriteriaScore;
use App\Models\ResultCumulative;
use App\Models\ResultIntervalDate;
use App\Models\ResultIntervalDateDelivery;
use App\Models\ResultJob;
use App\Models\ResultMission;
use App\Models\ResultPeriod;
use App\Models\ResultProgram;
use App\Models\ResultQuestionAsFilter;
use App\Models\ResultQuestionLevel;
use App\Models\ResultScenario;
use App\Models\ResultSequence;
use App\Models\ResultShop;
use App\Models\ResultSurvey;
use App\Models\ResultTheme;
use App\Models\ResultWave;
use App\Models\Sequence;
use App\Models\Society;
use App\Models\SurveyItem;
use App\Models\Theme;
use App\Models\User;
use App\Models\Wave;
use App\Models\WaveTarget;
use Cache;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;


class Results
{
    private $uigrid_index = -1;
    private $uigrid_index_key = -1;
    private $criteria_order = [];
    private $all_axe = [];
    private $all_axe_directory = [];
    private $line = [];
    private $formattedArr = [];
    private $deeplevel = 1;
    private $currentlevel = 1;
    private $sequence_on_criteria = [];
    private $question_base = [];
    private $question_shop = [];
    private $goal = [];
    private $survey_id = null;
    private $key_x = [];
    private $key_y = [];
    private $series_basic = [];
    private $series_c = [];
    private $filters = [];
    private $request_filters = [];
    private $save_filter = [];
    private $subCriteriaModelSource = null;
    private $subCriteriaModelId = null;
    private $ttl_cache = 60 * 7;
    private $compare_chart = false;
    private $floptop = null;
    private $livedata = false;
    private $hide_bases = false;
    private $sort_result = false;
    private $hide_goals = false;
    private $isGraph = false;
    private $disable_cache = "";
    private $type = "";
    private $source = "";
    private $from = null;
    private $to = null;
    private $criteria_group = [];
    private $save_x = null;
    private $save_y = null;

    public function queryFilter($model, $filter, $key)
    {
        //Retour
        $result_filter = [];
        if (isset($this->request_filters[$filter]) && $this->request_filters[$filter]) {
            $id = arrayHelper::getIds($this->request_filters[$filter]);
            if ($id) {
                $id = array_flatten($id);
                $id = array_unique($id);
                $quantity = count($id);
                if ($quantity === 1 && substr($id[0], 0, 2) === "v-") {
                    $sql = 'SELECT result_id from ' . $model->getTable() . ' WHERE(LOWER(value)=LOWER(\'' . substr($id[0], 2) . '\')) AND quantity = ' . $quantity . ' GROUP BY result_id HAVING COUNT(DISTINCT ' . $key . ')=' . $quantity;
                } else {
                    $id = implode(",", $id);
                    $sql = 'SELECT result_id from ' . $model->getTable() . ' WHERE ' . $key . ' IN(' . $id . ') AND quantity = ' . $quantity . ' GROUP BY result_id HAVING COUNT(DISTINCT ' . $key . ')=' . $quantity;
                }
                $result_filter = \DB::select($sql);
            } else {
                $result_filter = $model->select('result_id')
                    ->wherenull($key)->get()->toArray();
            }
        } else {
            $result_filter = $model->select('result_id')
                ->wherenull($key)->get()->toArray();
        }
        return $result_filter;
    }

    public function getSaveResult()
    {
        //get last update time for society
        $s = Society::find($this->user->current_society_id);
        $r = Result::where('isgraph', $this->isGraph);

        $r = $r->where('type', $this->type);
        $r = $r->where('x', $this->params['x']);
        $r = $r->where('y', $this->params['y']);
        if ($this->user->current_society_id !== 113) {
            if (isset($this->params['filters']['view_id']))
                $r = $r->where('view_id', $this->params['filters']['view_id']);
            $r = $r->where('created_by', $this->user->id);
        }
        $filters = $this->filters['general'];
        //axe as filter
        $filters['questions_as_filter'] = $this->_GetAllQuestionsasfilter();
        if (isset($filters['society']))
            $r = $r->where('society_id', $filters['society']['id']);
        if (isset($filters['base-min']) && $filters['base-min'])
            $r = $r->where('base_min', $filters['base-min']);
        if (isset($filters['goal']) && $filters['goal'])
            $r = $r->where('goal', $filters['goal']);
        if (isset($filters['top_5']))
            $r = $r->where('top_5', $filters['top_5'] ? true : false);
        if (isset($filters['flop_5']))
            $r = $r->where('flop_5', $filters['flop_5'] ? true : false);
        if (isset($filters['hide_bases']))
            $r = $r->where('hide_bases', $filters['hide_bases'] ? true : false);
        if (isset($filters['hide_goals']))
            $r = $r->where('hide_goals', $filters['hide_goals'] ? true : false);
        if (isset($filters['sort_result']))
            $r = $r->where('sort_result', $filters['sort_result'] ? true : false);
        $result_filter = [];


        //fix survey and question level when id is null
        if (isset($filters['question_level']) && $filters['question_level'] && !$filters['question_level']['id']) {
            unset($filters['question_level']);
        }
        if (isset($filters['survey']) && $filters['survey'] && !$filters['survey']['id']) {
            unset($filters['survey']);
        }
        $this->request_filters = $filters;

        $result_filter[] = $this->queryFilter(new ResultProgram(), 'program', 'program_id');
        $result_filter[] = $this->queryFilter(new ResultWave(), 'wave', 'wave_id');
        $result_filter[] = $this->queryFilter(new ResultCumulative(), 'cumulative', 'cumulative_id');
        $result_filter[] = $this->queryFilter(new ResultSurvey(), 'survey', 'survey_id');
        $result_filter[] = $this->queryFilter(new ResultShop(), 'shop', 'shop_id');
        $result_filter[] = $this->queryFilter(new ResultAxe(), 'axes', 'axe_id');
        $result_filter[] = $this->queryFilter(new ResultAxeAsFilter(), 'axes_as_filter', 'axe_id');
        $result_filter[] = $this->queryFilter(new ResultQuestionAsFilter(), 'questions_as_filter', 'answer_id');
        $result_filter[] = $this->queryFilter(new ResultQuestionLevel(), 'question_level', 'question_level_id');
        $result_filter[] = $this->queryFilter(new ResultTheme(), 'theme', 'theme_id');
        $result_filter[] = $this->queryFilter(new ResultCompareToAxe(), 'compare_to_axe', 'compare_to_axe_id');
        $result_filter[] = $this->queryFilter(new ResultCompareToShop(), 'compare_to_shop', 'compare_to_shop_id');
        $result_filter[] = $this->queryFilter(new ResultSequence(), 'sequence', 'sequence_id');
        $result_filter[] = $this->queryFilter(new ResultScenario(), 'scenario', 'scenario_id');
        $result_filter[] = $this->queryFilter(new ResultMission(), 'missions', 'mission_id');
        $result_filter[] = $this->queryFilter(new ResultCriteriaA(), 'criteria_a', 'criteria_a_id');
        $result_filter[] = $this->queryFilter(new ResultCriteriaB(), 'criteria_b', 'criteria_b_id');
        $result_filter[] = $this->queryFilter(new ResultCriteria(), 'criteria', 'criteria_id');
        $result_filter[] = $this->queryFilter(new ResultAnswer(), 'answer', 'answer_id');
        $result_filter[] = $this->queryFilter(new ResultJob(), 'job', 'job_id');

        if (isset($filters['criteria_score']) && $filters['criteria_score'] && isset($filters['criteria_score']['min']) && isset($filters['criteria_score']['max'])) {
            $result_filter[] = ResultCriteriaScore::select('result_id')
                ->where('min', $filters['criteria_score']['min'])
                ->where('max', $filters['criteria_score']['max'])
                ->get()->toArray();
        }
        if (isset($filters['period']) && $filters['period']) {
            $result_filter[] = ResultPeriod::select('result_id')
                ->where('period_id', $filters['period']['id'])
                ->get()->toArray();
        } else {
            $result_filter[] = ResultPeriod::select('result_id')
                ->wherenull('period_id')->get()->toArray();
        }
        if (isset($filters['interval-date']) && $filters['interval-date']) {
            $result_filter[] = ResultIntervalDate::select('result_id')
                ->where('from', $filters['interval-date']['from'])
                ->where('to', $filters['interval-date']['to'])
                ->get()->toArray();
        } else {
            $result_filter[] = ResultIntervalDate::select('result_id')
                ->wherenull('from')->wherenull('to')->get()->toArray();
        }
        if (isset($filters['interval-date-delivery']) && $filters['interval-date-delivery']) {
            $result_filter[] = ResultIntervalDateDelivery::select('result_id')
                ->where('from', $filters['interval-date-delivery']['from'])
                ->where('to', $filters['interval-date-delivery']['to'])
                ->get()->toArray();
        } else {
            $result_filter[] = ResultIntervalDateDelivery::select('result_id')
                ->wherenull('from')->wherenull('to')->get()->toArray();
        }

        $i = 0;
        $intersect_result_id = [];
        foreach ($result_filter as $res) {
            $res = array_flatten($res);
            if ($i > 0) {
                $intersect_result_id = array_intersect($res, $intersect_result_id);
            } else {
                $intersect_result_id = $res;
            }
            $i++;
        }

        if ($intersect_result_id)
            $r = $r->wherein('id', $intersect_result_id);
        else
            $r = $r->whereraw('1 = 0');
        $r = $r->first();
        if ($r) {
            Result::where('id', $r->id)->update(['last_access' => Carbon::now()]);
            if ($s['last_refresh_data'] < $r['last_refresh_data'])
                return unserialize($r['data']);
            else
                Result::where('id', $r->id)->delete();
        }
        return false;
    }

    public function saveResult($result_id)
    {
        $filters = $this->request_filters;
        if (isset($filters['program']) && $filters['program']) {
            $id = ArrayHelper::getIds($filters['program']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultProgram();
                    $r->result_id = $result_id;
                    $r->program_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultProgram();
                $r->result_id = $result_id;
                $r->program_id = null;
                $r->save();
            }
        } else {
            $r = new ResultProgram();
            $r->result_id = $result_id;
            $r->program_id = null;
            $r->save();
        }
        if (isset($filters['period']) && $filters['period']) {
            $id = ArrayHelper::getIds($filters['period']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultPeriod();
                    $r->result_id = $result_id;
                    $r->period_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultPeriod();
                $r->result_id = $result_id;
                $r->period_id = null;
                $r->save();
            }
        } else {
            $r = new ResultPeriod();
            $r->result_id = $result_id;
            $r->period_id = null;
            $r->save();
        }
        if (isset($filters['wave']) && $filters['wave']) {
            $id = ArrayHelper::getIds($filters['wave']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultWave();
                    $r->result_id = $result_id;
                    $r->wave_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultWave();
                $r->result_id = $result_id;
                $r->wave_id = null;
                $r->save();
            }
        } else {
            $r = new ResultWave();
            $r->result_id = $result_id;
            $r->wave_id = null;
            $r->save();
        }
        if (isset($filters['cumulative']) && $filters['cumulative']) {
            $id = ArrayHelper::getIds($filters['cumulative']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultCumulative();
                    $r->result_id = $result_id;
                    $r->cumulative_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultCumulative();
                $r->result_id = $result_id;
                $r->cumulative_id = null;
                $r->save();
            }
        } else {
            $r = new ResultCumulative();
            $r->result_id = $result_id;
            $r->cumulative_id = null;
            $r->save();
        }

        if (isset($filters['survey']) && isset($filters['survey']['id'])) {
            $id = ArrayHelper::getIds($filters['survey']);
            if (is_array($id)) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultSurvey();
                    $r->result_id = $result_id;
                    $r->survey_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultSurvey();
                $r->result_id = $result_id;
                $r->survey_id = null;
                $r->save();
            }
        } else {
            $r = new ResultSurvey();
            $r->result_id = $result_id;
            $r->survey_id = null;
            $r->save();
        }

        if (isset($filters['shop']) && $filters['shop']) {
            $id = ArrayHelper::getIds($filters['shop']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultShop();
                    $r->result_id = $result_id;
                    $r->shop_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultShop();
                $r->result_id = $result_id;
                $r->shop_id = null;
                $r->save();
            }
        } else {
            $r = new ResultShop();
            $r->result_id = $result_id;
            $r->shop_id = null;
            $r->save();
        }

        if (isset($filters['axes']) && $filters['axes']) {
            $id = ArrayHelper::getIds($filters['axes']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultAxe();
                    $r->result_id = $result_id;
                    $r->axe_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultAxe();
                $r->result_id = $result_id;
                $r->axe_id = null;
                $r->save();
            }
        } else {
            $r = new ResultAxe();
            $r->result_id = $result_id;
            $r->axe_id = null;
            $r->save();
        }

        if (isset($filters['axes_as_filter']) && $filters['axes_as_filter']) {
            $id = ArrayHelper::getIds($filters['axes_as_filter']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultAxeAsFilter();
                    $r->result_id = $result_id;
                    $r->axe_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultAxeAsFilter();
                $r->result_id = $result_id;
                $r->axe_id = null;
                $r->save();
            }
        } else {
            $r = new ResultAxeAsFilter();
            $r->result_id = $result_id;
            $r->axe_id = null;
            $r->save();
        }

        if (isset($filters['questions_as_filter']) && $filters['questions_as_filter']) {
            $ids = ArrayHelper::getIds($filters['questions_as_filter']);
            if ($ids) {
                $id = array_unique($id);
                $quantity = count($ids);
                foreach ($ids as $id) {
                    foreach ($id as $i) {
                        $r = new ResultQuestionAsFilter();
                        $r->result_id = $result_id;
                        if ($quantity === 1 && substr($id[0], 0, 2) === "v-")
                            $r->value = substr($id[0], 2);
                        else
                            $r->answer_id = $i;
                        $r->quantity = $quantity;
                        $r->save();
                    }
                }
            } else {
                $r = new ResultQuestionAsFilter();
                $r->result_id = $result_id;
                $r->answer_id = null;
                $r->save();
            }
        } else {
            $r = new ResultQuestionAsFilter();
            $r->result_id = $result_id;
            $r->answer_id = null;
            $r->save();
        }

        if (isset($filters['question_level']) && $filters['question_level'] && $filters['question_level']['id']) {
            $id = ArrayHelper::getIds($filters['question_level']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultQuestionLevel();
                    $r->result_id = $result_id;
                    $r->question_level_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultQuestionLevel();
                $r->result_id = $result_id;
                $r->question_level_id = null;
                $r->save();
            }
        } else {
            $r = new ResultQuestionLevel();
            $r->result_id = $result_id;
            $r->question_level_id = null;
            $r->save();
        }

        if (isset($filters['theme']) && $filters['theme']) {
            $id = ArrayHelper::getIds($filters['theme']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultTheme();
                    $r->result_id = $result_id;
                    $r->theme_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultTheme();
                $r->result_id = $result_id;
                $r->theme_id = null;
                $r->save();
            }
        } else {
            $r = new ResultTheme();
            $r->result_id = $result_id;
            $r->theme_id = null;
            $r->save();
        }

        if (isset($filters['compare_to_axe']) && $filters['compare_to_axe']) {
            $id = ArrayHelper::getIds($filters['compare_to_axe']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultCompareToAxe();
                    $r->result_id = $result_id;
                    $r->compare_to_axe_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultCompareToAxe();
                $r->result_id = $result_id;
                $r->compare_to_axe_id = null;
                $r->save();
            }
        } else {
            $r = new ResultCompareToAxe();
            $r->result_id = $result_id;
            $r->compare_to_axe_id = null;
            $r->save();
        }

        if (isset($filters['compare_to_shop']) && $filters['compare_to_shop']) {
            $id = ArrayHelper::getIds($filters['compare_to_shop']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultCompareToShop();
                    $r->result_id = $result_id;
                    $r->compare_to_shop_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultCompareToShop();
                $r->result_id = $result_id;
                $r->compare_to_shop_id = null;
                $r->save();
            }
        } else {
            $r = new ResultCompareToShop();
            $r->result_id = $result_id;
            $r->compare_to_shop_id = null;
            $r->save();
        }

        if (isset($filters['sequence']) && $filters['sequence']) {
            $id = ArrayHelper::getIds($filters['sequence']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultSequence();
                    $r->result_id = $result_id;
                    $r->sequence_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultSequence();
                $r->result_id = $result_id;
                $r->sequence_id = null;
                $r->save();
            }
        } else {
            $r = new ResultSequence();
            $r->result_id = $result_id;
            $r->sequence_id = null;
            $r->save();
        }

        if (isset($filters['scenario']) && $filters['scenario']) {
            $id = ArrayHelper::getIds($filters['scenario']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultScenario();
                    $r->result_id = $result_id;
                    $r->scenario_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultScenario();
                $r->result_id = $result_id;
                $r->scenario_id = null;
                $r->save();
            }
        } else {
            $r = new ResultScenario();
            $r->result_id = $result_id;
            $r->scenario_id = null;
            $r->save();
        }

        if (isset($filters['missions']) && $filters['missions']) {
            $id = ArrayHelper::getIds($filters['missions']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultMission();
                    $r->result_id = $result_id;
                    $r->mission_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultMission();
                $r->result_id = $result_id;
                $r->mission_id = null;
                $r->save();
            }
        } else {
            $r = new ResultMission();
            $r->result_id = $result_id;
            $r->mission_id = null;
            $r->save();
        }
        if (isset($filters['criteria_a']) && $filters['criteria_a']) {
            $id = ArrayHelper::getIds($filters['criteria_a']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultCriteriaA();
                    $r->result_id = $result_id;
                    $r->criteria_a_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultCriteriaA();
                $r->result_id = $result_id;
                $r->criteria_a_id = null;
                $r->save();
            }
        } else {
            $r = new ResultCriteriaA();
            $r->result_id = $result_id;
            $r->criteria_a_id = null;
            $r->save();
        }


        if (isset($filters['criteria_b']) && $filters['criteria_b']) {
            $id = ArrayHelper::getIds($filters['criteria_b']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultCriteriaB();
                    $r->result_id = $result_id;
                    $r->criteria_b_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultCriteriaB();
                $r->result_id = $result_id;
                $r->criteria_b_id = null;
                $r->save();
            }
        } else {
            $r = new ResultCriteriaB();
            $r->result_id = $result_id;
            $r->criteria_b_id = null;
            $r->save();
        }

        if (isset($filters['criteria']) && $filters['criteria']) {
            $id = ArrayHelper::getIds($filters['criteria']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultCriteria();
                    $r->result_id = $result_id;
                    $r->criteria_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultCriteria();
                $r->result_id = $result_id;
                $r->criteria_id = null;
                $r->save();
            }
        } else {
            $r = new ResultCriteria();
            $r->result_id = $result_id;
            $r->criteria_id = null;
            $r->save();
        }

        if (isset($filters['answer']) && $filters['answer']) {
            $id = ArrayHelper::getIds($filters['answer']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultAnswer();
                    $r->result_id = $result_id;
                    $r->answer_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultAnswer();
                $r->result_id = $result_id;
                $r->answer_id = null;
                $r->save();
            }
        } else {
            $r = new ResultAnswer();
            $r->result_id = $result_id;
            $r->answer_id = null;
            $r->save();
        }

        if (isset($filters['job']) && $filters['job']) {
            $id = ArrayHelper::getIds($filters['job']);
            if ($id) {
                $id = array_unique($id);
                $quantity = count($id);
                foreach ($id as $i) {
                    $r = new ResultJob();
                    $r->result_id = $result_id;
                    $r->job_id = $i;
                    $r->quantity = $quantity;
                    $r->save();
                }
            } else {
                $r = new ResultJob();
                $r->result_id = $result_id;
                $r->job_id = null;
                $r->save();
            }
        } else {
            $r = new ResultJob();
            $r->result_id = $result_id;
            $r->job_id = null;
            $r->save();
        }

        if (isset($filters['criteria_score']) && $filters['criteria_score'] && isset($filters['criteria_score']['min']) && isset($filters['criteria_score']['max'])) {
            $r = new ResultCriteriaScore();
            $r->result_id = $result_id;
            $r->min = $filters['criteria_score']['min'];
            $r->max = $filters['criteria_score']['max'];
            $r->save();
        } else {
            $r = new ResultCriteriaScore();
            $r->result_id = $result_id;
            $r->min = null;
            $r->max = null;
            $r->save();
        }


        if (isset($filters['interval-date']) && $filters['interval-date']) {
            if ($filters['interval-date']) {
                $r = new ResultIntervalDate();
                $r->result_id = $result_id;
                $r->from = $filters['interval-date']['from'];
                $r->to = $filters['interval-date']['to'];
                $r->save();
            }
        } else {
            $r = new ResultIntervalDate();
            $r->result_id = $result_id;
            $r->from = null;
            $r->to = null;
            $r->save();
        }
        if (isset($filters['interval-date-delivery']) && $filters['interval-date-delivery']) {
            if ($filters['interval-date-delivery']) {
                $r = new ResultIntervalDateDelivery();
                $r->result_id = $result_id;
                $r->from = $filters['interval-date-delivery']['from'];
                $r->to = $filters['interval-date-delivery']['to'];
                $r->save();
            }
        } else {
            $r = new ResultIntervalDateDelivery();
            $r->result_id = $result_id;
            $r->from = null;
            $r->to = null;
            $r->save();
        }
    }


    /**
     * @param $params
     * @param $user_id
     * @return array
     */
    public function generateResult($params, $user_id, $isGraph)
    {
        //
        // Check if dashboard_id && widget_id
        // Check if data is already in cache
        // Check if data is update if yes we send data else we continue
        // Table cache_result
        //

        // Field :
        // id
        // filter
        // user_id
        // society_id
        // data (serial)
        //


        $this->user = is_int($user_id) ? User::find($user_id) : $user_id;
        if ($this->user->disable_cache) {
            $this->disable_cache = "1";
        }

        //get shops from filters

        $this->params = $params;
        $this->filters = $this->params['filters'];
        $this->filters['general']['shop'] = $this->_getShopsFromFilters();
        $this->bonus = '';
        $this->key_x = [];
        $this->series = [];
        $this->item_score_null = [];
        $this->score_method = '';
        $this->save_x = $this->params['x'];
        $this->save_y = $this->params['y'];
        $this->request_filters = $this->params['filters'];
        $this->source = isset($this->params['filters']['source']) ? $this->params['filters']['source'] : null;
        $this->type = $isGraph ? $this->filters['type'] : 'table';
        $this->isGraph = $isGraph;
        $this->request_type = '';
        $r = $this->GetSaveResult();
        if ($r)
            return $r;

        //if graph is not ready
        if ($isGraph && $this->params['y'] === 'group') {
            return [
                "title" => [
                    "text" => "OUPS ! L'affichage par groupe n'est pas encore disponible, vous pouvez cependant avoir un affichage par tableau",
                ],
                "credits" => [
                    "enabled" => false,
                ],
                'xAxis' => [
                    'categories' => null
                ],
                'series' => null
            ];
        }

        //remove survey info if no survey selected
        if (isset($this->filters['general']['survey']) && is_array($this->filters['general']['survey']) && count($this->filters['general']['survey']) > 0) {
            if ($this->filters['general']['survey']['id'] == 0)
                if (!is_int($this->filters['general']['survey']))
                    unset($this->filters['general']['survey']);
        }



        $this->filters['general']['answer'] = $this->_GetAllQuestionsasfilter();
        $this->filters = GraphTemplateService::getFilter($this->filters, $this
            ->user
            ->current_society_id);



        //check if no split by
        if (!$this->params['x']) {
            $this->params['x'] = 'program';
        }


        //check if split_by is axe tag
        if (isset($this->filters['split_by']) && is_int($this->filters['split_by']['id'])) {
            $this->filters['tags'] = $this->filters['split_by']['id'];
            $this->params['x'] = 'group';
        }

        //check if range is axe tag
        if (isset($this->filters['range']) && is_int($this->filters['range']['id'])) {
            $this->filters['tags'] = $this->filters['range']['id'];
            $this->params['y'] = 'group';
        }

        if (isset($this->filters['hide_bases']) && $this->filters['hide_bases'])
            $this->hide_bases = true;

        if (isset($this->filters['hide_goals']) && $this->filters['hide_goals'])
            $this->hide_goals = true;

        if (isset($this->filters['sort_result']) && $this->filters['sort_result'])
            $this->sort_result = true;

        if (isset($this->params['type']) && $this->params['type'])
            $this->request_type = $this->params['type'];

        //get score on level question.
        $level_question = ['criteria', 'theme', 'job', 'criteria_a', 'criteria_b'];
        if ((in_array($this->params['y'], $level_question)) || (in_array($this->params['x'], $level_question))) {
            $this->score_method = 'question_';
            $this->bonus = 'without_bonus_';
        }
        //check if some filter that affect the way of score calculation are used
        foreach ($level_question as $value) {
            if ($this->filters[$value]) {
                $this->score_method = 'question_';
                $this->bonus = 'without_bonus_';
            }
        }

        if ($this->params['y'] == 'connection' || $this->params['y'] == 'user') {
            $this->hide_bases = $this->hide_goals = true;
            $users = User::where('society_id', $this->filters['society']['id']);
            //check if axe selected
            if ($$this->filters['shop']) {
                $users_id = Shop::getUsershop($this->filters['shop']);
                $users = $users->wherein('id', $users_id);
            }
            //get user from shops
            $this->filters['users'] = $users->get();
            foreach ($this->filters['users'] as $u) {
                $this->filters['users_id'][] = $u->id;
            }
        }

        //Get Column data to read
        //read x
        if ($this->params['x'] == 'group') {
            //split by axe
            $this->key_x = $this->_getKeyTag('split_by');
        } else if ($this->params['x'] == 'question_row_name') {
            //split by axe
            $this->key_x = $this->_getKeyAnswer();
        } else if ($this->params['x'] == 'day' || $this->params['x'] == 'week' || $this->params['x'] == 'month') {
            //split by axe
            $this->key_x = $this->_getKeyPeriod();
        } else if (isset($this->filters['period']['id']) && $this->filters['period']['id'] == 'cumulative') {
            $this->key_x = $this->_getKeyWave($this->params['x'], 'x');
        } else if ($this->params['x']) {
            $this->key_x = $this->_getKey($this->params['x'], 'x');
        };
        //read y
        if ($this->params['y'] == 'group' && isset($this->filters['range']) && is_int($this->filters['range']['id'])) {
            $this->key_y = $this->_getKeyTag('range');
        } else if ($this->params['y'] == 'group' && isset($this->filters['range']) && !is_int($this->filters['range']['id'])) {
            $this->key_y = null;
        } else if ($this->params['y'] == 'connection') {
            $this->key_y = null;
        } else if ($this->params['y'] == 'user') {
            $this->key_y = $this->_getKeyUser();
        } else if ($this->params['y'] == 'question_row') {
            $this->key_y = $this->_getKeyQuestion();
        } else if ($this->params['y'] !== 'criteria') {
            $this->key_y = $this->_getKey($this->params['y'], 'y');
        }
        //get criteria order && get criteria weight * question weight = 0
        if ($this->params['y'] !== 'connection' && $this->params['y'] !== 'user') {
            $cachekey = CacheHelper::SetCacheKey('predata_', $this->user, [$this->filters]);
            $predata = Cache::get($cachekey . $this->disable_cache, function () use ($cachekey) {
                $survey_id = null;
                $item_score_null = [];
                $order = \DB::table('show_criteria_' . $this->user->current_society_id);
                $order = GraphFilterController::_addFilters($order, $this->filters);
                $order = $order->orderBy('survey_id', 'DESC');
                $order = $order->get();
                foreach ($order as $o) {
                    $survey_id = $survey_id ? $survey_id : $o['survey_id']; //get last surveyid only
                    $item_score_null[$o['criteria_id']][$o['question_id']] = $o['criteria_weight'] * $o['question_weight'];
                }
                $r = [
                    'survey_id' => $survey_id,
                    'item_score_null' => $item_score_null
                ];
                Cache::put($cachekey, $r, $this->ttl_cache);
                return $r;
            });
            $this->item_score_null = $predata['item_score_null'];
            $this->survey_id = $predata['survey_id'];
            //$this->all_criteria_id = $predata['all_criteria_id'];
            $this->question_shop = $this->getQuestionShop();
        }

        //define all column

        $columnDefs = $this->setColumn();

        if ($this->params['y'] == 'criteria') {
            //get base for criteria
            $this->criteria_base = $this->getCriteriaBase();
            //get base for question
            $this->question_base = $this->getQuestionBase();
        }

        if ($this->params['x'] === 'question' && $this->filters['question_level'] && $this->filters['question_level']['id'] > 0) {
            //lecture des missions regroupées par réponses possible
            $this->params['x'] = "question_row";
        }

        if ($this->params['y'] == 'sequence') {
            $data = $this->showSequence();
        } else if ($this->params['y'] == 'group' && isset($this->filters['range']) && !is_int($this->filters['range']['id'])) {
            $data = $this->showGroup();
        } else if ($this->params['y'] == 'criteria') {
            //check if criteria is link to one or more question
            $data = $this->showCriteria();
        } else if ($this->params['y'] == 'question_row') {
            $data = $this->showAnswerRow();
        } else if ($this->params['y'] == 'connection' || $this->params['y'] == 'user') {
            $data = $this->showConnection();
        } else if ($this->params['y'] == 'program_g') {
            $data = $this->showProgramGroup();
        } else {
            $data = $this->showOther();
        }
        if ($isGraph) {
            //no x and y return score only
            $result_box = $this->show_scoring_multi('first', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id);
            $box =  [
                'type' => 'box',
                'data' => $result_box
            ];
            $hide_percentage = false;
            if (($this->params['y'] == 'connection') || ($this->params['y'] == 'connection')) {
                $hide_percentage = true;
            }
            $options = [
                'tooltip' => [
                    'enabled' => true
                ],
                'credits' => [
                    'enabled' => false
                ],
                'hidePercentage' => [
                    'enabled' => $hide_percentage
                ],
                'plotOptions' => [
                    'series' => [
                        'animation' => true,
                        'dataLabels' => [
                            'enabled' => true
                        ]
                    ]
                ],
                'colors' => ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#3F51B5', '#03A9F4', '#4CAF50', '#546E7A', '#D4526E', '#A5978B', '#4ECDC4', '#C7F464', '#81D4FA', '#FD6A6A', '#2B908F', '#F9A3A4', '#90EE7E', '#5A2A27', '#A300D6'],

                'title' => [
                    'text' => ''
                ],
                'dataLabels' => []
            ];
            if (isset($this->params['filters']['general']['hide_labels']) && !$this->params['filters']['general']['hide_labels']) {
                $options['dataLabels'] = [
                    'enabled' => true
                ];
            } else if (isset($this->params['filters']['general']['display_labels']) && $this->params['filters']['general']['display_labels']) {
                $options['dataLabels'] = [
                    'enabled' => true
                ];
            } else {
                $options['dataLabels'] = [
                    'enabled' => false
                ];
            }
            if (isset($this->params['filters']['general']['legend_position'])) {
                if (isset($this->params['filters']['general']['legend_position']['name'])) {
                    $options['legend'] = [
                        'position' => $this->params['filters']['general']['legend_position']['name']
                    ];
                } else {
                    $options['legend'] = [
                        'position' => $this->params['filters']['general']['legend_position']
                    ];
                }
            }

            //preapre enabledOnSeries
            $enabledOnSeries = $stroke_width = $dashArray = [];
            $i = -1;
            foreach ($this->series as $s) {
                $i++;
                array_push($enabledOnSeries, $i);
            }
            foreach ($this->series_basic as $s) {
                array_push($stroke_width, 0);
                array_push($dashArray, 0);
            }

            foreach ($this->series_c as $s) {
                array_push($stroke_width, 4);
                array_push($dashArray, 8);
            }

            if ($this->params['y'] !== 'connection' && $this->params['y'] !== 'user') {
                $yAxis = [
                    'min' => 0,
                    'max' => 100,
                    'tickAmount' => 10,
                    'forceNiceScale' => true
                ];
            } else {
                $yAxis = [
                    'tickAmount' => 10,
                    'forceNiceScale' => true
                ];
            }



            $column = [
                'chart' => [
                    'id' => 'column',
                    'type' => $this->compare_chart ? 'line' : 'bar',
                    'height' => 450,
                    'redrawOnParentResize' => true

                ],
                'plotOptions' => [
                    'bar' => [
                        'horizontal' => false,
                        'borderRadius' => 2,
                        'endingShape' => 'rounded',
                        'dataLabels' => [
                            'position' => 'top'
                        ]
                    ],
                    'line' => [
                        'borderRadius' => 2,
                        'dataLabels' => [
                            'enabled' => true,
                            'style' => [
                                'fontSize' => '12px',
                                'colors' => ['#000']
                            ]
                        ]
                    ],

                ],

                'dataLabels' => [
                    'style' => [
                        'fontSize' => '12px',
                        'colors' => ['#fff']
                    ],
                    'background' => [
                        'enabled' => false
                    ],

                ],

                'yaxis' => $yAxis,
                'stroke' => [
                    'curve' => 'smooth',
                    'dashArray' => $dashArray
                ],
                'xaxis' => $this->xAxis[0],
                'series' => count($this->series) !== 0 ? $this->series : [],
            ];


            //hot fix for comparaison
            //change all serie type to line

            $line_series = $this->series;
            foreach ($line_series as &$s) {
                $s['type'] = 'line';
            }

            $line = [
                'chart' => [
                    'id' => 'line',
                    'type' => 'line',
                    'redrawOnParentResize' => true
                ],
                'yaxis' => $yAxis,
                'stroke' => [
                    'curve' => 'smooth',
                    'dashArray' => $dashArray
                ],
                'xaxis' => $this->xAxis[0],
                'series' => count($line_series) !== 0 ? $line_series : [],
            ];

            //hot fix for comparaison
            //change all serie type to line
            $bar_series = $this->series;

            foreach ($bar_series as &$s) {
                $s['type'] = 'bar';
            }


            $bar = [
                'chart' => [
                    'id' => 'bar',
                    'type' => 'bar',
                    'redrawOnParentResize' => true
                ],
                'plotOptions' => [
                    'bar' => [
                        'horizontal' => true,
                        'dataLabels' => [
                            'position' => 'top'
                        ]
                    ]
                ],
                'dataLabels' => [
                    'enabled' => true,
                    'offsetX' => -6,
                    'style' => [
                        'fontSize' => '12px',
                        'colors' => ['#fff']
                    ]
                ],
                'stroke' => [
                    'curve' => 'smooth',
                    'dashArray' => $dashArray
                ],
                'xaxis' => $this->xAxis[0],
                'series' => count($bar_series) !== 0 ? $bar_series : [],
            ];
            $bar['xaxis']['max'] = 100;

            $pie = [
                'chart' => [
                    'id' => 'pie',
                    'type' => 'pie',
                    'redrawOnParentResize' => true
                ],
                'xAxis' => $this->xAxis,
                'series' => $this->series,
            ];

            $radar = [
                'chart' => [
                    'id' => 'radar',
                    'type' => 'line',
                    "polar" => true,
                    'redrawOnParentResize' => true
                ],
                'xAxis' => $this->xAxis,
                'series' => $this->series,
            ];
            $score_serie = round(floatval($box['data']['score']), 1);

            $color_serie = '#20e647';
            if ($score_serie < 75)
                $color_serie = '#FFAA00';
            if ($score_serie < 50)
                $color_serie = '#E74C3C';

            $averageScore = [
                'series' => $score_serie !== 0 ? [$score_serie] : [],
                'colors' => [$color_serie],
                'chart' => [
                    'id' => 'score',
                    'type' => 'radialBar',
                    'height' => 280,
                    'redrawOnParentResize' => true
                ],
                'plotOptions' => [
                    'radialBar' => [
                        'hollow' => [
                            'margin' => 0,
                            'size' => '70%'
                        ],
                        'track' => [
                            'dropShadow' => [
                                'enabled' => true,
                                'top' => 2,
                                'left' => 0,
                                'blur' => 4,
                                'opacity' => 0.15
                            ]
                        ],
                        'dataLabels' => [
                            'name' => [
                                'show' => false
                            ],
                            'value' => [
                                'color' => "#000000",
                                'fontSize' => "30px",
                                'show' => true
                            ]
                        ]
                    ]
                ],

                'stroke' => [
                    'lineCap' => "round"
                ]
            ];

            $res = [
                'column' => array_merge_recursive($options, $column),
                'line' => array_merge_recursive($options, $line),
                'bar' => array_merge_recursive($options, $bar),
                'pie' => array_merge_recursive($options, $pie),
                'radar' => array_merge_recursive($options, $radar),
                'score' => array_merge_recursive($options, $averageScore)

            ];

            if ($this->params['filters']['source'] == 'dashboard') {
                $res = $res[$this->params['filters']['type']];
            }
        } else {
            $res = ['columnDefs' => $columnDefs, 'data' => $data];
        }
        if (isset($user_id->id) && $this->source !== 'build') {
            $r = new Result();
            $r->society_id = $this->filters['society']['id'];
            $r->base_min = isset($this->filters['base-min']) && $this->filters['base-min'] ? $this->filters['base-min'] : null;
            $r->goal = isset($this->filters['goal']) && $this->filters['goal'] ? $this->filters['goal'] : null;
            $r->top_5 = isset($this->filters['top_5']) && $this->filters['top_5'] ? true : false;
            $r->flop_5 = isset($this->filters['flop_5']) && $this->filters['flop_5'] ? true : false;
            $r->hide_bases = isset($this->filters['hide_bases']) && $this->filters['hide_bases'] ? true : false;
            $r->hide_goals = isset($this->filters['hide_goals']) && $this->filters['hide_goals'] ? true : false;
            $r->sort_result = isset($this->filters['sort_result']) && $this->filters['sort_result'] ? true : false;
            $r->view_id = isset($this->params['filters']['view_id']) ? $this->params['filters']['view_id'] : null;
            $r->type = $isGraph ? $this->type : 'table';
            $r->data = serialize($res);
            $r->society_id = $this->user->current_society_id;
            $r->isgraph = $isGraph;
            $r->last_access = Carbon::now();
            $r->last_refresh_data = Carbon::now();
            $r->created_by = $user_id->id;
            $r->x = $this->save_x;
            $r->y = $this->save_y;
            $r->save();
            //save_other filter for this request
            //save only if table
            if ($r->type === 'table')
                $this->saveResult($r->id);
            else if ($r->type !== 'table')
                $this->saveResult($r->id);
        }
        return $res;
    }


    public function generateReferencial($user, $filters, $question_ids = null)
    {
        $this->hide_bases = true;
        $this->hide_goals = true;
        $this->sort_result = false;
        $this->user = $user;
        $this->filters = GraphTemplateService::getFilter($filters, $this
            ->user
            ->current_society_id);
        $this->filters['axes'] = [];
        $this->params['x'] = null;
        $this->params['y']  = 'sequence';
        $this->filters['top_5'] = null;
        $this->filters['flop_5'] = null;
        $this->filters['questions'] = $question_ids;
        $this->request_type = "referencial";
        $data = $this->showSequence();
        return $data;
    }
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    public function show_scoring_multi($first, $view, $type = null, $criteria = null, $key = null, $axe = null, $key_option = false, $all_shops = null)
    {
        if ($criteria && !isset($criteria['question_level'])) {
            $criteria['question_level'] = false;
        }

        $cachekey = CacheHelper::SetCacheKey($view, $this->user, [$this->filters, $key, $type, $view, $key_option, $axe, $criteria, $all_shops, $first]);
        $r = Cache::get($cachekey . $this->disable_cache, function () use ($cachekey, $key, $type, $view, $key_option, $axe, $criteria, $all_shops, $first) {
            $key_sql = null;
            if ($key)
                if ($key_option)
                    $key_sql = $key . '_id as id,' . $key . '_id as name,';
                else
                    $key_sql = $key . '_id as id,' . $key . '_name as name,';

            $r = \DB::table($view)
                ->selectRaw(
                    $type . $key_sql . '
                CASE WHEN SUM(' . $this->score_method . 'weight) > 0
                THEN SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                    ELSE null
                    END AS score,
                    COUNT(distinct wave_target_id) as quantity'
                )
                ->where('scoring', true)
                ->whereNotNull('score');
            if ($this->filters['base-min'] && $this->filters['base-min'] > 0 && $axe === 'y') {
                $r = $r->havingRaw('COUNT(distinct wave_target_id) >= ' . $this->filters['base-min']);
            }
            if ($criteria) {
                if ($criteria['question_level']) {
                    $r = $r->where('question_id', $criteria['question_id']);
                } else {
                    $r = $r->where('criteria_id', $criteria['id']);
                }
            }

            if (isset($this->filters['wave_target_id'])) {
                $r = $r->wherein('wave_target_id', ArrayHelper::getIds($this->filters['wave_target_id']));
            }
            if (isset($this->filters['current_shop'])) {
                $r = $r->wherein('shop_id', $this->filters['current_shop']);
            }

            if ($all_shops) {
                $r = $r->wherein('shop_id', ArrayHelper::getIds($all_shops));
            }
            if (isset($this->filters['wave_target_id'])) {
                $r = $r->wherein('wave_target_id', ArrayHelper::getIds($this->filters['wave_target_id']));
            }
            //fix more than one theme on one criteria
            if ($this->filters['theme'] && count($this->filters['theme']) > 0) {
                $r = $r->wherein('theme_id', ArrayHelper::getIds($this->filters['theme']));
            }
            if ($this->filters['job'] && count($this->filters['job']) > 0) {
                $r = $r->wherein('job_id', ArrayHelper::getIds($this->filters['job']));
            }
            if ($this->filters['criteria_a'] && count($this->filters['criteria_a']) > 0) {
                $r = $r->wherein('criteria_a_id', ArrayHelper::getIds($this->filters['criteria_a']));
            }
            if ($this->filters['criteria_b'] && count($this->filters['criteria_b']) > 0) {
                $r = $r->wherein('criteria_b_id', ArrayHelper::getIds($this->filters['criteria_b']));
            }


            if ($key)
                $r = $r->whereNotNull($key . '_id');
            if ($type)
                $r = $r->groupBy('type');

            if ($key) {
                if ($key === 'wave_target' && $this->params['y'] !== 'criteria') {
                    $r = $r->groupBy($key . '_id');
                } else if ($key === 'wave_target') {
                    $r = $r->groupBy('type', $key . '_id');
                } else if ($key === 'question_row_name') {
                    $r = $r->groupBy($key);
                } else if ($key_option) {
                    $r = $r->groupBy($key . '_id')->orderBy($key . '_id');
                } else {
                    $r = $r->groupBy($key . '_name', $key . '_id')->orderBy($key . '_id');
                }
            }
            $r = GraphFilterController::_addFilters($r, $this->filters);

            if ($first === 'first')
                $r = $r->first();
            else
                $r = $r->get();
            Cache::Put($cachekey, $r, $this->ttl_cache);
            return $r;
        });
        return $r;
    }

    public function showSequence()
    {
        //check if survey_id exist
        if (!isset($this->filters['survey']) || $this->filters['survey'] === '{}' || (is_array($this->filters['survey']) && count($this->filters['survey']) === 0)) {
            //if not we find first survey
            $get_survey = \DB::table('show_criteria_' . $this->user->current_society_id)
                ->selectRaw('survey_id');
            $get_survey = GraphFilterController::_addFilters($get_survey, $this->filters);
            $get_survey = $get_survey->orderBy('survey_id', 'DESC');
            $get_survey = $get_survey->first();
            $survey_id = $get_survey['survey_id'];
        } else {
            $survey_id = ArrayHelper::getIds($this->filters['survey']);
        }
        $seq_id = [];
        if (isset($this->filters['sequence'])) {
            foreach ($this->filters['sequence'] as $seq) {
                if (isset($seq['level']) && $seq['level'] === 0)
                    $seq_id[] = $seq['id'];
            }
        }
        if ($seq_id) {
            $cachekey = CacheHelper::SetCacheKey('sequenceFirstLevel_', $this->user, [$this->filters, $survey_id, $seq_id]);
            $sequenceFirstLevel = Cache::get($cachekey . $this->disable_cache . $this->request_type, function () use ($cachekey, $survey_id, $seq_id) {
                $r = SurveyItem::with('children')->where('survey_id', $survey_id)->whereNull('parent_id')->wherein('item_id', $seq_id)->where('display_report', true)
                    ->orderBy('order')
                    ->retrieveAll()
                    ->toArray();
                Cache::put($cachekey, $r, $this->ttl_cache);
                return $r;
            });
        } else {
            $cachekey = CacheHelper::SetCacheKey('sequenceFirstLevel_', $this->user, [$survey_id]);
            $sequenceFirstLevel = Cache::get($cachekey . $this->disable_cache . $this->request_type, function () use ($cachekey, $survey_id) {
                $r = SurveyItem::with('children')->where('survey_id', $survey_id)->whereNull('parent_id')->where('display_report', true)
                    ->orderBy('order')
                    ->retrieveAll()
                    ->toArray();
                Cache::put($cachekey, $r, $this->ttl_cache);
                return $r;
            });
        }

        if (($this->filters['flop_5'] || $this->filters['top_5'])) {
            $floptop  = $this->getScoreSequence($sequenceFirstLevel, $survey_id);
            foreach ($floptop as $key => $value)
                if (is_null($value['score']))
                    unset($floptop[$key]);

            if ($this->filters['flop_5']) {
                $columns = array_column($floptop, 'score');
                array_multisort($columns, SORT_ASC, $floptop);
                $this->floptop = array_slice($floptop, 0, 5);
            }
            if ($this->filters['top_5']) {
                $columns = array_column($floptop, 'score');
                array_multisort($columns, SORT_DESC, $floptop);
                $this->floptop = array_slice($floptop, 0, 5);
            }
        }


        $data = $this->addSequenceline($sequenceFirstLevel, $survey_id);

        return $data;
    }

    public function showProgramGroup()
    {
        $this->filter_y = 'program_g';

        $main_directories =  Program::with('children')->has('children')
            ->whereNull('parent_id')->whereNull('deleted_at')->where('society_id', $this->user->current_society_id)->get()->toArray();
        $data = $this->addGroupline($main_directories);
        return $data;
    }

    public function showGroup()
    {
        $this->filter_y = 'group';
        $axes_in_shop = $this->prepareAxe();
        $axes = Axe::select('id', 'axe_directory_id')->whereIn('id', $axes_in_shop)
            ->where('society_id', $this->user->current_society_id)
            ->get();
        foreach ($axes as $a) {
            if ($a->axe_directory_id) {
                $this->all_axe_directory[] = $this->showDirectoriesParent($a->axe_directory_id);
            }
        }
        $directories = AxeDirectory::relations()->where('axe_directory.hide_to_client', false)
            ->whereIn('axe_directory.id', $this->all_axe_directory)
            ->retrieveAll()
            ->toArray();
        $this->all_axe_directory = array_map(
            function ($value) {
                return $value['id'];
            },
            $directories
        );
        $main_directories = AxeDirectory::relations()->where('axe_directory.hide_to_client', false)
            ->whereIn('axe_directory.id', $this->all_axe_directory)
            ->whereNull('axe_directory.parent_id')
            ->retrieveAll()
            ->toArray();
        $data = $this->addGroupline($main_directories);
        return $data;
    }

    public function showCriteria()
    {

        $criteria_question = $this->getCriteriaQuestion();
        $this->sequence_on_criteria = $this->SequenceCriteria($this->survey_id);
        $this->criteria_group = Criteria::whereNotNull('criteria_group_id')->where('society_id', $this->user->current_society_id)->get()->toArray();
        $this->criteria_group_picture = CriteriaGroup::where('society_id', $this->user->current_society_id)->get()->toArray();
        /* criteria_question :
            type:"text_area"
            criteria_id:28317
            question_id:64467
            question_name:"{"cn": "", "de": "", "en": "", "es": "", "fr": "Coup de cœur ressenti durant la mission", "it": "", "nl": "", "pl": "", "pt": "", "ru": ""}"
            sequence_name:"{"cn": "", "de": "", "en": "", "es": "", "fr": "Global", "it": "", "nl": "", "pl": "", "ru": ""}"
            score:null
        */

        $data = [];
        $this->filter_y = 'criteria';
        //get all score for each criteria

        $criteria_score = $this->getCriteriaScoring();

        /* criteria_score :
            id:28347
            count:365
            type:"radio"
            name:"{"cn": "", "de": "", "en": "", "es": "", "fr": "Fluidité de l'embarquement ", "it": "", "nl": "", "pl": "", "pt": "", "ru": ""}"
            score:null
            progress:null
        */
        if ($this->sort_result) {
            $columns = array_column($criteria_score, 'score');
            array_multisort($columns, SORT_DESC, $criteria_score);
        }
        foreach ($criteria_score as $criteria) {
            if (!isset($criteria['type']))
                $criteria['type'] = null;
            if (!isset($criteria['progress']))
                $criteria['progress'] = null;
            if (!isset($criteria['col']))
                $criteria['col'] = [];
            //check if criteria is link to one or more question
            $q = $sub_question = $line = [];
            $nb = 0;
            $line = null;
            $series = [];
            $seq_name = null;
            if (isset($this->sequence_on_criteria[$criteria['id']])) {
                $a = array_flatten($this->sequence_on_criteria[$criteria['id']]);
                $seq_name = implode(" / ", $a);
                $seq_name .= " / ";
            }
            //check if more than one question for the criteria
            foreach ($criteria_question as $question) {
                if (!isset($question['criteria_id'])) {
                    $deb = 1;
                }
                if ($question['criteria_id'] == $criteria['id']) {
                    $q_id = $question['question_id'];
                    $nb++;
                }
            }


            if (($this->params['x'] === 'question_row') && ($this->filters['question_level']['id'] > 0)) { //on filtre sur une question
                $result = $res = [];
                foreach ($this->key_x as $k => $value) { // recuperation du score global en fonction du choix des réponses
                    $this->filters['wave_target_id'] = $this->getWavetargetByAnswer($value['id']);
                    $test = $this->filters['wave_target_id'];
                    $r = $this->show_scoring_multi('first', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, 'type,', $criteria);
                    $result[] = [
                        "id" => $value['id'],
                        "score" => $r['score'],
                        "base" => $r['quantity'],
                        "type" => $r['type'],
                        "info" => 'Score global de ' . $this->getAlias($this->params['y']) . ' lorsque cette réponse à la question "' . $this->filters['question_level']['name'] . '" est séléctionnée'
                    ];
                }
                if (!empty($result)) {
                    foreach ($result as $re) {
                        isset($re['type']) && ($re['type'] === 'satisfaction' || $re['type'] === 'number') ? $round = 1 : $round = 0;
                        $res[$re['id']] = [
                            'type' => isset($re['type']) ? $re['type'] : null,
                            'score' => ($re['score'] !== null ? round($re['score'], $round) : null),
                            'info' => (isset($re['info']) ? $re['info'] : null),
                            'quantity' => (isset($re['base']) ? $re['base'] : null),
                            'total_answer' => (isset($re['total_answer']) ? $re['total_answer'] : null),
                            'quantity_row_id' => (isset($re['quantity_row_id']) ? $re['quantity_row_id'] : null),
                            'base' => (isset($re['base']) ? $re['base'] : null)
                        ];
                    }
                } else {
                    $res = null;
                }
                $criteria['col'] = $res;
            }
            if ($this->params['x'] === 'question_row_name') {
                $result = \DB::table('show_scoring_multi_' . $this->bonus . $this
                    ->user
                    ->current_society_id)
                    ->select('question_row_name as id')
                    ->selectRaw(
                        'CASE WHEN SUM(' . $this->score_method . 'weight) > 0
                THEN SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                ELSE null
                END AS score,
                COUNT(distinct wave_target_id) as quantity_row_id'
                    );
                $result = $result->Where("criteria_id", $criteria['id']);
                $result = $result->groupby('question_row_name');
                $result = GraphFilterController::_addFilters($result, $this->filters);
                $result = $result->get();
                $total_answer = 0;
                foreach ($result as $r) {
                    $total_answer += $r['quantity_row_id'];
                }
                foreach ($result as &$r) {
                    $r['quantity'] = $total_answer;
                }
                foreach ($result as $re) {
                    $col[$re['id']] = [
                        'type' => null,
                        'score' => ($re['score'] !== null ? round($re['score'], 1) : null),
                        'info' => (isset($re['info']) ? $re['info'] : null),
                        'total_answer' => (isset($re['quantity']) ? $re['quantity'] : null),
                        'quantity_row_id' => (isset($re['quantity_row_id']) ? $re['quantity_row_id'] : null)
                    ];
                }
                $total_answer = 0;

                $criteria['col'] = isset($col) ? $col : null;
            }
            if ($nb > 1) {
                foreach ($criteria_question as $question) { //Parcours la liste des question liés au critère en cours
                    if ($question['criteria_id'] == $criteria['id']) { //si on trouve une question pour ce critère
                        $q = $this->addLine('question', $question['question_id'],  $question['question_id'], $question['question_name'], $question['type'], null, $question['score'], null, $question['col']); //préparation du sous niveau question
                        if ($q) {
                            $seq_name_q = null;
                            if (isset($this->sequence_on_criteria[$criteria['id']][$question['question_id']])) {
                                $seq_name_q = $this->sequence_on_criteria[$criteria['id']][$question['question_id']][0];
                            }
                            $q["col0"]['question_id'] = $question['question_id'];
                            $q_id = $question['question_id'];
                            $q["col0"]['info'] = $seq_name_q . " - " . $this->checkJson($question['question_name']);
                            array_push($sub_question, $q);
                        }
                    }
                }
            }
            if ($this->isGraph) {
                if ($this->params['x']) {
                    foreach ($this->key_x as $k => $x) {
                        $series[] = isset($criteria['col'][$x['id']]) ? round($criteria['col'][$x['id']]['score'], 0) : "";
                    }
                    $this->series[] = [
                        "data" => $series,
                        "name" => $this->checkJson($criteria['name'])
                    ];
                } else {
                    $this->series[] = [
                        "data" => [$criteria['score'] ? intval($criteria['score']) : ""],
                        "name" => $this->checkJson($criteria['name'])
                    ];
                }
            } else {
                $line = $this->addLine('criteria', $q_id, $criteria['id'], $criteria['name'], $criteria['type'], $criteria['count'], $criteria['score'], $criteria['progress'], $criteria['col']);
            }
            if ($line) {
                if (count($sub_question) < 2) { //le critère est lié à une seule question on ajoute le lien pour afficher directement le detail de la question en cliquant sur le critère
                    $line["col0"]['question_id'] = $q_id;
                }
                $line["col0"]['info'] = $seq_name . $this->checkJson($criteria['name']);
                if (count($sub_question) > 1) {
                    $line['data'] = $sub_question;
                }
                //check if filter on criteria score
                $show_line = true;
                if (isset($this->filters['criteria_score']) && isset($this->filters['criteria_score']['min']) && is_int($this->filters['criteria_score']['min'])) {
                    if ($criteria['score'] <= $this->filters['criteria_score']['min'] && $this->filters['criteria_score']['min'] !== 0)
                        $show_line = false;
                }
                if (isset($this->filters['criteria_score']) && isset($this->filters['criteria_score']['max']) && is_int($this->filters['criteria_score']['max']) && ($this->filters['criteria_score']['max'] > 0)) {
                    if ($criteria['score'] >= $this->filters['criteria_score']['max'] && $this->filters['criteria_score']['max'] < 100)
                        $show_line = false;
                }
                if ($show_line) {
                    array_push($data, $line);
                }
            }
        }
        if ($this->isGraph) {
            $xAxis = [];
            if ($this->params['x']) {
                foreach ($this->key_x as $k => $x) {
                    $xAxis[] = $this->checkJson($x['name']);
                }
                $this->xAxis[] = [
                    "categories" => $xAxis
                ];
            } else {
                $this->xAxis[] = [
                    "categories" => ['']
                ];
            }
        }
        return $data;
    }


    public function showAnswerRow()
    {
        $data = [];
        $col = [];
        //check if filter on question is set

        if (count($this->filters['question_level']) === 0) {
            return false;
        }
        foreach ($this->key_y as $k => $v) {
            $this->uigrid_index_key = 0;
            $line = [];
            $series = [];
            $xAxis = [];
            $save_filter = $this->filters['answer'];
            $this->filters['answer'] = [$v['id']];
            //lecture du score pour la colonne global
            $score = $this->getGlobalFromWaveTargets();
            //lecture du score pour toutes les autres colonnes (group by $this->params['x'])
            if ($this->params['x'])
                $col = $this->getScoreFromWaveTargets($this->params['x']);
            if ($this->isGraph) {
                if ($this->params['x']) {
                    foreach ($col as $c) {
                        $series[] = $c['score'];
                    }
                    $this->series[] = [
                        "data" => $series,
                        "name" => $this->checkJson($v['name'])
                    ];
                } else {
                    $this->series[] = [
                        "data" => [$score['score'] ? round($score['score'], 0) : ""],
                        "name" => $this->checkJson($v['name'])
                    ];
                }
            } else {
                $line = $this->addLine($this->params['y'], null, $v['id'], $v['name'], null, $score['quantity'], $score['score'], null, $col);
            }

            if ($line)
                array_push($data, $line);
            $this->filters['answer'] = $save_filter;
        }
        if ($this->isGraph) {
            $xAxis = [];
            if ($this->params['x']) {
                foreach ($this->key_x as $k => $x) {
                    $xAxis[] = $this->checkJson($x['name']);
                }
                $this->xAxis[] = [
                    "categories" => $xAxis
                ];
            } else {
                $this->xAxis[] = [
                    "categories" => ['']
                ];
            }
        }

        return $data;
    }

    public function showQuestionRow()
    {
        $data = [];
        $col = [];
        //check if filter on question is set

        if (count($this->filters['answer']) === 0) {
            return false;
        }

        foreach ($this->filters['answer'] as $k => $v) {
            $this->uigrid_index_key = 0;
            $line = [];
            $series = [];
            $xAxis = [];
            $save_filter = $this->filters['answer'];
            $this->filters['answer'] = [$v['id']];
            //lecture du score pour la colonne global
            $score = $this->getGlobalFromWaveTargets();
            //lecture du score pour toutes les autres colonnes (group by $this->params['x'])
            if ($this->params['x'])
                $col = $this->getScoreFromWaveTargets($this->params['x']);
            if ($this->isGraph) {
                if ($this->params['x']) {
                    foreach ($col as $c) {
                        $series[] = $c['score'];
                    }
                    $this->series[] = [
                        "data" => $series,
                        "name" => $this->checkJson($v['name'])
                    ];
                } else {
                    $this->series[] = [
                        "data" => [$score['score'] ? round($score['score'], 0) : ""],
                        "name" => $this->checkJson($v['name'])
                    ];
                }
            } else {
                $line = $this->addLine($this->params['y'], null, $v['id'], $v['name'], null, $score['quantity'], $score['score'], null, $col);
            }

            if ($line)
                array_push($data, $line);
            $this->filters['answer'] = $save_filter;
        }
        if ($this->isGraph) {
            $xAxis = [];
            if ($this->params['x']) {
                foreach ($this->key_x as $k => $x) {
                    $xAxis[] = $this->checkJson($x['name']);
                }
                $this->xAxis[] = [
                    "categories" => $xAxis
                ];
            } else {
                $this->xAxis[] = [
                    "categories" => ['']
                ];
            }
        }

        return $data;
    }

    public function showConnectionData($y = null)
    {
        $log = LogModel::selectRaw('date, COUNT(distinct id) as quantity, COUNT(distinct id) as score');
        $log = $log->where('action', 'login');
        $log = $log->where('date', '>', $this->from);
        if ($y)
            $log = $log->where('user_id', $y['id']);
        if (isset($this->filters['users']))
            $log = $log->wherein('user_id', $this->filters['users_id']);
        $log = $log->where('date', '<', $this->to);
        $log = $log->groupBy('date')->orderBy('date')->get()->toArray();
        $col = [];
        foreach ($log as $l) {
            if ($this->params['x'] === 'day') {
                $col[$l['date']] = $l;
            }
            if ($this->params['x'] === 'week') {
                $date_week = [];
                foreach ($log as $r) {
                    $date = Carbon::parse($r['date']);
                    $date = $date->weekOfYear;
                    $date_week[$date][] = $r;
                }
                foreach ($date_week as $k => $value) {
                    $quantity = 0;
                    foreach ($value as $v) {
                        $quantity = $quantity + $v['quantity'];
                    }
                    $col[$k] = [
                        "score" => $quantity,
                        "quantity" => $quantity
                    ];
                }
            }
            if ($this->params['x'] === 'month') {
                $date_week = [];
                foreach ($log as $r) {
                    $date = Carbon::parse($r['date']);
                    $date = $date->month;
                    $date_week[$date][] = $r;
                }
                foreach ($date_week as $k => $value) {
                    $quantity = 0;
                    foreach ($value as $v) {
                        $quantity = $quantity + $v['quantity'];
                    }
                    $col[$k] = [
                        "score" => $quantity,
                        "quantity" => $quantity
                    ];
                }
            }
        }
        return $col;
    }

    public function showConnection()
    {
        $data = [];
        $col = [];
        $this->uigrid_index_key = 0;
        $line = [];
        $series = [];
        $xAxis = [];
        if (!$this->key_y) {
            $col = $this->showConnectionData();
            if ($this->isGraph) {
                foreach ($col as $c) {
                    $series[] = $c['quantity'];
                }
                $this->series[] = [
                    "data" => $series,
                    "name" => "Nombre de connexions"
                ];
            } else {
                $line = $this->addLine($this->params['y'], null, null, "Connections", "number", null, null, null, $col);
                if ($line)
                    array_push($data, $line);
            }
        } else {
            foreach ($this->key_y as $y) {
                $col = $this->showConnectionData($y);
                $series = [];
                if ($this->isGraph) {
                    foreach ($col as $c) {
                        $series[] = $c['quantity'];
                    }
                    if (array_sum($series) > 0) {
                        $this->series[] = [
                            "data" => $series,
                            "name" => $y['name']
                        ];
                    }
                } else {
                    $line = $this->addLine($this->params['y'], null, null, $y['name'], "number", null, null, null, $col);
                    if ($line)
                        array_push($data, $line);
                }
            }
        }


        if ($this->isGraph) {
            $xAxis = [];
            if ($this->params['x']) {
                foreach ($this->key_x as $k => $x) {
                    $xAxis[] = $this->checkJson($x['name']);
                }
                $this->xAxis[] = [
                    "categories" => $xAxis
                ];
            } else {
                $this->xAxis[] = [
                    "categories" => ['']
                ];
            }
        }
        return $data;
    }

    public function goalGraph()
    {
        $goal = null;
        if (!$this->hide_goals)
            $goal = $this->getGoal();
        if ($goal) {
            $goal = [
                'name' => 'Objectif',
                'value' => $goal,
                'strokeWidth' => 5,
                'strokeColor' => '#004444'
            ];
        }
        return $goal;
    }

    public function showOther()
    {
        $data = [];
        $col = [];
        //add base

        foreach ($this->key_y as $k => $v) {
            //checkif axe tag
            $save_filter = $this->filters;
            if ($this->params['y'] == 'group') {
                if ($v['type'] == 'axe') {
                    $all_shops = $this->GetShopFromAxe([$v['id']]);
                    if (isset($this->filters['current_shop'])) {
                        $all_shops = array_intersect($this->filters['current_shop'], $all_shops);
                    }
                } else {
                    $val = AxeDirectory::relations()->where('axe_directory.hide_to_client', false)
                        ->where('axe_directory.id', $v['id'])->retrieveAll()
                        ->toArray();
                    $val = $this->AxeGetChild($val);
                    $all_shops = $this->GetShopFromAxe($val);
                    //get all shops link with same groupline
                    if (isset($this->filters['current_shop'])) {
                        $all_shops = array_intersect($this->filters['current_shop'], $all_shops);
                    }
                }
                $this->filters['shop'] = $all_shops;
            } else {
                //fix for program filter
                if ($this->params['y'] === 'program')
                    $this->filters[$this->params['y']] = ["id" => $v['id']];
                else
                    $this->filters[$this->params['y']] = [$v['id']];
            }
            $this->filter_y = $this->params['y'];

            $this->uigrid_index_key = 0;
            $line = [];
            $series = [];
            $this->series_c = [];
            $xAxis = [];

            //lecture du score pour la colonne global
            $score = $this->getGlobalFromWaveTargets();

            //lecture du score pour toutes les autres colonnes (group by $this->params['x'])
            if ($this->params['x'])
                $col = $this->getScoreFromWaveTargets($this->params['x']);
            if ($this->isGraph) {
                if ($this->params['x']) {
                    foreach ($this->key_x as $key_x) {
                        if (isset($this->filters[$this->params['x']])) {
                            $save_filter2 = $this->filters[$this->params['x']];
                            $this->filters[$this->params['x']] = $key_x;
                        }
                        $goal = $this->goalGraph();
                        if (isset($this->filters[$this->params['x']])) {
                            $this->filters[$this->params['x']] = $save_filter2;
                        }

                        $found = false;
                        if ($col) {
                            foreach ($col as $k => $c) {
                                if (is_int($k)) {
                                    if (isset($c['id']) && $key_x['id'] === $c['id']) {
                                        $series[] = [
                                            'x' => $this->checkJson($key_x['name']),
                                            'y' => $c['score'] ? round($c['score'], 0) : null,
                                            'base' => isset($c['quantity']) ? ' (' . $c['quantity'] . ')' : '',
                                            'info' => isset($c['quantity']) ? ' (' . $c['quantity'] . ')' : '',
                                            'goals' => $goal ? [$goal] : null
                                        ];
                                        $found = true;
                                    }
                                } else {
                                    foreach ($c as $r) {
                                        if (isset($r['id']) && $key_x['id'] === $r['id']) {
                                            $this->series_c[$k][] =  [
                                                'x' => $this->checkJson($r['name']),
                                                'y' => $r['score'] ? round($r['score'], 0) : null,
                                                'base' => isset($r['quantity']) ? ' (' . $r['quantity'] . ')' : '',
                                                'info' => isset($r['quantity']) ? ' (' . $r['quantity'] . ')' : '',
                                                'goals' => $goal ? [$goal] : null
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        if ($found === false) {
                            $series[] = ['x' => $this->checkJson($key_x['name']), 'y' => null];
                        }
                    }
                    $this->series[] = [
                        "data" => $series,
                        "name" => $this->checkJson($v['name']),
                        "type" => 'column'
                    ];

                    $this->series_basic = $this->series;
                } else {
                    $this->series_basic = [1];
                    $this->series[] = [
                        "data" => [$score['score'] ? intval($score['score']) : ""],
                        "name" => $this->checkJson($v['name'])
                    ];
                }
            } else {
                $line = $this->addLine($this->params['y'], null, $v['id'], $v['name'], null, $score['quantity'], $score['score'], null, $col);
            }
            //pour chaque colonne on affiches le score sinon null
            $show_subcriteria = false;
            if ($this->params['y'] === 'sequence' && ($this->params['x'] === 'wave' || $this->params['x'] === 'group' || $this->params['x'] === 'shop'))
                $show_subcriteria = true;
            if (($this->params['y'] === 'theme' || $this->params['y'] === 'job' || $this->params['y'] === 'criteria_a' || $this->params['y'] === 'criteria_b') && ($this->params['x'] === 'wave' || $this->params['x'] === 'group' || $this->params['x'] === 'shop' || $this->params['x'] === 'wave_target'))
                $show_subcriteria = true;
            if ($show_subcriteria && $line) {
                $line['data'] = $this->subCriteria($this->params['y'], $v['id']);
            }

            //add objectifs
            if ($this->params['x'] === 'theme' && $this->params['y'] === 'program' && $line) {
                $line['data'] = $this->subGoal($v['id']);
            }

            if ($line)
                array_push($data, $line);
            $this->filters = $save_filter;
        }
        if ($this->isGraph) {
            foreach ($this->series_c as $k => $s) {
                $this->compare_chart = true;
                $this->series[] = [
                    "data" => $s,
                    "name" => $k,
                    "type" => 'line'
                ];
            }
            $xAxis = [];
            if ($this->params['x']) {
                foreach ($this->key_x as $k => $x) {
                    $xAxis[] = $this->checkJson($x['name']);
                }
                $this->xAxis[] = [
                    "categories" => $xAxis
                ];
            } else {
                $this->xAxis[] = [
                    "categories" => ['']
                ];
            }
        }

        return $data;
    }


    public function _getShopsFromFilters()
    {
        //get data for axe as filter
        //be carreful, must filter on axe already set (no adding)
        $shops = $all_shop = [];
        foreach ($this->filters['general'] as $key => $value) {
            $all_axes = [];
            $axe_as_filter = [];
            if (strpos($key, 'axes_') !== false && is_array($value) && count($value) > 0) {
                foreach ($value as $v) {
                    //remove axe not present in axe as filter,
                    if (isset($value['id']))
                        $axe_as_filter[] = $value['id'];
                    else
                        $axe_as_filter[] = $v;
                }
                //flat all axes
                if (isset($axe_as_filter) && $axe_as_filter) {
                    if (isset($axe_as_filter[0]['id'])) {
                        array_push($all_axes, $this->AxeGetChild($axe_as_filter));
                    } else foreach ($axe_as_filter as $a) {
                        array_push($all_axes, $this->AxeGetChild($a));
                    }
                    $all_axes = array_flatten($all_axes);
                }
                //get all shops from axe
                $shops[$key] = $this->GetShopFromAxe($all_axes);
            }
        }
        $all_axes = [];
        //clean data if compare_to_axe -> "-"
        if (isset($this->filters['general']['compare_to_axe']) && count($this->filters['general']['compare_to_axe']) > 0) {
            if (isset($this->filters['general']['compare_to_axe']['name']) && $this->filters['general']['compare_to_axe']['name'] === "-") if (!is_int($this->filters['general']['compare_to_axe'])) unset($this->filters['general']['compare_to_axe']);
        }
        //clean data if compare_to_shop -> "-"
        if (isset($this->filters['general']['compare_to_shop']) && count($this->filters['general']['compare_to_shop']) > 0) {
            if (isset($this->filters['general']['compare_to_shop']['name']) && $this->filters['general']['compare_to_shop']['name'] === "-") if (!is_int($this->filters['general']['compare_to_shop'])) unset($this->filters['general']['compare_to_shop']);
        }

        if (isset($this->filters['general']['axes']) && $this->filters['general']['axes']) {
            if (isset($this->filters['general']['axes'][0]['id'])) {
                array_push($all_axes, $this->AxeGetChild($this->filters['general']['axes']));
            } else foreach ($this->filters['general']['axes'] as $a) {
                array_push($all_axes, $this->AxeGetChild($a));
            }
            //get all shops from axe
            $shops['axes'] = $this->GetShopFromAxe($all_axes);
        }
        if (isset($this->filters['general']['shop']) && $this->filters['general']['shop']) {
            $shops['shop'] = ArrayHelper::getIds($this->filters['general']['shop']);
        }

        if (isset($this->filters['general']['axesDirectory']) && count($this->filters['general']['axesDirectory'])) {
            $ids = AxeDirectory::getAxesIds($this->filters['general']['axesDirectory']);
            $shops['axesDirectory'] = $this->GetShopFromAxe($ids);
        }

        //retrieve common ids only
        $i = 0;
        $intersect_result_id = [];
        foreach ($shops as $res) {
            if ($i > 0) {
                $intersect_result_id = array_intersect($res, $intersect_result_id);
            } else {
                $intersect_result_id = $res;
            }
            $i++;
        }
        $this->filters['shop'] = $intersect_result_id;
        $all_shop = GraphTemplateService::getRestrictedshop($this->user, $this->filters);

        return array_flatten($all_shop);
    }

    public function _GetAllQuestionsasfilter()
    {
        //get data for question as filter
        //be carreful, must filter on axe already set (no adding)
        $all_questions = [];
        foreach ($this->filters['general'] as $key => $value) {
            if (strpos($key, 'questions_') !== false) {
                if (is_array($value) && count($value) > 0) {
                    $q_id = str_replace('questions_', "", $key);
                    foreach ($value as $v) {
                        //remove axe not present in axe as filter,
                        if (isset($v['id']))
                            $all_questions[$q_id][] = $v['id'];
                        else
                            $all_questions[$q_id][] = $v;
                    }
                } else {
                    if ($value) {
                        $q_id = str_replace('questions_', "", $key);
                        $all_questions[$q_id][] = 'v-' . $value; //add prefix v to set as value (string, search on input question)
                    }
                }
            }
        }
        return $all_questions;
    }





    public function _uigrid($name, $colType = null)
    {
        $this->uigrid_index++;

        return ['name' => $name, 'field' => 'col' . $this->uigrid_index, 'colType' => $colType];
        // 'type' => $type, only needed if colType === object
    }

    private function getWaveTargetByAnswer($question_row_id)
    {

        $cachekey = CacheHelper::SetCacheKey('getWaveTargetByAnswer', $this->user, [$this->filters, $question_row_id]);
        $result = Cache::get($cachekey . $this->disable_cache, function () use ($cachekey, $question_row_id) {
            $result = \DB::table('show_scoring_multi_with_text_' . $this
                ->user
                ->current_society_id)
                ->select('wave_target_id as id')
                ->where('question_row_id', $question_row_id)
                ->groupBy('wave_target_id');
            GraphFilterController::_addFilters($result, $this->filters);
            $result = $result->get();
            Cache::put($cachekey, $result, $this->ttl_cache);
            return $result;
        });

        return $result;
    }

    private function _getKeyTag($type)
    {

        //read all axe & anx_directory link to shops for current selection
        //get all axe for this shop
        $cachekey = CacheHelper::SetCacheKey('_getKeyTag', $this->user, [$this->filters, $type]);
        $arr = Cache::get($cachekey . $this->disable_cache, function () use ($cachekey, $type) {
            $arr = [];
            $shop_id = \DB::table('show_scoring_' . $this->user->current_society_id)
                ->select('shop_id')
                ->groupBy('shop_id');
            GraphFilterController::_addFilters($shop_id, $this->filters);
            $shop_id = $shop_id->get();
            $axes_in_shop = DB::table('shop_axe')->select('axe_id')->wherein('shop_id', $shop_id)->get();
            $this->all_axe = array_map(function ($value) {
                return $value['axe_id'];
            }, $axes_in_shop);
            $axes = Axe::select('id', 'axe_directory_id')->wherein('id', $axes_in_shop)->where('society_id', $this->user->current_society_id)->get();
            foreach ($axes as $a) {
                if ($a->axe_directory_id) {
                    $this->all_axe_directory[] = $this->showDirectoriesParent($a->axe_directory_id);
                }
            }
            $this->all_axe_directory = array_unique($this->all_axe_directory);
            $this->all_axe = array_unique($this->all_axe);
            $AxeTagItems = AxeTagItem::where('axe_tag_id', $this->filters[$type]['id'])->get();
            foreach ($AxeTagItems as $a) {
                if ($a['axe_tag_item_type'] === 'App\Models\Axe') {
                    if (in_array($a['axe_tag_item_id'], $this->all_axe)) {
                        $name = Axe::find($a['axe_tag_item_id']);
                        $arr[] = [
                            "id" => $a['axe_tag_item_id'],
                            "name" => $name->name,
                            "type" => "axe"
                        ];
                    }
                } else {
                    if (in_array($a['axe_tag_item_id'], $this->all_axe_directory)) {
                        $name = AxeDirectory::find($a['axe_tag_item_id']);
                        $arr[] = [
                            "id" => $a['axe_tag_item_id'],
                            "name" => $name->name,
                            "type" => "directory"
                        ];
                    }
                }
            }

            if (($this->filters['flop_5'] || $this->filters['top_5'])) {
                foreach ($arr as &$r) {
                    if ($r['type'] === 'axe') {
                        $all_shops = $this->GetShopFromAxe([$r['id']]);
                    } else {
                        $val = $this->AxeGetChild($r['id']);
                        $all_shops = $this->GetShopFromAxe($val);
                    }
                    $result = $this->show_scoring_multi('first', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, null, null, null, null, null, $all_shops);
                    $r['score'] = $result['score'];
                }
                if ($this->filters['flop_5']) {
                    $columns = array_column($arr, 'score');
                    array_multisort($columns, SORT_ASC, $arr);
                    $arr = array_slice($arr, 0, 5);
                }
                if ($this->filters['top_5']) {
                    $columns = array_column($arr, 'score');
                    array_multisort($columns, SORT_DESC, $arr);
                    $arr = array_slice($arr, 0, 5);
                }
            }
            Cache::put($cachekey, $arr, $this->ttl_cache);
            return $arr;
        });
        return $arr;
    }

    private function _getKeyUser()
    {
        //get all user from current selection
        $result = [];

        foreach ($this->filters['users'] as $u) {
            $result[] = ["id" => $u->id, "name" => $u->email];
        }

        return $result;
    }

    private function _getKeyWave()
    {
        return $this->filters['cumulative'];
    }

    private function _getKeyAnswer()
    {
        //get all answers
        $result = \DB::table('show_criteria_' . $this->user->current_society_id)
            ->selectRaw('
            question_row_name as id,
            question_row_name as name
            ')
            ->where('is_visible_on_split_by_question', true)
            ->whereNotNull('question_row_name')
            ->groupBy('question_row_name');
        $result = GraphFilterController::_addFilters($result, $this->filters);
        $result = $result->get();
        return $result;
    }

    private function _getKeyQuestion()
    {
        //get all answers
        $question_level = ArrayHelper::getIds($this->filters['question_level']);
        $result = \DB::table('show_criteria_' . $this->user->current_society_id)
            ->selectRaw('
            question_row_id as id,
            question_row_name as name
            ')
            ->whereNotNull('question_row_name');
        if ($question_level)
            $result = $result->wherein('question_id', $question_level);
        $result = $result->groupBy('question_row_id', 'question_row_name');
        $result = GraphFilterController::_addFilters($result, $this->filters);
        $result = $result->get();
        return $result;
    }

    private function _getKeyPeriod()
    {
        //get waves
        $result = $date_by_week = $date_by_month = [];
        foreach ($this->filters['wave'] as $w) {
            if (!$this->from) {
                $this->from = $w['date_start'];
                $this->to = $w['date_end'];
            }
            if ($this->from && $w['date_start'] < $this->from)
                $this->from = $w['date_start'];
            if ($this->to && $w['date_end'] > $this->to)
                $this->to = $w['date_end'];
        }
        $period = CarbonPeriod::create($this->from, $this->to);
        // Iterate over the period
        if ($this->params['x'] === 'day') {
            foreach ($period as $d) {
                $result[] = ["id" => $d->format('Y-m-d'), "name" => $d->format('Y-m-d')];
            }
        }
        if ($this->params['x'] === 'week') {
            foreach ($period as $d) {
                $date = $d->weekOfYear;
                $date_by_week[$date] = $date;
            }
            foreach ($date_by_week as $d) {
                $result[] = ["id" => $d, "name" => $d];
            }
        }
        if ($this->params['x'] === 'month') {
            foreach ($period as $d) {
                $date = $d->month;
                $date_by_month[$date] = $date;
            }
            foreach ($date_by_month as $d) {
                $result[] = ["id" => $d, "name" => $d];
            }
        }
        // Convert the period to an array of dates
        return $result;
    }

    private function _getKey($key, $axe)
    {
        $save_bonus = $this->bonus;
        if (($this->filters['flop_5'] || $this->filters['top_5']) && $axe === 'y') {
            $table = 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id;
        } else {
            $table = 'show_scoring_multi_' . $this->bonus . 'with_text_' . $this->user->current_society_id;
            $this->bonus = "";
        }

        if ($key === 'wave_target') {
            $results = $this->show_scoring_multi('get', $table, null, null, $key, $axe, true);
        } else if ($key === 'program_g') {
            //get all program first level
            $results =  \DB::table('program')
                ->whereNull('parent_id')->whereNull('deleted_at')->where('society_id', $this->user->current_society_id)->get();
        } else {
            $results = $this->show_scoring_multi('get', $table, null, null, $key, $axe);
        }
        $this->bonus = $save_bonus;
        if ($key === "society" && $results) { //remplace society name by Score Globale
            $results[0]['name'] = 'Score Global';
        }
        if ($this->filters['flop_5'] && $axe === 'y') {
            $i = 0;
            $columns = array_column($results, 'score');
            array_multisort($columns, SORT_ASC, $results);
            $result_data_flop = [];
            foreach ($results as $r) {
                if ($r['score'] !== null) {
                    $result_data_flop[] = $r;
                    $i++;
                }
                if ($i > 4)
                    break;
            }
            $results = $result_data_flop;
        }

        if ($this->filters['top_5'] && $axe === 'y') {
            $columns = array_column($results, 'score');
            array_multisort($columns, SORT_DESC, $results);
            $results = array_slice($results, 0, 5);
        }
        if ($key === 'theme') {
            //change order
            $all_theme = theme::where('society_id', $this
                ->user
                ->current_society_id)
                ->orderBy('order')
                ->get()
                ->toarray();
            $result_data = array();
            foreach ($all_theme as $r) {
                $data = ['id' => $r];
                $base_key = array_search($r['id'], array_column($results, 'id'));
                if ($base_key !== false) {
                    array_push($result_data, $results[$base_key]);
                }
            }
            $results = $result_data;
        }
        if ($key === 'question' && $this->filters['question_level']['id'] > 0) { //si filtre on filtre sur une question
            $results =  \DB::table('question_row')
                ->select('id', 'name')->where('question_id', $this->filters['question_level']['id'])->get();
        }
        if ($this->sort_result && $key != 'wave') {
            $columns = array_column($results, 'score');
            array_multisort($columns, SORT_DESC, $results);
        }

        return $results;
    }

    function AddSequenceLineScore($allSequence, $current_seq)
    {
        $data = [];
        $this->filter_y = 'sequence';
        $name = sequence::find($current_seq);
        $name = $this->checkJson($name['name']);
        $save_filter = $this->filters[$this->params['y']];

        //lecture du score pour la colonne global
        $this->filters[$this->params['y']] = $allSequence;
        if ($this->request_type !== 'referencial')
            $score = $this->getGlobalSequenceFromWaveTargets($allSequence);
        //$this->filters[$this->params['y']] = [$current_seq];

        //lecture du score pour toutes les autres colonnes (group by $this->params['x'])
        if ($this->request_type !== 'referencial')
            $col = $this->getScoreFromWaveTargets($this->params['x']);

        if (!isset($this->floptop) || (isset($this->floptop) && (array_search($current_seq, array_column($this->floptop, 'id')) !== false))) {
            if ($this->request_type === 'referencial') {
                $line = $this->addLine($this->params['y'], null, $current_seq, $name, null, null, null, null);
            } else {
                $line = $this->addLine($this->params['y'], null, $current_seq, $name, null, $score['quantity'], $score['score'], null, $col);
            }
            //pour chaque colonne on affiches le score sinon null
            if ($line)
                array_push($data, $line);
            $this->filters[$this->params['y']] = $save_filter;
            return $line;
        }
        return NULL;
    }

    function GetShopFromAxe($axes)
    {
        $axes = array_flatten($axes);

        $cachekey = CacheHelper::SetCacheKey('GetShopFromAxe_', $this->user, [$axes]);
        $shops_id = Cache::get($cachekey . $this->disable_cache, function () use ($cachekey, $axes) {
            $shops = DB::table('shop_axe')->select('shop_id')
                ->wherein('axe_id', $axes)->get();
            $shops_id = array_map(
                function ($value) {
                    return $value['shop_id'];
                },
                $shops
            );
            Cache::put($cachekey, $shops_id, $this->ttl_cache);
            return $shops_id;
        });

        return $shops_id;
    }

    function AddGroupLineScore($formatGroup, $current_group, $type = "axeDirectory")
    {
        $data = [];
        $save_filter = $this->filters;
        if ($type == "program") {
            $this->params['y'] = "program";
        }
        $line = [];
        if ($type == "axeDirectory") {
            $name = axeDirectory::find($current_group);
        } else if ($type == "program") {
            $name = Program::find($current_group);
        } else {
            $name = axe::find($current_group);
        }
        $name = $this->checkJson($name['name']);
        if ($type === 'program') {
            $this->filters['program'] = $formatGroup;
            $score = $this->getGlobalAxeFromWaveTargets();
        } else {
            $all_shops = $this->GetShopFromAxe($formatGroup);
            $this->filters['current_shop'] = $all_shops;
            $score = $this->getGlobalAxeFromWaveTargets($all_shops);
        }



        //lecture du score pour toutes les autres colonnes (group by $this->params['x'])
        $col = $this->getScoreFromWaveTargets($this->params['x']);

        $line = $this->addLine('axes', null, $current_group, $name, null, $score['quantity'], $score['score'], null, $col);
        //pour chaque colonne on affiches le score sinon null
        if ($line)
            array_push($data, $line);
        $this->filters = $save_filter;
        return $line;
    }

    private static function _traverse($collection, &$array, $object)
    {
        $new_array = array();
        foreach ($collection as $element) {
            self::_traverse($element->Children, $new_array, $element);
        }
        $array[] = $object;
        if (count($new_array) > 0) {
            $array[] = $new_array;
        }
    }

    function FormatGroup($val, $type = "axeDirectory")
    {
        $allchild = [];
        if ($this->params['y'] == 'program_g' || $this->params['y'] == 'program')
            $type  = "program";
        if (!empty($val)) {
            if ($val['children']) {
                $allchild = $this->AxeGetChild($val['children']);
            } else if ($type == 'axeDirectory') {
                $allchild = $this->AxeGetChild($val['axes']);
            }
            $allchild = array_flatten($allchild);
            $col = $this->AddGroupLineScore($allchild, $val['id'], $type);
            if ($val['children']) {
                $col['data'] = $this->SubFormatGroup($val['children']);
            } else if ($type == 'axeDirectory') {
                $col['data'] = $this->SubFormatGroup($val['axes']);
            }

            $this->line = $col;
        }
        return $this->line;
    }

    function subGoal($model_id)
    {
        //goal
        $subline = $col = [];
        $goal = null;
        foreach ($this->key_x as $k => $x) {
            $savefilter = $this->filters['theme'];
            $this->filters['theme'] = ['id' => $x['id']];
            $goal = $this->getGoal();
            $col[$x['id']] = $goal;
            $this->filters['theme'] = $savefilter;
        }
        $l = $this->addLine('criteria', $model_id, $model_id, "Objectif", null, null, null, null, $col);
        array_push($subline, $l);
        return $subline;
    }

    function subCriteria($model_source, $model_id)
    {

        //set x & y for subcriteria
        $this->subCriteriaModelSource = $model_source;
        $this->subCriteriaModelId = $model_id;
        $save_score_method = $this->score_method;
        $this->score_method = 'question_';
        $save_score_bonus = $this->bonus;
        $this->bonus = 'without_bonus_';
        //parcour la liste des critères pour cette séquence
        $criteria_question = $this->getCriteriaQuestion();
        $criteria_score = $this->getCriteriaScoring();
        $subline = [];
        foreach ($criteria_score as $criteria) {
            if (!isset($criteria['id']))
                return false;
            if (!isset($criteria['type'])) { //type de question : text / select / radio
                $criteria['type'] = null;
            }
            if (!isset($criteria['progress']))
                $criteria['progress'] = null;
            if (!isset($criteria['col']))
                $criteria['col'] = [];
            //check if criteria is link to one or more question
            $q = $sub_question = [];
            foreach ($criteria_question as $question) { //Parcours la liste des résultats
                if ($question['criteria_id'] == $criteria['id']) { //Lecture des questions liés au critère en cours
                    $q = $this->addLine('question', $question['question_id'],  $question['question_id'], $question['question_name'], $question['type'], null, $question['score'], null, $question['col']); //préparation du sous niveau question
                    if ($q)
                        array_push($sub_question, $q);
                }
            }
            $q_id = null;
            if (count($sub_question) < 2) { //le critère est lié à une seule question on ajoute le lien pour afficher directement le detail de la question en cliquant sur le critère
                $q_id = $q["col0"]['question_id'];
            }
            $l = $this->addLine('criteria', $q_id, $criteria['id'], $criteria['name'], $criteria['type'], $criteria['count'], $criteria['score'], $criteria['progress'], $criteria['col']);
            if (count($sub_question) > 1) {
                if ($l)
                    $l['data'] = $sub_question;
            }
            if ($l)
                array_push($subline, $l);
        }
        $this->subCriteriaModelSource = null;
        $this->subCriteriaModelId = null;
        $this->score_method = $save_score_method;
        $this->bonus = $save_score_bonus;
        return $subline;
    }



    function FormatSequence($subSequences)
    {
        $this->line = [];
        if (!empty($subSequences)) {
            foreach ($subSequences as $val) {
                $data = null;
                if ($val['children']) {
                    $allchild = $this->GetChild($subSequences);
                    $allchild = array_flatten($allchild);
                    $col = $this->AddSequenceLineScore($allchild, $val['item_id']);
                    $data =  $this->SubFormatSequence($val['children']);
                    if ($data)
                        $col['data'] = $data;
                    $this->line = $col;
                } else {
                    //get all seq
                    $this->line = $this->AddSequenceLineScore([$val['item_id']], $val['item_id']);
                    //get critera
                    $save_filter = $this->filters;
                    $this->filters['sequence'] = [$val['item_id']];
                    //if ($this->params['x'] !== 'question_row_name') {
                    //    $r = $this->subCriteria('sequence', [$val['item_id']]);
                    if ($this->request_type === 'referencial') {
                        $data = $this->line['data'] = $this->getSequenceCriteriasFromReferencial([$val['id']]);
                        if ($data)
                            $col['data'] = $data;
                    } else {
                        $this->line['data'] = [];
                    }
                    //}
                    $this->filters = $save_filter;
                }
            }
        }
        return $this->line;
    }

    function FormatSequenceCriteria($subSequences, $survey_item_id)
    {
        if (!empty($subSequences)) {
            foreach ($subSequences as $val) {
                if (isset($val['children'])) {
                    $allchild = $this->GetChild($subSequences);
                    $allchild = array_flatten($allchild);
                    $allchild = array_unique($allchild);
                    //get all subseq
                    foreach ($allchild as $c) {
                        $list = SurveyItem::where('survey_id', $this->filters['survey'])->where('item_id', $c)->where('type', 'sequence')
                            ->orderBy('order')
                            ->retrieveAll()
                            ->toArray();
                        $this->FormatSequenceCriteria($list, null);
                    }
                } else {
                    $this->FormatSequenceCriteria(null, $val['id']);
                }
            }
        } else {
            //Main level with no children
            //get all criteria
            $list = SurveyItem::select('criteria_id')->where('survey_id', $this->filters['survey'])->where('parent_id', $survey_item_id)->orderBy('order')
                ->retrieveAll()
                ->toArray();
            foreach ($list as $l) {
                array_push($this->criteria_order, $l['criteria_id']);
            }
        }
    }

    // Recursive Function( Build child )
    function SubFormatSequence($arr)
    {
        $line = [];
        if (!empty($arr)) {
            foreach ($arr as $val) {
                $col = [];
                $data = null;
                if ($val['children']) {
                    $allchild = $this->GetChild($arr);
                    $allchild = array_flatten($allchild);
                    $col = $this->AddSequenceLineScore($allchild, $val['item_id']);
                    $data = $this->SubFormatSequence($val['children']);
                    if ($data)
                        $col['data'] = $data;
                    if ($col)
                        $line[] = $col;
                } else {
                    $col = $this->AddSequenceLineScore([$val['item_id']], $val['item_id']);
                    $save_filter = $this->filters;
                    $this->filters['sequence'] = [$val['item_id']];
                    if ($this->request_type === 'referencial') {
                        $data = $this->line['data'] = $this->getSequenceCriteriasFromReferencial([$val['id']]);
                        if ($data) {
                            $col['data'] = $data;
                        }
                    } else {
                        $data = $this->line['data'] = [];
                        $col['data'] = $data;
                    }
                    $this->filters = $save_filter;
                    if ($col)
                        $line[] = $col;
                }
            }
            return $line;
        }
    }

    function getSequenceCriterias($survey_item_id)
    {
        $result = SurveyItem::with('criteria')->where('parent_id', $survey_item_id)->where('type', 'question')->orderBy('order')->get()->toArray();
        $final = [];

        foreach ($result as $item) {
            if (in_array($item['item_id'], $this->filters['questions']))
                array_push($final, [
                    'col0' =>
                    [
                        'value' => $item['criteria']['name'][$this->user->language->code],
                        'id' => $item['criteria']['id']
                    ]
                ]);
        }
        return $final;
    }

    function getSequenceCriteriasFromreferencial($survey_item_id)
    {
        $result = SurveyItem::with('criteria', 'question')->where('parent_id', $survey_item_id)->where('type', 'question')->orderBy('order')->get()->toArray();
        $final = [];

        foreach ($result as $item) {
            if (in_array($item['item_id'], $this->filters['questions']))
                array_push($final, [
                    'col0' =>
                    [
                        'value' => $item['criteria']['name'][$this->user->language->code],
                        'id' => $item['criteria']['id']
                    ],
                    'col1' =>
                    [
                        'value' => $item['question']['info'][$this->user->language->code],
                        'id' => $item['criteria']['id']
                    ]
                ]);
        }
        return $final;
    }

    function SubFormatGroup($arr)
    {
        if (!empty($arr)) {
            foreach ($arr as $val) {
                if ($this->params['y'] == 'program') {
                    $line[] = $this->AddGroupLineScore([$val['id']], $val['id'], "program");
                } else {
                    if ((isset($val['children']) && $val['children']) || (isset($val['axes']) && $val['axes'])) {
                        if (in_array($val['id'], $this->all_axe_directory)) {
                            if (isset($val['children']) && $val['children']) {
                                $allchild = $this->AxeGetChild($val['children']);
                                $allchild = array_flatten($allchild);
                                $col = $this->AddGroupLineScore($allchild, $val['id'], "axeDirectory");
                                $col['data'] = $this->SubFormatGroup($val['children']);
                                $line[] = $col;
                            } else if (isset($val['axes']) && $val['axes']) {
                                $allchild = $this->AxeGetChild($val['axes']);
                                $allchild = array_flatten($allchild);
                                $col = $this->AddGroupLineScore($allchild, $val['id'], "axeDirectory");
                                $col['data'] = $this->SubFormatGroup($val['axes']);
                                $line[] = $col;
                            }
                        }
                    } else {
                        if (in_array($val['id'], $this->all_axe)) {
                            $line[] = $this->AddGroupLineScore([$val['id']], $val['id'], "axe");
                        }
                    }
                }
            }
            if (isset($line)) return $line;
        }
    }

    public function getAlias($name)
    {
        $name = strtolower($name);
        # Get alias ex: shop for clubmed is "village"
        $alias = Alias::where('society_id', $this
            ->user
            ->current_society_id)
            ->first()
            ->toArray();
        if (isset($alias[$name][$this->user->language->code]) && ($alias[$name][$this->user->language->code] !== '')) {
            return $alias[$name][$this
                ->user
                ->language->code];
        } else {
            if ($name === 'criteria')
                return 'Critères';
            if ($name === 'shop')
                return 'Points de vente';
            if ($name === 'theme')
                return 'Thèmes';
            if ($name === 'sequence')
                return 'Séquences';
            else
                return $name;
        }
    }

    public function PrepareAxe()
    {
        //get all axes
        $shop_id = \DB::table('show_scoring_' . $this
            ->user
            ->current_society_id)
            ->select('shop_id')
            ->groupBy('shop_id');
        GraphFilterController::_addFilters($shop_id, $this->filters);
        $shop_id = $shop_id->get();

        //get all axe for this shop
        //filter on axe
        $axes_in_shop = DB::table('shop_axe')->select('axe_id')
            ->wherein('shop_id', $shop_id);


        $axes_in_shop = $axes_in_shop->get();

        $this->all_axe = array_map(
            function ($value) {
                return $value['axe_id'];
            },
            $axes_in_shop
        );
        return $axes_in_shop;
    }

    public function showDirectoriesChildren($item)
    {
        $childrens = $item->children;
        $childrens->each(function ($item, $key) use ($childrens) {
            if (!in_array($item->id, $this->all_axe_directory)) {
                $childrens->pull($key);
            }
            if ($item->axes) {
                $this->showAxesChildren($item);
            }
            if ($item->children) {
                $this->showDirectoriesChildren($item);
            }
        });
        unset($item->children);
        $item->children = $childrens->values();
    }

    public function showDirectoriesParent($id)
    {
        $ad = AxeDirectory::find($id);
        if ($ad->parent_id) {
            $this->all_axe_directory[] = $id;
            $this->all_axe_directory[] = $this->showDirectoriesParent($ad->parent_id);
        }
        return $ad->id;
    }

    public function showAxesChildren($item)
    {
        $axes = $item->axes;
        $axes->each(function ($item, $key) use ($axes) {
            if (!in_array($item->id, $this->all_axe)) {
                unset($axes[$key]);
            }
        });
        //unsetRelation not available on Laravel 5.1 so use unset to break relation and allow overwriting
        unset($item->axes);
        $item->axes = $axes->values();
    }

    function GetChild($arr, $clean = true)
    {
        $cachekey = CacheHelper::SetCacheKey('GetChild', null, [$arr]);
        if ($clean === true) {
            $this->formattedArr = [];
            Cache::forget($cachekey);
        }
        $subSequences = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey, $arr) {
            if (!empty($arr)) {
                foreach ($arr as $val) {
                    //add first level
                    if ($val['children']) {
                        $this->formattedArr[] = [$val['item_id']];
                        $this->returnArr = $this->GetChild($val['children'], false); // call recursive function
                        if (!empty($this->returnArr)) {
                            $this->formattedArr[] = $this->returnArr;
                        }
                    } else {
                        $this->formattedArr[] = [$val['item_id']];
                    }
                }
            }
            Cache::Put($cachekey, $this->formattedArr, $this->ttl_cache);
            return $this->formattedArr;
        });
        return $subSequences;
    }

    // Recursive Function( Build child )
    function AxeGetChild($arr, $clean = true, $deep = 0)
    {
        if (!is_array($arr))
            $arr = [$arr];
        if ($clean === true) {
            $this->formattedArr = [];
        }
        if (!empty($arr)) {
            foreach ($arr as $val) {
                if ($deep > 0) { //use to get axe fro a specific level only
                    if ($deep == $this->deeplevel) {
                        if ((isset($val['children']) && $val['children']) || (isset($val['axes']) && $val['axes'])) {
                            $type = 'directory';
                        } else {
                            $type = 'axe';
                        }
                        $this->formattedArr[] = ['id' => $val['id'], 'name' => is_array($val['name']) ? $val['name'][$this->user->language->code] : $val['name'], 'type' => $type];
                    }
                    //add first level
                    if ($deep <= $this->deeplevel) {
                        $this->returnArr = [];
                        if (isset($val['children']) && $val['children']) {
                            $this->returnArr = $this->AxeGetChild($val['children'], false, $deep + 1); // call recursive function

                        } else if (isset($val['axes']) && $val['axes']) {
                            $this->returnArr = $this->AxeGetChild($val['axes'], false, $deep + 1); // call recursive function

                        }
                    }
                } else {
                    //add first level
                    $this->returnArr = [];
                    if (isset($val['children']) && $val['children']) {
                        $this->returnArr = $this->AxeGetChild($val['children'], false, $deep); // call recursive function

                    } else if (isset($val['axes']) && $val['axes']) {
                        $this->returnArr = $this->AxeGetChild($val['axes'], false, $deep); // call recursive function

                    } else if (isset($val['id'])) {
                        $this->formattedArr[] = [$val['id']];
                    } else {
                        $this->formattedArr[] = [$val];
                    }
                }
            }
        }
        return $this->formattedArr;
    }

    private function addGroupLine($group)
    {
        $line = [];
        $can_push = false;

        foreach ($group as $k => $g) {
            //
            if ($this->filters["flop_5"] || $this->filters["top_5"]) {
                $flop_array = [["id" => 231, "score" => 80], ["id" => 172, "score" => 80]];
                foreach ($flop_array as $r)
                    if ($g["id"] == $r["id"]) {
                        $can_push = true;
                        break;
                    }
            } else
                $can_push = true;
            if ($can_push == true) {
                $line[] = $this->FormatGroup($g);
                $can_push = false;
            }
        }
        return $line;
    }

    private function addSequenceLine($sequenceFirstLevel, $survey_id)
    {
        $line = $xAxis = [];
        foreach ($sequenceFirstLevel as $k => $sequence) {
            //
            //lecture du score pour la ligne au global
            $cachekey = CacheHelper::SetCacheKey('addSequenceLine_', $this->user, [$survey_id, $sequence['item_id']]);
            $subSequences = Cache::Get($cachekey . $this->disable_cache . $this->request_type, function () use ($cachekey, $survey_id, $sequence) {
                $r = $subSequences = SurveyItem::with('children')->where('survey_id', $survey_id)->where('item_id', $sequence['item_id'])->where('display_report', true)->orderBy('order')
                    ->retrieveAll()
                    ->toArray();
                Cache::Put($cachekey, $r, $this->ttl_cache);
                return $r;
            });


            //Recursivité de la sequence
            if ($this->isGraph) {
                if (!empty($subSequences)) {
                    foreach ($subSequences as $val) {
                        if ($val['children']) {
                            $allchild = $this->GetChild($subSequences);
                            $allchild = array_flatten($allchild);
                            $this->AddSequenceGraphScore($allchild, $val['item_id']);
                        } else {
                            //get all seq
                            $this->AddSequenceGraphScore([$val['item_id']], $val['item_id']);
                        }
                    }
                }
            } else {
                $r = $this->FormatSequence($subSequences);
                if (count($r) > 1)
                    $line[] = $r;
            }
        }
        if ($this->isGraph) {
            if ($this->params['x']) {
                foreach ($this->key_x as $k => $x) {
                    $xAxis[] = $this->checkJson($x['name']);
                }
                $this->xAxis[] = [
                    "categories" => $xAxis
                ];
            } else {
                $this->xAxis[] = [
                    "categories" => ['']
                ];
            }
        }
        return $line;
    }

    function AddSequenceGraphScore($formatSequence, $current_seq)
    {
        if (!isset($this->floptop) || (isset($this->floptop) && (array_search($current_seq, array_column($this->floptop, 'id')) !== false))) {
            $data = [];
            $this->filter_y = 'sequence';
            $name = sequence::find($current_seq);
            $name = $this->checkJson($name['name']);
            $save_filter = $this->filters[$this->params['y']];

            //lecture du score pour la colonne global
            $this->filters[$this->params['y']] = $formatSequence;
            $score = $this->getGlobalSequenceFromWaveTargets($formatSequence);

            //lecture du score pour toutes les autres colonnes (group by $this->params['x'])
            $col = $this->getScoreFromWaveTargets($this->params['x']);
            $this->filters[$this->params['y']] = [$current_seq];


            //get goal
            $goal = $this->goalGraph();
            if ($this->params['x']) {
                foreach ($this->key_x as $x) {
                    $key = false;
                    if ($col)
                        $key = array_search($x['id'], array_column($col, 'id'));
                    if ($key !== false) {
                        $series[] = [
                            'x' => $this->checkJson($x['name']),
                            'y' => $col[$key]['score'] !== null ? round($col[$key]['score'], 0) : null,
                            'base' => $col[$key]['quantity'] ? ' (' . $col[$key]['quantity'] . ')' : '',
                            'goals' => $goal ? [$goal] : null
                        ];
                    } else {
                        $series[] = [
                            'x' => $this->checkJson($x['name']),
                            'y' => null,
                            'base' => null,
                            'goals' => null
                        ];
                    }
                }
                $this->series[] = [
                    "data" => $series,
                    "name" => $name,
                    "type" => 'column'
                ];
            } else {
                $this->series[] = [
                    "data" => [[
                        'x' => $this->key_x ? $this->checkJson($this->key_x[0]['name']) : null,
                        'y' => $score['score'] ? round($score['score'], 0) : null,
                        'z' => $score['quantity'],
                        'goals' => $goal ? [$goal] : null
                    ]],
                    "name" => $name,
                    "type" => 'column'
                ];
            }
            $this->series_basic = $this->series;
            //pour chaque colonne on affiches le score sinon null
            $this->filters[$this->params['y']] = $save_filter;
        }
    }


    private function getSequenceLine($sequenceFirstLevel)
    {
        foreach ($sequenceFirstLevel as $k => $sequence) {

            //lecture du score pour la ligne au global
            $cachekey = CacheHelper::SetCacheKey('getSequenceLine_', null, [$this->filters, $sequence['item_id']]);
            $subSequences = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey, $sequence) {
                $r = SurveyItem::with('children')->where('survey_id', $this->filters['survey'])->where('item_id', $sequence['item_id'])->where('display_report', true)->orderBy('order')
                    ->retrieveAll()
                    ->toArray();
                Cache::Put($cachekey, $r, $this->ttl_cache);
                return $r;
            });


            //Recursivité de la sequence
            $this->FormatSequenceCriteria($subSequences, $sequence['id']);
        }
    }

    public function getScoreFromWaveTargets($group_by)
    {
        ($this->params['y'] === 'criteria') ? $type = "type," : $type = "";

        if ($group_by === 'group') {
            //split by group
            //get all shop

            foreach ($this->key_x as $x) {

                if ($x['type'] == 'axe') {
                    $cachekey = CacheHelper::SetCacheKey('all_shops_axe', $this->user, [$x['id'], $this->filters]);
                    $all_shops = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey, $x) {
                        $all_shops = $this->GetShopFromAxe([$x['id']]);
                        if (isset($this->filters['current_shop'])) {
                            $all_shops = array_intersect($this->filters['current_shop'], $all_shops);
                        }
                        Cache::put($cachekey, $all_shops, $this->ttl_cache);
                        return $all_shops;
                    });
                } else {
                    $cachekey = CacheHelper::SetCacheKey('all_shops_directory', $this->user, [$x['id'], $this->filters]);
                    $all_shops = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey, $x) {
                        $val = AxeDirectory::relations()->where('axe_directory.hide_to_client', false)
                            ->where('axe_directory.id', $x['id'])->retrieveAll()
                            ->toArray();
                        $val = $this->AxeGetChild($val);
                        $all_shops = $this->GetShopFromAxe($val);
                        //get all shops link with same groupline
                        if (isset($this->filters['current_shop'])) {
                            $all_shops = array_intersect($this->filters['current_shop'], $all_shops);
                        }
                        Cache::put($cachekey, $all_shops, $this->ttl_cache);
                        return $all_shops;
                    });
                }

                $cachekey = CacheHelper::SetCacheKey('getScoreFromWaveTargets', $this->user, [$type, $all_shops, $this->filters]);
                $r = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey, $x, $type, $all_shops) {
                    $result = \DB::table('show_scoring_multi_' . $this->bonus . $this
                        ->user
                        ->current_society_id)
                        ->selectRaw(
                            $type . '
                CASE WHEN SUM(' . $this->score_method . 'weight) > 0
                THEN SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                ELSE null
                END AS score,
                ' . $x['id'] . ' AS id,
                COUNT(distinct wave_target_id) as quantity'
                        )->where('scoring', true)
                        ->whereNotNull('score')
                        ->wherein('shop_id', $all_shops); // voir pour utiliser current_shop à la place
                    $result = GraphFilterController::_addFilters($result, $this->filters);
                    if ($type)
                        $result = $result->groupBy('type');
                    Cache::put($cachekey, $result->first(), $this->ttl_cache);
                    return $result->first();
                });

                $e[] = $r;
                $result = $e;
            }
        } else if (($group_by === 'question_row') && ($this->filters['question_level']['id'] > 0)) { //on filtre sur une question
            foreach ($this->key_x as $k => $value) { // recuperation du score global en fonction du choix des réponses
                $this->filters['wave_target_id'] = $this->getWavetargetByAnswer($value['id']);
                $r = $this->show_scoring_multi('first', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, null, null, null, null, false);
                $result[] = [
                    "id" => $value['id'],
                    "score" => ($r['score'] !== null) ? round($r['score'], 0) : null,
                    "base" => $r['quantity'],
                    "info" => 'Score global de ' . $this->getAlias($this->params['y']) . ' lorsque cette réponse à la question "' . $this->filters['question_level']['name'] . '" est séléctionnée'
                ];
            }
        } else {
            if ($group_by === 'question_row_name') {
                $result = \DB::table('show_scoring_multi_' . $this->bonus . $this
                    ->user
                    ->current_society_id)
                    ->select('question_row_name as id')
                    ->selectRaw(
                        $type . '
                CASE WHEN SUM(' . $this->score_method . 'weight) > 0
                THEN SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                ELSE null
                END AS score,
                COUNT(distinct question_row_id) as quantity_row_id,
                COUNT(distinct wave_target_id) as quantity'
                    );
                $result = $result->whereNotNull($group_by);
                if (isset($this->filters['current_shop'])) {
                    $result = $result->wherein('shop_id', $this->filters['current_shop']);
                }
                $result = $result->groupby('question_row_name');
                $result = GraphFilterController::_addFilters($result, $this->filters);
                $result = $result->get();
            } else {
                $key_option = false;
                if ($group_by === 'wave_target')
                    $key_option = true;
                $result = $this->show_scoring_multi('get', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, null, null, $group_by, null, $key_option);
            }



            if ($group_by === 'question_row_name') {
                $total_answer = 0;
                foreach ($result as $r) {
                    $total_answer += $r['quantity_row_id'];
                }
                foreach ($result as &$r) {
                    $r['total_answer'] = $total_answer;
                }
            }
        }

        if (isset($this->filters['compare_to_period']) && count($this->filters['compare_to_period']) > 0 && !is_null($this->filters['compare_to_period']['id']) && $group_by <> 'wave') {
            $result = $this->GetPeriodCompare($result);
        }

        //get compare axe
        if (isset($this->filters['compare_to_axe']) && count($this->filters['compare_to_axe']) > 0) {
            $result = $this->GetAxeCompare($result, null, null, $group_by);
        }

        //get compare shop
        if (isset($this->filters['compare_to_shop']) && count($this->filters['compare_to_shop']) > 0) {
            $result = $this->GetShopCompare($result, null, $group_by);
        }





        if (!empty($result)) {
            foreach ($result as $k => $re) {
                isset($re['type']) && ($re['type'] === 'satisfaction' || $re['type'] === 'number') ? $round = 1 : $round = 0;
                $key = 0;
                if ($group_by) {
                    if ($this->isGraph)
                        $key = $k;
                    else
                        $key = $re['id'];
                }
                if ($this->isGraph) {
                    $res[$key] = $re;
                } else {
                    $res[$key] = [
                        'type' => isset($re['type']) ? $re['type'] : null,
                        'score' => ($re['score'] !== null ? round($re['score'], $round) : null),
                        'info' => (isset($re['info']) ? $re['info'] : null),
                        'total_answer' => (isset($re['total_answer']) ? $re['total_answer'] : null),
                        'quantity_row_id' => (isset($re['quantity_row_id']) ? $re['quantity_row_id'] : null),
                        'quantity' => (isset($re['quantity']) ? $re['quantity'] : null)
                    ];
                }
            }
        } else {
            $res = null;
        }
        return $res;
    }

    private function GetPeriodCompare($result)
    {
        if ($result) {
            //change wave
            $save_wave = $this->filters['wave'];

            $this->filters['wave'] = $this->previousWave();
            if (is_array($this->filters['wave']) && count($this->filters['wave']) > 0) {
                $save_score_method = $this->score_method;
                if ($this->params['y'] == 'criteria') {
                    $this->score_method = 'question';
                } else {
                    $this->score_method = '';
                }

                $this->score_method = $save_score_method;
                $result_compare = $this->show_scoring_multi('get', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, null, null, $group_by, null, false);
            }

            foreach ($result as $r) {
                //get previous score
                $previous_score = null;
                if (is_array($this->filters['wave']) && count($this->filters['wave']) > 0) {
                    foreach ($result_compare as $key => $val) {
                        if ($val['id'] === $r['id']) {
                            $previous_score = $val['score'];
                        }
                    }
                    $res[$r['id']] = ["value" => round($r['score']), "compare" => round($r['score'] - $previous_score)];
                } else {
                    $res[$r['id']] = ["value" => round($r['score']), "compare" => null];
                }
            }
            $this->filters['wave'] = $save_wave;
        } else {
            $res = null;
        }
        return $result;
    }

    private function GetAxeCompare($result, $criteria_id = null, $question_level = false, $group_by = null)
    {
        if ($result) {
            //remove current shop to compare to full axe
            if ($this->params['y'] !== 'group') {
                $save_filter = $this->filters[$this->params['y']];
            }
            //disable filter on shop
            if ($this->params['y'] === 'shop' || $this->params['x'] === 'shop')
                unset($this->filters['shop']);


            if ($this->params['y'] === 'group') {
                $save_current_shop = isset($this->filters['current_shop']) ? $this->filters['current_shop'] : null;
                unset($this->filters['current_shop']);
            }


            foreach ($this->filters['compare_to_axe'] as $compare_to_axe) {
                $r = null;
                if (isset($compare_to_axe['children']) && count($compare_to_axe['children']) > 0) {
                    $allchild = $this->AxeGetChild($compare_to_axe['children']);
                    $axes = array_flatten($allchild);
                } else if (isset($compare_to_axe['axes']) && count($compare_to_axe['axes']) > 0) {
                    $allchild = $this->AxeGetChild($compare_to_axe['axes']);
                    $axes = array_flatten($allchild);
                } else {
                    $axes = $compare_to_axe;
                }

                $all_shops = $this->GetShopFromAxe(ArrayHelper::getIds($axes));
                if ($this->isGraph) { //use to get graph compare
                    if ($this->params['x'] === 'shop')
                        $group_by = null;
                    $r_compare = $this->show_scoring_multi('get', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, null, $criteria_id, $group_by, null, null, $all_shops);
                    //check if all column have score to avoid skip column bug
                    foreach ($this->key_x as $x) {
                        if ($this->params['x'] === 'shop') {
                            //set score for all shops
                            $r[] = $r_compare[0];
                        } else {
                            $key = array_search($x['id'], array_column($r_compare, 'id'));
                            if (false !== $key) {
                                $r[] = $r_compare[$key];
                            } else {
                                $r[] = [];
                            }
                        }
                    }
                } else {
                    $r = $this->show_scoring_multi('first', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, null, $criteria_id, null, null, null, $all_shops);
                }
                if ($r) {
                    if ($this->isGraph) { //use to get graph compare
                        $result[$compare_to_axe['name']] = $r;
                    } else {
                        $result['axe_' . $compare_to_axe['id']] = ["id" => 'axe_' . $compare_to_axe['id'], "score" => $r['score'] !== null ? round($r['score']) : null];
                    }
                } else
                    $result['axe_' . $compare_to_axe['id']] = ["id" => 'axe_' . $compare_to_axe['id'], "score" => null];
            }

            if ($this->params['y'] === 'group') {
                $this->filters['current_shop'] = $save_current_shop;
            }
            if ($this->params['y'] !== 'group') {
                $this->filters[$this->params['y']] = $save_filter;
            }
            return $result;
        }
    }

    private function GetShopCompare($result, $criteria = null, $group_by = null)
    {
        if ($result) {
            //disable filter on shop
            if ($this->params['y'] === 'shop')
                unset($this->filters['shop']);


            if ($this->params['y'] === 'group') {
                $save_current_shop = $this->filters['current_shop'];
                unset($this->filters['current_shop']);
            }

            ///
            foreach ($this->filters['compare_to_shop'] as $compare_to_shop) {
                $r = null;
                if ($this->isGraph) { //use to get graph compare
                    if ($this->params['x'] === 'shop')
                        $group_by = null;
                    $r_compare = $this->show_scoring_multi('get', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, null, $criteria, $group_by, null, null, $compare_to_shop);
                    //check if all column have score to avoid skip column bug
                    foreach ($this->key_x as $x) {
                        if ($this->params['x'] === 'shop') {
                            //set score for all shops
                            $r[] = $r_compare[0];
                        } else {
                            $key = array_search($x['id'], array_column($r_compare, 'id'));
                            if (false !== $key) {
                                $r[] = $r_compare[$key];
                            } else {
                                $r[] = [];
                            }
                        }
                    }
                } else {
                    $r = $this->show_scoring_multi('first', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, null, $criteria, null, null, null, $compare_to_shop);
                }
                if ($r) {
                    if ($this->isGraph) { //use to get graph compare
                        $result[$compare_to_shop['name']] = $r;
                    } else {
                        $result['shop_' . $compare_to_shop['id']] = ["id" => 'shop_' . $compare_to_shop['id'], "score" => $r['score'] !== null ? round($r['score']) : null];
                    }
                } else
                    $result['shop_' . $compare_to_shop['id']] = ["id" => 'shop_' . $compare_to_shop['id'], "score" => null];
            }

            if ($this->params['y'] === 'group') {
                $this->filters['current_shop'] = $save_current_shop;
            }
            return $result;
        }
    }

    private function getGlobalFromWaveTargets()
    {
        if ($this->livedata) {
            $view = 'show_scoring_multi';
        } else {
            $view = 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id;
        }
        $i = $this->params['y'];
        if ($this->params['y'] === 'criteria' || $this->params['y'] === 'shop' || $this->params['y'] === 'theme' || $this->params['y'] === 'group' || $this->params['y'] ===  'question_row') {
            $cachekey = CacheHelper::SetCacheKey('getGlobalFromWaveTargetsBase', $this->user, [$view, $this->filters]);
            $base = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey, $view) {
                $r = \DB::table($view)
                    ->select('wave_target_id')
                    ->where('scoring', true)
                    ->whereNotNull('score')
                    ->groupBy('wave_target_id');
                GraphFilterController::_addFilters($r, $this->filters);
                $r = $r->get();
                Cache::Put($cachekey, count($r), $this->ttl_cache);
                return count($r);
            });
        } else {
            $base = null;
        }
        if ($this->params['y'] === 'wave_target')
            $result = $this->show_scoring_multi('first', $view, null, null, null, null, true);
        else if ($this->params['y'] !== 'question_row' && $this->params['y'] !== 'group')
            $result = $this->show_scoring_multi('first', $view, null, null, $this->params['y'], null, false);
        else
            $result = $this->show_scoring_multi('first', $view, null, null, null, null, false);

        if (!is_null($result['score'])) {
            $res['score'] = round($result['score'], 0);
            $res['quantity'] = $base;
        } else {
            $res['score'] = null;
            $res['quantity'] = null;
        }
        return $res;
    }

    public function getGlobal()
    {
        $cachekey = CacheHelper::SetCacheKey('getGlobal', $this->user, [$this->filters]);
        $result = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey) {
            $r = \DB::table('show_scoring_multi_' . $this
                ->user
                ->current_society_id)
                ->selectRaw('
                CASE WHEN SUM(weight) > 0
                THEN SUM(score) / SUM(CAST(weight AS FLOAT))
                ELSE null
                END AS score,
                COUNT(distinct wave_target_id) as quantity')
                ->where('scoring', true)
                ->whereNotNull('score');
            GraphFilterController::_addFilters($r, $this->filters);
            $r = $r->first();
            Cache::Put($cachekey, $r, $this->ttl_cache);
            return $r;
        });


        return $result;
    }

    public function getGlobalSequenceFromWaveTargets($id)
    {
        $id = array_unique($id);
        $cachekey = CacheHelper::SetCacheKey('getGlobalFromWaveTargetsBase', $this->user, [$id, $this->filters]);
        $result = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey, $id) {
            $r = \DB::table('show_scoring_multi_' . $this->bonus .  $this
                ->user
                ->current_society_id)
                ->selectRaw('
                CASE WHEN SUM(' . $this->score_method . 'weight) > 0
                THEN SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                ELSE null
                END AS score,
                COUNT(distinct wave_target_id) as quantity')
                ->where('scoring', true)
                ->whereNotNull('score')
                ->whereIn('sequence_id', $id);
            GraphFilterController::_addFilters($r, $this->filters);
            $r = $r->first();
            Cache::Put($cachekey, $r, $this->ttl_cache);
            return $r;
        });


        return $result;
    }

    private function getGlobalAxeFromWaveTargets($ids = null)
    {
        $result = $this->show_scoring_multi('first', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, null, null, null, null, false, $ids);

        return $result;
    }

    private function checkJson($v)
    {
        if (isset($v['name']) && is_object(json_decode($v['name']))) {
            $name = json_decode($v['name'], true);
            $name = (isset($name[$this->user->language->code])) ? $name[$this->user->language->code] : $name['fr'];
        } else if (is_string($v) && is_object(json_decode($v))) {
            $name = json_decode($v, true);
            $name = (isset($name[$this->user->language->code])) ? $name[$this->user->language->code] : $name['fr'];
        } else if (isset($v['name'])) {
            $name = $v['name'];
        } else if (is_array($v)) {
            $name = (isset($v[$this->user->language->code])) ? $v[$this->user->language->code] : $v['fr'];
        } else {
            $name = $v;
        }

        return $name;
    }

    private function previousWave(): ?array
    {
        $wave_id = $this->filters['wave'];
        if (!$wave_id) return null;
        $this->filters['wave'] = null;
        if (is_array($wave_id) && !isset($wave_id['id'])) {
            $wave_id = $wave_id[0]['id'];
        }
        $res = \DB::table('show_wave_with_missions_' . $this
            ->user
            ->current_society_id)
            ->select('wave_id')
            ->groupBy('wave_id');

        GraphFilterController::_addFilters($res, $this->filters);

        $waves_use = $res->get();
        $waves = Wave::where('id', '<', $wave_id)->whereIn('id', $waves_use)->orderBy('date_start', 'DESC')
            ->limit($this->filters['compare_to_period']['id'])->get()
            ->toArray();

        return $waves;
    }

    public function array_flatten($array = null): array
    {
        $result = array();

        if (!is_array($array)) {
            $array = func_get_args();
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
            } else {
                $result = array_merge($result, array(
                    $key => $value
                ));
            }
        }

        return $result;
    }

    public function getquestiondetails($survey_id, $question_id)
    {
        $surveyItems = SurveyItem::with('themes', 'jobs', 'criterionA', 'criterionB')->where('type', 'question')->where('item_id', $question_id)->where('survey_id', $survey_id)->get()->toArray();
        $themes = $jobs  = $criteriaA = $criteriaB = [];
        foreach ($surveyItems as $surveyItem) {
            foreach ($surveyItem['themes'] as $theme) {
                if (array_search($theme['id'], array_column($themes, 'id')) === false)
                    array_push($themes, [
                        "id" => $theme['id'],
                        "name" => $theme['name']
                    ]);
            }
            foreach ($surveyItem['jobs'] as $job) {
                if (array_search($job['id'], array_column($jobs, 'id')) === false)
                    array_push($jobs, [
                        "id" => $job['id'],
                        "name" => $job['name']
                    ]);
            }
            foreach ($surveyItem['criterion_a'] as $criteria) {
                if (array_search($criteria['id'], array_column($criteriaA, 'id')) === false)
                    array_push($criteriaA, [
                        "id" => $criteria['id'],
                        "name" => $criteria['name']
                    ]);
            }
            foreach ($surveyItem['criterion_b'] as $criteria) {
                if (array_search($criteria['id'], array_column($criteriaB, 'id')) === false)
                    array_push($criteriaB, [
                        "id" => $criteria['id'],
                        "name" => $criteria['name']
                    ]);
            }
        }
        return [
            'themes' => $themes,
            'jobs' => $jobs,
            'criteriaA' => $criteriaA,
            'criteriaB' => $criteriaB,

        ];
    }

    public function question($params, $user)
    {
        $this->params = $params;
        $this->user = $user;
        $this->filters = $this->params['filters'];
        $this->score_method = 'question_';
        $this->bonus = '';


        $this->filters['general']['shop'] = $this->_getShopsFromFilters();
        $this->filters['general']['answer'] = $this->_GetAllQuestionsasfilter();



        if (isset($this->filters['general']['sequence']) && count($this->filters['general']['sequence'])) {
            $ids = SurveyItem::getSequencesIds($this->filters['general']['sequence']);

            $this->filters['general']['sequence'] = $ids;
        }

        $this->filters = GraphTemplateService::getFilter($this->filters, $this
            ->user
            ->current_society_id);

        $result = \DB::table('show_criteria_' . $this->user->current_society_id)
            ->selectRaw('
            sequence_name,
            question_name,
            survey_id,
            question_id,
            criteria_id,
            response_value,
            type,
            answer_id,
            comment,
            wave_id,
            shop_name,
            wave_name,
            shop_id,
            question_row_name,
            visit_date,
            date_status,
            image,
            program_id,
            user_id,
            na,
            claim_status,
            anonymous_mode,
            full_name,
            wave_target_id
            ')
            ->orderBy('shop_name')
            ->orderBy('visit_date', 'DESC')
            ->where(function ($q) {
                $q->where('na', false)->orWhereNull('na');
            });
        if (isset($this->params['filters']['question_id']))
            $result = $result->where('question_id', $this->params['filters']['question_id']);

        $result = GraphFilterController::_addFilters($result, $this->filters);
        $result = $result->get();

        //read theme, criteria a & b for current question
        if ($result) {
            $infos = $this->getquestiondetails($result[0]['survey_id'], $result[0]['question_id']);
        }
        foreach ($result as &$r) {
            //get mission data
            $image = AnswerImage::where('answer_id', $r['answer_id'])->get();
            if ($image) {
                $r['sequence_name'] = json_decode($r['sequence_name'])->{$this->user->language->code};
                $r['question_name'] = json_decode($r['question_name'])->{$this->user->language->code};
                if ($r['question_row_name']) $r['question_row_name'] = json_decode($r['question_row_name'])->{$this->user->language->code};
                else $r['question_row_name'] = null;
                $r['image'] = $image;
            }
            $file = AnswerFile::where('answer_id', $r['answer_id'])->get();
            if ($file) {
                $r['file'] = $file;
            }
            $comments = AnswerComment::with('questionrowcomment')->where('answer_id', $r['answer_id'])->get();
            if ($comments) {
                $r['comments'] = $comments;
            }
            //find action plan
            $ap = ActionPlan::select('id')->where('answer_id', $r['answer_id'])->first();
            if ($ap) {
                $r['action_id'] = $ap['id'];
            } else {
                $r['action_id'] = null;
            }
            if (!$r['visit_date']) {
                $r['visit_date'] = $r['date_status'];
            }
            $r['infos'] = $infos;
        }

        if (!isset($this->params['filters']['question_id'])) {
            $criterias = \DB::table('show_criteria_' . $this
                ->user
                ->current_society_id)
                ->selectRaw('
                type,
                criteria_id,
                criteria_name,
                CASE WHEN SUM(' . $this->score_method . 'weight) > 0 THEN
                SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                ELSE null
                END AS score,
                    count(distinct wave_target_id) as count
                ')
                ->where(function ($q) {
                    $q->where('na', false)->orWhereNull('na');
                })
                ->groupBy('type', 'criteria_id', 'criteria_name')
                ->orderBy('score');

            $criterias = GraphFilterController::_addFilters($criterias, $this->filters);
            $criterias = $criterias->get();

            foreach ($criterias as &$criteria) {
                $criteria['type'] === 'satisfaction' || $criteria['type'] === 'number' ? $round = 1 : $round = 0;
                $criteria['criteria_name'] = json_decode($criteria['criteria_name'])->{$this->user->language->code};
                $criteria['score'] = ($criteria['score'] !== null) ? round($criteria['score'], $round) : null;
                $criteria['items'] = [];

                foreach ($result as $answer) {
                    if ($criteria['criteria_id'] === $answer['criteria_id']) {
                        $criteria['items'][] = $answer;
                    }
                }
            }

            return ($criterias);
        }


        return ($result);
    }

    public function rowdata($params, $user)
    {
        $this->hide_bases = false;
        $this->hide_goals = false;
        $this->sort_result = false;
        $this->params = $params;
        $this->user = $user;
        $this->filters = $this->params['filters'];
        $this->score_method = 'question_';
        $this->bonus = '';


        //remove survey info if no survey selected
        if (isset($this->filters['general']['survey']) && is_array($this->filters['general']['survey']) && count($this->filters['general']['survey']) > 0) {
            if ($this->filters['general']['survey']['id'] == 0)
                if (!is_int($this->filters['general']['survey']))
                    unset($this->filters['general']['survey']);
        }

        //get axe and all children
        $this->filters['general']['answer'] = $this->_GetAllQuestionsasfilter();
        $this->filters['general']['shop'] = $this->_getShopsFromFilters();

        $this->filters = GraphTemplateService::getFilter($this->filters, $this
            ->user
            ->current_society_id);

        //filter on restricted shop only

        //check if split_by is axe tag
        if (isset($this->filters['split_by']) && is_int($this->filters['split_by']['id'])) {
            $this->filters['tags'] = $this->filters['split_by']['id'];
            $this->params['x'] = 'group';
        }

        //check if range is axe tag
        if (isset($this->filters['range']) && is_int($this->filters['range']['id'])) {
            $this->filters['tags'] = $this->filters['range']['id'];
            $this->params['y'] = 'group';
        }

        if (isset($this->filters['hide_bases']) && $this->filters['hide_bases'])
            $this->hide_bases = true;

        if (isset($this->filters['hide_goals']) && $this->filters['hide_goals'])
            $this->hide_goals = true;

        if (isset($this->filters['sort_result']) && $this->filters['sort_result'])
            $this->sort_result = true;




        //get score on level question.
        $level_question = ['criteria', 'theme', 'job', 'criteria_a', 'criteria_b'];
        if ((in_array($this->params['y'], $level_question)) || (in_array($this->params['x'], $level_question))) {
            $this->score_method = 'question_';
            $this->bonus = 'without_bonus_';
        }
        //check if some filter that affect the way of score calculation are used
        foreach ($level_question as $value) {
            if ($this->filters[$value]) {
                $this->score_method = 'question_';
                $this->bonus = 'without_bonus_';
            }
        }
        //Get Column data to read
        //read x
        if ($this->params['x'] == 'group') {
            //split by axe
            $this->key_x = $this->_getKeyTag('split_by');
        } else if (isset($this->filter['period']['id']) && $this->filter['period']['id'] == 'cumulative') {
            //split by axe
            $this->key_x = $this->_getKeyWave();
        } else if ($this->params['x'] == 'question_row_name') {
            //split by axe
            $this->key_x = $this->_getKeyAnswer();
        } else if ($this->params['x']) {
            $this->key_x = $this->_getKey($this->params['x'], 'x');
        };

        $row = $this->params['row'];
        $this->filters[$row['y']] = [$row['id']];
        $r = $this->subCriteria($row['y'], [$row['id']]);
        return ($r);
    }

    private function getCriteriaQuestion()
    {
        //please create new migration file for show_question_criteria_scoring view
        //add show_scoring.score
        //add show_scoring.weight
        //rename id_critere to criteria_id
        //rename id_question to question_id

        $group_by = null;
        $cachekey = CacheHelper::SetCacheKey('getCriteriaQuestion_', $this->user, [$this->bonus, $this->score_method, $this->params['x'], $this->filters]);
        $result_data = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey) {
            $result_data = [];
            $result = \DB::table('show_criteria_' . $this
                ->user
                ->current_society_id)
                ->selectRaw('
                type,
                criteria_id as id,
                criteria_id,
                question_id,
                question_name,
                sequence_name,
                CASE WHEN SUM(' . $this->score_method . 'weight) > 0 THEN
                    SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                ELSE null
                END AS score,
                COUNT(distinct wave_target_id) as quantity
                ')
                ->groupBy('criteria_id', 'question_id', 'question_name', 'sequence_name', 'type')
                ->orderBy('criteria_id');

            $result = GraphFilterController::_addFilters($result, $this->filters);
            $result = $result->get();
            foreach ($result as $r) {
                $r['col'] = [];
                array_push($result_data, $r);
            }
            if (isset($this->filters['compare_to_period']) && isset($this->filters['compare_to_period']['id'])) {
                if ($result_data) {
                    $save_wave = $this->filters['wave'];
                    $this->filters['wave'] = $this->previousWave($this->filters, $this->filters['wave']);
                    if (is_array($this->filters['wave']) && count($this->filters['wave']) > 0) {
                        $result = $this->show_scoring_multi('get', 'show_criteria_' . $this->bonus . $this->user->current_society_id, null, null, 'question');
                        foreach ($result_data as &$key) {
                            $base_key = array_search($key['question_id'], array_column($result, 'question_id'));
                            if (false !== $base_key) {
                                $key['compare'] = $result[$base_key]['score'];
                            } else {
                                $key['compare'] = null;
                            }
                        }
                    } else {
                        foreach ($result_data as &$key) {
                            $key['compare'] = null;
                        }
                    }
                    $this->filters['wave'] = $save_wave;
                }
            }
            //get compare axe
            if (isset($this->filters['compare_to_axe']) && count($this->filters['compare_to_axe']) > 0) {
                foreach ($result_data as $k => $r) {
                    //add information about question level
                    $r['question_level'] = true;
                    $result_data[$k] = $this->GetAxeCompare($r, $r);
                }
            }

            //get compare shop
            if (isset($this->filters['compare_to_shop']) && count($this->filters['compare_to_shop']) > 0) {
                foreach ($result_data as $k => $r) {
                    $r['question_level'] = true;
                    $result_data[$k] = $this->GetShopCompare($r, $r);
                }
            }

            if ($this->params['x']) {
                $group_by = $id_value = $this->params['x'] . '_id';
            } else {
                $id_value = 0;
            }
            if ($this->params['x'] === 'question_row_name') {
                $id_value = 'question_row_name';
                $group_by = 'question_row_name';
            }
            //get score by wave

            if ($this->params['x'] === 'group') {
                $r_g = [];
                foreach ($this->key_x as $x) {
                    if ($x['type'] == 'axe') {
                        $all_shops = $this->GetShopFromAxe([$x['id']]);
                    } else {
                        $val = AxeDirectory::relations()->where('axe_directory.hide_to_client', false)
                            ->where('axe_directory.id', $x['id'])->retrieveAll()
                            ->toArray();
                        $val = $this->AxeGetChild($val);
                        $all_shops = $this->GetShopFromAxe($val);
                    }
                    $result_g = \DB::table('show_criteria_' . $this
                        ->user
                        ->current_society_id)
                        ->selectRaw('
                                    question_id,
                                    CASE WHEN SUM(' . $this->score_method . 'weight) > 0 THEN
                                        SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                                    ELSE null
                                    END AS score,
                                    COUNT(distinct wave_target_id) as quantity,
                    ' . $x['id'] . ' AS group_id')
                        ->wherein('shop_id', $all_shops);
                    $result_g = GraphFilterController::_addFilters($result_g, $this->filters);
                    $result_g = $result_g->groupBy('question_id');
                    $r_g[] = $result_g->get();
                }
                $result_g = $r_g;
                foreach ($result_g as $groups) {
                    foreach ($groups as $key) {
                        $base_key = array_search($key['question_id'], array_column($result_data, 'question_id'));
                        if (false !== $base_key) {
                            $result_data[$base_key]['col'][$key['group_id']] = $key['score'] !== null ? $key['score'] : null;
                            $result_data = $this->formatCompare($result_data, $base_key);
                        }
                    }
                }
            } else if ($this->params['x'] === 'wave' && isset($this->filters['period']['id']) && $this->filters['period']['id'] === 'cumulative') {
                $r_g = [];
                foreach ($this->key_x as $x) {
                    $result_g = \DB::table('show_criteria_' . $this
                        ->user
                        ->current_society_id)
                        ->selectRaw('
                                    question_id,
                                    CASE WHEN SUM(' . $this->score_method . 'weight) > 0 THEN
                                        SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                                    ELSE null
                                    END AS score,
                                    COUNT(distinct wave_target_id) as quantity,
                    ' . $x['id'] . ' AS group_id');
                    $result_g = GraphFilterController::_addFilters($result_g, $this->filters);
                    $result_g = $result_g->groupBy('question_id');
                    $r_g[] = $result_g->get();
                }
                $result_g = $r_g;
                foreach ($result_g as $groups) {
                    foreach ($groups as $key) {
                        $base_key = array_search($key['question_id'], array_column($result_data, 'question_id'));
                        if (false !== $base_key) {
                            $result_data[$base_key]['col'][$key['group_id']] = $key['score'] !== null ? $key['score'] : null;
                            $result_data = $this->formatCompare($result_data, $base_key);
                        }
                    }
                }
            } else {
                if ($this->bonus)
                    $view = 'show_scoring_multi_' . $this->bonus . 'with_text_';
                else
                    $view = 'show_scoring_multi_with_text_';
                $result = \DB::table($view . $this
                    ->user
                    ->current_society_id)
                    ->selectRaw(
                        $id_value . ',
                criteria_id as id,
                criteria_id,
                question_id,
                CASE WHEN SUM(' . $this->score_method . 'weight) > 0 THEN
                    SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                ELSE null
                END AS score,
                COUNT(distinct wave_target_id) as quantity
                '
                    )
                    ->groupBy('criteria_id', 'question_id', $group_by, 'criteria_name')
                    ->orderBy('criteria_id');
                $result = GraphFilterController::_addFilters($result, $this->filters);
                $result = $result->get();

                foreach ($result as $key) {
                    $base_key = array_search($key['question_id'], array_column($result_data, 'question_id'));

                    if (false !== $base_key) {
                        $result_data = $this->formatCompare($result_data, $base_key);
                        $result_data[$base_key]['col'][$key[$id_value]]['score'] = $key['score'] !== null ? round($key['score']) : null;
                        $result_data[$base_key]['col'][$key[$id_value]]['quantity'] = $key['quantity'];
                    } else {
                        $result_data[$base_key]['col'][$key[$id_value]]['score'] = null;
                        $result_data[$base_key]['col'][$key[$id_value]]['quantity'] = null;
                    }
                }
            }
            Cache::put($cachekey, $result_data, $this->ttl_cache);
            return $result_data;
        });


        return $result_data;
    }

    private function formatCompare($result_data, $base_key)
    {
        if (isset($this->filters['compare_to_axe']) && count($this->filters['compare_to_axe']) > 0) {
            foreach ($this->filters['compare_to_axe'] as $compare_axe) {
                if (isset($result_data[$base_key]['axe_' . $compare_axe['id']])) {
                    $result_data[$base_key]['col']['axe_' . $compare_axe['id']]['score'] = $result_data[$base_key]['axe_' . $compare_axe['id']]['score'];
                    $result_data[$base_key]['col']['axe_' . $compare_axe['id']]['quantity'] = null;
                }
            }
        }
        if (isset($this->filters['compare_to_shop']) && count($this->filters['compare_to_shop']) > 0) {
            foreach ($this->filters['compare_to_shop'] as $compare_shop) {
                if (isset($result_data[$base_key]['shop_' . $compare_shop['id']])) {
                    $result_data[$base_key]['col']['shop_' . $compare_shop['id']]['score'] = $result_data[$base_key]['shop_' . $compare_shop['id']]['score'];
                    $result_data[$base_key]['col']['shop_' . $compare_shop['id']]['quantity'] = null;
                }
            }
        }
        return $result_data;
    }

    private function getCriteriaBase()
    {

        $cachekey = CacheHelper::SetCacheKey('getCriteriaBase', $this->user, [$this->filters]);
        $result = Cache::get($cachekey . $this->disable_cache, function () use ($cachekey) {
            $result = \DB::table('show_criteria_' . $this
                ->user
                ->current_society_id)
                ->selectRaw("
                criteria_id,
                count(wave_target_id)
                ")
                ->where(function ($q) {
                    $q->where('na', false)->orWhereNull('na');
                })
                ->groupBy('criteria_id');
            $result = GraphFilterController::_addFilters($result, $this->filters);
            $result = $result->get();
            Cache::put($cachekey, $result, $this->ttl_cache);
            return $result;
        });
        return $result;
    }

    private function getQuestionBase()
    {

        $cachekey = CacheHelper::SetCacheKey('getQuestionBase', $this->user, [$this->filters]);
        $result = Cache::get($cachekey . $this->disable_cache, function () use ($cachekey) {
            $result = \DB::table('show_criteria_' . $this
                ->user
                ->current_society_id)
                ->selectRaw("
                criteria_id,
                question_id,
                count(distinct wave_target_id)
                ")
                ->where(function ($q) {
                    $q->where('na', false)->orWhereNull('na');
                })
                ->groupBy('criteria_id', 'question_id');
            $result = GraphFilterController::_addFilters($result, $this->filters);
            $result = $result->get();
            Cache::put($cachekey, $result, $this->ttl_cache);
            return $result;
        });
        return $result;
    }

    private function getQuestionShop()
    {
        $cachekey = CacheHelper::SetCacheKey('getQuestionShop', $this->user, [$this->score_method, $this->filters]);
        $result = Cache::get($cachekey . $this->disable_cache, function () use ($cachekey) {
            $result = \DB::table('show_criteria_' . $this
                ->user
                ->current_society_id)
                ->selectRaw('
                question_id,
                shop_id,
                CASE WHEN SUM(' . $this->score_method . 'weight) > 0 THEN
                SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
                ELSE null
                END AS score
                ')
                ->groupBy('question_id', 'shop_id');
            $result = GraphFilterController::_addFilters($result, $this->filters);
            $result = $result->get();
            Cache::put($cachekey, $result, $this->ttl_cache);
            return $result;
        });

        return $result;
    }

    private function getCriteriaScoring()
    {

        //please create new migration file for show_question_criteria_scoring view
        //add show_scoring.score
        //add show_scoring.weight
        //rename id_critere to criteria_id
        //rename id_question to question_id
        //sudo php artisan society:createview 57
        //get order
        //get all criteria

        $cachekey = CacheHelper::SetCacheKey('getCriteriaScoring_', $this->user, [$this->params['x'], $this->filters]);
        $result_data = Cache::get($cachekey . $this->disable_cache, function () use ($cachekey) {
            $order = \DB::table('show_criteria_' . $this
                ->user
                ->current_society_id)
                ->selectRaw("
            survey_id
            ");
            $order = GraphFilterController::_addFilters($order, $this->filters);
            $order = $order->orderBy('survey_id', 'DESC');
            $order = $order->first();
            $group_by = null;
            $survey_id = $order['survey_id'];

            $order = $this->OrderCriteria($survey_id);
            $result_data = $this->resultData($order);

            if ($this->filters['flop_5']) {
                $i = 0;
                $columns = array_column($result_data, 'score');
                array_multisort($columns, SORT_ASC, $result_data);
                $result_data_flop = [];
                foreach ($result_data as $r) {
                    if ($r['score'] !== null && $r['type'] !== 'number' && $r['type'] !== 'satisfaction') {
                        $result_data_flop[] = $r;
                        $i++;
                    }
                    if ($i > 4)
                        break;
                }
                $result_data = $result_data_flop;
            }

            if ($this->filters['top_5']) {
                $columns = array_column($result_data, 'score');
                array_multisort($columns, SORT_DESC, $result_data);
                $result_data = array_slice($result_data, 0, 5);
            }
            if (isset($this->filters['compare_to_period']) && isset($this->filters['compare_to_period']['id'])) {
                if ($result_data) {
                    $save_wave = $this->filters['wave'];
                    $this->filters['wave'] = $this->previousWave($this->filters, $this->filters['wave']);
                    if (is_array($this->filters['wave']) && count($this->filters['wave']) > 0) {
                        $result = $this->show_scoring_multi('get', 'show_criteria_' . $this->bonus . $this->user->current_society_id, null, null, 'criteria');
                        foreach ($result_data as &$key) {
                            $base_key = array_search($key['id'], array_column($result, 'id'));
                            if (false !== $base_key) {
                                $key['compare'] = $result[$base_key]['score'];
                            } else {
                                $key['compare'] = null;
                            }
                        }
                    } else {
                        foreach ($result_data as &$key) {
                            $key['compare'] = null;
                        }
                    }
                    $this->filters['wave'] = $save_wave;
                }
            }
            //get compare axe
            if (isset($this->filters['compare_to_axe']) && count($this->filters['compare_to_axe']) > 0) {
                foreach ($result_data as $k => $r) {
                    $r['question_level'] = false;
                    $result_data[$k] = $this->GetAxeCompare($r, $r);
                }
            }

            //get compare shop
            if (isset($this->filters['compare_to_shop']) && count($this->filters['compare_to_shop']) > 0) {
                foreach ($result_data as $k => $r) {
                    $r['question_level'] = false;
                    $result_data[$k] = $this->GetShopCompare($r, $r);
                }
            }

            $r = null;



            //get score by wave
            if ($this->params['x'] === 'group') {
                $r = [];
                foreach ($this->key_x as $x) {
                    if ($x['type'] == 'axe') {
                        $all_shops = $this->GetShopFromAxe([$x['id']]);
                    } else {
                        $val = AxeDirectory::relations()->where('axe_directory.hide_to_client', false)
                            ->where('axe_directory.id', $x['id'])->retrieveAll()
                            ->toArray();
                        $val = $this->AxeGetChild($val);
                        $all_shops = $this->GetShopFromAxe($val);
                    }
                    if ($this->bonus)
                        $view = 'show_scoring_multi_' . $this->bonus;
                    else
                        $view = 'show_scoring_multi_';

                    $result = \DB::table($view . $this
                        ->user
                        ->current_society_id)
                        ->selectRaw('
            CASE WHEN SUM(' . $this->score_method . 'weight) > 0 THEN
            SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
            ELSE null
            END AS score,
            criteria_id as id,
            COUNT(distinct wave_target_id) as quantity,
            ' . $x['id'] . ' AS group_id')
                        ->wherein('shop_id', $all_shops);
                    $result = GraphFilterController::_addFilters($result, $this->filters);
                    $result = $result->groupBy('criteria_id');
                    $r[] = $result->get();
                }
                $result = $r;
                foreach ($result as $groups) {
                    foreach ($groups as $key) {
                        $base_key = array_search($key['id'], array_column($result_data, 'id'));
                        if (false !== $base_key) {
                            $result_data[$base_key]['col'][$key['group_id']]['score'] = $key['score'] !== null ? $key['score'] : null;
                            $result_data[$base_key]['col'][$key['group_id']]['quantity'] = $key['quantity'];
                            $result_data = $this->formatCompare($result_data, $base_key);
                        }
                    }
                }
            } else if ($this->params['x'] === 'wave' && isset($this->filters['period']['id']) && $this->filters['period']['id'] === 'cumulative') {
                $r = [];
                foreach ($this->key_x as $x) {
                    if ($this->bonus)
                        $view = 'show_scoring_multi_' . $this->bonus;
                    else
                        $view = 'show_scoring_multi_';
                    $result = \DB::table($view . $this
                        ->user
                        ->current_society_id)
                        ->selectRaw('
            CASE WHEN SUM(' . $this->score_method . 'weight) > 0 THEN
            SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
            ELSE null
            END AS score,
            criteria_id as id,
            COUNT(distinct wave_target_id) as quantity,
            ' . $x['id'] . ' AS group_id');
                    $result = GraphFilterController::_addFilters($result, $this->filters);
                    $result = $result->groupBy('criteria_id');
                    $r[] = $result->get();
                }
                $result = $r;
                foreach ($result as $groups) {
                    foreach ($groups as $key) {
                        $base_key = array_search($key['id'], array_column($result_data, 'id'));
                        if (false !== $base_key) {
                            $result_data[$base_key]['col'][$key['group_id']]['score'] = $key['score'] !== null ? $key['score'] : null;
                            $result_data[$base_key]['col'][$key['group_id']]['quantity'] = $key['quantity'];
                            $result_data = $this->formatCompare($result_data, $base_key);
                        }
                    }
                }
            } else {

                if ($this->params['x']) {
                    $group_by = $id_value = $this->params['x'] . '_id';
                } else {
                    $id_value = 0;
                }
                if ($this->params['x'] === 'question_row_name') {
                    $id_value = 'question_row_name';
                    $group_by = 'question_row_name';
                }
                if ($this->bonus)
                    $view = 'show_scoring_multi_' . $this->bonus . 'with_text_';
                else
                    $view = 'show_scoring_multi_with_text_';
                $result = \DB::table($view . $this
                    ->user
                    ->current_society_id)
                    ->selectRaw(
                        $id_value . ',
        criteria_id as id,
        criteria_name as name,
        CASE WHEN SUM(' . $this->score_method . 'weight) > 0 THEN
        SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
        ELSE null
        END AS score,
        COUNT(distinct wave_target_id) as quantity
        '
                    );
                if ($this->filters['base-min'] && $this->filters['base-min'] > 0) {
                    $result = $result->havingRaw('COUNT(distinct wave_target_id) >= ' . $this->filters['base-min']);
                }
                if ($group_by) {
                    $result = $result->groupBy('criteria_id', $group_by, 'criteria_name');
                } else {
                    $result = $result->groupBy('criteria_id', 'criteria_name');
                }
                $result = $result->orderBy('criteria_id');
                $result = GraphFilterController::_addFilters($result, $this->filters);
                $result = $result->get();
                if ($this->params['x']) {
                    foreach ($result as $key) {
                        $base_key = array_search($key['id'], array_column($result_data, 'id'));

                        if (false !== $base_key) {
                            $result_data[$base_key]['col'][$key[$id_value]]['score'] = $key['score'] !== null ? $key['score'] : null;
                            $result_data[$base_key]['col'][$key[$id_value]]['quantity'] = $key['quantity'];
                            $result_data = $this->formatCompare($result_data, $base_key);
                        }
                    }
                }
            }
            if (is_array($this->filters['wave']) && count($this->filters['wave']) == 1) {
                //get score of previous wave
                if ($result_data) {
                    $this->filters['compare_to_period'] = isset($this->filters['compare_to_period']) ? $this->filters['compare_to_period'] : ["id" => null];
                    $save_compare_to_period = $this->filters['compare_to_period'];
                    $this->filters['compare_to_period']['id'] = 1;
                    $save_wave = $this->filters['wave'];
                    $this->filters['wave'] = $this->previousWave($this->filters, $this->filters['wave']);
                    if (is_array($this->filters['wave']) && count($this->filters['wave']) > 0) {
                        $result = $this->show_scoring_multi('get', 'show_scoring_multi_' . $this->bonus . $this->user->current_society_id, null, null, 'criteria');
                        foreach ($result_data as &$key) {
                            $base_key = array_search($key['id'], array_column($result, 'id'));
                            if (false !== $base_key && $key['score'] !== null && $result[$base_key]['score'] !== null) {
                                $key['progress'] = round($key['score'] - $result[$base_key]['score']);
                            } else {
                                $key['progress'] = null;
                            }
                        }
                    }
                    $this->filters['compare_to_period'] = $save_compare_to_period;
                    $this->filters['wave'] = $save_wave;
                }
            }



            //update for compare
            Cache::put($cachekey, $result_data, $this->ttl_cache);
            return $result_data;
        });


        return $result_data;
    }

    public function OrderCriteria($survey_id)
    {
        if (!isset($this->filters['survey'])) {
            $this->filters['survey'] = [];
        }
        $save_survey = $this->filters['survey'];
        $cachekey = CacheHelper::SetCacheKey('OrderCriteria', null, [$survey_id, $this->filters]);
        $order = Cache::get($cachekey . $this->disable_cache, function () use ($survey_id, $cachekey) {

            //temp update survey_id
            $this->filters['survey'] = $survey_id;
            $sequenceFirstLevel = SurveyItem::with('children')->where('survey_id', $survey_id)->whereNull('parent_id')
                ->orderBy('order')
                ->retrieveAll()
                ->toArray();;
            $this->getSequenceLine($sequenceFirstLevel);
            $order = array_unique($this->criteria_order);
            Cache::Put($cachekey, $order, $this->ttl_cache);
            return $order;
        });
        $this->filters['survey'] = $save_survey;
        return $order;
    }

    public function resultData($order)
    {
        $cachekey = CacheHelper::SetCacheKey('resultData', $this->user, [$order, $this->score_method, $this->filters]);
        $result = Cache::get($cachekey . $this->disable_cache, function () use ($cachekey) {

            $r = \DB::table('show_criteria_' . $this
                ->user
                ->current_society_id)
                ->selectRaw('
            criteria_id,
            criteria_name,
            type,
            CASE WHEN SUM(' . $this->score_method . 'weight) > 0 THEN
                SUM(' . $this->score_method . 'score) / SUM(CAST(' . $this->score_method . 'weight AS FLOAT))
            ELSE null
            END AS score,
            count(distinct wave_target_id) as count
            ')
                ->where('display_report', true)
                ->where(function ($q) {
                    $q->where('na', false)->orWhereNull('na');
                });
            if ($this->filters['base-min'] && $this->filters['base-min'] > 0) {
                $r = $r->havingRaw('COUNT(distinct wave_target_id) >= ' . $this->filters['base-min']);
            }
            $r = $r->groupBy('criteria_id', 'criteria_name', 'type');

            $r = GraphFilterController::_addFilters($r, $this->filters);
            Cache::Put($cachekey, $r->get(), $this->ttl_cache);

            return $r->get();
        });
        $result_data = array();
        if ($result) {
            foreach ($order as $r) {
                if (false === array_search($r, array_column($result_data, 'criteria_id'))) {
                    $data['id'] = $r;
                    $base_key = array_search($r, array_column($result, 'criteria_id'));
                    $data['count'] = $result[$base_key]['count'];
                    $data['type'] = $result[$base_key]['type'];
                    $data['name'] = $result[$base_key]['criteria_name'];
                    if ($result[$base_key]['score'] !== null) {
                        $result[$base_key]['type'] === 'satisfaction' || $result[$base_key]['type'] === 'number' ? $round = 1 : $round = 0;
                        $data['score'] = round($result[$base_key]['score'], $round);
                    } else {
                        $data['score'] = null;
                    }
                    if ($base_key !== false) {
                        array_push($result_data, $data);
                    }
                }
            }
        }

        return $result_data;
    }

    public function SequenceCriteria($survey_id)
    {
        $cachekey = CacheHelper::SetCacheKey('SequenceCriteria', $this->user, [$survey_id]);
        $sequencecriteria = Cache::get($cachekey . $this->disable_cache, function () use ($survey_id, $cachekey) {
            $result = SurveyItem::select('item_id', 'criteria_id', 'parent_id')->where('type', 'question')->whereNotNull('criteria_id')
                ->where('survey_id', $survey_id)->get();
            $sequencecriteria = [];
            foreach ($result as $r) {
                //find sequence info
                $seq = DB::table('survey_item')->select('sequence.name')
                    ->where('survey_item.id', $r['parent_id'])->join('sequence', 'survey_item.item_id', '=', 'sequence.id')
                    ->first();
                if ($this->checkJson($seq['name']))
                    $sequencecriteria[$r['criteria_id']][$r['item_id']][] = $this->checkJson($seq['name']);
            }
            Cache::Put($cachekey, $sequencecriteria, $this->ttl_cache);
            return $sequencecriteria;
        });

        return $sequencecriteria;
    }

    public function image($params, $user, $export = false)
    {
        $this->params = $params;
        $this->user = $user;
        $this->filters = $this->params['filters'];
        $this->filters['general']['answer'] = $this->_GetAllQuestionsasfilter();
        $this->filters['general']['shop'] = $this->_getShopsFromFilters();


        $this->filters = GraphTemplateService::getFilter($this->filters, $this
            ->user
            ->current_society_id);



        $result = DB::table('show_images')->orderby('id', 'desc')
            ->limit(200);
        $result = GraphFilterController::_addFilters($result, $this->filters);
        if (isset($this->filters['score']) && (count($this->filters['score']) > 0)) {
            foreach ($this->filters['score'] as $score) {
                if ($score['id'] === 0) {
                    $result = $result->whereNull('response_value');
                }
                if ($score['id'] === 1) {
                    $result = $result->where('response_value', 100);
                }
                if ($score['id'] === 2) {
                    $result = $result->where('response_value', 0)
                        ->whereNotNull('response_value');
                }
            }
        }
        $result = $result->get();
        if ($export) {
            $f = new ExportImageController($this->user);
            $f->export_image($result);
        }
        foreach ($result as &$ai) {
            $ai['question_name'] = $this->checkJson($ai['question_name']);
            $ai['sequence_name'] = $this->checkJson($ai['sequence_name']);
            $ai['answer'] = $this->checkJson($ai['answer']);
            $ai['url'] = str_replace('api.smice.com', 'ik.imagekit.io/smice', $ai['url']);
        }
        return $result;
    }

    public function getGoal($seq = null)
    {

        //
        //lecture du score pour la ligne au global
        $cachekey = CacheHelper::SetCacheKey('get_Goal', $this->user, [$seq, $this->filters]);
        $goal = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey, $seq) {
            if ($seq)
                $this->filters['sequence'] = $seq;
            $goal = goal::select('score');
            if ($this->filters['sequence'])
                $goal = $goal->where(function ($query) {
                    $query->whereIn('sequence_id', ArrayHelper::getIds($this->filters['sequence']));
                });
            else
                $goal = $goal->whereNull('sequence_id');
            if ($this->filters['criteria'])
                $goal = $goal->where(function ($query) {
                    $query->whereIn('criteria_id', ArrayHelper::getIds($this->filters['criteria']));
                });
            else
                $goal = $goal->whereNull('criteria_id');
            if ($this->filters['program']) {
                $ids = ArrayHelper::getIds($this->filters['program']);
                if ($ids && count($ids) === 1) {
                    $ids = array_flatten($ids);
                    $goal = $goal->where(function ($query) use ($ids) {
                        $query->where('program_id', $ids)
                            ->orWhereNull('program_id');
                    });
                } else
                    $goal = $goal->whereNull('program_id');
            } else {
                $goal = $goal->whereNull('program_id');
            }
            if ($this->filters['scenario'])
                $goal = $goal->where(function ($query) {
                    $query->whereIn('scenario_id', ArrayHelper::getIds($this->filters['scenario']))
                        ->orWhereNull('scenario_id');
                });
            else
                $goal = $goal->whereNull('scenario_id');
            if ($this->filters['theme'])
                $goal = $goal->where(function ($query) {
                    $query->whereIn('theme_id', ArrayHelper::getIds($this->filters['theme']))
                        ->orWhereNull('theme_id');
                });
            else
                $goal = $goal->whereNull('theme_id');
            if ($this->filters['question'])
                $goal = $goal->where(function ($query) {
                    $query->where('question_id', ArrayHelper::getIds($this->filters['question']));
                });
            else
                $goal = $goal->whereNull('question_id');
            $s_id = ArrayHelper::getIds($this->filters['survey']);
            if (!$s_id) //if no survey selected
                $s_id = $this->survey_id;
            $goal = $goal->where(function ($query) use ($s_id) {
                $query->where('survey_id', $s_id)
                    ->orWhereNull('survey_id');
            });
            $goal = $goal->first();
            Cache::Put($cachekey, $goal, $this->ttl_cache);
            return $goal;
        });
        return $goal['score'];
    }

    public function setGoal()
    {
        //lecture du score pour la ligne au global
        $cachekey = CacheHelper::SetCacheKey('setGoal', $this->user, [$this->params['y'], $this->filters, $this->survey_id]);

        $goal = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey) {
            $goal = [];
            $key = $this->params['y'];
            if ($key === 'group')
                $key = 'axe';
            if (($key === 'job') || ($key === 'mission') || ($key === 'question_row') || ($key === 'criteria_a') || ($key === 'criteria_b'))
                $key = null;
            $goal = goal::where('survey_id', $this->survey_id);
            if ($key)
                $goal = $goal->whereNotNull($key . '_id');
            if ($this->params['y'] !== 'criteria') {
                if ($this->filters['criteria'])
                    $goal = $goal->whereIn('criteria_id', ArrayHelper::getIds($this->filters['criteria']));
                else
                    $goal = $goal->whereNull('criteria_id');
            }
            if ($this->params['y'] !== 'scenario') {
                if ($this->filters['scenario'])
                    $goal = $goal->whereIn('scenario_id', ArrayHelper::getIds($this->filters['scenario']));
                else
                    $goal = $goal->whereNull('scenario_id');
            }
            if ($this->params['y'] !== 'theme') {
                if ($this->filters['theme'])
                    $goal = $goal->whereIn('theme_id', ArrayHelper::getIds($this->filters['theme']));
                else
                    $goal = $goal->whereNull('theme_id');
            }
            if ($this->params['y'] !== 'sequence') {
                if ($this->filters['sequence'])
                    $goal = $goal->whereIn('sequence_id', ArrayHelper::getIds($this->filters['sequence']));
                else
                    $goal = $goal->whereNull('sequence_id');
            }
            if ($this->filters['program']) {
                $ids = ArrayHelper::getIds($this->filters['program']);
                if ($ids && count($ids) === 1) {
                    $ids = array_flatten($ids);
                    $goal = $goal->where(function ($query) use ($ids) {
                        $query->where('program_id', $ids)
                            ->orWhereNull('program_id');
                    });
                } else
                    $goal = $goal->whereNull('program_id');
            } else {
                $goal = $goal->whereNull('program_id');
            }
            if ($this->filters['question'])
                $goal = $goal->whereIn('question_id', ArrayHelper::getIds($this->filters['question']));
            else
                $goal = $goal->whereNull('question_id');
            $goal = $goal->get();
            Cache::Put($cachekey, $goal, $this->ttl_cache);
            return $goal;
        });
        return $goal;
    }

    public function setColumn()
    {
        $columnDefs = [];

        if (isset($this->filters['compare_to_period']) && count($this->filters['compare_to_period']) > 0 && !is_null($this->filters['compare_to_period']['id'])) {
            $type = 'object';
        } else {
            $type = 'percentage';
        }
        $name = $this->getAlias($this->params['y']);
        $global = $this->getGlobal();
        if (($this->params['y'] !== 'user') && ($this->params['y'] !== 'connection')) {
            $name = $name . "  - Score global : " . round($global['score']) . " %";
        }
        //Column 1 Title
        array_push($columnDefs, $this->_uigrid($name, 'expand'));

        //Column 2 Base
        if (!$this->hide_bases)
            array_push($columnDefs, $this->_uigrid('Base'));

        if ($this->params['x'] == 'wave') {
            $type = 'percentage';
        }
        if ($this->params['y'] == 'user' || $this->params['y'] == 'connection') {
            $type = 'string';
        }

        if (!$this->hide_goals) {
            array_push($columnDefs, $this->_uigrid('Objectif', 'goal'));
        }
        //if (count($this->goal) == 0) {
        //    array_push($columnDefs, $this->_uigrid('Score', 'percentage'));
        //}
        //More than one wave : add 'Score cumulé' column
        if (($this->params['y'] !== 'user') && ($this->params['y'] !== 'connection')) {
            if (count($this->key_x) > 1)
                array_push($columnDefs, $this->_uigrid(config('dictionary.result.cumulative_score')[$this->user->language->code], 'percentage'));
        }
        //Add one column by x data (wave for criteria page)
        foreach ($this->key_x as $k) {
            //change to date if colun are wave_target
            $name = null;
            if ($this->params['x'] === 'wave_target') {
                $name = WaveTarget::with('shop')->select('visit_date', 'shop_id')->where('id', $k['id'])->first();
                if ($name->visit_date)
                    $date = strtotime($name->visit_date);
                $name = $name->shop->name . " - Mission " . $k['id'] . " du " . date('d/m/Y', $date);
            } else {
                $name = $this->checkJson($k);
                //if (isset($k['score']) && (count($this->key_x) > 1))
                //$name = $name . " " . round($k['score']) . "%";
            }
            array_push($columnDefs, $this->_uigrid($name, $type));
        }

        //Only on wave
        if ((count($this->key_x) == 1) && ($this->params['y'] == 'criteria')) {
            array_push($columnDefs, $this->_uigrid('Progression', 'progression'));
        }

        //Filter compare to period activate : add column to compare previons period
        if (isset($this->filters['compare_to_period']) && isset($this->filters['compare_to_period']['id'])) {
            //get compare name
            $waves_name = $this->previousWave();
            $wave_name = null;
            foreach ($waves_name as $wn) {
                $wave_name .= "/" . $wn['name'];
            }
            array_push($columnDefs, $this->_uigrid($wave_name, 'percentage'));
        }

        //Filter compare to axe activate : add column to compare with other axe
        if (isset($this->filters['compare_to_axe']) && count($this->filters['compare_to_axe']) > 0) {
            //get compare name
            foreach ($this->filters['compare_to_axe'] as $compare_axe) {
                array_push($columnDefs, $this->_uigrid($compare_axe['name'], 'percentage'));
            }
        }
        //Filter compare to axe activate : add column to compare with other axe
        if (isset($this->filters['compare_to_shop']) && count($this->filters['compare_to_shop']) > 0) {
            //get compare name
            foreach ($this->filters['compare_to_shop'] as $compare_shop) {
                array_push($columnDefs, $this->_uigrid($compare_shop['name'], 'percentage'));
            }
        }

        //add evolution column
        // if ($this->params['y'] == 'criteria') {
        //     array_push($columnDefs, $this->_uigrid('Courbe évolution', 'evolution-button'));
        // }

        //add nc shop
        //if ((count($this->key_x) == 1) && ($this->params['y'] == 'criteria')) {
        //    array_push($columnDefs, $this->_uigrid('Nb PDV non conformes'));
        //}

        //add action plan column only for criteria
        //if (($this->params['y'] == 'criteria')) {
        //    array_push($columnDefs, $this->_uigrid('Actions', 'action-plan-button'));
        //}
        return $columnDefs;
    }

    private function getScoreColor($score)
    {
        if (!$this->hide_goals) {
            $goal = $this->getGoal();
            if ($goal && $score) {

                if ($score >= $goal)
                    return 'green';
                else if ($score > $goal - 10)
                    return 'orange';
                else
                    return 'red';
            } else
                return 'nocolor';
        } else {
            if ($score < 50)
                return 'red';
            if ($score < 75)
                return 'orange';
            else return 'green';
        }
        return 'nocolor';
    }

    public function colAddFilters($col, $x, $x_id, $y, $y_id, $question_id = false)
    {
        $color = 'nocolor';
        if (array_key_exists('type', $col)) {
            if ($col['type'] !== 'satisfaction' && $col['type'] !== 'number') {
                $color = $this->getScoreColor($col['value']);
            }
        }
        $params = [
            'x' => $x,
            'y' => $y,
            'id_x' => $x_id,
            'id_y' =>  $y_id,
            'color' => $color
        ];

        if ($question_id) {
            $params['question_id'] = $question_id;
        }

        return array_merge($col, $params);
    }

    public function colAddActionplan($col, $criteria_id, $wave_id)
    {
        if (count($this->filters['shop']) === 1) {
            $shop_id = $this->filters['shop'][0];
        } else {
            $shop_id = null;
        }
        if (count($this->filters['axes']) === 1) {
            $axe_id = $this->filters['axes'][0];
        } else {
            $axe_id = null;
        }
        $params = [
            'criteria_id' => $criteria_id,
            'actionplan' => true,
            'wave_id' => $wave_id,
            'shop_id' => $shop_id,
            'axe_id' => $axe_id
        ];

        //action must be available if we list criteria, we hide it if more than shop or axe selected
        if (!$this->subCriteriaModelId && $this->params['y'] !== 'criteria' || (is_array($this->filters['wave']) && count($this->filters['wave']) === 0)) {
            return $col;
        } else {
            return array_merge($col, $params);
        }
    }

    public function addLine($source, $q_id, $id, $name, $type, $base, $global_score, $progress = null,  $col = null, $action_plan = null, $photo = null)
    {
        //question_id = $q_id
        //criteria_id = $id
        $weight_less = false;
        if (isset($this->item_score_null[$id][$q_id])) {
            if ($this->item_score_null[$id][$q_id] === 0)
                $weight_less = true;
        }

        $savefilters = $this->filters;
        $this->filters[$source] = [$id];
        isset($type) && ($type === 'satisfaction' || $type === 'number') ? $round = 1 : $round = 0;

        //name
        $i = 0;
        //find
        $base_key = false;
        if ($source === 'criteria') {
            $base_key = array_search($id, array_column($this->criteria_group, 'id')); //find info on criteria
            if ($base_key !== false) //find picture of criteria_group link to criteria
                $base_key = array_search($this->criteria_group[$base_key]['criteria_group_id'], array_column($this->criteria_group_picture, 'id'));
        }

        $q['col' . $i] = [ // add data for first column : sequence name / question name
            'value' => $this->checkJson($name),
            'picture' => $base_key !== false ? $this->criteria_group_picture[$base_key]['picture'] : null,
            'weight_less' => $weight_less
        ];

        // ajout des infos pour pouvoir cliquer sur le nom
        if ($this->subCriteriaModelId) {
            $q['col' . $i] = $this->colAddFilters($q['col' . $i], null, null, $this->subCriteriaModelSource, $this->subCriteriaModelId, $q_id);
        } else {
            $q['col' . $i] = $this->colAddFilters($q['col' . $i], null, null, $source, $id, $q_id);
        }

        //base
        if (!$this->hide_bases) {
            $i++;
            $q['col' . $i] = ['value' => $base];
        }
        if (isset($this->request_type) && $this->request_type === 'referencial') {
            $i++;
            $q['col' . $i] = [ // add data for first column : sequence name / question name
                'value' => null,
            ];
        }


        //goal
        $goal = null;
        if (!$this->hide_goals) {
            $i++;
            $goal = $this->getGoal();
            $q['col' . $i] = ['value' => $goal];
        }

        //score
        if (($this->params['y'] !== 'user') && ($this->params['x'] !== 'connection')) {

            if (count($this->key_x) > 1) {
                $i++;
                $q['col' . $i] = [
                    'value' => $global_score !== null ? round($global_score, $round) : null,
                    'type' => $type,
                    'info' => $base,
                    'base' => $base
                ];
                if ($this->subCriteriaModelId)
                    $q['col' . $i] = $this->colAddFilters($q['col' . $i], null, null, $this->subCriteriaModelSource, $this->subCriteriaModelId, $q_id);
                else
                    $q['col' . $i] = $this->colAddFilters($q['col' . $i], null, null, $source, $id, $q_id);
            }
        }

        //ajout des colonnes aprés le score cumulé
        if (($this->params['y'] == 'criteria') && ($this->params['x'] !== 'question_row_name')) {
            //lecture du score pour toutes les autres colonnes (group by $this->params['x'])
            //$score = $this->getScoreFromWaveTargets($this->params['x']);
            //pour chaque colonne on affiches le score sinon null
            foreach ($this->key_x as $k => $v) {
                $i++;
                //if ((isset($score[$v['id']['score']])) && (isset($score[$v['id']]['value']))) {
                //    $q["col" . $i] =  $score[$v['id']['score']];
                //} else if (isset($score[$v['id']]['score'])) {
                //    $q["col" . $i] =  ["value" => $score[$v['id']]['score'] !== null ? $score[$v['id']]['score'] : null, "type" => $score[$v['id']]['type']];
                //    $q["col" . $i] = $this->colAddFilters($q["col" . $i], $this->params['x'], $v['id'], $source, $id, $q_id);
                //} else {
                //    $q["col" . $i] = ["value" => null];
                //}
                if (isset($col[$v['id']]) && (is_array($col[$v['id']]))) {
                    // var_dump($col[$v['id']]);
                    $q["col" . $i] = [
                        "value" => $col[$v['id']]['score'] !== null ? round($col[$v['id']]['score'], $round) : null,
                        "type" => $type,
                        "info" => isset($col[$v['id']]['quantity']) ? $col[$v['id']]['quantity'] : null,
                        "base" => isset($col[$v['id']]['quantity']) ? $col[$v['id']]['quantity'] : null,

                    ];
                    $q["col" . $i] = $this->colAddFilters($q["col" . $i], $this->params['x'], $v['id'], $source, $id, $q_id);
                } else if (isset($col[$v['id']])) {
                    // var_dump($col[$v['id']]);
                    $q["col" . $i] =  [
                        "value" => $col[$v['id']] !== null ? round($col[$v['id']], $round) : null,
                        "type" => $type,
                        "base" => null
                    ];
                    $q["col" . $i] = $this->colAddFilters($q["col" . $i], $this->params['x'], $v['id'], $source, $id, $q_id);
                } else {
                    $q['col' . $i] = ['value' => null];
                }


                //$q["col" . $i] = $this->colAddFilters($q["col" . $i], $question_id, $v['id'], $question_id);
            }
        } else if ($this->params['x'] === 'question_row_name') {

            if (!empty($col)) {
                foreach ($col as $re => $r) {
                    $res[$re] = [
                        'type' => isset($r['type']) ? $r['type'] : null,
                        'score' => ($r['score'] !== null ? round($r['score'], $round) : null),
                        'info' => (isset($r['info']) ? $r['info'] : null),
                        'total_answer' => (isset($r['total_answer']) ? $r['total_answer'] : null),
                        'quantity' => (isset($r['quantity_row_id']) ? $r['quantity_row_id'] : null),
                        'base' => (isset($r['quantity_row_id']) ? $r['quantity_row_id'] : null)
                    ];
                }
            } else {
                $res = null;
            }
            foreach ($this->key_x as $k) { //each wave
                $i++;
                if (isset($res[$k['id']])) {
                    if (intval($res[$k['id']]['total_answer']) === 0)
                        $res[$k['id']]['total_answer'] = 1;
                    $s = $res[$k['id']]['quantity'] / $res[$k['id']]['total_answer'] * 100;
                    $q["col" . $i] = ["value" => round($s, $round)];
                    $q["col" . $i] = $this->colAddFilters($q["col" . $i], $this->params['x'], $k['id'], $source, $id, $q_id);
                } else {
                    $q['col' . $i] = ["value" => null];
                }
            }
        } else {
            foreach ($this->key_x as $k) {
                //score cumulé de la question
                $i++;
                if (isset($col[$k['id']]) && is_array($col[$k['id']]) && array_key_exists('score', $col[$k['id']]))
                    $q["col" . $i] = [
                        "value" => isset($col[$k['id']]['score']) && $col[$k['id']]['score'] !== null ? round($col[$k['id']]['score'], $round) : null,
                        "info" => isset($col[$k['id']]['quantity']) ? $col[$k['id']]['quantity'] : null,
                        "type" => $type,
                        "base" => isset($col[$k['id']]['quantity']) ? $col[$k['id']]['quantity'] : null

                    ];
                else
                    $q["col" . $i] = [
                        "value" => isset($col[$k['id']]) && $col[$k['id']] !== null ? round($col[$k['id']], $round) : null,
                        "info" => isset($col[$k['id']]['quantity']) ? $col[$k['id']]['quantity'] : null,
                        "type" => $type,
                        "base" => null
                    ];

                if ($this->subCriteriaModelId) {
                    $q["col" . $i] = $this->colAddFilters($q["col" . $i], $this->params['x'], $k['id'], $this->subCriteriaModelSource, $this->subCriteriaModelId, $q_id);
                } else
                    $q["col" . $i] = $this->colAddFilters($q["col" . $i], $this->params['x'], $k['id'], $source, $id, $q_id);
                if ($source == 'criteria') {
                    $q["col" . $i] = $this->colAddActionplan($q["col" . $i], $id, $k['id']);
                }
            }
        }
        if ((count($this->key_x) === 1) && ($this->params['y'] == 'criteria')) {
            //progress
            $i++;
            $q["col" . $i] = [ // progress
                "value" => $progress ? $progress : null
            ];
        }

        //axe compare
        if (isset($this->filters['compare_to_period']['id'])) {
            $i++;
            if (isset($source['compare'])) {
                $q["col" . $i] = [
                    "value" => round($source['compare'], $round),
                    "type" => $type,
                    "base" => null
                ];
            }
        }
        if (isset($this->filters['compare_to_axe']) && count($this->filters['compare_to_axe']) > 0) {
            foreach ($this->filters['compare_to_axe'] as $compare_axe) {
                $i++;
                if (isset($col['axe_' . $compare_axe['id']])) {
                    $q["col" . $i] = ["value" => $col['axe_' . $compare_axe['id']]['score'] ? round($col['axe_' . $compare_axe['id']]['score'], $round) : null, "type" => $type];
                } else {
                    $q["col" . $i] = ["value" => null];
                }
            }
        }

        if (isset($this->filters['compare_to_shop']) && count($this->filters['compare_to_shop']) > 0) {
            foreach ($this->filters['compare_to_shop'] as $compare_shop) {
                $i++;
                if (isset($col['shop_' . $compare_shop['id']])) {
                    $q["col" . $i] = [
                        "value" => $col['shop_' . $compare_shop['id']]['score'] ? round($col['shop_' . $compare_shop['id']]['score'], $round) : null,
                        "type" => $type,
                        "base" => null
                    ];
                } else {
                    $q["col" . $i] = ["value" => null];
                }
            }
        }
        //ajout du graph pour l'historique
        // if ($this->params['y'] == 'criteria') {
        //     $i++;
        //     $q["col" . $i] = [
        //         "id" => $id,
        //         "type" => $source
        //     ];
        // }

        //nc
        //if ((count($this->key_x) == 1) && ($this->params['y'] == 'criteria')) {
        //    $i++;
        //    $nb_shop = 0;
        //    $nb_shop_checked = false;
        //    foreach ($this->question_shop as $shop) {
        //        if ($shop['question_id'] == $q_id && $shop['score'] < 100 && $shop['score'] !== null) {
        //            $nb_shop++;
        //            $nb_shop_checked = true;
        //        }
        //    }
        //    $q["col" . $i] = ["value" => !$nb_shop_checked ? "-" : $nb_shop];
        //}


        //$i++;
        //if (($this->params['y'] == 'criteria')) {
        //    $q["col" . $i] = [ //action
        //        "value" => ""
        //    ];
        //}


        $this->filters = $savefilters;

        //base minimum
        if ($this->filters['base-min'] && $this->filters['base-min'] > 0) {
            if ($base < $this->filters['base-min'])
                return null;
        }


        if (isset($this->filters['goal']) && $this->filters['goal'] !== "") {
            $this->filters['goal'] = intval($this->filters['goal']);
            if ($goal > 0 && $goal - $global_score > $this->filters['goal']) {
                return $q;
            } else {
                return null;
            }
        }
        return $q;
    }

    public function _getGlobalFromWaveTargets($params, $user_id, $livedata = false)
    {
        $this->livedata = $livedata;
        if (is_int($user_id))
            $this->user = User::find($user_id);
        else
            $this->user = $user_id;
        $this->filters = GraphTemplateService::getFilter($params['filters'], $this
            ->user
            ->current_society_id);
        $this->params = $params;
        $this->bonus = '';
        $this->score_method = '';
        //get score on level question.
        $level_question = ['criteria', 'theme', 'job', 'criteria_a', 'criteria_b'];
        if ((in_array($this->params['y'], $level_question)) || (in_array($this->params['x'], $level_question))) {
            $this->score_method = 'question_';
            $this->bonus = 'without_bonus_';
        }
        //check if some filter that affect the way of score calculation are used
        foreach ($level_question as $value) {
            if ($this->filters[$value]) {
                $this->score_method = 'question_';
                $this->bonus = 'without_bonus_';
            }
        }

        return $this->getGlobalFromWaveTargets();
    }

    public function  getScoreSequence($sequenceFirstLevel, $survey_id)
    {
        $line = $xAxis = [];

        foreach ($sequenceFirstLevel as $k => $sequence) {
            //
            //lecture du score pour la ligne au global
            $cachekey = CacheHelper::SetCacheKey('addSequenceLine_', $this->user, [$survey_id, $sequence['item_id']]);
            $subSequences = Cache::Get($cachekey . $this->disable_cache, function () use ($cachekey, $survey_id, $sequence) {
                $r = $subSequences = SurveyItem::with('children')->where('survey_id', $survey_id)->where('item_id', $sequence['item_id'])->where('display_report', true)->orderBy('order')
                    ->retrieveAll()
                    ->toArray();
                Cache::Put($cachekey, $r, $this->ttl_cache);
                return $r;
            });


            //Recursivité de la sequence
            if (!empty($subSequences)) {
                foreach ($subSequences as $val) {
                    if ($val['children']) {
                        $allchild = $this->GetChild($subSequences);
                        $allchild = array_flatten($allchild);
                        $line[] = $this->AddSequenceScoreOnly($allchild, $val['item_id']);
                    } else {
                        //get all seq
                        $line[] = $this->AddSequenceScoreOnly([$val['item_id']], $val['item_id']);
                    }
                }
            }
        }
        return $line;
    }

    function AddSequenceScoreOnly($formatSequence, $current_seq)
    {
        $data = [];
        $this->filter_y = 'sequence';
        $name = sequence::find($current_seq);
        $name = $this->checkJson($name['name']);
        $save_filter = $this->filters[$this->params['y']];

        //lecture du score pour la colonne global
        $this->filters[$this->params['y']] = ArrayHelper::getIds($formatSequence);
        $score = $this->getGlobalSequenceFromWaveTargets(ArrayHelper::getIds($formatSequence));
        //pour chaque colonne on affiches le score sinon null
        $this->filters[$this->params['y']] = $save_filter;
        $score['id'] = $current_seq;
        return $score;
    }
}
