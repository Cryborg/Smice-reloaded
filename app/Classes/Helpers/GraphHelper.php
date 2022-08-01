<?php

namespace App\Classes\Helpers;


use App\Models\Answer;
use App\Models\QuestionRow;

class GraphHelper
{
    /**
     * @param $data
     * @param $template
     * @param $model
     * @param $language
     * @return mixed
     */
    public static function formatRadarGraph($data, $template, $model, $language)
    {
        $tab_series_data = ['pointPlacement' => 'on', 'data' => [], 'name' => ''];
        $tab_series = [];
        $tab_x_axis = ['categories' => [], 'tickmarkPlacement' => 'on', 'lineWidth' => 0];

        $t = $d = $h = [];

        foreach ($data as $item) {
            $criteria_a_name = is_string($item[$model])
                ? json_decode($item[$model], true)[$language]
                : $item[$model]->{$language};
            $t[$criteria_a_name][] = $item['score'];
            $d[] = $criteria_a_name;
        }

        $tab_x_axis['categories'] = array_values(ArrayHelper::superUniqueArray($d));

        $tab_series[0] = $tab_series_data;
        foreach ($t as $key => $value) {
            $tab_series[0]['name'] = 'Score';
            array_push($tab_series[0]['data'], (round(array_sum($value) / count($value), 1)));
        }

        $template['xAxis'] = $tab_x_axis;
        $template['series'] = $tab_series;
        return $template;
    }

    /**
     * @param $data
     * @param $template
     * @param $model
     * @param $language
     * @return mixed
     */
    public static function formatWaveGraph($data, $template, $model, $language)
    {
        $tab_series_data = ['type' => 'column', 'data' => [], 'name' => ''];
        $tab_series = [];
        $tab_x_axis = ['categories' => []];
        $t = $d = [];
        foreach ($data as $item) {
            $name = json_decode($item[$model], true)[$language];
            $t[$name][$item['wave_id']][] = $item['score'];
            $d[$item['wave_id']] = $item['wave_name'];
        }
        foreach ($d as $key => $value) {
            array_push($tab_x_axis['categories'], $value);
        }
        $i = 0;
        foreach ($t as $key => $value) {
            $tab_series[$i] = $tab_series_data;
            $tab_series[$i]['name'] = $key;
            foreach ($value as $k => $v) {
                array_push($tab_series[$i]['data'], (round($v[0], 1)));
            }
            $i++;
        }

        $template['xAxis'] = $tab_x_axis;
        $template['series'] = $tab_series;
        return $template;
    }

    /**
     * @param $data
     * @param $template
     * @param $model
     * @param $model_id
     * @param $language
     * @return mixed
     */
    public static function formatEvoGraph($data, $template, $model, $model_id, $language)
    {
        $tab_series_data = ['type' => 'column', 'data' => [], 'name' => ''];
        $tab_series = [];
        $tab_x_axis = ['categories' => []];
        $t = $d = $h = $r = [];
        foreach ($data as $item) {
            $theme_name = json_decode($item[$model], true)[$language];
            $t[$item[$model_id]][$theme_name][] = $item['score'];
            $d[] = '';
            array_push($r, $item[$model_id]);
        }
        $tab_x_axis['categories'] = ArrayHelper::superUniqueArray($d);
        $i = 0;
        foreach ($t as $key => $value) {
            foreach ($value as $k => $v) {
                $tab_series[$i] = $tab_series_data;
                $tab_series[$i]['name'] = $k;
                array_push($tab_series[$i]['data'], (round(array_sum($v) / count($v), 1)));
                $h[$k] = (round(array_sum($v) / count($v), 1));
                $i++;
            }
        }

        $template['xAxis'] = $tab_x_axis;
        $template['series'] = $tab_series;
        $template['h'] = $h;
        $template['r'] = $r;

        return $template;
    }

