<?php

namespace App\Classes\Services;

use App\Classes\Helpers\GeoHelper;
use App\Classes\Helpers\SearchHelper;
use App\Classes\SmiceClasses\SmiceFinder;
use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Exceptions\SmiceException;
use App\Http\User\Models\User;
use App\Models\Answer;
use App\Models\Mission;
use App\Models\Survey;
use App\Models\Wave;
use App\Models\WaveTarget;
use App\Models\WaveUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;

class UserService
{
    private $user,
        $model;

    /**
     * UserService constructor.
     * @param $user
     * @param $model
     * @param $request
     */
    public function __construct($user, $model, $request)
    {
        $this->user = $user;
        $this->loadUser($request);
        $this->model = $model;
    }

    /**
     * @param $user_id
     * @param $password
     */
    public static function signUpEmail($user_id, $password)
    {
        SmiceMailSystem::send(SmiceMailSystem::NEW_ACCOUNT, function ($message) use ($user_id, $password) {
            $message->to([30]);
            $message->subject('Compte crée');
            $message->addGlobalMergeVars([
                30 => ['password' => $password]
            ]);
        });
    }

    /**
     * @param $request
     * @throws SmiceException
     */
    public function loadUser($request): void
    {
        $user = $request->route('id_me');

        if ($user && $user !== 'me') {
            $this->user = User::find($user);
        }

//        if (is_null($this->user)) {
//            throw new SmiceException(
//                SmiceException::HTTP_NOT_FOUND,
//                SmiceException::E_RESOURCE,
//                'User does not exist.'
//            );
//        }
    }

    /**
     * @param Request $params
     * @param $status
     * @param null $options
     * @return \App\Classes\SmiceClasses\SmiceClass
     */
    public function showUserMissions($params, $status, $options = null)
    {
        $this->loadUser($params);
        $params = $params->all();
        $lat = array_get($params, 'lat');
        $lng = array_get($params, 'lng');

        $coords = [
            'lat' => $lat,
            'lng' => $lng
        ];

        $targets = WaveTarget::with('scenario', 'passageProofs')->whereHas('waveUsers', function ($query) use ($status, $options) {
            $query->whereNotNull('status_id');
            $query->where('status_id', '!=', 12);
            $query->where(function ($q) use ($status) {
                $q->whereIn('status_id', $status);
            });
            $query->where(function ($q) { //remove all mission with no position and status not selected
                $q->where('status_id', '!=', 5);
                $q->orWhereNotNull('positioned_at');
            });
            $query->where(function ($q) { //remove all mission with no position and status not selected
                $q->where('created_at', '>', Carbon::now()->subDays(30));
                $q->orWhereNotIn('status_id', [5, 12]);
            });

            //if candidate : show mission with shop 7365 in offer
            if (isset($options['type'])) {
                if ($options['type'] == 'offer') {
                    $query->where(function ($q) { //remove all mission with no position and status not selected
                        $q->where('shop_id', 7365);
                        $q->orWhereNotIn('status_id', [13]);
                    });
                }
            }
            //if candidate : show mission with shop =! 7665 in todo
            if (isset($options['type'])) {
                if ($options['type'] == 'todo') {
                    $query->where(function ($q) { //remove all mission with no position and status not selected
                        $q->where('shop_id', '<>', 7365);
                        $q->orWhereNotIn('status_id', [13]);
                    });
                }
            }

            if (isset($options['limit'])) {
                if ($options['limit'] == 'date') {
                    $query->where('date_end', '>=', Carbon::now());
                }
            }
            $query->where('user_id', $this->user->getKey());
        })->with(['waveUsers' => function ($query) {
            $query->where('user_id', $this->user->getKey());
        }, 'waveUsers.status', 'shop']);

        if (isset($options['order'])) {
            foreach ($options['order'] as $order) {
                $targets->orderByRaw($order);
            }
        } else {
            $targets->orderBy('date_start');
        }

        $targets = (new SmiceFinder($targets, $params, $this->user))->get();

        if (property_exists($targets, 'paginator')) {
            $targets->paginator = $this->formatTarget($targets->paginator, $coords);
        } else {
            $targets->paginator = $this->formatTarget($targets->data, $coords);
        }

        return $targets;
    }

