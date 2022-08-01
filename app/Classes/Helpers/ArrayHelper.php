<?php

namespace App\Classes\Helpers;


class ArrayHelper
{
    /**
     * little func for create array of ids
     * and remove key
     * @param $array
     * @return array|null
     */
    public static function getIds($array)
    {
        $tab = [];
        if ((null !== $array || [] === $array) && is_array($array)) {
            if (array_key_exists('id', $array)) {
                if ($array['id'] !== null)
                    array_push($tab, $array['id']);
                else return null;
            } else {
                foreach ($array as $item) {
                    if (isset($item['id']))
                        array_push($tab, $item['id']);
                    else
                        array_push($tab, $item);
                }
            }
            return $tab;
        }
        else if (is_int($array)) {
            return $array;
        }
        else {
            return null;
        }
    }

    /**
     * @param $array
     * @return array|mixed
     */
    public static function sameArrayValue($array)
    {
        $full = [];

        foreach ($array as $k => $c) {
            foreach ($c as $item) {
                $full[$k][] = $item['wave_target_id'];
            }
        }

        $unique_item = (!empty($full)) ? $full[0] : [];
        foreach ($full as $k => $item) {
            if (isset($full[$k + 1])) {
                $unique_item = array_intersect($unique_item, $full[$k], $full[$k + 1]);
            }
        }

        return $unique_item;
    }

    /**
     * @param $questions
     * @return array
     */
    public static function getAnswerIds($questions)
    {
        $tab = [];

        foreach ($questions as $question) {
            if (isset($question['answer_id'])) {
                array_push($tab, $question['answer_id']);
            }
            else {
                array_push($tab, $question);
            }
        }

        return $tab;
    }

    /**
     * array unique for multidimensional array
     * @param $array
     * @return array
     */
    public static function superUniqueArray($array)
    {
        $result = array_map('unserialize', array_unique(array_map('serialize', $array)));

        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::superUniqueArray($value);
            }
        }

        return $result;
    }

    /**
     * @param array $filters
     * @param array $list
     * @return array
     */
    public static function getIdsForFilterFields($filters, $list)
    {
        $result = [];
        foreach ($list as $item) {
            $result[$item] = self::getIds($filters[$item]);
        }

        return $result;
    }

    /**
     * @param array $list
     * @param array $missionsScoresForShop
     * @return array
     */
    public static function getTableFromShopScoreByWave($list, $missionsScoresForShop)
    {
        $headers = [];
        $table = [];
        $tmpTable = [];
        $newTable = [];

        foreach ($list as $wave) {
            foreach ($wave as $shop) {
                if (!in_array($shop['wave'], $headers)) {
                    $headers[] = $shop['wave'];
                }
                $tmpTable[$shop['name']][] = [
                    'wave' => $shop['wave'],
                    'score' => $shop['score']
                ];
            }
        }
        foreach ($tmpTable as $key => $row) {
            foreach ($headers as $header) {
                $table[$key][$header] = [
                    'score' => ''
                ];
            }

        }
        foreach ($table as $key => $row) {
            foreach ($tmpTable as $shop => $value) {
                foreach ($value as $item) {
                    if ($key == $shop) {
                        $table[$key][$item['wave']]['score'] = $item['score'];
                    }
                }
                $table[$key]['summary']['score'] = $missionsScoresForShop[$key];
            }
        }
        foreach ($table as $shop => $item) {
            foreach ($headers as $header) {
                $newTable[$shop][] = [
                    'wave' => $header,
                    'score' => $item[$header]['score']
                ];
            }
            $newTable[$shop][] = [
                'wave' => 'summary',
                'score' => $item['summary']['score']
            ];
        }
        array_unshift($headers, ' ');
        array_push($headers, 'Cumul');
        return [
            'headers' => $headers,
            'table' => $newTable
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public static function getTableFromScenariosByShop($data)
    {
        $headers = [];
        $table = [];
        $globals = [];
        $globalsScenarios = [];
        $scenariosCount = [];
        $shopsCount = [];

        foreach ($data as $key => $row) {
            foreach ($row as $key2 => $item) {
                $scenariosCount[$key] = 0;
                $shopsCount[$item['scenario']] = 0;
                if (!in_array($item['scenario'], $headers)) {
                    $headers[] = $item['scenario'];
                }
                $scorebyscenario[$item['scenario']][] = [
                    'scenario' => $item['scenario'],
                    'score' => $item['score']
                ];
            }
        }
        foreach ($data as $key => $row) {
            foreach ($headers as $header) {
                $table[$key][] = [
                    'scenario' => $header,
                    'score' => ''
                ];
            }

        }
        foreach ($data as $key => $row) {

            foreach ($row as $key2 => $item) {
                if ($item['score'])
                $shopsCount[$item['scenario']] += 1;
                $scenariosCount[$key] += 1;
              $table[$key][array_search($item['scenario'], $headers)]['score'] = $item['score'];
                isset($globals[$key])
                    ? $globals[$key] += $item['score']
                    : $globals[$key] = $item['score'];
                isset($globalsScenarios[$item['scenario']])
                    ? $globalsScenarios[$item['scenario']] += $item['score']
                    : $globalsScenarios[$item['scenario']] = $item['score'];
            }

        }

        foreach ($globals as $key => $global) {
            $globals[$key] = round($global / $scenariosCount[$key], 2);
        }
        foreach ($globalsScenarios as $key => $global) {
            $globalsScenarios[$key] = round($global / $shopsCount[$key], 2);
        }
        return [
            'scenarios' => array_values($globalsScenarios),
            'globals' => $globals,
            'headers' => $headers,
            'table' => $table
        ];
    }
}