    /**
     * @param $comparison
     * @param $data
     * @return array
     */
    public static function waveTemplate($comparison, $data)
    {
        $tab_series_data = ['data' => [], 'name' => 'Ma sélection'];
        $compare_name = null;
        if (isset($comparison[0]['name'])) {
            $compare_name = $comparison[0]['name'];
        }
        if (isset($comparison[1]['name'])) {
            $compare_name .= " " . $comparison[1]['name'];
        }
        if (isset($comparison[2]['name'])) {
            $compare_name .= " " . $comparison[2]['name'];
        }
        $tab_series_data_comparaison = [
            'dashStyle' => 'ShortDash',
            'color' => 'black',
            'data' => [],
            'name' => $compare_name
        ];

        $tab_series = [];
        $tab_x_axis = ['categories' => []];
        for ($i = 0; $i < count($data); $i++) {
            if (isset($data[$i]['score'])) {
                if (null !== $data[$i]['score']) {
                    array_push($tab_series_data['data'], $data[$i]['score']);
                    array_push($tab_x_axis['categories'], $data[$i]['name']);
                }
            }
        }

        for ($i = 0; $i < count($comparison); $i++) {
            if (isset($comparison[$i]['score']) && null !== $comparison[$i]['score']) {
                array_push($tab_series_data_comparaison['data'], $comparison[$i]['score']);
            }
        }

        array_push($tab_series, $tab_series_data);
        if ($comparison) {
            array_push($tab_series, $tab_series_data_comparaison);
        }

        return [
            'xAxis' => $tab_x_axis,
            'series' => $tab_series
        ];
    }

    /**
     * @param $data
     * @param $comparison
     * @param $name
     * @return array
     */
    public static function shopsTemplate($data, $comparison, $name)
    {
        $wave_key = [];

        $tab_series_data = ['data' => [], 'name' => ''];
        $tab_series_data_comparaison = [
            'dashStyle' => 'ShortDash',
            'color' => 'black',
            'data' => [],
            'name' => (isset($comparison[0]) && isset($comparison[0][0])) ? $comparison[0][0]['name'] : $name
        ];

        $tab_series = [];
        $tab_x_axis_tmp = $tab_x_axis = ['categories' => []];

        $t = $d = [];
        # parcours chaque vague
        foreach ($data as $key => $item) {
            $wave_key[$key] = null;
            #  parcours chaque mission
            foreach ($item as $shops) {
                $wave_key[$key] = $shops['wave'];
                $t[$shops['name']][$key][] = $shops['score'];
                $d[$shops['name']][$key] = (round(array_sum($t[$shops['name']][$key]) / count($t[$shops['name']][$key]), 2));
            }
            if ($wave_key[$key]) {
                array_push($tab_x_axis['categories'], $wave_key[$key]);
                array_push($tab_x_axis_tmp['categories'], $key);
            }

        }

        $i = 0;
        foreach ($d as $key => $value) {
            # each shop
            $tab_series[$i] = $tab_series_data;
            $tab_series[$i]['name'] = $key;
            # pacrours toutes les vagues
            foreach ($tab_x_axis_tmp['categories'] as $wave_value) {
                if (array_key_exists($wave_value, $value)) {
                    array_push($tab_series[$i]['data'], $value[$wave_value]);
                } else {
                    array_push($tab_series[$i]['data'], null);
                }
            }

            $i++;
        }

        for ($i = 0; $i < count($comparison); $i++) {
            if (isset($comparison[$i][0]) && null !== $comparison[$i][0]['score']) {
                array_push($tab_series_data_comparaison['data'], $comparison[$i][0]['score']);
            }
        }

        if ($comparison) {
            array_push($tab_series, $tab_series_data_comparaison);
        }

        return [
            'xAxis' => $tab_x_axis, //wave name jan fev mars ...
            'series' => $tab_series //score by shop
        ];
    }

    /**
     * @param $data
     * @return array
     */
    public static function scenarioGraphTemplate($data)
    {
        $xAxis = [
            'categories' => []
        ];
        $series = $tmpSeries = [];

        foreach ($data as $wave) {
            $xAxis['categories'][] = $wave[0]['wave'];
            foreach ($wave as $item) {
                if (isset($item['name'])) {
                    $tmpSeries[$item['name']][] = $item['score'];
                }

            }
        }

        foreach ($tmpSeries as $key => $value) {
            $series[] = [
                'data' => $value,
                'name' => $key
            ];
        }

        return [
            'xAxis' => $xAxis,
            'series' => $series
        ];
    }

