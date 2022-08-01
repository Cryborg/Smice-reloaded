<?php

namespace App\Classes\Helpers;


use App\Models\AnswerImage;
use App\Models\AnswerComment;
use App\Models\QuestionRowComment;

use App\Models\QuestionRow;

class FusionHelper
{
    /**
     * @param $array
     * @param $language_code
     * @return array
     */
    public static function fusionCriteriaQuestionScoreId($array, $language_code)
    {
        $t = [];

        $final = collect($array);
        $final->each(function ($item) use (&$t, &$final, &$language_code) {

            $name = json_decode($item['question_name'])->{$language_code};
            if (null === $item['question_info']) {
                $question_info = "";
            } else {
                $question_info = json_decode($item['question_info'])->{$language_code};
            }

            if (array_key_exists('question_info', $item)) {
                $cadrage = isset(json_decode($item['question_info'])->{$language_code})
                    ? json_decode($item['question_info'])->{$language_code} : "na";
            } else {
                $cadrage = "na";
            }

            $t[$item['id_critere']][$name][] = $item['response_value'];
            $t[$item['id_critere']]['id_question'][$name] = $item['id_question'];
            $t[$item['id_critere']]['question_info'][$name] = $question_info;
            $t[$item['id_critere']]['cadrage'][$name] = $cadrage;
        });
        return $t;
    }

    /**
     * @param $array
     * @param $language_code
     * @return array
     */
    public static function fusionCriteriaScore($array, $language_code)
    {
        $t = [];
        $sequence_name_output = [];
        foreach ($array as $item) {
            $key = $item['id_critere'];
            $t[$key]['theme_name'] = is_null($item['theme_name']) ? '' : json_decode($item['theme_name'], true)[$language_code];
            $t[$key]['critere_name'] = is_null($item['criteria_name']) ? '' : json_decode($item['criteria_name'], true)[$language_code];
            $t[$key]['id_wave'] = $item['wave_id'];
            $t[$key]['id_critere'] = $item['id_critere'];
            # A criterion can be linked to several questions that are themselves in several sequences
            $t[$key]['first_id_sequence'] = $item['sequence_id']; # Un critère peut être lié à plusieurs questions qui sont elles mêmes dans plusieurs séquence
            # Récupérer toutes les séquences liés au critères
            # Céer une vue pour récupérer les sequences, en fonctions de la wavetaget, surveyitem (check parent_id)
            # Get all the sequences linked to the criteria
            # Create a view to retrieve the sequences, according to the wave target, survey item (check parent_id)

            if (!isset($result[$key])) {
                $result[$key] = \DB::table('show_survey_item_parent')
                    ->where('criteria_id', $item['id_critere'])
                    ->where('survey_id', $item['survey_id'])
                    ->get();
            }
            $seq_id = [];
            foreach ($result[$key] as $seq => $s) {
                $seq_id[] = $s['item_id'];
            }
            if (!isset($sequence_name_output[$key])) {
                if (count($seq_id) > 1) { //if criteria is present in more than one sequence
                    $sequence = \DB::table('sequence')
                        ->select('*')
                        ->whereIn('id', $seq_id)
                        ->get();
                    # Suppression des doublons
                    $sequence_name = [];
                    foreach ($sequence as $seq) {
                        $sequence_name[$seq['id']] = json_decode($seq['name'], true)[$language_code];
                    }
                    $i = 0;
                    $sequence_name_multi = '';
                    foreach ($sequence_name as $seq) {
                        if ($i > 0) {
                            $sequence_name_multi .= ',';
                        }

                        $sequence_name_multi .= $seq;
                        $i++;
                    }
                    $sequence_name_output[$key] = $sequence_name_multi;
                } else {
                    if (isset($item['sequence_name'])) {
                        $sequence_name_output[$key] = json_decode($item['sequence_name'], true)[$language_code];
                    } else {
                        $sequence_name_output[$key] = null;
                    }
                }
            }
            $t[$key]['scoring'] = $item['scoring'];
            $t[$key]['criteria_weight'] = $item['criteria_weight'];
            $t[$key]['sequence_name'] = $sequence_name_output[$key];
            # Ajouter le score de chaque question d'un mÃªme critere
            # critere_id[score] Score question * Poids question :
            # critere_id[poids question] : somme des poids d'une question
            $t[$key]['base'] = array_key_exists('base', $t[$key]) ? $t[$key]['base'] + 1 : 1;
            # si pas de score Ã  la rÃ©ponse (null) sortir des resultats exemple de rÃ©ponse sans score : NA
            # if no score at the answer (null) exit from the score example results without score: NA
            if (is_int($item['response_value'])) {
                $t[$key]['response_value'] = array_key_exists('response_value', $t[$key])
                    ? $t[$key]['response_value'] + ($item['response_value'] * $item['question_weight'])
                    : $item['response_value'] * $item['question_weight'];
                $t[$key]['question_weight'] = array_key_exists('question_weight', $t[$key])
                    ? $t[$key]['question_weight'] + $item['question_weight']
                    : $item['question_weight'];
            } else {
                $t[$key]['response_value'] = null;
                $t[$key]['question_weight'] = null;
            }
        }

        foreach ($t as $total_critere) {
            $key = $total_critere['id_critere'];
            # pour chaque critere id on calcul score du critere: score = critere_id[score] / critere_id[poids question]
            # pas de score global a calculer ici
            # for each criterion id on calcul criterion score: score = critere_id [score] / critere_id [question weight]
            # no overall score to calculate here
            $t[$key]['poids_critere'] = null;
            $t[$key]['score_critere'] = null;
            $t[$key]['lastscore'] = null;
            if ($t[$key]['question_weight'] > 0) {
                $t[$key]['score_critere'] = round($t[$key]['response_value'] / $t[$key]['question_weight'], 1);
                $t[$key]['poids_critere'] = $t[$key]['criteria_weight'];
            }
        }

        return $t;
    }

