<?php

namespace App\Models;

use App\Classes\MissionFilter;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Response;
use Webpatser\Uuid\Uuid;

/**
 * App\Models\Wave
 *
 * @property int $id
 * @property string $name
 * @property string $date_start
 * @property string $date_end
 * @property int $program_id
 * @property int $society_id
 * @property int $hours_to_reconfirm
 * @property int $hours_to_answer
 * @property int $number_validations
 * @property string|null $launched
 * @property bool $canceled
 * @property bool $ended
 * @property int $satisfaction_low_range
 * @property int $satisfaction_medium_range
 * @property int $satisfaction_good_range
 * @property int $satisfaction_excellent_range
 * @property int|null $created_by
 * @property string $attribution
 * @property int $confirm_days_before_start
 * @property string $selection
 * @property string|null $automatic_selection_type
 * @property int $random_start
 * @property string $validation
 * @property bool|null $permanent_mission
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mission[] $missions
 * @property-read \App\Models\Program $program
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Shop[] $shops
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WaveTarget[] $targets
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereCanceled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereEnded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereHoursToAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereHoursToReconfirm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereLaunched($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereNumberValidations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereSatisfactionExcellentRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereSatisfactionGoodRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereSatisfactionLowRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereSatisfactionMediumRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereAttribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereAutomaticAssignation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereAutomaticSelectionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereConfirmDaysBeforeStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereRandomStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereSelection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave whereValidation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Wave wherePermanentMission($value)
 * @mixin \Eloquent
 */
class Wave extends SmiceModel implements iREST, iProtected
{
    const SELECTION_AUTOMATIC = 'automatic';
    const SELECTION_MANUAL = 'manual';

    const VALIDATION_AUTOMATIC = 'automatic';
    const VALIDATION_MANUAL = 'manual';

    const AUTOMATIC_SELECTION_TYPE_FIFS = 'fifs';
    const AUTOMATIC_SELECTION_TYPE_RANDOM = 'random';

    const ENDED_YES = true;
    const ENDED_NO = false;