    /**
     * @param array $data
     * @param array $comparison
     * @param string $name
     * @param string $lang
     * @param string $waveName
     * @return array
     */
    public static function sequenceWaveTemplate($data, $comparison, $name, $lang, $waveName)
    {
        $tab_series_data = config('hightcharts.plotOptions.series');

        //$tab_series_data = ['type' => 'column', 'data' => [], 'name' => ''];
        $tab_series_data_comparaison = [
            'dashStyle' => 'ShortDash',
            'color' => 'black',
            'data' => [],
            'name' => $name
        ];
        $tab_series = [];
        $tab_x_axis = $tab_x_axis_tmp = ['categories' => []];
        $t = $d = [];
        foreach ($data as $key => $item) {
            $sequence = null;
            foreach ($item as $sequence) {
                if ($sequence['score'] > 0) {
                    $t[$sequence['sequence']->{$lang}][$key][] = round($sequence['score'], 1);
                }
            }
            if ($sequence) {
                array_push($tab_x_axis['categories'], $waveName[$key]);
                array_push($tab_x_axis_tmp['categories'], $key);
            }
        }

        if ($comparison) {
            foreach ($comparison as $comp_wave) {
                foreach ($comp_wave as $comp_seq) {
                    if (null !== $comp_seq['score']) {
                        array_push($tab_series_data_comparaison['data'], round($comp_seq['score'], 2));
                    }
                    # code...
                }
                # code...
            }
        }


        $i = 0;

        foreach ($t as $key => $value) {
            $tab_series[$i] = $tab_series_data;
            $tab_series[$i]['name'] = $key;


            foreach ($tab_x_axis_tmp['categories'] as $wave_value) {
                if (array_key_exists($wave_value, $value)) {
                    array_push($tab_series[$i]['data'], [$value[$wave_value][0]]);
                } else {
                    array_push($tab_series[$i]['data'], null);
                }


            }

            $i++;
        }

        return [
            'xAxis' => $tab_x_axis,
            'series' => $tab_series
        ];
    }

    /**
     * @param $data
     * @param $lang
     * @param $waveName
     * @return array
     */
    public static function sequenceEvoTemplate($data, $lang, $waveName)
    {
        $tab_series_data = ['type' => 'column', 'data' => [], 'name' => ''];
        $tab_series = [];
        $tab_x_axis = ['categories' => []];
        $t = $d = $e = [];
        foreach ($data as $key => $item) {
            foreach ($item as $sequence) # pour chaque vague
            {
                if ($sequence['score'] > 0) {
                    $t[$key][$sequence['sequence']->{$lang}][] = round($sequence['score'], 1);
                    $d[] = $sequence['sequence']->{$lang};
                    $e[] = $sequence['id'];
                }
            }
        }

        $d = array_unique($d);
        $e = array_unique($e);
        $seq = [];
        foreach ($d as $s) {
            $seq[] = $s;
        }

        $tab_x_axis['categories'] = $seq;
        $i = 0;
        foreach ($t as $key => $value) {
            $tab_series[$i] = $tab_series_data;
            $tab_series[$i]['name'] = $waveName[$key];
            foreach ($tab_x_axis['categories'] as $sequence_value) {
                if (array_key_exists($sequence_value, $value)) {
                    array_push($tab_series[$i]['data'], [$value[$sequence_value][0]]);
                } else {
                    array_push($tab_series[$i]['data'], null);
                }
            }
            $i++;
        }

        return [
            'seq' => $e,
            'template' => [
                'xAxis' => $tab_x_axis,
                'series' => $tab_series
            ]
        ];
    }

