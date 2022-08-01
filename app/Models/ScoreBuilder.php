<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class Score
 * @package App\Models
 *
 * The Score class will help you calculate scores for the surveys
 */
class ScoreBuilder extends Builder
{
    /**
     * The view from which we select the results
     * @var string
     */
    public $from            = 'show_scoring';

    /**
     * The user making the request
     * @var null|User
     */
    public static $user      = null;

    /**
     * The language in which the query will be translated
     * @var null
     */
    private $language       = null;

    /**
     * ScoreBuilder constructor.
     * @param array $params
     * @param null $from
     */
    public function         __construct(array $params, $from = null)
    {
        $conn               = DB::connection();

        parent::__construct($conn, $conn->getQueryGrammar(), $conn->getPostProcessor());

        $this->_setTable($from);
        $this->_setLanguage(array_get($params, 'language'));
        $this->_filter($params);
    }

    /**
     * Set the user making the request.
     * @param User $user
     */
    public static function  setUser(User $user)
    {
        static::$user       = $user;
    }

    /**
     * Set the translation language.
     * @param $language
     */
    private function        _setLanguage($language)
    {
        if (isset($language) && is_string($language) && Language::where('code', $language)->first())
            $this->language = $language;
        else
            $this->language = static::$user->language->code;
    }

    /**
     * Set the table on which the query will be executed.
     * @param $table
     */
    private function        _setTable($table)
    {
        if ($table && is_string($table))
            $this->from($table);
    }

    /**
     * Add an SQL whereIn condition depending on scenarios id. Only the scores
     * for the given scenarios will be taken in account.
     * @param $scenarios
     */
    private function        _setScenarios($scenarios)
    {
        if ($scenarios)
        {
            Validator::make(
                ['scenarios' => $scenarios],
                ['scenarios' => 'int_array|read:scenarios']
            )->passOrDie();
            $this->whereIn('scenario_id', $scenarios);
        }
    }

    /**
     * Add an SQL whereIn condition depending on shops id. Only the scores
     * for the given shops will be taken in account.
     * @param $shops
     */
    private function        _setShops($shops)
    {
        if ($shops)
        {
            Validator::make(
                ['shops' => $shops],
                ['shops' => 'int_array|read:shops']
            )->passOrDie();
            $this->whereIn('shop_id', $shops);
        }
    }

    /**
     * Add an SQL whereIn condition depending on missions id. Only the scores
     * for the given missions will be taken in account.
     * @param $missions
     */
    private function        _setMissions($missions)
    {
        if ($missions)
        {
            Validator::make(
                ['missions' => $missions],
                ['missions' => 'int_array|read:missions']
            )->passOrDie();
            $this->whereIn('mission_id', $missions);
        }
    }

    /**
     * Group the results to format an array like this:
     *
     * - society
     *      - shop
     *          - program
     *              - wave
     *                  - scenario
     *                      - mission
     * - ...
     *
     * - society n°X
     *      - shop n°X
     *          - program n°X
     *              - wave n°X
     *                  - scenario n°X
     *                      - mission n°X
     *
     * The array will start at the first level of imbrication that the user asked for,
     * so for example if the user selects only X programs, the array will start at program.
     *
     * @param $items
     * @param $grouped_params
     * @return mixed
     */
    private function         _groupResults($items, $grouped_params)
    {
        $grouping_param     = array_shift($grouped_params);
        $items              = $items->groupBy($grouping_param);

        if (!empty($grouped_params))
        {
            foreach ($items as $key => $item)
                $items[$key] = $this->_groupResults($item, $grouped_params);
        }
        else
            $items->transform(function($item)
            {
                return ['score' => $item->first()['score']];
            });

        return $items;
    }

    /**
     * Apply the WHERE IN to narrow the results and if required the GROUP BY to group the results
     *
     * @param $param
     * @param $id
     */
    private function        _narrowResults($param, $id)
    {
        $this->whereIn($param .'_id', $id);

        if (!$this->grouped)
        {
            $this->addSelect($param .'_id', $param .'_name')->groupBy($param .'_id', $param .'_name');
            $this->grouped_params[] = $param .'_name';
        }
    }

    /**
     * Retrieve the global score on the demanded answers
     * Take the sequence, criteria and question weight in account
     * Group the results if required
     *
     * @return mixed
     */
    private function        _getGlobalScore()
    {
        $tmp_self           = clone $this;
        $results            = $tmp_self->selectRaw('SUM(score) / SUM(weight) as score')->get();

        if (!empty($tmp_self->grouped_params))
            $results = $this->_groupResults($results, $tmp_self->grouped_params);
        else
            $results = array_shift($results);

        return $results;
    }

