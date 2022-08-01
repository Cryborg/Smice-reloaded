<?php

namespace App\Classes\Services;

use App\Models\Alert;
use App\Models\User;
use App\Models\AlertPending;
use App\Models\WaveTarget;
use Carbon\Carbon;
use App\Classes\Results\Results;
use App\Classes\Helpers\NotificationHelper;


class AlertService
{
    public function addAlert($wave_target_id, $type = '')
    {
        //get if wave_target is link to alert
        $waveTarget = WaveTarget::with('mission')->whereId($wave_target_id)->first();
        if ($waveTarget) {
            $this->sendNotification($type, $wave_target_id, $waveTarget->user_id, $waveTarget->name);
        }

        if (count($waveTarget->mission->alerts) > 0) {
            //foreach alert
            foreach ($waveTarget->mission->alerts as $alert) {
                if (($alert->option === 1)) {
                    //$this->addHookAlert($alert, $waveTarget, $type);
                }
                if ($alert->option === 2 && ($waveTarget->status === 'read' || $waveTarget->status === 'answered')) {
                    $this->addScoreAlert($alert, $waveTarget);
                }
                if ($alert->type !== $type) {
                    continue;
                }

                /*
                $alertPending = new AlertPending($alert->getAttributes());
                if ($alert->hours) {
                    $alertPending->scheduled_for = Carbon::now();
                } elseif ($alert->date) {
                    $alertPending->scheduled_for = $alert->date;
                }

                if ($alert->scheduled_for == Alert::SCHEDULED_FOR_END_WAVE_BY_SHOP) {
                    $res = $this->missionDoneForOneShop($waveTarget->wave_id, $waveTarget->shop_id);
                    if (!$res) {
                        $alertPending->scheduled_alert = Alert::SCHEDULED_FOR_END_WAVE_BY_SHOP;
                    }
                }
                if ($alert->scheduled_for == Alert::SCHEDULED_FOR_END_WAVE_ALL_SHOPS) {
                    $res = $this->missionDoneForAllShops($waveTarget->wave_id);
                    if (!$res) {
                        $alertPending->scheduled_alert = Alert::SCHEDULED_FOR_END_WAVE_ALL_SHOPS;
                    }
                }
                */
            }
        }
    }

    public function addScoreAlert($alert, $waveTarget)
    {

        //check score condition
        //read score_of
        //theme id
        //get score on level question.

        $score = $this->GetScore($alert, $waveTarget);
        $score_condition = false;
        if ($score !== null) {
            if ($alert->operand === 'inferior') {
                if ($score < intval($alert->score)) {
                    $score_condition = true;
                }
            } else {
                if ($score > intval($alert->score)) {
                    $score_condition = true;
                }
            }
        }
        if ($score_condition) {
            //ok alert must be create
            $this->saveAlert($waveTarget, $alert, Carbon::now(), $score);
        }
    }

    public function saveAlert($waveTarget, $alert, $scheduled_for, $score_result)
    {
        $a = new AlertPending();
        $a->scheduled_alert = $alert->scheduled_for;
        $a->scheduled_for = $scheduled_for;
        $a->alert_id = $alert->id;
        $a->score_of = $alert->type;
        $a->operand = $alert->operand;
        $a->score_result = $score_result;
        $a->score = $alert->score;
        $a->wave_target_id = $waveTarget->id;
        $a->shop_id = $waveTarget->shop_id;
        $a->checked_at = Carbon::now();
        $a->created_by = 30;
        $a->save();
    }

    public function Getfilters($alert, $WaveTarget)
    {

        switch ($alert->type) {
            case "wave":
                $score_item = 'program';
                $score_filter = $WaveTarget->program_id;
                break;
            case "mission":
                $score_item = 'wave_target';
                $score_filter = $WaveTarget->id;
                break;
            default:
                $score_item = $alert->type;
                $score_filter = $alert->{$alert->type . "_id"};
                break;
        }
        return [
            'y' => $score_item,
            'x' => 'wave',
            'filters' => [
                'general' => [
                    $score_item => [$score_filter],
                    'survey' => $alert->survey_id ? $alert->survey_id : null,
                    'program' => [$WaveTarget->program_id],
                    'society' => $WaveTarget->wave->society_id,
                    'axes' => [],
                    'axes_as_filter' => [],
                    'wave_target_id' => [$WaveTarget->id],
                    'question_level' => []
                ]

            ]
        ];
    }

    public function GetScore($alert, $WaveTarget)
    {
        //get user link to society
        $u = User::where('society_id', $WaveTarget->wave->society_id)->first();
        $request = $this->Getfilters($alert, $WaveTarget);
        $result = new Results();
        $r = $result->_getGlobalFromWaveTargets($request, $u, true);
        return $r['score'];
    }

    public function missionDoneForOneShop($wave_id, $shop_id)
    {
        $count = WaveTarget::with(['wave' => function ($query) use ($wave_id) {
            $query->where('id', $wave_id);
        }])->whereStatus(WaveTarget::STATUS_READ)->whereShopId($shop_id)->count();

        return $count ? true : false;
    }

    public function missionDoneForAllShops($wave_id)
    {
        $count = WaveTarget::with(['wave' => function ($query) use ($wave_id) {
            $query->where('id', $wave_id);
        }])->whereStatus(WaveTarget::STATUS_READ)->count();

        return $count ? true : false;
    }

    public function sendNotification($type, $wave_target_id, $user_id, $mission_name) {
        if ($type === Alert::TYPE_SURVEY_READ) {
            //check if relecture
            $waveTarget = WaveTarget::whereId($wave_target_id)->first();
            if ($waveTarget->validation === WaveTarget::VALIDATION_MANUAL) {
                NotificationHelper::PushNotification($user_id, "Félicitation ! Votre mission est validée", $mission_name);
            }
        }

        if ($type === Alert::TYPE_ASSIGN_MISSION) {
            //check if assignation
            NotificationHelper::PushNotification($user_id, $mission_name, "Une mission vous a été attribuée");
        }

        if ($type === Alert::TYPE_SURVEY_INVALIDATED) {
            //check if invalidation
            NotificationHelper::PushNotification($user_id, $mission_name, "Smice a une question");
        }


        
    }
}