    /**
     * @param $data
     * @param $comparison
     * @param $name
     * @param $waveName
     * @param $seq
     * @param $lang
     * @return array
     */
    public static function sequencePeriodTemplate($data, $comparison, $name, $waveName, $seq, $lang)
    {
        $tab_series_data = ['type' => 'column', 'data' => [], 'name' => ''];
        $tab_series_data_comparaison = [
            'dashStyle' => 'ShortDash',
            'color' => 'black',
            'data' => [],
            'name' => isset($comparison[0]) ? $comparison[0]['name'] : $name
        ];
        $tab_series = [];
        $tab_x_axis = ['categories' => []];
        $sequence_ids = [];
        $t = $d = $h = [];
        foreach ($seq as $s) {
            # use to keep same sequence order
            foreach ($data as $key => $sequence) {
                if ($s == $sequence['id']) {
                    array_push($sequence_ids, $sequence['id']);
                    $t[$waveName][$sequence['sequence']->{$lang}][] = round($sequence['score'], 1);
                    $d[] = $sequence['sequence']->{$lang};
                    $h[$waveName][$sequence['sequence']->{$lang}] = round(((array_sum($t[$waveName][$sequence['sequence']->{$lang}])
                        / count($t[$waveName][$sequence['sequence']->{$lang}]))), 1);
                }
            }
        }

        if ($comparison) {
            for ($i = 0; $i < count($comparison[0]); $i++) {
                if (isset($comparison[0][$i]) && null !== $comparison[0][$i]['score']) {
                    array_push($tab_series_data_comparaison['data'], round($comparison[0][$i]['score'], 2));
                }
            }
        }

        $tab_x_axis['categories'] = array_values(ArrayHelper::superUniqueArray($d));
        $i = 0;
        foreach ($h as $key => $value) {
            $tab_series[$i] = $tab_series_data;
            $tab_series[$i]['name'] = $key;
            foreach ($value as $v) {
                array_push($tab_series[$i]['data'], $v);
            }
            $i++;
        }

        if ($comparison) {
            array_push($tab_series, $tab_series_data_comparaison);
        }

        return [
            'sequence_box_score' => array_values($h),
            'sequence_id' => ArrayHelper::superUniqueArray($sequence_ids),
            'template' => [
                'xAxis' => $tab_x_axis,
                'series' => $tab_series
            ]
        ];
    }

    /**
     * @param $data
     * @param string $lang
     * @param string $waveName
     * @return array
     */
    public static function sequenceRadarTemplate($data, $lang, $waveName)
    {
        $tab_series_data = ['pointPlacement' => 'on', 'data' => [], 'name' => ''];
        $tab_series = [];
        $tab_x_axis = ['categories' => [], 'tickmarkPlacement' => 'on', 'lineWidth' => 0];

        $t = $d = $h = [];
        foreach ($data as $sequence) {

            $t[$waveName][$sequence['sequence']->{$lang}][] = round($sequence['score'], 1);
            $d[] = $sequence['sequence']->{$lang};
            $h[$waveName][$sequence['sequence']->{$lang}] = array_sum(
                    $t[$waveName][$sequence['sequence']->{$lang}])
                / count($t[$waveName][$sequence['sequence']->{$lang}]);
        }

        $tab_x_axis['categories'] = array_values(ArrayHelper::superUniqueArray($d));
        $i = 0;
        foreach ($h as $key => $value) {
            $tab_series[$i] = $tab_series_data;
            $tab_series[$i]['name'] = $key;
            foreach ($value as $v) {
                array_push($tab_series[$i]['data'], $v);
            }
            $i++;
        }

        return [
            'xAxis' => $tab_x_axis,
            'series' => $tab_series
        ];
    }