    /**
     * Retrieve a score given a parameter: sequence, theme, criteria, ...
     * Take the question weight in account
     * Group the results if required
     *
     * @param $param
     * @return mixed
     */
    private function        _getOtherScore($param)
    {
        $tmp_self           = clone $this;
        $results            = $tmp_self->addSelect($param .'_id')
                                    ->selectRaw('SUM(question_score) / SUM(question_weight) as score,
                                    '. $param .'_name->>\''. $this->language .'\' as '. $param .'_name')
                                    ->whereNotNull($param .'_id')
                                    ->groupBy($param .'_id', $param .'_name')
                                    ->get();

        array_unshift($tmp_self->grouped_params, $param .'_name');
        if (!empty($tmp_self->grouped_params))
            $results = $this->_groupResults(collect($results), $tmp_self->grouped_params);

        return $results;
    }

    /**
     * Retrieve a score given a sequence
     * Take the question and criteria weight in account
     * Group the results if required
     *

     * @return mixed
     */
    private function        _getSequenceScore()
    {
        $tmp_self           = clone $this;
        $results            = $tmp_self->addSelect('sequence_id')
                                    ->selectRaw('
                                        SUM(criteria_score) / SUM(criteria_weight) as score,
                                        sequence_name->>\''. $this->language .'\' as sequence_name')
                                    ->whereNotNull('sequence_id')
                                    ->groupBy('sequence_id', 'sequence_name')
                                    ->get();
        array_unshift($tmp_self->grouped_params, 'sequence_name');
        if (!empty($tmp_self->grouped_params))
            $results = $this->_groupResults(collect($results), $tmp_self->grouped_params);

        return $results;
    }

    /**
     * Calculate all the possibles scores
     *
     * @return array
     */
    public function         calculate()
    {
        #$function           = $this->function;

        #return $this->$function();

        /*
         * Get scores
         */
        $global_scores      = $this->_getGlobalScore();
        $sequence_scores    = $this->_getSequenceScore();
        $theme_scores       = $this->_getOtherScore('theme');
        $job_scores         = $this->_getOtherScore('job');
        $criteria_scores    = $this->_getOtherScore('criteria');
        $criteria_a_scores  = $this->_getOtherScore('criteria_a');
        $criteria_b_scores  = $this->_getOtherScore('criteria_b');

        return [
            'global'     => $global_scores,
            'sequence'   => $sequence_scores,
            'theme'      => $theme_scores,
            'job'        => $job_scores,
            'criteria'   => $criteria_scores,
            'criteria_a' => $criteria_a_scores,
            'criteria_b' => $criteria_b_scores
        ];
    }

    /**
     * This scope lets you define by which ID you want to narrow the results
     *
     * @param array $params
     * @return mixed
     */
    private function    _filter(array $params)
    {
        $mission        = array_get($params, 'mission_id');
        $program        = array_get($params, 'program_id');
        $society        = array_get($params, 'society_id');
        $scenario       = array_get($params, 'scenario_id');
        $shop           = array_get($params, 'shop_id');
        $wave           = array_get($params, 'wave_id');
        $start          = array_get($params, 'date_start');
        $end            = array_get($params, 'date_end');
        $grouped        = array_get($params, 'grouped', true);
        $language       = array_get($params, 'language');
        $validator      = Validator::make(
            [
                'mission'       => $mission,
                'program'       => $program,
                'society'       => $society,
                'scenario'      => $scenario,
                'shop'          => $shop,
                'wave'          => $wave,
                'grouped'       => $grouped,
                'start'         => $start,
                'end'           => $end,
                'language'      => $language
            ],
            [
                'society'       => 'int_array|read:societies|required_with:program',
                'program'       => 'int_array|read:programs|required_with:wave',
                'wave'          => 'int_array|read:waves|required_with:mission,scenario,shop',
                'shop'          => 'int_array|read:shops',
                'mission'       => 'int_array|read:missions',
                'scenario'      => 'int_array|read:scenarios',
                'grouped'       => 'boolean',
                'start'         => 'date',
                'end'           => 'date',
                'language'      => 'string',
                'target'        => 'int|required_without_all:society,program,wave'
            ]
        );

        $validator->passOrDie();
        $this->params = $params;
        $this->grouped = $grouped;
        /*
         * We set the language of the query
         */
        if ($language && Language::where('code', $language)->first())
            $this->language = $language;

        if ($wave)
            $this->function = '_resultsForWaves';
        elseif ($program)
            $this->function = '_resultsForPrograms';
        elseif ($society)
            $this->function = '_resultsForSocieties';
        else
            $this->function = '_resultsForTarget';

        #return ;
        /*
         * Narrow the results and apply a GROUP BY in SQL if the results
         * should NOT be grouped for Front charts.
         */
        if ($society)
            $this->whereIn('society_id', $society);
            //$this->_narrowResults('society', $society);
        if ($shop)
            $this->_narrowResults('shop', $shop);
        if ($program)
            $this->_narrowResults('program', $program);
        if ($wave)
        {
            if (count($wave) === 1)
                $this->function = '_getOneWave';

            $this->whereIn('wave_id', $wave);
            //$this->_narrowResults('wave', $wave);
        }
        if ($scenario)
            $this->_narrowResults('scenario', $scenario);
        if ($mission)
            $this->_narrowResults('mission', $mission);
        if ($start)
            $this->where('visit_date', '>', $start);
        if ($end)
            $this->where('visit_date', '<', $end);

        return $this;
    }

    

