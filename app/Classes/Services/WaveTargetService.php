<?php

namespace App\Classes\Services;

use App\Exceptions\SmiceException;
use App\Classes\Helpers\ArrayHelper;
use App\Classes\MissionFilter;
use App\Hooks\Hook;
use App\Hooks\HookTargetsLaunched;
use App\Models\Shop;
use App\Models\Wave;
use App\Models\WaveTarget;
use App\Models\WaveUser;
use App\Models\WaveTargetAnonymous;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Webpatser\Uuid\Uuid;

class WaveTargetService extends SmiceService
{
    /**
     * The request parameters
     * @var null|array
     */
    private $params = null;

    public function all()
    {
        if (!isset($this->params['society_id'])) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_ACCEPTABLE,
                SmiceException::E_RESOURCE,
                'Parameter is missing'
            );
        }

        $mode       = array_get($this->params, 'mode', 3);
        $type       = array_get($this->params, 'type');
        $status     = array_get($this->params, 'status');
        $supervisor = array_get($this->params, 'supervisor');
        $reader     = array_get($this->params, 'reader');
        $user_ids   = array_get($this->params, 'user_id');
        $search     = array_get($this->params, 'search');
        $score      = array_get($this->params, 'score');
        if ($user_ids) {
            $user_ids = explode(',', $user_ids);
        }
        $wave_target_table = "show_targets";
        if ($this->_isAnonymousMode()) {
            $target = new WaveTargetAnonymous();
            $wave_target_table = "show_targets_anonymous";
        } else {
            $target = new WaveTarget();
        }

        $missions = $target->newListQuery();
        $this->roleFilter();

        $missions = $this->_addWhereStatement($missions, $mode, $wave_target_table);
        if ($this->_isAnonymousMode()) {
            $restricted_shop = Shop::getRestrictedshop(
                $this->user->getKey(),
                $this->user->society_id,
                $this->user->current_society_id
            );
            $linked_shops = ArrayHelper::getIds($restricted_shop->get()->toArray());
            $missions->whereIn($wave_target_table . '.shop_id', $linked_shops);
        }

        if ($type) {
           // $missions->where($wave_target_table .'.type', $type);
        }

        if ($user_ids) {
            $missions->whereIn($wave_target_table .'.user_id', $user_ids);
        }
        if ($supervisor) {
            $missions->where($wave_target_table . '.reviewer_id', $supervisor);
        }

        if ($reader) {
            $missions->where($wave_target_table . '.reader_id', $reader);
        }

        if ($status) {
            $arr = explode(',', $status);
            $missions->whereIn($wave_target_table . '.status', $arr);
        }
        if ($score > 0) {
            $missions->where($wave_target_table . '.global_score', '>', $score);
        }
        else if ($score < 0) {
            $missions->where($wave_target_table . '.global_score', '<', abs($score));
        }
        if ($search) {
            $missions->where(function ($q) use ($search, $wave_target_table) {
                $q->where('user', 'ilike', "%$search%")
                    ->orWhere('shop', 'ilike', "%$search%")
                    ->orWhere('mission', 'ilike', "%$search%")
                    ->orWhere('wave', 'ilike', "%$search%")
                    ->orWhere('email', 'ilike', "%$search%")
                    ->orWhere('phone', 'ilike', "%$search%")
                    ->orWhere($wave_target_table . '.id', intval($search));
            });
        }
        $missions->orderBy('id', 'asc');

        return $missions;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    private function _isAnonymousMode()
    {
        return (1 === $this->user->society_id) ? false : true;
    }

    private function roleFilter()
    {
        if ($this->user->isReader()) {
            $this->params['reader_id']  = 15;
            $this->params['society_id'] = $this->user->society->getKey();
        }
    }

    private function _addWhereStatement($missions, $mode, $wave_target_table)
    {
        // if is array wave or shop where in
        if (isset($this->params['society_id'])) {
            $missions->where($wave_target_table . '.society_id', $this->params['society_id']);
        }
        if (isset($this->params['program_id'])) {
            $missions->where($wave_target_table . '.program_id', $this->params['program_id']);
        }
        if (isset($this->params['scenario_id']) && '' != $this->params['scenario_id']) {
            $missions->whereIn($wave_target_table . '.scenario_id', explode(',', $this->params['scenario_id']));
        }
        if (isset($this->params['mission_id'])) {
            $missions->where($wave_target_table . '.mission_id', $this->params['mission_id']);
        }
        if (isset($this->params['shop_id'])) {
            if (strpos($this->params['shop_id'], ',') !== false) {
                $this->params['shop_id'] = explode(',', $this->params['shop_id']);
                $missions->whereIn($wave_target_table . '.shop_id', $this->params['shop_id']);
            } else {
                $missions->where($wave_target_table . '.shop_id', $this->params['shop_id']);
            }
        }
        if (isset($this->params['late_survey']) && $this->params['late_survey']) {
            $missions->where($wave_target_table . '.status', WaveTarget::STATUS_SELECTED);
            $missions->where($wave_target_table . '.visit_date', '<', Carbon::now()->addDays(2));
        }
        if (isset($this->params['visit_date'])) {
            $missions->where($wave_target_table . '.visit_date', $this->params['visit_date_condition'], $this->params['visit_date']);
        }
        if (isset($this->params['percentage_completeness'])) {
            $missions->where($wave_target_table . '.percentage_completeness', $this->params['percentage_completeness_condition'], $this->params['percentage_completeness']);
        }
        if (isset($this->params['date_status'])) {
            $missions->where($wave_target_table . '.date_status', $this->params['date_status']);
        }
        if (isset($this->params['date_start'])) {
            $missions->where($wave_target_table . '.date_start', $this->params['date_start']);
        }
        if (isset($this->params['date_end'])) {
            $missions->where($wave_target_table . '.date_end', $this->params['date_end']);
        }
        if (isset($this->params['axe_id'])) {
            //get all shop for selected axe
            $axes     = explode(',', $this->params['axe_id']);

            $shops_id = \DB::table('shop_society')->where('society_id', $this->user->currentSociety->getKey())->select('shop_id');
            $sid      = [];
            foreach ($shops_id->get() as $id) {
                $sid[] = $id['shop_id'];
            }
            $shops = Shop::whereIn('id', $sid);
            if (count($axes)) {
                $shops->whereHas('axes', function ($query) use ($axes) {
                    $query->whereIn('id', $axes);
                });
            }

            $shop_id = $shops->get(['id'])->toArray();
            $missions->whereIn($wave_target_table . '.shop_id', $shop_id);
        }
        if (isset($this->params['wave_id']) && ('' !== $this->params['wave_id'])) {
            if (strpos($this->params['wave_id'], ',') !== false) {
                $this->params['wave_id'] = explode(',', $this->params['wave_id']);
                $missions->whereIn($wave_target_table . '.wave_id', $this->params['wave_id']);
            } else {
                $missions->where($wave_target_table . '.wave_id', $this->params['wave_id']);
            }
        }
        if (isset($this->params['reader_id'])) {
            $missions->where($wave_target_table . '.boss_id', $this->params['reader_id']);
        }

        if (isset($this->params['status'])) {
            $missions->where($wave_target_table . '.status', $this->params['status']);
        }

        if (isset($this->params['nc'])) {
            $missions->where($wave_target_table . '.global_score', '<', 100);
            $missions->where($wave_target_table . '.status', 'read');

        }

        if (isset($this->params['status_2'])) {
            if ($this->params['status_2'] === 'done') { //terminé
                $missions->wherein($wave_target_table . '.status', ['read', 'rejected']);
            }
            if ($this->params['status_2'] === 'pending') { //en cours
                $missions->wherein($wave_target_table . '.status', ['selected', 'accepted', 'answered']);
            }
        }

        if (2 == $mode) {
            $missions->where($wave_target_table . '.ended', Wave::ENDED_YES);
        } elseif (1 == $mode) {
            $missions->where($wave_target_table . '.ended', Wave::ENDED_NO);
        }

        /*$missions = $missions->leftJoin('wave_user', function ($join) {
        $join->on('show_targets.id', '=', 'wave_user.wave_target_id');
        $join->on('wave_user.user_id', '=', 'show_targets.selected_user');
        })->select('show_targets.id as id', 'show_targets.*', 'wave_user.uuid as uuid');
         */
        if (isset($this->params['mode'])) {
            unset($this->params['mode']);
        }
        if (!$this->_isAnonymousMode()) {
            $missions = $missions->leftJoin('user', function ($join) use ($wave_target_table) {
                $join->on($wave_target_table . '.user_id', '=', 'user.id');
                })->select($wave_target_table . '.*', 'user.email as email', 'user.phone as phone');
            
        }  
        $missions->orderby('id', 'desc');
        
        return $missions;
    }

    public function relaunch($wave_id, $user = null)
    {
        if ($user) {
            $this->user = $user;
        }
        $save = $users_id = [];
        $targetIds = array_get($this->params, 'missions');
        Validator::make(
            ['missions' => $targetIds],
            ['missions' => 'required|array']
        )->passOrDie();

        $targets = WaveTarget::with('shop')
            ->where('wave_id', $wave_id)
            ->whereIn('id', $targetIds)
            ->get();

        foreach ($targets as $target) {
            //check if user is selected

            if (!$target->user_id) {

                $target->status = WaveTarget::STATUS_PROPOSED;
                $target->date_status = Carbon::now()->toDateString();
                $target->save();
                $filters                           = $target->filters;
                $shop['name']                      = $target->filters['coordinates']['shops'][0]['name'];
                $shop['lat']                       = $target->shop->lat;
                $shop['lon']                       = $target->shop->lon;
                $filters['coordinates']['shops'][] = $shop;
                //search society_id for this mission
                //use wave_id to find society id
                $society_id = Wave::select('society_id')->where('id', $target->wave_id)->first();
                $society_id = $society_id->society_id;
                $users                             = MissionFilter::filter($filters, $target->mission->scope, $this->user, $target->shop->id, $society_id);
                if (count($users) > 0) {
                    $users                             = $users[$shop['name']]->all();
                }

                //remove all user link to this mission
                WaveUser::where('wave_target_id', $target->getKey())
                    ->whereIn('status_id', [5, 6, 2, 3])
                    ->delete();

                //Get all user already link to mission
                $q = WaveUser::select('user_id')
                    ->where('wave_target_id', $target->getKey())
                    ->get()->toArray();
                $user_already_exist = [];
                foreach ($q as $userid) {
                    array_push($user_already_exist, $userid['user_id']);
                }
                $q = WaveUser::select('user_id')
                    ->where('wave_target_id', $target->getKey())
                    ->where('status_id', 12)
                    ->get()->toArray();
                $user_already_refuse = [];
                foreach ($q as $userid) {
                    array_push($user_already_refuse, $userid['user_id']);
                }
                foreach ($users as $key => $user_id) {
                    if (!in_array($user_id, $user_already_exist)) {
                        //add only new
                        array_push($save, [
                            'user_id'          => $user_id,
                            'wave_target_id'   => $target->getKey(),
                            'uuid'             => Uuid::generate(4)->string,
                            'invitation_email' => true,
                            'status_id'        => 2,
                        ]);
                    }
                    //remove user already refused mission
                    if (!in_array($user_id, $user_already_refuse)) {
                        array_push($users_id, $user_id);
                    }
                }
            } else {
                throw new SmiceException(
                    SmiceException::HTTP_NOT_ACCEPTABLE,
                    SmiceException::E_RESOURCE,
                    'Impossible de relancer une mission déjà attribuée à un smiceur'
                );
            }
        }
        if (!empty($users_id)) {
            foreach ($save as $s) {
                WaveUser::insert($s);
            }
            //WaveTarget::whereIn('id', $targetIds)->update(['status' => 'proposed']); // reset??
            $users_id = array_unique($users_id);
            Hook::launch(HookTargetsLaunched::class, function ($hook) use ($targetIds, $users_id) {
                $hook->setUsers($users_id);
                $hook->setTargets($targetIds);
            });
        }
    }
}