    /**
     * @param $targets
     * @param null $coords
     * @return mixed
     */
    public function formatTarget($targets, $coords = null)
    {
        $targets->map(function ($item) use ($coords) {
            $waveUser = $item->waveUsers->first();
            $item->status = $waveUser->status->status[$this->user->language->code];
            $item->status_id = $waveUser->status->getKey();
            $item->uuid = $waveUser->uuid;
            $item->mission = $item->name;
            $item->shop_place = $item->shop->name;
            $item->shop_city = $item->shop->city;
            $item->shop_postcode = $item->shop->postal_code;
            $item->shop_address = ($item->shop->street2) ? $item->shop->street . " " . $item->shop->street2 : $item->shop->street;
            $item->shop_lat = $item->shop->lat;
            $item->shop_lng = $item->shop->lon;
            $item->shop_phone = $item->shop->phone;

            if (!$coords['lat'] || !$coords['lng'] || !$item->shop->lat || !$item->shop->lon)
                $item->distance = 'missing location';

            $item->distance = ($coords['lat'] && $coords['lng'])
                ? GeoHelper::distance($coords['lat'], $coords['lng'], $item->shop->lat, $item->shop->lon, 'K')
                : GeoHelper::distance($this->user->lat, $this->user->lon, $item->shop->lat, $item->shop->lon, 'K');
            $item->distance = round($item->distance, 2);
            if (!$item->visit_date) {
                $item->visit_date = '';
            }
            //get report_author
            $mission = mission::find($item->mission_id);
            if ($mission) {
                $item->report_author = $mission->report_author;
            }
            unset($item->shop, $item->filters);
            return $item;
        });

        return $targets;
    }

    /**
     * @return float|int
     */
    public function surveyProgress()
    {
        $survey = Survey::where([
            'user_survey' => true,
            'society_id' => $this->model->society->getKey()
        ])->first();

        if ($survey) {
            $items = $survey->items->where('type', 'question');
            $answers = $survey->answers->where('user_id', $this->user->getKey());
            $nb_question = $items->count();
            $nb_answers = $answers->count();
            $progress = ((($nb_answers / $nb_question) * 100) * 90) / 100;
        } else {
            $progress = 0;
        }

        return $progress;
    }

    /**
     * @return int
     */
    public function mainInfoProgress()
    {
        $progress = 0;

        if ($this->user->picture)
            $progress++;
        if ($this->user->gender)
            $progress++;
        if ($this->user->first_name)
            $progress++;
        if ($this->user->last_name)
            $progress++;
        if ($this->user->phone)
            $progress++;
        if ($this->user->email)
            $progress++;
        if ($this->user->street)
            $progress++;
        if ($this->user->postal_code)
            $progress++;
        if ($this->user->city)
            $progress++;
        if ($this->user->country)
            $progress++;

        return $progress;
    }