    /**
     * @param $template_bar
     * @param $aw_ids
     * @param $questionPossibleAnswers
     * @param $lang
     * @return array
     */
    public static function questionsAnswersTemplate($template_bar, $aw_ids, $questionPossibleAnswers, $lang)
    {
        $array_template = [];
        $array_template_ids = [];
        $tpl_series_data = ['type' => 'pie', 'data' => [], 'name' => ''];
        $tpl_series = [];

        $tab_series = ['data' => [], 'name' => 'Smice', 'colorByPoints' => true];

        $h = $j = [];

        foreach ($questionPossibleAnswers as $key => $item) {
            array_push($array_template_ids, $item['id']);
            foreach ($item['answers'] as $k => $answer) {
                $aw = Answer::where('question_row_id', $answer['id'])->whereIn('wave_target_id', $aw_ids);
                if (null == $aw) {
                    $counter = 0;
                } else {
                    $counter = $aw->count();
                }

                $q_answer = QuestionRow::where('id', $answer['id'])->first();
                $q_name = $q_answer->name[$lang];

                if ('checkbox' === $item['type'] || 'matrix_radio' === $item['type']) {
                    $j[$key]['title'] = $item['name'][$lang];
                    $j[$key][$k][$q_name] = $counter;
                } else {
                    $h[$key]['title'] = $item['name'][$lang];
                    $h[$key][$k]['y'] = $counter;
                    $h[$key][$k]['name'] = $q_name;
                }
            }
        }
        $j = ArrayHelper::superUniqueArray($j);
        foreach ($j as $item => $content) {
            $i = 0;
            foreach ($content as $k => $v) {
                if (isset($content['title'])) {
                    $template_bar['title']['text'] = $content['title'];
                    unset($content[$k]);
                }

                $tpl_series[$i] = $tpl_series_data;

                if (is_array($v)) {
                    $tpl_series[$i]['data'] = array_values($v);
                    $tpl_series[$i]['name'] = key($v);
                    $i++;
                }
            }
            $template_bar['series'] = $tpl_series;

            array_push($array_template, $template_bar);
            $tpl_series = [];
        }
        $h = ArrayHelper::superUniqueArray($h);
        foreach ($h as $key => $value) {
            $template['title']['text'] = $value['title'];
            unset($value['title']);
            $tab_series['type'] = 'pie';
            $tab_series['data'] = $value;
            $template['series'] = [$tab_series];
            array_push($array_template, $template);
        }
        return [
            'array_template' => $array_template,
            'question_template_ids' => $array_template_ids
        ];
    }

    /**
     * @param $data
     * @return array
     */
    public static function tagWaveTemplate($data)
    {
        $tab_series_data = ['type' => 'column', 'data' => [], 'name' => ''];
        $tab_series = [];
        $tab_x_axis = ['categories' => []];

        $t = $d = [];
        foreach ($data as $item) {
            foreach ($item as $it) {
                $t[$it['name']][$it['wave_name']][] = $it['score'];
                array_push($tab_x_axis['categories'], $it['wave_name']);
            }
        }

        $i = 0;
        $tab_x_axis['categories'] = array_values(ArrayHelper::superUniqueArray($tab_x_axis['categories']));
        foreach ($t as $key => $value) {
            $tab_series[$i] = $tab_series_data;
            $tab_series[$i]['name'] = $key;

            for ($j = 0; $j < count($tab_x_axis['categories']); $j++) {
                $s = array_get($value, $tab_x_axis['categories'][$j], null);
                if (!is_null($s)) {
                    array_push($tab_series[$i]['data'], (round(array_sum($s) / count($s), 1)));
                }
            }
            $i++;
        }

        return [
            'xAxis' => $tab_x_axis,
            'series' => $tab_series
        ];
    }

    /**
     * @param $data
     * @return array
     */
    public static function tagEvoTemplate($data)
    {
        $tab_series_data = ['type' => 'column', 'data' => [], 'name' => ''];
        $tab_series = [];
        $tab_x_axis = ['categories' => []];

        $t = $d = $h = [];

        foreach ($data as $key => $item) {
            foreach ($item as $it) {
                if ($it['score']) {
                    $t[$it['wave_name']][$it['name']][] = round($it['score'], 1);
                    $d[] = $it['name'];
                }
            }
        }

        $tab_x_axis['categories'] = array_values(ArrayHelper::superUniqueArray($d));
        $i = 0;
        foreach ($t as $key => $value) {
            $tab_series[$i] = $tab_series_data;
            $tab_series[$i]['name'] = $key;
            foreach ($value as $v) {
                array_push($tab_series[$i]['data'], $v);
            }
            $i++;
        }

        return [
            'xAxis' => $tab_x_axis,
            'series' => $tab_series
        ];
    }

    /**
     * @param $data
     * @return array
     */
    public static function tagPeriodTemplate($data)
    {
        $tab_series_data = ['type' => 'column', 'data' => [], 'name' => ''];
        $tab_series = [];
        $tab_x_axis = ['categories' => []];

        $t = $d = $h = [];

        $name = NameHelper::makeTagName($data);
        foreach ($data as $key => $item) {
            foreach ($item as $it) {
                if ($it['score']) {
                    $t[$name][$it['name']][] = $it['score'];
                    $d[] = $it['name'];
                    $h[$name][$it['name']] = round($it['score'], 1);
                }
            }
        }

        $tab_x_axis['categories'] = array_values(ArrayHelper::superUniqueArray($d));
        $i = 0;
        foreach ($h as $key => $value) {
            $tab_series[$i] = $tab_series_data;
            $tab_series[$i]['name'] = $key;
            foreach ($value as $v) {
                array_push($tab_series[$i]['data'], $v);
            }
            $i++;
        }

        return [
            'xAxis' => $tab_x_axis,
            'series' => $tab_series
        ];
    }

