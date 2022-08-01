<?php

namespace App\Classes\Helpers;

use App\Exceptions\SmiceException;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class SearchHelper
{
    public static function sqlEqual($params, $query, $field)
    {
        if (isset($params[$field]) && !is_null($params[$field]) && !is_bool($params[$field])) {
            $query->whereRaw('(' . $field . ' = \'' . $params[$field] . '\')');
        }
        return $query;
    }

    public static function sqlLike($params, $query, $field)
    {
        if (isset($params[$field]) && !is_null($params[$field])) {
            $query->whereRaw("(COALESCE(:field, '') ILIKE :value)",
                [
                    'field' => $field,
                    'value' => '%' . $params['city'] . '%'
                ]);
        }

        return $query;
    }

    public static function searchProfileAnswer($params)
    {
        $users = [];
        if (!empty($params['answers'])) {
            $count_answers = 0;
            foreach ($params['answers'] as $answer) {
                if (!isset($answer['value'])) {
                    continue;
                }
                if ($answer['value'] === 'default' && $answer['question_row_id'] === 'default') {
                    continue;
                }
                $count_answers++;
                $fQuery = DB::table('answer')->selectRaw('user_id');
                if (array_key_exists('question_col_id', $answer)) {
                    //matrice
                } else if (array_key_exists('value', $answer)) {
                    if (array_key_exists('question_row_id', $answer)) {
                        $question = Question::find($answer['question_id']);
                        if ($question && $question->type == 'select') {
                            $q = "(question_id = " . $answer['question_id']
                                . " AND question_row_id = " . $answer['question_row_id'] . ")";
                        } else {
                            $q = "(question_id = " . $answer['question_id'] . " AND question_row_id = "
                                . $answer['question_row_id'] . " AND value = " . "'" . $answer['value'] . "')";
                        }
                    } elseif ($answer['value']) {
                        $q = "(question_id = " . $answer['question_id'] . " AND value = "
                            . "'" . $answer['value'] . "'" . ")";
                    }
                } else {
                    $q = "(question_id = " . $answer['question_id'] . " AND question_row_id = "
                        . $answer['question_row_id'] . ")";
                }
                if (isset($q)) {
                    $fQuery = $fQuery->whereRaw($q);
                    /** @var array $user */
                    $users = $fQuery->get();
                    $users = array_column($users, 'user_id');
                }
            }
            $fitting_users = [];
            if (isset($q)) {
                $users = array_count_values($users);
                foreach ($users as $user => $good_answers) {
                    if ($good_answers === $count_answers) {
                        array_push($fitting_users, $user);
                    }
                }
                $fitting_users = implode(',', $fitting_users);
            }
            return $fitting_users;
        }
    }

    public static function geolocation($params, $query)
    {
        if (isset($params['coordinates']) && is_array($params['coordinates']) && $params['coordinates']['radius']) {
            $radius = $params['coordinates']['radius'];
            if (isset($params['address']) && is_array($params['address'])) {

                $radius = $params['coordinates']['radius'];
                if (isset($params['address']['geometry']['location']['lat']) && isset($params['address']['geometry']['location']['lng'])) {
                    
                    $lat = $params['address']['geometry']['location']['lat'];
                    $lon = $params['address']['geometry']['location']['lng'];
                    $query->whereRaw('ACOS(SIN(RADIANS(lat)) * SIN(RADIANS(' . $lat
                        . ')) + COS(RADIANS(lat)) * COS(RADIANS(' . $lat . ')) * COS(RADIANS(lon) - RADIANS(' . $lon
                        . '))) * 6380 < ' . $radius);
                } else {
                    throw new SmiceException(
                        400,
                        SmiceException::E_RESOURCE,
                        'Couldn\'t get geolocation'
                    );
                }
            } elseif (isset($params['coordinates']['shops'][0]['lat']) && isset($params['coordinates']['shops'][0]['lon'])) {
                $lat = $params['coordinates']['shops'][0]['lat'];
                $lon = $params['coordinates']['shops'][0]['lon'];
                $query->whereRaw('ACOS(SIN(RADIANS(lat)) * SIN(RADIANS(' . $lat
                    . ')) + COS(RADIANS(lat)) * COS(RADIANS(' . $lat
                    . ')) * COS(RADIANS(lon) - RADIANS(' . $lon . '))) * 6380 < ' . $radius);
            } else {
                throw new SmiceException(
                    400,
                    SmiceException::E_RESOURCE,
                    'Couldn\'t get geolocation'
                );
            }
        }

        return $query;
    }

    public static function searchByAge($params, $query)
    {
        if (isset($params['age']) && isset($params['age']['min']) && isset($params['age']['max']) && !is_null($params['age']['min']) && !is_null($params['age']['max'])) {
            $dateMin = DateHelper::setDate($params['age']['min']);
            $dateMax = DateHelper::setDate($params['age']['max']);
            $query->whereRaw('(birth_date BETWEEN :dmin AND :dmax)',
                [
                    'dmax' => $dateMax,
                    'dmin' => $dateMin
                ]);
        } elseif (isset($params['age']['min']) && !is_null($params['age']['min'])) {
            $dateMin = DateHelper::setDate($params['age']['min']);
            $query->whereRaw('(birth_date <= :dmin)',
                [
                    'dmin' => $dateMin
                ]);
             $query->whereRaw('birth_date IS NOT NULL');
        } elseif (isset($params['age']['max']) && !is_null($params['age']['max'])) {
            $dateMax = DateHelper::setDate($params['age']['max']);
            $query->whereRaw('(birth_date >= :dmax)',
                [
                    'dmax' => $dateMax
                ]);
            $query->whereRaw('birth_date IS NOT NULL');
        }
        return $query;
    }

    public static function searchByActivity($params, $query)
    {
        if (isset($params['activity'])) {
            if (isset($params['activity']['score_global']['number']) && !is_null($params['activity']['score_global']['number'])
                && ($params['activity']['score_global']['number'] > 0)) {
                $query->whereRaw("(scoring >= :scoring)",
                    [
                        'scoring' => $params['activity']['score_global']['number'],
                    ]);
            }
            if (!empty($params['activity']['number']['number'])) {
                $query->whereRaw('u.id IN (SELECT user_id FROM user_activity WHERE validated_mission >= '
                    . $params['activity']['number']['number'] . ')');
            }
        }
        return $query;
    }

    public static function searchByHistory2($params, $query, $society_id)
    {
        if (isset($params['user_activity']) && $params['user_activity'] === true && isset($params['history'])) {
            //no mission for this client
            $query_wave = DB::table('wave')->select('wave.id');

            if (!isset($params['history']['society_id'])) {
                return;
            }

            $query_wave->where('society_id', $params['history']['society_id']);

            if (isset($params['history']['waves']) && !empty($params['history']['waves'])) {
                $query_wave->whereIn('wave.id', $params['history']['waves']);
            }

            if (isset($params['history']['mission_id'])) {
                $query_wave
                    ->join('wave_mission', 'wave.id', '=', 'wave_mission.wave_id')
                    ->where('mission_id', $params['history']['mission_id']);
            }

            if (isset($params['history']['period_id']) && $params['history']['period_id'] !== 'perso') {
                $query_wave->limit($params['history']['period_id']);
            }

            $result = $query_wave->orderBy('date_start', 'DESC')->get();

            $wave_ids = array_column($result, 'id');
            $waves = implode(',', $wave_ids);

            if (empty($waves)) {
                $query->whereRaw('0 = 1');
            } else {
                if (isset($params['history']['wave_target_status'])) {
                    $query->whereRaw('u.id IN (SELECT user_id FROM wave_target WHERE user_id is not null AND status = \'' . $params['history']['wave_target_status'] . '\' AND wave_id IN (' . $waves . '))');
                } else {
                    $query->whereRaw('u.id IN (SELECT user_id FROM wave_target WHERE user_id is not null AND wave_id IN (' . $waves . '))');
                }
            }
        }
        return $query;
    }

    public static function searchByHistory($params, $query, $society_id, $shop_id = null)
    {
        //filter on last missions of smicers
        if (isset($params['history_visits']) && $params['history_visits']) {
            //no mission for this client
            $queryWave = DB::table('wave')
                ->select('id')
                ->where('society_id', $society_id);

           
           
            if (isset($params['history']) && $params['history']['waves']) {
                $queryWave->whereIn('id', $params['history']['waves']);
            }

            if (isset($params['history']) && $params['history']['period_id'] != 'perso' && $params['history']['period_id']) {
                $queryWave->limit($params['history']['period_id']+1);
            }

            $result = $queryWave->orderBy('date_start', 'DESC')->get();

            $wave_ids = array_map(function ($value) {
                return $value['id'];
            }, $result);
            $waves = implode(',', $wave_ids);

            if (isset($params['history']) && $params['history']['program_id']) {
                $query->whereRaw('u.id NOT IN (SELECT user_id FROM wave_target WHERE user_id is not null AND program_id = ' . $params['history']['program_id'] . ' AND wave_id IN (' . $waves . '))');
            }

            if (isset($params['history']) && $params['history']['history_type'] == 'shop') {
                if ($shop_id && $waves) {
                    $query->whereRaw('u.id NOT IN (SELECT user_id FROM wave_target WHERE user_id is not null AND shop_id = ' . $shop_id . ' AND wave_id IN (' . $waves . '))');
                }
            } else {
                if (!empty($waves)) {
                    $query->whereRaw('u.id NOT IN (SELECT user_id FROM wave_target WHERE user_id is not null AND wave_id IN (' . $waves . '))');
                }
            }
        }

        return $query;
    }

    public static function searchBySkills($params, $query)
    {
        if (isset($params['show_skills']) && !empty($params['skills'])) {
                $skills = implode(',', $params['skills']);
                $query->whereRaw('u.id IN (SELECT user_id FROM user_skill WHERE skill_id IN (' . $skills . '))');
            }
        return $query;
    }

    public static function searchByExclusion($query, $society_id, $shop_id = null) {
        //get all users exclude for sociéty
        $user_id = [];
        $user_exclusion_society = DB::table('user_exclusion_society')
            ->select('user_id')
            ->where('society_id', $society_id)
            ->get();

        foreach ($user_exclusion_society as $exclusion_society) {
            $user_id[] = $exclusion_society['user_id'];
        }

        if ($shop_id) {
            $user_exclusion_shop = DB::table('user_exclusion_shop')
            ->select('user_id')
            ->where('shop_id', $shop_id)
            ->get();
            foreach ($user_exclusion_shop as $exclusion_shop) {
                $user_id[] = $exclusion_shop['user_id'];
            }
        }
        $users = implode(',', $user_id);
        
        if ($users)
            $query->whereRaw('u.id NOT IN (' . $users . ')');

        return $query;
    }

}