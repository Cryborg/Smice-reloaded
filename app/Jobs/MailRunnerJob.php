<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Mandrill;

class MailRunnerJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    private $user       = null;
    private $emails     = null;
    private $round      = null;

    public function     __construct($user, $emails, $round = 1)
    {
        $this->user     = $user;
        $this->emails   = $emails;
        $this->round    = $round;
    }

    public function     handle()
    {
        $mandrill = new Mandrill(Config::get('services.mandrill.secret'));

        if ($this->round == 1) {
            $users = [];
            $results = $mandrill->messages->send($this->emails);
            foreach ($results as $key => $result) {
                $user_data = [
                    'user_id' => $this->emails['to'][$key]['id'],
                    'society_id' => $this->emails['to'][$key]['society_id'],
                    'email' => $this->emails['to'][$key]['email'],
                    'mandrill_id' => $result['_id'],
                    'status' => $result['status'],
                    'date_send' => date('Y-m-d H:i:s', time()),
                    'reject_reason' => null
                ];
                if (in_array($result['status'], ['rejected', 'hard-bounce', 'soft-bounce', 'spam', 'unsub', 'custom', 'invalid-sender', 'invalid', 'test-mode-limit', 'unsigned', 'rule'])) {
                    $user_data['reject_reason'] = $result['reject_reason'];
                }

                array_push($users, $user_data);
            }
            $this->emails = $users;
        }
        /*else
        {
            $mail_logs = [];
            foreach ($this->emails as $key => $result)
            {
                try
                {
                    $info = $mandrill->messages->info($result['mandrill_id']);
                    $content = $mandrill->messages->content($result['mandrill_id']);
                } catch (\Mandrill_Error $e)
                {
                    var_dump($e);
                }
                array_push($mail_logs, [
                    'to'   => $result['email'],
                    'from' => $content['from_email'],
                    'status' => $info['state'],
                    'reject_reason' => $result['reject_reason'],
                    'subject' => $content['subject'],
                    'html' => $content['html'],
                    'attachments' => json_encode($content['attachments']),
                    'date_send' => date('Y-m-d H:i:s', $content['ts']),
                    'mandrill_id' => $result['mandrill_id'],
                    'user_id' => 1,
                    'created_by' => 1,
                    'society_id'  => $result['society_id']
                ]);

                unset($this->emails[$key]);
            }
            DB::table('mail_log')->insert($mail_logs);
        }
        if (!empty($this->emails))
        {
            $job = (new MailRunnerJob($this->user, $this->emails, 2))
                ->delay(60)
                ->onQueue('emails');

            $this->dispatch($job);
        }*/
    }
}