    /**
     * @param $criteria
     * @param $language_code
     * @return array
     */
    public static function fusionCriteriaResponse($criteria, $language_code)
    {
        $t = [];
        $criteria = collect($criteria)->sortBy('question_row_id');
        foreach ($criteria as $item) {
            $userImages = null;
            if (isset($item['image']) && $item['image']) {
                $userImages = AnswerImage::where('answer_id', $item['answer_id'])->get()->toArray();
            }
            $question_row_name = json_decode($item['question_row_name']);
            $question_row_name = $question_row_name ? $question_row_name->{$language_code} : '';

            # si question ouverte, un recupére la valeur et supprime le score 0
            if (('number' == $item['type']) || ('text' == $item['type']) || ('text_area' == $item['type']) || ('date' == $item['type']) || ('hour' == $item['type'])) {
                $response = (($item['answer_value']) || ($item['answer_value'] == 0)) ? $item['answer_value'] : '';
            } else {
                $response = (false == $item['question_row_id']) ? '' : $question_row_name;
            }
            if (!isset($tab[$item['id_question']]) || $tab[$item['id_question']][0] !== $item['answer_id']) { //use to remove duplicate record id question is linl to several theme, crit a or b
                //find pre-list answer (comments)
                // collect comments (pre-list answer)
                $comments = AnswerComment::with('questionrowcomment')->where('answer_id', $item['answer_id'])->get()->toArray();
                
                // if ($comments) {
                //     foreach ($comments as &$c) {
                //         $QuestionRowComment = QuestionRowComment::find($c['question_row_comment_id'])->ToArray();
                //         if ($QuestionRowComment)
                //             $c['QuestionRowComment'] = $QuestionRowComment;
                //     }
                // }
                $t[$item['id_question']][] = [
                'date' => $item['visit_date'],
                'id_reponse' => $item['answer_id'],
                'uuid' => $item['uuid'],
                'image' => $userImages,
                'comment' => (false == $item['comment']) ? '' : $item['comment'],
                'comments' => $comments,
                'reponse' => $response,
                'type' => $item['type'],
                'shop' => $item['shop_name'],
                'image_report' => null
                ];
                $tab[$item['id_question']][] = $item['answer_id'];
            }
        }
        return $t;
    }

    /**
     * @param $questions
     * @param $criteria
     * @param $answers
     * @return mixed
     */
    public static function fusionFinalCriteriaMission($questions, $criteria, $answers)
    {
        foreach ($criteria as $key => $value) {
            foreach ($value as $k => $v) {
                foreach ($questions[$key][$k] as $b => $c) {
                    if (is_array($c)) {
                        $questions[$key][$k][$b]['score'] = round(array_sum($c) / count($c), 1);
                        if (array_key_exists('0', $c)) {
                            if (!is_int($c[0])) {
                                $questions[$key][$k][$b]['score'] = 'na';
                            }
                        }
                    }
                    if (('id_question' != $b) && ('question_info' != $b) && ('cadrage' != $b)) {
                        $questions[$key][$k][$b]['reponse'] = $answers[$key][$questions[$key][$k]['id_question'][$b]];
                        $questions[$key][$k][$b]['question_info'] = $questions[$key][$k]['question_info'][$b];
                        $questions[$key][$k][$b]['cadrage'] = $questions[$key][$k]['cadrage'][$b];
                    }
                }
                $criteria[$key][$k]['questions'] = $questions[$key][$k];
                unset($criteria[$key][$k]['questions']['question_info']);
                unset($criteria[$key][$k]['questions']['id_question']);
                unset($criteria[$key][$k]['questions']['cadrage']);
            }
        }

        return $criteria;
    }

    /**
     * @param $res
     * @return array
     */
    public static function fusionPreviousWave($res)
    {
        $v1 = $res[0];
        $v2 = $res[1];
        $criterion_id = array_column($v2[0], 'id_critere');
        $result = [];
        foreach ($v1 as $v) {
            foreach ($v as $criterion) {
                $criterion['lastscore'] = null;
                if (array_key_exists('id_critere', $criterion)) {
                    $found_key = array_search($criterion['id_critere'], $criterion_id);

                    if (false !== $found_key) {
                        $criterion['lastscore'] = $v2[0][$criterion['id_critere']]['score_critere'];
                    }
                }
                array_push($result, $criterion);
            }
        }
        return $result;
    }

