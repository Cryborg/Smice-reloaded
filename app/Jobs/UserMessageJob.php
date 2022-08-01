<?php

namespace App\Jobs;

use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class UserMessageJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /* @var $from User */
    private $from = null;
    private $type = null;
    private $subject = null;
    private $message = null;
    private $users = [];

    public function __construct(User $from, $users, $type, $message, $subject = null)
    {
        $this->from = $from;
        $this->users = $users;
        $this->type = $type;
        $this->message = $message;
        $this->subject = $subject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (($this->type === 'email_intercom') || ($this->type === 'inapp')) {
            $admin_users = \Intercom::admins()->getAdmins();
            $admin_user = null;

            foreach ($admin_users->admins as $admin) {
                if ($admin->email === $this->from->email) {
                    $admin_user = $admin;
                    break;
                }
            }
            if (!$admin_user) {
                return;
            }
            $intercom_users = [];
            foreach ($this->users as $user) {
                $users = \Intercom::users()->getUsers(['email' => $user->email]);
                if (!empty($users->users)) {
                    $intercom_user = $users->users[0];
                } else {
                    $intercom_user = \Intercom::users()->create([
                        'user_id' => $this->user->id,
                        'email' => $this->user->email,
                        'name' => $this->user->first_name . ' ' . $this->user->last_name,
                        'phone' => $this->user->phone
                    ]);
                }
                $intercom_users[] = $intercom_user;
            }
            $message = [
                'message_type' => 'inapp',
                'body' => $this->message,
                'from' => [
                    'type' => 'admin',
                    'id' => $admin_user->id
                ],
                'to' => [
                    'type' => 'user',
                    'id' => -1
                ]
            ];
            if ($this->type === 'email_intercom') {
                $message['subject'] = isset($this->subject) ? $this->subject :'Nouveau message de Smice';
                $message['message_type'] = 'email';
            }
            foreach ($intercom_users as $user) {
                $message['to']['id'] = $user->id;
                \Intercom::messages()->create($message);
            }
        } else if ($this->type === 'email_smice') {
            $subject = isset($this->subject) ? $this->subject :'Nouveau message de Smice';
            foreach ($this->users as $user) {
                SmiceMailSystem::send(SmiceMailSystem::USER_MESSAGE, function (SmiceMailSystem $mail) use ($user, $subject) {
                    $mail->from($this->from->email);
                    $mail->to([$user->id]);
                    $mail->subject($subject);
                    $mail->addMergeVars([
                        $user->id => ['message' => $this->message]
                    ]);
                }, 'fr');
            }
        } else if ($this->type === 'push') {
            $wonderpush = new \WonderPush\WonderPush(env('WONDERPUSH_ACCESS_TOKEN'));
            $wonderpush->deliveries()->create(
                \WonderPush\Params\DeliveriesCreateParams::_new()
                    ->setTargetUserIds(array_column($this->users->toArray(), 'email'))
                    ->setNotification(\WonderPush\Obj\Notification::_new()
                        ->setAlert(\WonderPush\Obj\NotificationAlert::_new()
                            ->setTitle('Smice')
                            ->setText($this->message)
                        ))
            );
        }
    }
}
