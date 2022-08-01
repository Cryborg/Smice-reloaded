<?php

namespace App\Hooks;

use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Classes\Helpers\NotificationHelper;
use App\Models\Mission;
use App\Models\WaveUser;
use Carbon\Carbon;

/**
 * The Hook called when targets are launched
 *
 * Class HookTargetsLaunched
 * @package App\Hooks
 */
class HookTargetsLaunched extends Hook
{
    protected $system       = true;

    private $users_id       = [];

    private $targets_id     = [];

    private $scope          = Mission::SCOPE_TO_SMICERS;

    public final function   run()
    {
        $this->_sendPropositionEmails();
    }

    public final function   setUsers(array $users_id)
    {
        $this->users_id     = $users_id;
    }

    public final function   getUsers()
    {
        return $this->users_id;
    }

    public final function   setTargets(array $targets_id)
    {
        $this->targets_id   = $targets_id;
    }

    public final function   getTargets()
    {
        return $this->targets_id;
    }

    public final function   setScope($scope)
    {
        $this->scope        = $scope;
    }

    public final function   getScope()
    {
        return $this->scope;
    }

    private final function  _sendPropositionEmails()
    {
        $wave_users = WaveUser::with('target.shop', 'user')
            ->whereIn('user_id', $this->getUsers())
            ->whereIn('wave_target_id', $this->getTargets())
            ->get()
            ->groupBy('user_id')
            ->map(function ($item) {
                $rows = [];
                $rows['rows'] = [];
                foreach ($item as $wave_user) {
                    /* @var $wave_user WaveUser */
                    $rows['user_id'] = $wave_user->user_id;
                    $rows['accroche'] = $wave_user->target->accroche;
                    $rows['short_brief'] = $wave_user->target->description;
                    $rows['target_picture'] = $wave_user->target->picture;
                    $rows['name'] = $wave_user->user->first_name;
                    $rows['mission_link'] = env('FRONT_URL') . '/#/mission/date/' . $wave_user->uuid;
                    array_push($rows['rows'], [
                        'shop_name'             => $wave_user->target->shop->name,
                        'shop_city'             => $wave_user->target->shop->city,
                        'shop_street'           => $wave_user->target->shop->street,
                        'shop_street2'          => $wave_user->target->shop->street2,
                        'shop_postal_code'      => $wave_user->target->shop->postal_code,
                        'target_name'           => $wave_user->target->name,
                        'target_start'          => $wave_user->target->date_start,
                        'target_end'            => $wave_user->target->date_end,
                        'target_description'    => $wave_user->target->description,
                        'target_hours'          => $wave_user->target->hours,
                        'target_type'           => $wave_user->target->type,
                        'target_picture'        => $wave_user->target->picture,
                        'uuid'                  => $wave_user->uuid,
                    ]);
                }
                return $rows;
            })->keyBy('user_id');
        $delay = 0;
        $mail['to'] = $wave_users->keys()->all();
        $mail['merge_vars'] = $wave_users->all();
        if ($wave_users->count()) {
            $current_date = strtotime(Carbon::now()->toDateString());
            $start = array_get($wave_users->first(), 'rows.0.target_start');
            //$launch_date = strtotime($start); //no reason to send mail at this date, mail are sent when user click on launch
            //if ($current_date < $launch_date)
                //$delay = $launch_date - $current_date;
        }
        if ($this->scope === Mission::SCOPE_TO_CONTRIBUTORS) {
           //on envoi rien
        } elseif ($this->scope === Mission::SCOPE_TO_SMICERS && count($mail['to'])) {
            if ($delay && $delay > 0) {
                SmiceMailSystem::later($delay, SmiceMailSystem::MISSION_INVITATION, function($message) use ($mail) {
                    $message->to($mail['to']);
                    $message->subject('Une nouvelle mission Smice est disponible, découvrez-la vite!');
                    $message->addMergeVars($mail['merge_vars']);   
                }, 'fr');
            } else {
                SmiceMailSystem::send(SmiceMailSystem::MISSION_INVITATION, function($message) use ($mail)
                {
                    $message->to($mail['to']);
                    $message->subject('Une nouvelle mission Smice est disponible, découvrez-la vite!');
                    $message->addMergeVars($mail['merge_vars']);
                }, 'fr');
                //NotificationHelper::PushNotification($mail['to'], "Une nouvelle mission est disponible !");
            }
        } elseif ($this->scope === Mission::SCOPE_TO_AUTO_CONTRIBUTORS) {
                //on envoi rien
        }
    }
}