    /**
     * @param $array
     * @param $language_code
     * @return array
     */
    public static function fusionShopCriteriaScore($array, $language_code)
    {
        $t = $h = [];

        $final = collect($array);
        $final->each(function ($item) use (&$t, &$h, &$language_code) {
            $c_name = json_decode($item['criteria_name']);
            if ($c_name) {
                # test si critere attache a la question
                $h[$item['shop_name']][$c_name->$language_code]['shop_name'] = $item['shop_name'];
                $h[$item['shop_name']][$c_name->$language_code]["score"][] = $item['score'];
            }
        });
        //find average
        foreach ($h as $key => $value) {
            foreach ($value as $criteria => $value2) {
                $h[$key][$criteria]["score"] = round(array_sum($h[$key][$criteria]["score"]) / count($h[$key][$criteria]["score"]),1);
            }
        }

        return $h;
    }

    /**
     * @param $array
     * @return array
     */
    public static function fusionRowsCriteriaTag($array)
    {
        $wat = ['rows' => [], 'result' => []];
        $ad = [];
        $all_criteria = [];
        $nb_shop = count($array);
        foreach ($array as $item) {
            if (isset($item['tag_name'])) {
                $wat['rows'][] = $item['tag_name'];
                foreach ($item as $criteria => $value) {
                    if ('tag_name' !== $criteria) {
                        array_push($all_criteria, $criteria);
                    }
                }
            }
        }

        $all_criteria = ArrayHelper::superUniqueArray($all_criteria);

        foreach ($all_criteria as $k => $criteria) {
            for ($i = 0; $i < $nb_shop; $i++) {
                if (array_key_exists($criteria, $array[$i])) {
                    $ad[$criteria][] = strval($array[$i][$criteria]);
                } else {
                    $ad[$criteria][] = null;
                }
            }
        }

        foreach ($ad as $k => $v) {
            $a = array_values($v);
            array_unshift($a, null);
            array_push($wat['result'], ([$k] + $a));
        }

        return $wat;
    }

    /**
     * @param $data
     * @param $sequences
     * @param $language_code
     * @return array
     */
    public static function fusionShopSequenceScore($data, $sequences, $language_code)
    {
        $t = $h = $f = [];
        foreach ($data as $item) {
            $s_name = json_decode($item['sequence_name']);
            $h[$item['shop_name']]['shop_name'] = $item['shop_name'];
            $h[$item['shop_name']][$s_name->$language_code] = round($item['score'], 1);
            # code...
        }

        //Vérifie la présence d'un score pour chaque séquence
        foreach ($h as $shop) {
            foreach ($sequences as $sequence) {
                $f[$shop['shop_name']]['shop_name'] = $shop['shop_name'];
                if (isset($shop[$sequence])) {
                    $f[$shop['shop_name']][$sequence] = $shop[$sequence];
                } else {
                    $f[$shop['shop_name']][$sequence] = null;
                }
            }
        }

        foreach ($f as $key => $value) {
            array_push($t, $value);
        }

        return $t;
    }

    /**
     * @param $data
     * @param $model
     * @param $language_code
     * @return array
     */
    public static function fusionShopScore($data, $model, $language_code)
    {
        $t = [];
        $h = [];
        $all = [];
        foreach ($data as $item) {
            $t_name = json_decode($item[$model]);
            $h[$item['shop_name']]['shop_name'] = $item['shop_name'];
            $h[$item['shop_name']][$t_name->$language_code] = round($item['score'], 1);
            $all[$t_name->$language_code] = $t_name->$language_code;

        }
        # check that all item are completed
        foreach ($h as $key => $value) {
            foreach ($all as $k) {
                if ($model == 'criteria_a_name')
                    if (!isset($value[$k]))
                        $h[$value['shop_name']][$all[$k]] = null;
            }
        }
        foreach ($h as $key => $value) {
            array_push($t, $value);
        }
        return $t;
    }

    /**
     * @param $data
     * @param $language_code
     * @return array
     */
    public static function fusionThemeSequenceScore($data, $language_code)
    {
        $t = $h = $alls = $final = [];
        # Get all theme
        $allt = [];
        foreach ($data as $item) {
            $t_name = json_decode($item['theme_name']);
            $allt[$t_name->$language_code] = $t_name->$language_code;
        }
        foreach ($data as $item) {
            $s_name = json_decode($item['sequence_name']);
            $t_name = json_decode($item['theme_name']);
            $t[$s_name->$language_code][] = $item['score'];
            $h[$s_name->$language_code]['sequence_name'] = $s_name->$language_code;
            $h[$s_name->$language_code][$t_name->$language_code] = (round(array_sum($t[$s_name->$language_code]) / count($t[$s_name->$language_code]), 1));
        }
        $i = 0;
        foreach ($h as $key => $k) {
            $final[$i]['sequence_name'] = $k['sequence_name'];
            foreach ($allt as $t) {
                if (!isset($k[$t])) {
                    $final[$i][$t] = null;
                } else {
                    $final[$i][$t] = $k[$t];
                }
            }
            ksort($final[$i]);
            $i++;
        }
        return $final;
    }

}