    private function        _resultsForSocieties()
    {
        $results            = $this->selectRaw('
            society_id as id,
            society_name as name,
            COUNT(DISTINCT shop_id) as shops,
            COUNT(DISTINCT program_id) as programs,
            COUNT(DISTINCT wave_id) as waves,
            COUNT(DISTINCT wave_target_id) as missions
            ')
            ->whereIn('society_id', [1])
            ->groupBy('society_id', 'society_name')
            ->get();

        return $results;
    }

    /**
     * Fetch the global scores for one or several waves.
     * @param $waves_id
     * @param array $params
     * @return array|\Illuminate\Support\Collection|static
     */
    static public function          getWaveGlobalScore($waves_id, array $params)
    {
        $builder                    = new self($params);

        Validator::make(
            ['waves' => $waves_id],
            ['waves' => 'required|int_array|read:waves']
        )->passOrDie();
        $builder->_setScenarios(array_get($params, 'scenarios'));
        $builder->_setShops(array_get($params, 'shops'));
        $builder->_setMissions(array_get($params, 'missions'));

        if (count($waves_id) === 1)
            return static::_getOneWaveGlobalScore($builder, $waves_id[0], array_get($params, 'months_limit'));
        else
            return static::_getWavesGlobalScore($builder, $waves_id);
    }

    /**
     * Fetch the global score for one wave. A limit of n past months can be specified
     * to compare results with previous waves.
     * @param $builder
     * @param $wave_id
     * @param $months_limit
     * @return array|\Illuminate\Support\Collection|static
     */
    static private function         _getOneWaveGlobalScore($builder, $wave_id, $months_limit)
    {
        $months_limit               = (is_int($months_limit) && $months_limit >= 0) ? $months_limit : 0;
        $builder->selectRaw('
                    wave_id,
                    wave_name,
                    program_id,
                    SUM(score) / SUM(weight) as score,
                    date_start')
            ->groupBy('wave_id', 'wave_name', 'program_id', 'date_start')
            ->orderBy('date_start', 'desc');

        $query_one = $builder;
        $query_two = clone $builder;

        if (!($current_wave = $query_one->where('wave_id', $wave_id)->first()))
            return [];
        // 10000: to make sure the user doesn't break the time limit
        $months_limit = ($months_limit > 10000) ? 10000 : $months_limit;
        $waves = $query_two->where('program_id', $current_wave['program_id'])
            ->whereRaw("
                date_start >= (date '{$current_wave['date_start']}' - interval '{$months_limit} months')
                AND
                date_start < '{$current_wave['date_start']}'
                ")
            ->get();

        array_unshift($waves, $current_wave);
        $waves = collect($waves);
        $waves->transform(function($item)
        {
            return [
                'id' => $item['wave_id'],
                'name' => $item['wave_name'],
                'score' => $item['score'],
                'month' => Carbon::createFromFormat('Y-m-d', $item['date_start'])->format('F')
            ];
        });
        $waves = $waves->reverse();

        return $waves;
    }

    /**
     * Sum the global scores of different waves and return the result as score.
     * @param $builder
     * @param array $waves_id
     * @return mixed
     */
    static private function         _getWavesGlobalScore($builder, array $waves_id)
    {
        $builder->selectRaw('SUM(score) / SUM(weight) as score')->whereIn('wave_id', $waves_id);

        $waves = $builder->first();

        return $waves;
    }

    /**
     * Fetch the global score for one program. A limit of n past months can be specified
     * to fetch only the waves in this interval, otherwise fetch all the waves of the program.
     * @param $programs_id
     * @param array $params
     * @return \Illuminate\Support\Collection|mixed|static
     */
    static public function          getProgramGlobalScore($programs_id, array $params)
    {
        $builder                    = new self($params);

        Validator::make(
            ['programs' => $programs_id],
            ['programs' => 'required|int_array|read:programs']
        )->passOrDie();
        $builder->_setScenarios(array_get($params, 'scenarios'));
        $builder->_setShops(array_get($params, 'shops'));
        $builder->_setMissions(array_get($params, 'missions'));

        if (count($programs_id) === 1)
            return static::_getOneProgramGlobalScore($builder, $programs_id[0], array_get($params, 'months_limit'));
        else
            return static::_getProgramsGlobalScore($builder, $programs_id);
    }

    /**
     * @param $builder
     * @param $program_id
     * @param $months_limit
     * @return \Illuminate\Support\Collection|static
     */
    static private function         _getOneProgramGlobalScore($builder, $program_id, $months_limit)
    {
        $months_limit               = (is_int($months_limit) && $months_limit >= 0) ? $months_limit : null;
        $builder->selectRaw('
                    wave_id,
                    wave_name,
                    SUM(score) / SUM(weight) as score,
                    date_start')
            ->groupBy('wave_id', 'wave_name', 'date_start')
            ->orderBy('date_start', 'desc')
            ->where('program_id', $program_id);

        if ($months_limit)
        {
            // 10000: to make sure the user doesn't break the time limit
            $months_limit = ($months_limit > 10000) ? 10000 : $months_limit;
            $builder->whereRaw("date_start >= (NOW() - interval '{$months_limit} months')");
        }

        $waves = collect($builder->get());
        $waves->transform(function($item)
        {
            return [
                'id' => $item['wave_id'],
                'name' => $item['wave_name'],
                'score' => $item['score'],
                'month' => Carbon::createFromFormat('Y-m-d', $item['date_start'])->format('F')
            ];
        });
        $waves = $waves->reverse();

        return $waves;
    }

    static private function         _getProgramsGlobalScore($builder, $programs_id)
    {
        /* Behaviour to determine
         * Question: what scores should we fetch when the user selects multiple program ids
         * (without wave_id specified)
         */
        return [];
    }

    /**
     * Fetch the global scores for one or several waves.
     * @param $waves_id
     * @param array $params
     * @return array|\Illuminate\Support\Collection|static
     */
    static public function          getWaveSequenceScore($waves_id, array $params)
    {
        $builder                    = new self($params);

        Validator::make(
            ['waves' => $waves_id],
            ['waves' => 'required|int_array|read:waves']
        )->passOrDie();
        $builder->_setScenarios(array_get($params, 'scenarios'));
        $builder->_setShops(array_get($params, 'shops'));
        $builder->_setMissions(array_get($params, 'missions'));

        if (count($waves_id) === 1)
            return static::_getOneWaveSequenceScore($builder, $waves_id[0], array_get($params, 'months_limit'));
        else
            return static::_getWavesGlobalScore($builder, $waves_id);
    }

    static private function         _getOneWaveSequenceScore($builder, $wave_id, $months_limit)
    {
        $months_limit               = (is_int($months_limit) && $months_limit >= 0) ? $months_limit : 0;
        $builder->selectRaw("
                    wave_id as id,
                    wave_name as name,
                    sequence_name->>'{$builder->language}' as sequence,
                    program_id,
                    SUM(score) / SUM(weight) as score,
                    date_start")
            ->groupBy('wave_id', 'wave_name', 'sequence_name', 'program_id', 'date_start')
            ->orderBy('date_start', 'desc');

        $query_one = $builder;
        $query_two = clone $builder;

        $current_wave = collect($query_one->where('wave_id', $wave_id)->get());
        if ($current_wave->isEmpty())
            return [];
        // 10000: to make sure the user doesn't break the time limit
        $months_limit = ($months_limit > 10000) ? 10000 : $months_limit;
        $waves = $query_two->where('program_id', $current_wave->first()['program_id'])
            ->whereRaw("
                date_start >= (date '{$current_wave->first()['date_start']}' - interval '{$months_limit} months')
                AND
                date_start < '{$current_wave->first()['date_start']}'
                ")
            ->get();

        $waves = $current_wave->merge($waves)->groupBy('sequence')->sort();
        $waves->transform(function($item)
        {
            return $item->reverse()->transform(function($item)
            {
                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'score' => $item['score']
                ];
            });
        });

        return $waves;
    }

}