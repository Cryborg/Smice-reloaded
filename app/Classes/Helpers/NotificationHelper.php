<?php

namespace App\Classes\Helpers;

use App\Models\User;
use App\Classes\SmiceClasses\SmiceMailSystem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Cache;
use Mail;

class NotificationHelper
{
    /**
     * little func to send push notification
     * @param $users, $title, $body, $action
     * @return null
     */
    public static function PushNotification($users, $body, $title = "Smice", $url = null, $action = null)
    {
        if ($users) {
            if (is_int($users)) {
                $u =  User::find($users);
                $users = $u->email;
            }
            if ($users) {

                $wonderpush = new \WonderPush\WonderPush(env('WONDERPUSH_ACCESS_TOKEN'));
                $wonderpush->deliveries()->create(
                    \WonderPush\Params\DeliveriesCreateParams::_new()
                        ->setTargetUserIds($users)
                        ->setNotification(\WonderPush\Obj\Notification::_new()
                            ->setAlert(
                                \WonderPush\Obj\NotificationAlert::_new()
                                    ->setTitle($title)
                                    ->setText($body)
                                    ->setTargetUrl($url)
                            ))
                );
            }
        }
    }

    public static function sendMail($to, $subject, $var, $type, $template_name)
    {
        Validator::make(
            ['subject' => $subject],
            ['subject' => 'required|string'],
            ['to' => $to],
            ['to' => 'required|string']
        )->passOrDie();


        $mandrill = new \Mandrill(\Config::get('services.mandrill.secret'));
        $cachekey = 'getListTemplaes';
        $templates = Cache::get($cachekey , function () use ($cachekey, $mandrill) {
            $templates = $mandrill->templates->getList();
            Cache::put($cachekey, $templates, 30);
            return $templates;
        });
        $defaultTemplate = $template_name . '_fr';
        $key = array_search($defaultTemplate, array_column($templates, 'name'));
        $template = $key ? $templates[$key] : $templates[array_search($defaultTemplate, array_column($templates, 'name'))];
        if ($type === 'actionplan') {
            $dateFormat = 'd-m-Y';
            $due_date = $var['due_date'] ? Carbon::parse($var['due_date'])->format($dateFormat) : null;
            $created_at = $var['created_at'] ? Carbon::createFromFormat('Y-m-d H:i:s', $var['created_at'])->format($dateFormat) : null;
            $created_by = $var['created_by'] ? User::find($var['created_by'])->email : null;
            $due_date_old = $var['due_date'];
            if ($var['extern'])
                $assigned_to = $var['extern'];
            else {
                $u = User::find($var['assigned_to']);
                $assigned_to = $u->name;

            }
            $template['code'] = str_replace('{{action_plan_name}}', $var['name'], $template['code']);
            $template['code'] = str_replace('{{extern}}', $var['extern'], $template['code']);
            $template['code'] = str_replace('{{assigned_to}}', $assigned_to, $template['code']);
            $template['code'] = str_replace('{{content}}', $var['content'], $template['code']);
            $template['code'] = str_replace('{{due_date}}', $due_date, $template['code']);
            $template['code'] = str_replace('{{due_date_old}}', $due_date_old, $template['code']);
            $template['code'] = str_replace('{{created_at}}', $created_at, $template['code']);
            $template['code'] = str_replace('{{created_by}}', $created_by, $template['code']);
        }

        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

        Mail::send([], [], function ($message) use ($to, $subject, $template) {
            $message
                ->from(SmiceMailSystem::NO_REPLY_EMAIL)
                ->to($to)
                ->subject($subject)
                ->setBody($template['code'], 'text/html');
        });
    }
}