    /**
     * @param $data
     * @param $template
     * @return array
     */
    public static function shopGroupGraphTemplate($data, $template)
    {
        $graphs = [];
        $template['xAxis']['labels']['enabled'] = true;
        $template['legend']['enabled'] = false;
        foreach ($data as $key => $graph) {
            $series = [[
                'type' => 'bar',
                'data' => [],
                "colorByPoint" => true
            ]];
            $categories = [];
            foreach ($graph as $item) {
                $series[0]['data'][] = $item['score'];
                $categories[] = $item['name'];
            }
            $template['series'] = $series;
            $template['xAxis']['categories'] = $categories;
            $graphs[] = [
                'id' => $key,
                'template' => $template
            ];
        }
        return $graphs;
    }

    /**
     * @param array $data
     * @param array $template
     * @return array
     */
    public static function activityUsersMissionsTemplate($data, $template)
    {
        return self::activityMissionsTemplate($data, $template, 'users_count');
    }

    /**
     * @param array $data
     * @param array $template
     * @return array
     */
    public static function activityShopsMissionsTemplate($data, $template)
    {
        return self::activityMissionsTemplate($data, $template, 'shops');
    }

    /**
     * @param array $data
     * @param array $template
     * @param string $param
     * @return mixed
     */
    private static function activityMissionsTemplate($data, $template, $param)
    {
        $series = [];
        $categories = [];
        foreach ($data as $value) {
            $series[] = $value[$param];
            $categories[] = $value['missions_done'] . " mission(s)";
        }

        $template['series'][0]['data'] = array_reverse($series);
        unset($template['series'][1]);
        $template['xAxis']['categories'] = array_reverse($categories);
        unset($template['tooltip']);

        return $template;
    }

    /**
     * @param array $data
     * @param array $template
     * @return array
     */
    public static function shopScoreTemplate($data, $template)
    {
        $series = [[
            'type' => 'bar',
            'data' => [],
            "colorByPoint" => true
        ]];
        $categories = [];
        foreach ($data as $item) {
            $series[0]['data'][] = $item['score'];
            $categories[] = $item['name'];
        }
        $template['series'] = $series;
        $template['xAxis']['categories'] = $categories;
        $template['xAxis']['labels']['enabled'] = true;
        $template['legend']['enabled'] = false;

        return $template;
    }

    /**
     * @param array $data
     * @param array $template
     * @return array
     */
    public static function shopGroupsGraphTemplate($data, $template)
    {
        $categories = [];
        $preData = [];
        $series = [];
        foreach ($data as $wave) {
            $categories[] = $wave['wave'];
            foreach ($wave['scores'] as $score) {
                $preData[$score['name']][] = $score['score'];
            }
        }

        foreach ($preData as $name => $axe) {
            $series[] = [
                "name" => $name,
                "data" => $axe
            ];
        }
        $template['xAxis']['categories'] = $categories;
        $template['series'] = $series;

        return $template;
    }

    /**
     * @param array $data
     * @param array $template
     * @return array
     */
    public static function formatAchievedUsersGraph($data, $template)
    {
        $series = [];
        $categories = [];
        foreach ($data['periods'] as $value) {
            $series[] = round($value['us'] / $data['users'] * 100, 2);
            $categories[] = $value['name'];
        }
        $template['xAxis']['categories'] = $categories;
        $template['series'] = [['data' => $series]];

        return $template;
    }

    /**
     * @param array $data
     * @param array $template
     * @return array
     */
    public static function formatAchievedShopsGraph($data, $template)
    {
        $series = [];
        $categories = [];
        foreach ($data['periods'] as $value) {
            $series[] = round($value['shops'] / $data['max_shops'] * 100, 2);
            $categories[] = $value['name'];
        }
        $template['xAxis']['categories'] = $categories;
        $template['series'] = [['data' => $series]];

        return $template;
    }
}