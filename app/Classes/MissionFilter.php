<?php

namespace App\Classes;

use App\Classes\Helpers\DateHelper;
use App\Classes\Helpers\SearchHelper;
use App\Exceptions\SmiceException;
use App\Models\Mission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MissionFilter
{
    /**
     * function that return skeleton to fill in order to attach user to mission by criteria
     *
     * @return array
     */
    static public function jsonSkell()
    {
        $skell = [
            'country_id' => null,//
            'first_name' => null,
            'last_name' => null,
            'language_id' => null,
            'civility' => null,//
            'postal_code' => null,//
            'address' => null,
            'city' => null,//
            'country_name' => null,//
            'parent_id' => null,
            'role_id' => null,
            'coordinates' => [
                'radius' => null,
                'shops' => []
            ],
            'age' => [
                'min' => null,
                'max' => null
            ],
            'history_visits' => false,
            'show_skills' => false,
            'show_user_survey' => false,
            'program_id' => null,
            'waves' => [],
            'skills' => [],
            'period_id' => null,
            'activity' => [
                'point' => [
                    'operator' => null,
                    'number' => null
                ],
                'flash' => [
                    'operator' => null,
                    'number' => null
                ],
                'number' => [
                    'operator' => null,
                    'number' => null
                ],
                'score_no' => [
                    'operator' => null,
                    'number' => null,
                ],
                'score_yes' => [
                    'operator' => null,
                    'number' => null,
                ],
                'score_test' => [
                    'operator' => null,
                    'number' => null
                ],
                'score_global' => [
                    'operator' => null,
                    'number' => null,
                ]
            ],
            'history' => [
                'euros' => [
                    'sum' => null,
                    'months' => null
                ],
                'points' => [
                    'sum' => null,
                    'months' => null
                ],
                'missionDone' => [
                    'months' => null
                ],
                'missionDoneClient' => [
                    'months' => null,
                    'client' => null
                ],
                'missionDoneSector' => [
                    'months' => null
                ],
                'program_id' => null,
                'waves' => null,
                'history_type' => null,
                'period_id' => null
            ],
            'answers' => [],
            'selected_contributors' => [],
            'current_society_id' => null,
            'users_to_alert' => [],
            'alerts_to_mission' => []
        ];

        return $skell;
    }

    static public function typeJsonSkell()
    {
        $skell = [
            'country_id' => 'int',//
            'first_name' => 'string',
            'last_name' => 'string',
            'language_id' => 'int',
            'civility' => 'string',//
            'postal_code' => 'int',//
            'address' => 'string',
            'city' => 'string',//
            'country_name' => 'string',//
            'parent_id' => 'int',
            'role_id' => 'int',
            'coordinates' => [
                'radius' => 'int',
                'shops' => []
            ],
            'age' => [
                'min' => 'int',
                'max' => 'int'
            ],
            'history_visits' => 'boolean',
            'show_skills' => 'boolean',
            'show_user_survey' => 'boolean',
            'program_id' => 'int',
            'waves' => 'array',
            'period_id' => [],
            'activity' => [
                'point' => [
                    'operator' => 'string',
                    'number' => 'int'
                ],
                'flash' => [
                    'operator' => 'string',
                    'number' => 'int'
                ],
                'number' => [
                    'operator' => 'string',
                    'number' => 'int'
                ],
                'score_no' => [
                    'operator' => 'string',
                    'number' => 'int',
                ],
                'score_yes' => [
                    'operator' => 'string',
                    'number' => 'int',
                ],
                'score_test' => [
                    'operator' => 'string',
                    'number' => 'int'
                ],
                'score_global' => [
                    'operator' => 'string',
                    'number' => 'int'
                ],
            ],
            'history' => [
                'euros' => [
                    'sum' => 'int',
                    'months' => 'date'
                ],
                'points' => [
                    'sum' => 'int',
                    'months' => 'date'
                ],
                'missionDone' => [
                    'months' => 'date'
                ],
                'missionDoneClient' => [
                    'months' => 'date',
                    'client' => 'string'
                ],
                'missionDoneSector' => [
                    'months' => 'date'
                ],
                'program_id' => null,
                'waves' => null,
                'history_type' => null,
                'period_id' => null
            ],
            'answers' => [],
            'selected_contributors' => [],
            'current_society_id' => 'int',
            'users_to_alert' => [],
            'alerts_to_mission' => []
        ];

        return $skell;
    }

    /**
     * Will return total of users and users number by shops' name
     *
     * @param $params
     * @return array
     * @throws SmiceException
     */
    public static function countFilter($params, $scope, $current_user, $shop_id, $society_id)
    {

        //cancel if shop have no coordinates
        if (count($params['coordinates']['shops'])) {

            $filled_skell = static::_checkSkell($params);
            $users = static::_queryFinder($filled_skell, $scope, $current_user, $shop_id, $society_id);
            $shopList = $filled_skell['coordinates']['shops'];
            /*
             * will group users by nearest given shops
             */
            foreach ($users as $key => $user) {


                $i = [];
                $i['id'] = $user['id'];

                //unset($user['id']);
                /*
                 * If the user was not found related to a shop, we delete it.
                 */
                if (empty($user)) {
                    unset($users[$key]);
                } else {
                    /*
                     * We find the value of the minimum distance, then we get
                     * the key of the shop in order to find it's name.
                     */
                    $d = array_search(min($user), $user);
                    $shop_key = intval(str_replace('d', '', $d));

                    $users[$key] = $user;
                }
            }
            $shops = $users->groupBy('name')->transform(function ($item) {
                return $item->count();
            });

            /*
             * will indicate number of users by shop name
             */
            $total = count($users);
            $count = [
                'total' => $total,
                'shops' => $shops,
                'users' => $users,
            ];

            return $count;
        } else {
            $count = [
                'total' => 0,
                'shops' => 0
            ];
            return $count;
        }


    }

    public static function filter($params, $scope, $current_user, $shop_id, $society_id)
    {
        $filled_skell = static::_checkSkell($params);
        $users = static::_queryFinder($filled_skell, $scope, $current_user, $shop_id, $society_id);
        $shopList = $filled_skell['coordinates']['shops'];
        /*
         * will group users by nearest given shops
         */
        foreach ($users as $key => $user) {
            $i = [];
            $i['id'] = $user['id'];

            unset($user['id']);
            /*
             * If the user was not found related to a shop, we delete it.
             */
            if (empty($user)) {
                unset($users[$key]);
            } else {
                /*
                 * We find the value of the minimum distance, then we get
                 * the key of the shop in order to find it's name.
                 */
                $d = array_search(min($user), $user);
                $shop_key = intval(str_replace('d', '', $d));

                $i['name'] = $shopList[$shop_key]['name'];
                $users[$key] = $i;
            }
        }
        $users = $users->groupBy('name')->transform(function ($item) {
            return $item->transform(function ($item) {
                return $item['id'];
            });
        });

        return $users;
    }

    public static function cleanSkell($params)
    {
        return static::_checkSkell($params);
    }

    /**
     * will check given array validity
     *
     * @param $params
     * @return array
     * @throws SmiceException
     */
    private static function _checkSkell($params)
    {
        $validator = Validator::make(
            [
                'country_id' => array_get($params, 'country_id'),
                'language_id' => array_get($params, 'language_id'),
                'gender' => array_get($params, 'gender'),
                'age' => array_get($params, 'age'),
                'coordinates' => array_get($params, 'coordinates'),
                'activity' => array_get($params, 'activity'),
                'answers' => array_get($params, 'answers'),
                'selected_contributors' => array_get($params, 'selected_contributors'),
                'first_name' => array_get($params, 'first_name'),
                'last_name' => array_get($params, 'last_name'),
                'postal_code' => array_get($params, 'postal_code'),
                'address' => array_get($params, 'address'),
                'city' => array_get($params, 'city'),
                'country_name' => array_get($params, 'country_name'),
                'history_visits' => array_get($params, 'history_visits'),
                'history' => array_get($params, 'history'),
                'show_skills' => array_get($params, 'show_skills'),
                'show_user_survey' => array_get($params, 'show_user_survey'),
                'program_id' => array_get($params, 'program_id'),
                'waves' => array_get($params, 'waves'),
                'skills' => array_get($params, 'skills'),
                'period_id' => array_get($params, 'period_id'),
                'parent_id' => array_get($params, 'parent_id'),
                'current_society_id' => array_get($params, 'current_society_id'),
                'users_to_alert' => array_get($params, 'users_to_alert'),
                'alerts_to_mission' => array_get($params, 'alerts_to_mission')
            ],
            [
                'country_id' => 'integer|min:1',
                'language_id' => 'integer|min:1',
                'gender' => 'string|in:male,female',
                'age' => 'array',
                'coordinates' => 'array',
                'activity' => 'array',
                'answers' => 'array',
                'selected_contributors' => 'array',
                'first_name' => 'string',
                'last_name' => 'string',
                'postal_code' => 'integer',
                'address' => 'array',
                'city' => 'string',
                'country_name' => 'string',
                'parent_id' => 'integer',
                'history_visits' => 'boolean',
                'history' => 'array',
                'show_skills' => 'boolean',
                'show_user_survey' => 'boolean',
                'program_id' => 'integer',
                'waves' => 'array',
                'skills' => 'array',
                'current_society_id' => 'integer',
                'users_to_alert' => 'array',
                'alerts_to_mission' => 'array'
            ]
        );

        $validator->passOrDie();

        $params = static::_compareArray($params);
        static::_checkAnswers(array_get($params, 'answers'));
        static::_checkHistory(array_get($params, 'history'));
        static::_checkAge(array_get($params, 'age'));
        static::_checkSelectedContributors(array_get($params, 'selected_contributors'));
        static::_checkCoordinates(array_get($params, 'coordinates'));
        static::_checkActivity(array_get($params, 'activity'));
        static::_checkUsersToAlert(array_get($params, 'users_to_alert'));
        static::_checkAlertsToMission(array_get($params, 'alerts_to_mission'));

        $filled_skell = [
            'country_id' => array_get($params, 'country_id'),
            'language_id' => array_get($params, 'language_id'),
            'gender' => array_get($params, 'gender'),
            'age' => array_get($params, 'age'),
            'answers' => array_get($params, 'answers'),
            'selected_contributors' => array_get($params, 'selected_contributors'),
            'coordinates' => array_get($params, 'coordinates'),
            'activity' => array_get($params, 'activity'),
            'first_name' => array_get($params, 'first_name'),
            'last_name' => array_get($params, 'last_name'),
            'postal_code' => array_get($params, 'postal_code'),
            'address' => array_get($params, 'address'),
            'city' => array_get($params, 'city'),
            'country_name' => array_get($params, 'country_name'),
            'history_visits' => array_get($params, 'history_visits'),
            'history' => array_get($params, 'history'),
            'show_skills' => array_get($params, 'show_skills'),
            'show_user_survey' => array_get($params, 'show_user_survey'),
            'program_id' => array_get($params, 'program_id'),
            'waves' => array_get($params, 'waves'),
            'skills' => array_get($params, 'skills'),
            'period_id' => array_get($params, 'period_id'),
            'parent_id' => array_get($params, 'parent_id'),
            'current_society_id' => array_get($params, 'current_society_id'),
            'users_to_alert' => array_get($params, 'users_to_alert'),
            'alerts_to_mission' => array_get($params, 'alerts_to_mission')
        ];
        return $filled_skell;
    }

    /**
     * Will compare skeleton with given array to remove wrong Json infos
     *
     * @param $params
     * @return mixed
     */
    private static function _compareArray($params)
    {
        $skel = static::jsonSkell();

        $diff = array_diff_key($skel, $params);

        foreach ($diff as $key => $dif) {
            if ($key == 'activity' || $key == 'coordinates' || $key == 'history' || $key == 'answers') {
                $params[$key] = [];
            } elseif ($key == 'age') {
                $params[$key] = [
                    'min' => null,
                    'max' => null
                ];
            } else {
                $params[$key] = null;
            }
        }

        $difHist = array_diff_key($skel['history'], $params['history']);
        $difAct = array_diff_key($skel['activity'], $params['activity']);
        $difCoo = array_diff_key($skel['coordinates'], $params['coordinates']);
        $difAge = array_diff_key($skel['age'], $params['age']);

        foreach ($difHist as $key => $dh) {
            $params['history'][$key] = $dh;
        }

        foreach ($difCoo as $key => $dc) {
            $params['coordinates'][$key] = $dc;
        }

        if (empty($params['coordinates']['radius']))
            $params['coordinates']['radius'] = 0;

        foreach ($difAge as $key => $dag) {
            $params['age'][$key] = $dag;
        }

        foreach ($difAct as $key => $da) {
            $params['activity'][$key] = $da;
        }

        foreach ($params as $key => $param) {
            if (!is_array($param)) {
                if (empty($param) && !is_null($param))
                    $params[$key] = null;
            }
        }

        return $params;
    }

    /**
     * Will check coordinates format
     *
     * @param $coordinates
     * @throws SmiceException
     */
    private static function _checkCoordinates($coordinates) {
        $toValidate = [];
        $rules = [];

        if (is_array($coordinates)) {
            foreach ($coordinates as $key => $coordinate) {
                if (is_array($coordinate)) {
                    foreach ($coordinate as $element) {
                        if (is_array($element) && isset($element['lat']) && isset($element['lon']) && $element['lat'] !== null && $element['lon'] !== null) {
                            foreach ($element as $key_1 => $val) {
                                switch ($key_1) {
                                    case 'lon':
                                        $toValidate[$key_1] = $val;
                                        $rules[$key_1] = 'required|numeric';
                                        break;
                                    case 'lat':
                                        $toValidate[$key_1] = $val;
                                        $rules[$key_1] = 'required|numeric';
                                        break;
                                    case 'name':
                                        $toValidate[$key_1] = $val;
                                        $rules[$key_1] = 'required|string';
                                        break;
                                    default:
                                        break;
                                }
                            }

                            $validator = Validator::make($toValidate, $rules);

                            $validator->passOrDie();
                        }
                    }
                } else if ($key == 'radius') {
                    $validator = Validator::make(
                        [
                            'radius' => $coordinate
                        ],
                        [
                            'radius' => 'integer|min:0|max:100'
                        ]
                    );
                    $validator->passOrDie();

                } else {
                    unset($coordinates[$key]);
                }
            }
        }

    }

    /**
     * Will validate users History information
     *
     * @param $history
     * @throws SmiceException
     */
    private static function _checkHistory($history)
    {
        $validator = Validator::make(

            [
                'euroSum' => array_get($history['euros'], 'sum'),
                'euroMonth' => array_get($history['euros'], 'months'),

                'pointSum' => array_get($history['points'], 'sum'),
                'pointMonth' => array_get($history['points'], 'months'),

                'missionDoneMonth' => array_get($history['missionDone'], 'months'),

                'missionDoneClientM' => array_get($history['missionDoneClient'], 'months'),
                'missionDoneClientC' => array_get($history['missionDoneClient'], 'client'),

                'missionDoneSectorM' => array_get($history['missionDoneSector'], 'months'),

                'program_id' => $history['program_id'],
                'waves' => $history['waves'],
                'history_type' => $history['history_type'],
                'period_id' => $history['period_id'],
            ],
            [
                'euroSum' => 'integer',
                'euroMonth' => 'integer|between:1,12',

                'pointSum' => 'integer',
                'pointMonth' => 'integer|between:1,12',

                'missionDoneMonth' => 'integer|between:1,12',

                'missionDoneClientM' => 'integer|between:1,12',
                'missionDoneClientC' => 'integer|min:1',

                'missionDoneSectorM' => 'integer|between:1,12',

                'program_id' => 'integer',
                'waves' => 'array',
                'history_type' => 'string',
            ]

        );

        $validator->passOrDie();
    }

    /**
     * Will validate Answers fields
     *
     * @param $answers
     * @throws SmiceException
     */
    private static function _checkAnswers($answers)
    {
        $toValidate = [];
        $rules = [];

        if (is_array($answers)) {
            foreach ($answers as $key => $answer) {
                if (is_array($answer)) {
                    foreach ($answer as $key_1 => $element) {
                        Switch ($key_1) {
                            case 'question_id':
                                $toValidate[$key_1] = $element;
                                $rules[$key_1] = 'required|integer';
                                break;
                            case 'question_row_id':
                                $toValidate[$key_1] = $element;
                                $rules[$key_1] = 'required|integer';
                                break;
                            case 'question_col_id':
                                $toValidate[$key_1] = $element;
                                $rules[$key_1] = 'integer';
                                break;
                            default:
                                break;
                        }
                    }

                    $validator = Validator::make($toValidate, $rules);

                    $validator->passOrDie();
                } else {
                    unset($answers[$key]);
                }
            }
        }
    }

    /**
     * Will validate activity operator and number
     *
     * @param $activity
     * @throws SmiceException
     */
    private static function _checkActivity($activity)
    {
        $validator = Validator::make(

            [
                'pOperator' => array_get($activity['point'], 'operator'),
                'pNumber' => array_get($activity['point'], 'number'),

                'fOperator' => array_get($activity['flash'], 'operator'),
                'fNumber' => array_get($activity['flash'], 'number'),

                'Noperator' => array_get($activity['number'], 'operator'),
                'Nnumber' => array_get($activity['number'], 'number'),

                'sNOperator' => array_get($activity['score_no'], 'operator'),
                'sNNumber' => array_get($activity['score_no'], 'number'),

                'sYOperator' => array_get($activity['score_yes'], 'operator'),
                'sYNumber' => array_get($activity['score_yes'], 'number'),

                'sTOperator' => array_get($activity['score_test'], 'operator'),
                'sTNumber' => array_get($activity['score_test'], 'number'),

                'sGOperator' => array_get($activity['score_global'], 'operator'),
                'sGNumber' => array_get($activity['score_global'], 'number')

            ],
            [
                'pOperator' => 'in:<,<=,>,>=,=',
                'pNumber' => 'integer',

                'fOperator' => 'in:<,<=,>,>=,=',
                'fNumber' => 'integer',

                'Noperator' => 'in:<,<=,>,>=,=',
                'Nnumber' => 'integer',

                'sNOperator' => 'in:<,<=,>,>=,=',
                'sNNumber' => 'integer',

                'sYOperator' => 'in:<,<=,>,>=,=',
                'sYNumber' => 'integer',

                'sTOperator' => 'in:<,<=,>,>=,=',
                'sTNumber' => 'integer',

                'sGOperator' => 'in:<,<=,>,>=,=',
                'sGNumber' => 'integer'
            ]
        );

        $validator->passOrDie();
    }

    /**
     * Will validate given age
     *
     * @param $age
     * @throws SmiceException
     */
    private static function _checkAge($age)
    {
        $validator = Validator::make(
            [
                'min' => array_get($age, 'min'),
                'max' => array_get($age, 'max')
            ],
            [
                'min' => 'integer|between:15,100',
                'max' => 'integer|between:15,100'
            ]
        );

        $validator->passOrDie();
        if (($age['min'] > 0) && ($age['max'] >  0)) {
            if ($age['min'] > $age['max'])
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VALIDATION,
                    $validator->messages()
                );
        }
        
    }

    /**
     * Will validate given selected contributors array
     *
     * @param $selected_contributors
     * @throws SmiceException
     */
    private static function _checkSelectedContributors($selected_contributors)
    {
        $validator = Validator::make(
            [
                'selected_contributors' => $selected_contributors
            ],
            [
                'selected_contributors' => 'array'
            ]
        );

        $validator->passOrDie();
    }

    /**
     * Will validate given users to alert array
     *
     * @param $users_to_alert
     * @throws SmiceException
     */
    private static function _checkUsersToAlert($users_to_alert)
    {
        $validator = Validator::make(
            [
                'users_to_alert' => $users_to_alert
            ],
            [
                'users_to_alert' => 'array'
            ]
        );

        $validator->passOrDie();
    }

    /**
     * Will validate given alerts to mission array
     *
     * @param $alerts_to_mission
     * @throws SmiceException
     */
    private static function _checkAlertsToMission($alerts_to_mission)
    {
        $validator = Validator::make(
            [
                'alerts_to_mission' => $alerts_to_mission
            ],
            [
                'alerts_to_mission' => 'array'
            ]
        );

        $validator->passOrDie();
    }

    /**
     * Function that will build query
     *
     * @param $filled_skell
     * @return array
     */
    private static function _queryFinder($filled_skell, $scope, $current_user, $shop_id, $society_id)
    {
        //$current_user info sur user en cours
        $fQuery = null;

        //if radius & no lar in shop exit
        
        /*
         * query that search users by user_survey
         */
        $query = DB::table('user as u')
            ->selectRaw('u.id, gender, first_name, last_name, birth_date, email, postal_code, city, scoring, user_activity.validated_mission')
            ->leftJoin('user_activity', 'u.id', '=', 'user_activity.user_id');

        $fitting_users = SearchHelper::searchProfileAnswer($filled_skell);

        if (!empty($fitting_users)) {
            $query->whereRaw('u.id IN (' . $fitting_users . ')');
        }



        $shopLoop = $filled_skell['coordinates']['shops'];

        $query = SearchHelper::searchByExclusion($query, $society_id, $shop_id);

        $query = SearchHelper::searchBySkills($filled_skell, $query);

        $query = SearchHelper::searchByHistory($filled_skell, $query, $society_id, $shop_id);

        $query = SearchHelper::searchByAge($filled_skell, $query);

        $query = SearchHelper::searchByActivity($filled_skell, $query);

        //$query = SearchHelper::sqlEqual($filled_skell, $query, 'country_name');
        //$query = SearchHelper::sqlEqual($filled_skell, $query, 'country_id');
        $query = SearchHelper::sqlEqual($filled_skell, $query, 'gender');
        $query = SearchHelper::sqlLike($filled_skell, $query, 'city');
        $query = SearchHelper::sqlLike($filled_skell, $query, 'first_name');
        $query = SearchHelper::sqlLike($filled_skell, $query, 'last_name');
        $query = SearchHelper::sqlLike($filled_skell, $query, 'postal_code');

        //only user with sleepstatus = 0
        $query->whereRaw('sleepstatus = 0');
        $query->whereRaw('deleted_at IS NULL');
        $query->whereRaw('user_level_id > 1');
        //remove user @smiceplus.com 
        $query->whereRaw('email not like \'%smiceplus.com\'');


        foreach ($shopLoop as $key => $values) {
            if (($filled_skell['coordinates']['radius']) && ($values['lat'] === null)) {
                 $query->whereRaw('1 = 0'); //no lat for this shop, unable to use radius to find smiceur
            } else {
                /*
                * limit to 7 char because of sql restriction
                */
                if (strlen($values['lat']) > 8) {
                    $values['lat'] = substr($values['lat'], 0, 7);
                }
                if (strlen($values['lon']) > 8) {
                    $values['lon'] = substr($values['lon'], 0, 7);
                }
                if ($filled_skell['coordinates']['radius']) {
                    $query->whereRaw("ACOS(SIN(RADIANS(lat)) * SIN(RADIANS(" . $values['lat'] . ")) + COS(RADIANS(lat)) * COS(RADIANS(" . $values['lat'] . ")) * COS(RADIANS(lon) - RADIANS(" . $values['lon'] . "))) * 6380 < " . $filled_skell['coordinates']['radius']);
                }
            }
        }
        if ($scope == Mission::SCOPE_TO_LIMIT_SMICER) {
            //invitation uniquement aux utilisateurs attachees a la societe courante
            $query->whereRaw('society_id = ' . $current_user->currentSociety->getKey());
        } elseif ($scope == Mission::SCOPE_TO_SMICERS) {
            //invitation  tous les smiceurs
            $query->whereRaw('society_id = ' . $current_user->society_id);
        } else {
            //invitation a tous les smiceurs attachees a la societe courante de l utilisqteur connecte
            $query->whereRaw('society_id = 1');
        }
        //Limit to 450
        $users = collect($query->orderByRaw('RANDOM()')->limit(450)->get());


        return $users;
    }
}