    protected $table = 'wave';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'date_start',
        'date_end',
        'hours_to_reconfirm',
        'hours_to_answer',
        'number_validations',
        'program_id',
        'society_id',
        'satisfaction_low_range',
        'satisfaction_medium_range',
        'satisfaction_good_range',
        'satisfaction_excellent_range',
        'ended',
    ];

    protected $mass_fillable = [
        'ended',
    ];

    protected $hidden = [
        'created_by',
    ];

    protected $list_rows = [
        'id',
        'name',
        'date_start',
        'date_end',
        'launched',
        'ended',
        'society_id'
    ];

    protected $rules = [
        'name'                         => 'string|required|unique_with:wave,society_id,{id}',
        'date_start'                   => 'date|required',
        'date_end'                     => 'date|required|after:date_start',
        'hours_to_reconfirm'           => 'integer|required_if:attribution,complete',
        'hours_to_answer'              => 'integer|required_if:attribution,complete',
        'number_validations'           => 'integer|required_if:validation,manual',
        'society_id'                   => 'integer|required',
        'created_by'                   => 'integer',
        'satisfaction_low_range'       => 'integer|min:0',
        'satisfaction_medium_range'    => 'integer|min:0',
        'satisfaction_good_range'      => 'integer|min:0',
        'satisfaction_excellent_range' => 'integer|min:0',
        'ended'                        => 'boolean',
    ];

    public static function getURI()
    {
        return 'waves';
    }

    public static function getName()
    {
        return 'wave';
    }

    public function getModuleName()
    {
        return 'waves';
    }

    public function targets()
    {
        return $this->hasMany('App\Models\WaveTarget');
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function shops()
    {
        return $this->belongsToMany('App\Models\Shop', 'wave_shop');
    }

    public function missions()
    {
        return $this->belongsToMany('App\Models\Mission', 'wave_mission');
    }

    public function scopeMinimum($query)
    {
        return $query->select('id', 'name');
    }

    public function linkingmission($missions, $program_id)
    {
        $present = DB::table('wave_mission')
            ->where('wave_id', $this->getKey())
            ->wherein('mission_id', $missions)
            ->where('program_id', $program_id)
            ->get();
        if (!$present) {
            $target['wave_id']                                  = $this->getKey();
            $target['mission_id']                               = $missions[0];
            $target['program_id']                               = $program_id;
            DB::table('wave_mission')->insert($target);
        }
    }

    public function linkingshop($shops, $program_id)
    {
        foreach ($shops as $s) {
            $present = DB::table('wave_shop')
                ->where('wave_id', $this->getKey())
                ->where('shop_id', $s)
                ->where('program_id', $program_id)
                ->get();
            if (!$present) {
                $target['wave_id']                                  = $this->getKey();
                $target['shop_id']                                  = $s;
                $target['program_id']                               = $program_id;
                DB::table('wave_shop')->insert($target);
            }
        }
    }

    public function linking($shops, $missions, $user = null, $quantity = 1, $program_id)
    {
        $targets      = [];
        $newWaveShops = DB::table('wave_shop')
            ->where('wave_id', $this->getKey())
            ->whereIn('shop_id', $shops)
            ->where('program_id', $program_id)
            ->get();
        $newWaveMissions = DB::table('wave_mission')
            ->where('wave_id', $this->getKey())
            ->whereIn('mission_id', $missions)
            ->where('program_id', $program_id)
            ->get();
        foreach ($newWaveMissions as $newWaveMission) {
            foreach ($newWaveShops as $newWaveShop) {
                $mission                                            = Mission::where('id', $newWaveMission['mission_id'])->first()->toArray();
                $scope                                              = $mission['scope'];
                $target['wave_id']                                  = $this->getKey();
                $target['shop_id']                                  = $newWaveShop['shop_id'];
                $target['program_id']                               = $newWaveMission['program_id'];
                $target['mission_id']                               = $newWaveMission['mission_id'];
                $target['mission_filter']                           = $mission['filters'];
                $target['mission_filter']                           = MissionFilter::cleanSkell($target['mission_filter']);
                $target['shop']                                     = Shop::where('id', $target['shop_id'])->get(['id', 'lat', 'lon', 'name'])->first()->toArray();
                $target['mission_filter']['coordinates']['shops'][] = $target['shop'];
                if ($scope == Mission::SCOPE_TO_SMICERS) {
                    //search society_id for this mission
                    //use wave_id to find society id
                    $society_id = Wave::select('society_id')->where('id', $this->getKey())->first();
                    $society_id = $society_id->society_id;

                    $target['smicer']                               = MissionFilter::countFilter($target['mission_filter'], $scope, $user, $newWaveShop['shop_id'], $society_id);
                    $target['smicer']                               = $target['smicer']['total'];
                } else {
                    $target['smicer'] = 0;
                }
                $target['mission_filter']                           = json_encode($target['mission_filter'], true);

                $target['fk1']                                      = $newWaveShop['id'];
                $target['fk2']                                      = $newWaveMission['id'];
                $target['quantity']                                 = $quantity;
                unset($target['shop']);
                array_push($targets, $target);
            }
        }
        DB::table('wave_shop_mission')->insert($targets);
    }

    public function unlinking($shops, $missions, $program_id)
    {
        $waveId = $this->getKey();

        DB::table('wave_shop_mission')
            ->where('wave_id', $waveId)
            ->where('program_id', $program_id)
            ->whereIn('shop_id', $shops)
            ->whereIn('mission_id', $missions)
            ->delete();

        DB::table('wave_shop')
            ->where('wave_id', $waveId)
            ->where('program_id', $program_id)
            ->whereRaw("shop_id NOT IN (
            SELECT shop_id
            FROM wave_shop_mission
            WHERE wave_id = " . $waveId . "
            GROUP BY shop_id
        )")->delete();

        DB::table('wave_mission')
            ->where('wave_id', $waveId)
            ->where('program_id', $program_id)
            ->whereRaw("mission_id IN (
            SELECT mission_id
            FROM wave_shop_mission
            WHERE wave_id = " . $waveId . "
            GROUP BY mission_id
        )")->delete();
    }

    public function build($program_id)
    {
        $targets = $mission_ids = $shop_ids = [];

        $futureTargets = DB::table('wave_shop_mission')
            ->select('mission.*', 'wave_shop_mission.*')
            ->where('wave_shop_mission.wave_id', $this->getKey())
            ->where('wave_shop_mission.program_id', $program_id)
            ->where('created', false)
            ->join('mission', 'wave_shop_mission.mission_id', '=', 'mission.id')
            ->get();

        $wm = collect(DB::table('wave_mission')
            ->where('wave_id', $this->getKey())
            ->where('program_id', $program_id)
            ->join('wave_mission_date', 'wave_mission.id', '=', 'wave_mission_date.wave_mission_id')
            ->get())
            ->groupBy('wave_mission_id');

        foreach ($futureTargets as $futureTarget) {
            $i                               = 0;
            $target                          = [];
            $target['wave_id']               = $this->getKey();
            $target['shop_id']               = $futureTarget['shop_id'];
            $target['mission_id']            = $futureTarget['mission_id'];
            $target['scenario_id']           = $futureTarget['scenario_id'];
            $target['program_id']            = $program_id;
            $target['survey_id']             = $futureTarget['survey_id'];
            $target['quiz_id']               = $futureTarget['quiz_id'];
            $target['name']                  = $futureTarget['name'];
            $target['hours']                 = $futureTarget['hours'];
            $target['accroche']              = $futureTarget['accroche'];
            $target['description']           = $futureTarget['description'];
            $target['has_briefing']          = $futureTarget['has_briefing'];
            $target['briefing_id']           = $futureTarget['briefing_id'];
            $target['filters']               = $futureTarget['mission_filter']; // prendre les filtres de wave_mission_shop
            $target['monday']                = $futureTarget['monday'];
            $target['tuesday']               = $futureTarget['tuesday'];
            $target['wednesday']             = $futureTarget['wednesday'];
            $target['thursday']              = $futureTarget['thursday'];
            $target['friday']                = $futureTarget['friday'];
            $target['saturday']              = $futureTarget['saturday'];
            $target['sunday']                = $futureTarget['sunday'];
            $target['refund']                = $futureTarget['refund'];
            $target['max_refund']            = $futureTarget['refund'];
            $target['payment_delay']         = $futureTarget['payment_delay'];
            $target['type']                  = $futureTarget['type'];
            $target['sign_template']         = $futureTarget['sign_template'];
            $target['sign_rate']             = $futureTarget['sign_rate'];
            $target['compensation']          = $futureTarget['compensation'];
            $target['nb_quiz_error']         = $futureTarget['nb_quiz_error'];
            $target['picture']               = $futureTarget['picture'];
            $target['reader_id']             = NULL;
            $target['ask_proof']             = $futureTarget['ask_proof'];
            $target['ask_refund']            = $futureTarget['ask_refund'];
            $target['is_paid']               = $futureTarget['is_paid'];
            $target['validation']            = $futureTarget['validation'];
            $target['permanent_mission'] = $futureTarget['permanent_mission'];
            $target['has_quiz']              = false;
            $target['answered_quiz']         = false;
            $target['answered_survey']       = false;
            $target['read_survey']           = false;
            $target['uuid']                  = Uuid::generate(4)->string;
            $target['reviewer_id']           = $futureTarget['reviewer_id'];
            $target['status']                = WaveTarget::STATUS_DOODLE;
            $futureTarget['filters']         = json_decode($futureTarget['filters']);
            if ($futureTarget['scope'] === Mission::SCOPE_TO_CONTRIBUTORS) {
                foreach ($futureTarget['filters']->selected_contributors as $user_id) {
                    $contributor = User::find($user_id);
                    if ($contributor) {
                        foreach ($wm[$futureTarget['fk2']] as $value) {
                            $target['date_start']  = $value['date_start'];
                            $target['date_end']    = $value['date_end'];
                            $target['date_exclusion']    = $value['date_exclusion'];
                            $target['user_id']     = $user_id;
                            $target['date_status'] = Carbon::now()->toDateString();
                            $target['uuid']        = Uuid::generate(4)->string;
                            array_push($targets, $target);
                        }
                        $i++;
                    }
                }

                if ($i == 0) {
                    //pas d'utilisateur dans la liste des colaborateurs on ajoute une mission par point de vente sans affecter d'utilisateur
                    foreach ($wm[$futureTarget['fk2']] as $value) {
                        $target['date_start']  = $value['date_start'];
                        $target['date_end']    = $value['date_end'];
                        $target['date_exclusion']    = $value['date_exclusion'];
                        $target['user_id']     = null;
                        $target['date_status'] = Carbon::now()->toDateString();
                        $target['uuid']        = Uuid::generate(4)->string;
                        array_push($targets, $target);
                    }
                }
            } else if ($futureTarget['scope'] === Mission::SCOPE_TO_AUTO_CONTRIBUTORS) {
                //limiter aux user avec les droits sur les pdv
                $i = 0;
                foreach ($wm[$futureTarget['fk2']] as $value) {
                    // pour chaque point de vente
                    if (count($futureTarget['filters']->selected_contributors) == 0) { //pas de user dans la liste on recupere les droits
                        //Rechercher les utilisateurs attachés aux points de vente
                        $shop_contributors = collect(DB::table('user_shop')
                            ->where('shop_id', $futureTarget['shop_id'])
                            ->get());
                    } else {
                        //Rechercher les user selectionné comme destinataires
                        //Rechercher les utilisateurs attachés aux points de vente
                        $shop_contributors = collect(DB::table('user_shop')
                            ->where('shop_id', $futureTarget['shop_id'])
                            ->whereIn('user_id', $futureTarget['filters']->selected_contributors)
                            ->get());
                        //test si l'utilisateur a les droits sur le point de vente
                    }
                    foreach ($shop_contributors as $sc) {
                        $user = User::find($sc['user_id']);
                        if ($user->society_id === $futureTarget['society_id']) {
                            //Ajout de la mission pour chaque colaborateur
                            $target['date_start']  = $value['date_start'];
                            $target['date_end']    = $value['date_end'];
                            $target['date_exclusion']    = $value['date_exclusion'];
                            $target['user_id']     = $sc['user_id'];
                            $target['date_status'] = Carbon::now()->toDateString();
                            $target['uuid']        = Uuid::generate(4)->string;
                            array_push($targets, $target);
                            $i++;
                        }
                    }
                    if ($futureTarget['shop_id'] == 0) {
                        foreach ($futureTarget['filters']->selected_contributors as $sc) {
                            //Ajout de la mission pour chaque colaborateur
                            $target['date_start']  = $value['date_start'];
                            $target['date_end']    = $value['date_end'];
                            $target['date_exclusion']    = $value['date_exclusion'];
                            $target['user_id']     = $sc;
                            $target['date_status'] = Carbon::now()->toDateString();
                            $target['uuid']        = Uuid::generate(4)->string;
                            array_push($targets, $target);
                            $i++;
                        }
                    }
                    if ($i == 0) {
                        //pas d'utilisateur dans la liste des colaborateurs on ajoute une mission par point de vente sans affecter d'utilisateur
                        foreach ($wm[$futureTarget['fk2']] as $value) {
                            $target['date_start']  = $value['date_start'];
                            $target['date_end']    = $value['date_end'];
                            $target['date_exclusion']    = $value['date_exclusion'];
                            $target['user_id']     = null;
                            $target['date_status'] = Carbon::now()->toDateString();
                            $target['uuid']        = Uuid::generate(4)->string;
                            array_push($targets, $target);
                        }
                    }
                }
            } else {
                foreach ($wm[$futureTarget['fk2']] as $value) {
                    $target['date_start']  = $value['date_start'];
                    $target['date_end']    = $value['date_end'];
                    $target['date_exclusion']    = $value['date_exclusion'];
                    $target['user_id']     = null;
                    $target['date_status'] = Carbon::now()->toDateString();
                    $target['uuid']        = Uuid::generate(4)->string;
                    array_push($targets, $target);
                }
            }
        }

        DB::table('wave_shop_mission')->where('wave_id', $this->getKey())->update(['created' => true]);
        foreach ($targets as $target) {
            WaveTarget::insert($target);
            array_push($mission_ids, $target['mission_id']);
            array_push($shop_ids, $target['shop_id']);
        }
        $mission_ids = array_unique($mission_ids);
        $shop_ids    = array_unique($shop_ids);

        //remove all shops link to current wave in wave_shop, job is done record can be remove
        DB::table('wave_shop')
            ->where('wave_id', $targets[0]['wave_id'])
            ->delete();

        if (!empty($targets)) {
            return DB::table('wave_target')
                ->select('id')
                ->where('wave_id', $targets[0]['wave_id'])
                ->whereIn('shop_id', $shop_ids)
                ->whereIn('mission_id', $mission_ids)
                ->where('status', WaveTarget::STATUS_DOODLE)
                ->get();
        }
    }

    public function insertDates($objects)
    {
        foreach ($objects as $object) {
            DB::table('wave_target_date')
                ->where('wave_target_id', $object['wave_target_id'])
                ->insert([
                    'wave_target_id' => $object['wave_target_id'],
                    'date'           => $object['date']
                ]);
        }
        return new Response(['response' => 'OK']);
    }

    public function getShopsForMissions($missions)
    {
        $query = Shop::listQuery();

        $query = $query->whereRaw(
            "id IN (
                SELECT shop_id
                FROM wave_shop_mission
                WHERE wave_id = " . $this->getKey() . "
                AND mission_id IN (" . collect($missions)->flatten()->implode(',') . ")
                GROUP BY shop_id)"
        );

        return $query;
    }

    public function getMissionsForShops($shops)
    {
        $query = Mission::listQuery();

        $query = $query->whereRaw(
            "id IN (
                SELECT mission_id
                FROM wave_shop_mission
                WHERE wave_id = " . $this->getKey() . "
                AND shop_id IN (" . collect($shops)->flatten()->implode(',') . ")
                GROUP BY mission_id)"
        );

        return $query;
    }

    public function setMissionDates($missionId, $periods, $programId, $date_exlusion)
    {

        $old_dates = DB::table('wave_mission')
            ->select('id')
            ->where('wave_id', $this->getKey())
            ->where('program_id', $programId)
            ->where('mission_id', $missionId)
            ->get();
        DB::table('wave_mission_date')
            ->where('wave_mission_id', $old_dates[0]['id'])
            ->delete();

        foreach ($periods as $date) {
            $validator = Validator::make(
                [
                    'date' => $date,
                    'date_start' => $date['date_start'],
                    'date_end' => $date['date_end'],
                ],
                [
                    'date' => 'required|array:date_start,date_end',
                    'date_start' => 'date|required',
                    'date_end' => 'date|required',
                ]
            );
            $validator->passOrDie();
            DB::table('wave_mission_date')
                ->insert([
                    'wave_mission_id' => $old_dates[0]['id'],
                    'date_start' => $date['date_start'],
                    'date_end' => $date['date_end'],
                    'date_exclusion' => json_encode($date_exlusion),
                ]);
        }
    }

    /**
     * Return the number of smices of the wave.
     * (1 smice = 1 target mark as read)
     * @return int
     */
    public function getSmices()
    {
        $result = DB::table('show_scoring')
            ->selectRaw('COUNT (DISTINCT wave_target_id) as quantity')
            ->where('wave_id', $this->getKey())
            ->first();

        return ($result) ? $result['quantity'] : 0;
    }

    /**
     * Return the global score of the wave.
     * @return null|int
     */
    public function getScore()
    {
        $result = DB::table('show_scoring')
            ->selectRaw('SUM(score) / SUM(weight) as score')
            ->where('wave_id', $this->getKey())
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the score for one sequence of the wave.
     * @param $sequence_id
     * @return null|int
     */
    public function getSequenceScore($sequence_id)
    {
        $result = DB::table('show_scoring')
            ->selectRaw('SUM(criteria_score) / SUM(criteria_weight) as score')
            ->where([
                'wave_id'     => $this->getKey(),
                'sequence_id' => $sequence_id,
            ])
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the scores for one or more sequences of the wave
     * @param array $sequences_id
     * @param $language
     * @return array An array whose keys are the sequences id and the values the scores.
     */
    public function getSequencesScores(array $sequences_id = [], $language = 'fr')
    {
        $query = DB::table('show_scoring')
            ->selectRaw("
            sequence_id as id,
            sequence_name as sequence,
            SUM(criteria_score) / SUM(criteria_weight) as score
            ")
            ->where('wave_id', $this->getKey())
            ->where('scoring', true)
            ->orderBy('sequence_order', 'ASC')
            ->groupBy('sequence_id', 'sequence_name', 'sequence_order');

        if (!empty($sequences_id)) {
            $query->whereIn('sequence_id', $sequences_id);
        }

        $results = $query->get();
        foreach ($results as $key => $result) {
            $results[$key]['sequence'] = json_decode($result['sequence']);
        }

        return $results;
    }

    /**
     * Return an array containing the satisfaction percentage for the wave
     * Default:
     *  - low [0 - 25]
     *  - medium [26 - 50]
     *  - good [51 - 75]
     *  - excellent [75 - 100 ]
     *
     * @return array
     *
     * https://www.youtube.com/watch?v=a0fkNdPiIL4
     */
    public function getSatisfaction()
    {
        $smices        = $this->getSmices();
        $low           = 0;
        $low_max       = $this->satisfaction_low_range;
        $medium        = $this->satisfaction_low_range + 1;
        $medium_max    = $this->satisfaction_medium_range;
        $good          = $this->satisfaction_medium_range + 1;
        $good_max      = $this->satisfaction_good_range;
        $excellent     = $this->satisfaction_good_range + 1;
        $excellent_max = $this->satisfaction_excellent_range;
        $satisfaction  = DB::table('show_wave_target_scoring')
            ->selectRaw("
            COUNT(CASE WHEN score BETWEEN {$low} AND {$low_max} THEN 1 ELSE NULL END) as low,
            COUNT(CASE WHEN score BETWEEN {$medium} AND {$medium_max} THEN 1 ELSE NULL END) as medium,
            COUNT(CASE WHEN score BETWEEN {$good} AND {$good_max} THEN 1 ELSE NULL END) as good,
            COUNT(CASE WHEN score BETWEEN {$excellent} AND {$excellent_max} THEN 1 ELSE NULL END) as excellent
            ")
            ->where('wave_id', $this->getKey())
            ->first();

        foreach ($satisfaction as $level => $number_smices) {
            $satisfaction[$level] = round(($number_smices * 100) / $smices);
        }

        return $satisfaction;
    }

    /**
     * Return the score for one theme of the wave.
     * @param $theme_id
     * @return null
     */
    public function getThemeScore($theme_id)
    {
        $result = DB::table('show_scoring')
            ->selectRaw('SUM(question_score) / SUM(question_weight) as score')
            ->where([
                'wave_id'  => $this->getKey(),
                'theme_id' => $theme_id,
            ])
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the score for one job of the wave.
     * @param $job_id
     * @return null
     */
    public function getJobScore($job_id)
    {
        $result = DB::table('show_scoring')
            ->selectRaw('SUM(question_score) / SUM(question_weight) as score')
            ->where([
                'wave_id' => $this->getKey(),
                'job_id'  => $job_id,
            ])
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the score for one criteria a of the wave.
     * @param $criteria_a_id
     * @return null
     */
    public function getCriteriaAScore($criteria_a_id)
    {
        $result = DB::table('show_scoring')
            ->selectRaw('SUM(question_score) / SUM(question_weight) as score')
            ->where([
                'wave_id'       => $this->getKey(),
                'criteria_a_id' => $criteria_a_id,
            ])
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the score for one criteria b of the wave.
     * @param $criteria_b_id
     * @return null
     */
    public function getCriteriaBScore($criteria_b_id)
    {
        $result = DB::table('show_scoring')
            ->selectRaw('SUM(question_score) / SUM(question_weight) as score')
            ->where([
                'wave_id'       => $this->getKey(),
                'criteria_b_id' => $criteria_b_id,
            ])
            ->first();

        return ($result) ? $result['score'] : null;
    }

    /**
     * Return the score for one criteria of the wave.
     * @param $criteria_id
     * @return null
     */
    public function getCriteriaScore($criteria_id)
    {
        $result = DB::table('show_scoring')
            ->selectRaw('SUM(question_score) / SUM(question_weight) as score')
            ->where([
                'wave_id'     => $this->getKey(),
                'criteria_id' => $criteria_id,
            ])
            ->first();
        return ($result) ? $result['score'] : null;
    }

    public function getShops()
    {
        $wave_target = DB::table('show_shops_from_wave_target')
            ->selectRaw('*')
            ->where('wave_id', $this->getKey())
            ->get();

        return ($wave_target) ? $wave_target : [];
    }

    public static function getPeriod($period, $programs, $society_id)
    {
        if (!intval($period) || !intval($society_id))
            return false;
        $w = Wave::select('id')->where('society_id', $society_id)->get();
        $res = \DB::table('wave_target')
            ->select('wave_id')
            ->wherein('program_id', $programs)
            ->wherein('wave_id', $w)
            ->groupBy('wave_id');
        $waves_use = $res->get();

        $result = Wave::select('id')->where('society_id', $society_id)
            ->whereIn('id', $waves_use)
            ->orderBy('date_start', 'DESC')
            ->limit($period)
            ->get()
            ->toArray();
        return $result;
    }
}