    /**
     * @param array $params
     * @param $count
     * @param bool $answerTag
     * @return bool
     * @throws SmiceException
     */
    public function saveAnswer(array $params = [], $count, $answerTag = null)
    {
        $answer = new Answer($params);
        $survey = Survey::where([
            'user_survey' => true,
            'society_id' => $this->model->society->getKey()
        ])->first();

        $answer->user()->associate($this->model->getKey());
        $answer->survey()->associate($survey);
        $answer->question()->associate($answer->question_id);

        if ($answer->question_row_id) {
            $answer->question_row()->associate($answer->question_row_id);
        }

        $answer->validate();

        if (!Survey::hasQuestion($answer->question_id, $answer->survey_id)) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_FOUND,
                SmiceException::E_RESOURCE,
                'This question is not linked to the survey'
            );
        } else {
            // Check everything about the answer
            $answer->check();
            // Delete the old answer if it exists
            $deleteFormerAnswer = Answer::where([
                'user_id' => $answer->user->getKey(),
                'question_id' => $answer->question_id,
                'question_row_id' => $answer->question_row_id,
                'wave_target_id' => $answer->wave_target_id,
                'survey_id' => $answer->survey->getKey()
            ]);

            if (!array_key_exists($answer->question->getKey(), $count)) {
                $deleteFormerAnswer->delete();
                if ($answer->value !== 'false') {
                    Answer::create($answer->getAttributes());
                }
                return true;
            } elseif ($answer->value !== 'false') {
                Answer::create($answer->getAttributes());
            }
        }

        return true;
    }

    /**
     * OfflineList : List all mission available to mark as offlinee,
     *
     * @param $params
     * @return \App\Classes\SmiceClasses\SmiceClass
     */
    public function offlineList($params)
    {
        $status = [4, 6];
        $options = null;
        $lat = array_get($params, 'lat');
        $lng = array_get($params, 'lng');
        $coords = [
            'lat' => $lat,
            'lng' => $lng
        ];
        $targets = WaveTarget::whereHas('waveUsers', function ($query) use ($status, $options) {
            $query->whereNotNull('status_id');
            $query->whereIn('status_id', $status);
            $query->where('offline', false);
            if ($options) {
                if ($options['limit'] == 'date')
                    $query->where('date_end', '>', Carbon::now());
            }
            $query->where('user_id', $this->user->getKey());
        })
            ->where('quiz_id', null)
            ->orWhere('answered_quiz', true)
            ->with(['waveUsers' => function ($query) {
                $query->where('user_id', $this->user->getKey());
            }, 'waveUsers.status', 'shop'])
            ->orderBy('date_start', 'ASC');

        $targets = (new SmiceFinder($targets, $params, $this->user))->get();

        if (property_exists($targets, 'paginator')) {
            $targets->paginator = $this->formatTarget($targets->paginator, $coords);
        } else {
            $targets->paginator = $this->formatTarget($targets->data, $coords);
        }

        return $targets;
    }

    /**
     * @param $params
     * @param $request
     * @return \App\Classes\SmiceClasses\SmiceClass
     */
    public function offlineMode($params, $request)
    {
        $status = [4, 6];

        $options = null;

        $lat = array_get($params, 'lat');
        $lng = array_get($params, 'lng');

        $coords = [
            'lat' => $lat,
            'lng' => $lng
        ];

        $targets = WaveTarget::whereHas('waveUsers', function ($query) use ($status, $options, $request) {
            $query->whereNotNull('status_id');
            $query->whereIn('status_id', $status);
            $query->where('wave_target_id', $request->route('id'));
            $query->where('offline', false);
            if ($options) {
                if ($options['limit'] == 'date')
                    $query->where('date_end', '>', Carbon::now());
            }
            $query->where('user_id', $this->user->getKey());
        })
            ->where('quiz_id', null)
            ->orWhere('answered_quiz', true)
            ->with(['waveUsers' => function ($query) {
                $query->where('user_id', $this->user->getKey());
            }, 'waveUsers.status', 'shop'])
            ->orderBy('date_start', 'ASC');
        $targets_to_array = $targets->get()->toArray();
        $targets = (new SmiceFinder($targets, $params, $this->user))->get();

        foreach ($targets_to_array as $target) {
            $wave_user = WaveUser::where('uuid', $target["wave_users"][0]["uuid"])->first();
            $wave_user->offline = true;
            $wave_user->offline_date = Carbon::now();
            $wave_user->save();
        }

        if (property_exists($targets, 'paginator')) {
            $targets->paginator = $this->formatTarget($targets->paginator, $coords);
        } else {
            $targets->paginator = $this->formatTarget($targets->data, $coords);
        }

        return $targets;
    }

    /**
     * @param $params
     * @param Request $request
     * @return \App\Classes\SmiceClasses\SmiceClass
     */
    public function onlineMode($params, Request $request)
    {

        $options = null;

        $lat = array_get($params, 'lat');
        $lng = array_get($params, 'lng');

        $coords = [
            'lat' => $lat,
            'lng' => $lng
        ];

        $targets = WaveTarget::whereHas('waveUsers', function ($query) use ($request) {
            $query->where('offline', true);
            $query->where('wave_target_id', $request->route('id'));
            $query->where('user_id', $this->user->getKey());
        })
            ->with(['waveUsers' => function ($query) {
                $query->where('user_id', $this->user->getKey());
            }, 'waveUsers.status', 'shop']);

        $targets_to_array = $targets->get()->toArray();

        $targets = (new SmiceFinder($targets, $params, $this->user))->get();

        foreach ($targets_to_array as $target) {
            $wave_user = WaveUser::where('uuid', $target["wave_users"][0]["uuid"])->first();
            $wave_user->offline = false;
            $wave_user->online_date = Carbon::now();
            $wave_user->save();
        }

        if (property_exists($targets, 'paginator')) {
            $targets->paginator = $this->formatTarget($targets->paginator, $coords);
        } else {
            $targets->paginator = $this->formatTarget($targets->data, $coords);
        }

        return $targets;
    }


    /**
     * @param $answers
     * @return Boolean
     */
    private function has_valid_answers($answers)
    {
        if (empty($answers)) {
            return false;
        }
        foreach ($answers as $answer) {
            if (isset($answer['value'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $params
     * @return \App\Classes\SmiceClasses\SmiceClass
     * @throws SmiceException
     */
    public function userSelection($params, $rawData = false)
    {
        //scope : recupere le type d affectation a effectuer (info du modele de mission)
        //scope : retrieve the type of assignment to perform (mission template info)
        //$current_user info sur user en cours
        //$current_user info on user in progress
        $fQuery = null;

        if (isset($params['paramsFromSuivi']) && $params['paramsFromSuivi']) {
            $query = DB::table('user as u')->selectRaw('DISTINCT u.id')
            ->whereIn('u.email', $params['paramsFromSuivi'])
            ->leftJoin('user_activity', 'u.id', '=', 'user_activity.user_id');
        } else {
            $query = DB::table('user as u')->selectRaw('DISTINCT u.id')
            ->leftJoin('user_activity', 'u.id', '=', 'user_activity.user_id');

            /*
            * query that search users by user_survey
            */
            if (isset($params['user_answers']) && $params['user_answers'] && isset($params['answers']) && !empty($params['answers'])) {
                $fitting_users = SearchHelper::searchProfileAnswer($params);

                if (!$this->has_valid_answers($params['answers']) || empty($fitting_users)) {
                    $user = new User();
                    $user = $user->newListQuery();
                    $users = $user->whereIn('id', []);
                    return (new SmiceFinder($users, $params, $this->user))->get();
                } else {
                    $query->whereRaw('u.id IN (' . $fitting_users . ')');
                }
            }
            if (isset($params['email']) && !empty($params['email'])) {
                $query->where('email', $params['email']);
            }
            $query = SearchHelper::searchByExclusion($query, $this->user->currentSociety->getKey());

            $query = SearchHelper::geolocation($params, $query);

            $query = SearchHelper::searchByAge($params, $query);

            $query = SearchHelper::searchByActivity($params, $query);

            if (isset($params['coordinates']['shops'][0])) {
                $query = SearchHelper::searchByHistory($params, $query, $this->user->currentSociety->getKey(), $params['coordinates']['shops'][0]['id']);
            } else {
                $query = SearchHelper::searchByHistory2($params, $query, $this->user->currentSociety->getKey());
            }
            $query = SearchHelper::searchBySkills($params, $query);

            //$query = $query->selectRaw("id " . $searchRadius);

            //$query = SearchHelper::sqlEqual($params, $query, 'country_name');
            $query = SearchHelper::sqlEqual($params, $query, 'gender');
            //        $query = SearchHelper::sqlLike($params, $query, 'city');
            //        $query = SearchHelper::sqlLike($params, $query, 'first_name');
            //        $query = SearchHelper::sqlLike($params, $query, 'last_name');
            //        $query = SearchHelper::sqlLike($params, $query, 'postal_code');

            //only user with sleepstatus = 0
            $query->whereRaw('sleepstatus = 0');
            if (isset($params['history']['user_level_id']))
                $query->whereRaw('user_level_id >= ' . $params['history']['user_level_id']);
            //remove user @smiceplus.com
            $query->whereRaw('email not like \'%smiceplus.com\'');

            $query->whereRaw('deleted_at IS NULL');

            if (isset($params['coordinates']) && is_array($params['coordinates']) && $params['coordinates']['radius']) {
                $query->whereRaw('society_id = 1');
            } elseif ($this->user->currentSociety->getKey() !== 1) {
                $query->whereRaw('society_id = ' . $this->user->currentSociety->getKey());
            }
        }
        if (!$rawData) {
            $users = collect($query->get());

            $user = new User();
            $user = $user->newListQuery();
            $users = $user->whereIn('id', $users);
            $response = (new SmiceFinder($users, $params, $this->user))->get();

            return $response;
        }

        $response = $query->get();
        $users = User::whereIn('id', array_column($response, 'id'))->get();

        return $users;
    }



    /**
     * @return array
     */
    public function globalScorePerShop()
    {
        $score_per_shop = [];
        $nb_critere = [];
        $array_critere = [];
        $user_shops = DB::table('user_shop')
            ->where('user_id', $this->user->id)
            ->select('shop_id')
            ->get();

        $wave_targets_scoring = DB::table('show_scoring')->whereIn('shop_id', $user_shops)->get();

        foreach ($wave_targets_scoring as $key => $wave_target) {
            $score_per_shop[$wave_target['shop_id']]['score'][] = $wave_target['criteria_score'];
            $score_per_shop[$wave_target['shop_id']]['shop_name'] = $wave_target['shop_name'];
        }
        //recupere le nombre de critere complété
        //retrieve the number of criteria completed
        foreach ($wave_targets_scoring as $key => $wave_target) {
            $nb_critere[$wave_target['shop_id']][] = $wave_target['criteria_id'];
        }
        //supprime les critere en double, répondu dans plusieurs missions
        //suppresses duplicate criteria, answered in multiple missions
        foreach ($nb_critere as $key => $critere) {
            $array_critere[$key] = array_unique($critere);
        }
        foreach ($score_per_shop as $shop_id => $shop) {
            $sum = array_sum($shop['score']);
            $count = count($shop['score']);
            $score_per_shop[$shop_id]['score'] = round($sum / $count, 1) . ' %';
            $score_per_shop[$shop_id]['score_brut'] = $sum / $count;
            $score_per_shop[$shop_id]['nb_critere'] = count($array_critere[$shop_id]);
        }

        foreach ($score_per_shop as $k => $v) {
            $score[$k] = $v['score_brut'];
        }

        array_multisort($score, SORT_NUMERIC, $score_per_shop);

        return $score_per_shop;
    }

    /**
     * @param $mode
     * @return array
     */
    public function globalScoreSequence($mode = null)
    {
        $score_per_sequence = [];

        $user_shops = DB::table('user_shop')
            ->where('user_id', $this->user->id)
            ->select('shop_id')
            ->get();
        $wave_targets_scoring = DB::table('show_scoring')->whereIn('shop_id', $user_shops)->where('scoring', true)->whereNotNull('question_score')->get();

        foreach ($wave_targets_scoring as $key => $wave_target) {
            $score_per_sequence[$wave_target['sequence_id']]['score'][] = $wave_target['criteria_score'];
            $score_per_sequence[$wave_target['sequence_id']]['weight'][] = $wave_target['criteria_weight'];
            $score_per_sequence[$wave_target['sequence_id']]['sequence_name'] = $wave_target['sequence_name'];
        }
        $min['score'] = 100;
        $max['score'] = 0;
        foreach ($score_per_sequence as $sequence_id => $sequence) {
            $sum = array_sum($sequence['score']);
            $count = count($sequence['weight']);
            $score_per_sequence[$sequence_id]['score'] = round($sum / $count, 1) . ' %';
            $score_per_sequence[$sequence_id]['score_brut'] = $sum / $count;
            if ($score_per_sequence[$sequence_id]['score_brut'] < $min['score']) {
                $min['score'] = $score_per_sequence[$sequence_id]['score'];
                $min['sequence_name'] = $score_per_sequence[$sequence_id]['sequence_name'];
            }
            if ($score_per_sequence[$sequence_id]['score_brut'] > $max['score']) {
                $max['score'] = $score_per_sequence[$sequence_id]['score'];
                $max['sequence_name'] = $score_per_sequence[$sequence_id]['sequence_name'];
            }
        }

        switch ($mode) {
            case 'min':
                return $min;
            case 'max':
                return $max;
            default:
                return $score_per_sequence;
        }
    }

    /**
     * @param $params
     * @return \App\Classes\SmiceClasses\SmiceClass
     */
    public function filter($params)
    {
        $group_id = array_get($params, 'group_id');
        $user_ids = [];

        Validator::make(
            ['group_id' => $group_id],
            ['group_id' => 'integer|required']
        )->passOrDie();

        $group_user = DB::table('group_user')
            ->where('group_id', $group_id)
            ->select('group_user.user_id')
            ->get();

        foreach ($group_user as $user_id) {
            $user_ids[] = $user_id['user_id'];
        }
        $user = new User();
        $user = $user->newListQuery();
        $users = $user->whereIn('id', $user_ids);

        return (new SmiceFinder($users, $params, $this->user))->get();
    }

    /**
     * @throws \Exception
     */
    public static function addMissionTest($user_id)
    {
        $mois = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $wave_search = Wave::where('name', $mois[date('n')] . ' ' . date('Y'))
            ->where('society_id', 95)->first();
        $new_target = new WaveTarget();
        //YES : wave is ready
        if (isset($wave_search->id)) {
            $wave_id = $wave_search->id;
        } //NO : we add new wave
        else {
            $new_wave = new Wave();
            $new_wave->name = $mois[date('n')] . ' ' . date('Y');
            $new_wave->society_id = 95;
            $new_wave->created_by = 1;
            $new_wave->date_start = new Carbon('first day of this month');
            $new_wave->date_end = new Carbon('last day of this month');
            $new_wave->save();
            //Reatribution du questionnaire dans la nouvelle vague
            $wave_id = $new_wave->id;
        }
        $futureTarget = Mission::find(592);
        $target = new WaveTarget;
        $target->wave_id = $wave_id;
        $target->shop_id = 7365;
        $target->program_id = 171;
        $target->user_id = $user_id;
        $target->mission_id = $futureTarget['id'];
        $target->scenario_id = $futureTarget['scenario_id'];
        $target->survey_id = $futureTarget['survey_id'];
        $target->quiz_id = $futureTarget['quiz_id'];
        $target->name = $futureTarget['name'];
        $target->hours = $futureTarget['hours'];
        $target->description = $futureTarget['description'];
        $target->has_briefing = $futureTarget['has_briefing'];
        $target->briefing_id = $futureTarget['briefing_id'];
        $target->filters = $futureTarget['filters']; // prendre les filtres de wave_mission_shop
        $target->monday = $futureTarget['monday'];
        $target->tuesday = $futureTarget['tuesday'];
        $target->wednesday = $futureTarget['wednesday'];
        $target->thursday = $futureTarget['thursday'];
        $target->friday = $futureTarget['friday'];
        $target->saturday = $futureTarget['saturday'];
        $target->sunday = $futureTarget['sunday'];
        $target->refund = $futureTarget['refund'];
        $target->compensation = $futureTarget['compensation'];
        $target->nb_quiz_error = $futureTarget['nb_quiz_error'];
        $target->picture = $futureTarget['picture'];
        $target->reader_id = NULL;
        $target->ask_proof = $futureTarget['ask_proof'];
        $target->ask_refund = $futureTarget['ask_refund'];
        $target->is_paid = $futureTarget['is_paid'];
        $target->validation = $futureTarget['validation'];
        $target->permanent_mission = $futureTarget['permanent_mission'];
        $target->has_quiz = false;
        $target->answered_quiz = false;
        $target->answered_survey = false;
        $target->read_survey = false;
        $target->uuid = Uuid::generate(4)->string;
        $target->reviewer_id = $futureTarget['reviewer_id'];
        $target->status = WaveTarget::STATUS_SELECTED;
        $target->filters = json_encode($futureTarget['filters']);
        $target->date_start  = Carbon::now()->toDateString();
        $target->date_end    = Carbon::now()->addDays(30)->format("Y-m-d H:i:s");
        $target->user_id     = $user_id;
        $target->date_status = Carbon::now()->toDateString();
        $target->uuid        = Uuid::generate(4)->string;
        $target->save();

        $WaveUser = [
            'user_id' => $user_id,
            'wave_target_id' => $target->getKey(),
            'uuid' => Uuid::generate(4)->string,
            'invitation_email' => true,
            'selected_at' => Carbon::now(),
            'status_id' => 13,
        ];
        WaveUser::insert($WaveUser);

        $futureTarget = Mission::find(652);
        $target = new WaveTarget();
        $target->wave_id = $wave_id;
        $target->program_id = 171;
        $target->shop_id = 7365;
        $target->user_id = $user_id;
        $target->mission_id = $futureTarget['id'];
        $target->scenario_id = $futureTarget['scenario_id'];
        $target->survey_id = $futureTarget['survey_id'];
        $target->quiz_id = $futureTarget['quiz_id'];
        $target->name = $futureTarget['name'];
        $target->hours = $futureTarget['hours'];
        $target->description = $futureTarget['description'];
        $target->has_briefing = $futureTarget['has_briefing'];
        $target->briefing_id = $futureTarget['briefing_id'];
        $target->filters = $futureTarget['filters']; // prendre les filtres de wave_mission_shop
        $target->monday = $futureTarget['monday'];
        $target->tuesday = $futureTarget['tuesday'];
        $target->wednesday = $futureTarget['wednesday'];
        $target->thursday = $futureTarget['thursday'];
        $target->friday = $futureTarget['friday'];
        $target->saturday = $futureTarget['saturday'];
        $target->sunday = $futureTarget['sunday'];
        $target->refund = $futureTarget['refund'];
        $target->compensation = $futureTarget['compensation'];
        $target->nb_quiz_error = $futureTarget['nb_quiz_error'];
        $target->picture = $futureTarget['picture'];
        $target->reader_id = NULL;
        $target->ask_proof = $futureTarget['ask_proof'];
        $target->ask_refund = $futureTarget['ask_refund'];
        $target->is_paid = $futureTarget['is_paid'];
        $target->validation = $futureTarget['validation'];
        $target->permanent_mission = $futureTarget['permanent_mission'];
        $target->has_quiz = false;
        $target->answered_quiz = false;
        $target->answered_survey = false;
        $target->read_survey = false;
        $target->uuid = Uuid::generate(4)->string;
        $target->reviewer_id = $futureTarget['reviewer_id'];
        $target->status = WaveTarget::STATUS_SELECTED;
        $target->filters = json_encode($futureTarget['filters']);
        $target->date_start  = Carbon::now()->toDateString();
        $target->date_end   = Carbon::now()->addDays(30)->format("Y-m-d H:i:s");
        $target->user_id     = $user_id;
        $target->date_status = Carbon::now()->toDateString();
        $target->uuid        = Uuid::generate(4)->string;

        $target->save();

        $WaveUser = [
            'user_id' => $user_id,
            'wave_target_id' => $target->getKey(),
            'uuid' => Uuid::generate(4)->string,
            'invitation_email' => true,
            'selected_at' => Carbon::now(),
            'status_id' => 13,
        ];
        WaveUser::insert($WaveUser);
    }
}
