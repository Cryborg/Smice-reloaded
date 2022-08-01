<?php

namespace App\Classes\SmiceClasses;

use App\Jobs\MailRunnerJob;
use App\Models\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Validator;


/**
 * This class is an Abstract Class used to send emails through the Mandrill API.
 * Other classes can inherit from this class to provide simple ways to send emails
 * on Smiceplus.
 * Every variables used to craft the email is written in a Mandrill compliant way. In
 * the end a JSON message is craft and given sent to Mandrill. This powerful feature enable
 * us to send multiples emails at the same time.
 *
 * To see the Mandrill documentation and how the email should be written, see:
 * https://mandrillapp.com/api/docs/messages.JSON.html#method=send
 *
 * Class SmiceMail
 * @package App\Classes
 */
abstract class SmiceMail
{
    use DispatchesJobs;

    /**
     * List of recipients
     * @var array|\Illuminate\Support\Collection
     */
    protected $to                   = [];

    /**
     * Delay in seconds before sending the emails
     * @var int
     */
    protected $delay                = 0;

    /**
     * ID of the sender. 0 is the SERVER itself, otherwise it should be a user_id
     * @var null
     */
    protected $sender_id            = null;

    /**
     * The subject, must be a string
     * @var null
     */
    protected $subject              = null;

    /**
     * The address from which the email is to be sent
     * @var null
     */
    protected $from                 = null;

    /**
     * The content, must be a string
     * @var null
     */
    protected $html                 = null;

    /**
     * Headers applied to the email
     * @var array|\Illuminate\Support\Collection
     */
    protected $headers              = [];

    /**
     * Any kind of attachments for the email
     * The array must be as defined by Mandrill's documentation
     * @var array
     */
    protected $attachments          = [];

    /**
     * Any kind of image you want to INLINE attach in the email
     * The array must be mandrill compliant
     * <img src="cid:image_name"/>
     * @var array
     */
    protected $images               = [];

    /**
     * Mandrill merge_vars attribute
     * @var array|\Illuminate\Support\Collection
     */
    protected $merge_vars           = [];

    /**
     * Mandrill global_merge_vars attribute
     * @var array|\Illuminate\Support\Collection
     */
    protected $global_merge_vars    = [];

    /**
     * The queue on which the emails will be pushed
     */
    CONST EMAIL_QUEUE               = 'emails';

    protected function              __construct()
    {
        $this->to                   = collect($this->to);
        $this->headers              = collect($this->headers);
        $this->global_merge_vars    = collect($this->global_merge_vars);
    }

    public final function           to(array $to)
    {
        $users = User::whereIn('id', $to)
            ->selectRaw('id,society_id,CONCAT(first_name, \' \', last_name) as name,email,\'bcc\' as type')
            ->get();

        $this->to = collect($users->toArray());
        $users->each(function($user) {
            $this->mergeVars($user['email'], 'name', $user['name']);
        });

        return $this;
    }

    public final function           sender($sender_id)
    {
        $this->sender_id            = $sender_id;

        return $this;
    }

    public final function           subject($subject)
    {
        $this->subject              = $subject;

        return $this;
    }

    protected final function        html($html)
    {
        $this->html                 = $html;

        return $this;
    }

    public final function           from($from)
    {
        $this->from                 = $from;

        return $this;
    }

    public final function           header($key, $value)
    {
        $this->headers->put($key, $value);

        return $this;
    }

    public final function           attachments(array $files)
    {
        if (!empty($files)) {
            $this->attachments = $files;
        }

        return $this;
    }

    public final function           images(array $images)
    {
        if (!empty($images)) {
            $this->images = $images;
        }

        return $this;
    }

    protected final function        delay($delay)
    {
        $this->delay                = intval($delay);

        return $this;
    }

    protected final function        mergeVars($rcpt, $key, $content)
    {
        if (!isset($this->merge_vars[$rcpt]))
        {
            $merge_vars = [
                'rcpt' => $rcpt,
                'vars' => []
            ];
            $this->merge_vars[$rcpt] = $merge_vars;
        }
        array_push(
            $this->merge_vars[$rcpt]['vars'],
            ['name' => $key, 'content' => $content]
        );

        return $this;
    }

    protected final function globalMergeVars(array $global_merge_vars)
    {
        $this->global_merge_vars->push($global_merge_vars);
        return $this;
    }

    private final function          _validate()
    {
        $validator                  = Validator::make(
            [
                'to'                => $this->to->all(),
                'delay'             => $this->delay,
                'sender_id'         => $this->sender_id,
                'subject'           => $this->subject,
                'html'              => $this->html
            ],
            [
                'to'            => 'array',
                'delay'         => 'integer|min:0',
                'sender_id'     => 'integer|required|min:0',
                'subject'       => 'string',
                'html'          => 'string|required'
            ]
        );

        $validator->passOrDie();
    }

    private final function          _craft()
    {
        $message                    = [];

        $this->_validate();
        $message['merge']               = true;
        $message['html']                = $this->html;
        $message['to']                  = $this->to->all();
        $message['subject']             = $this->subject;
        $message['from_email']          = $this->from;
        $message['merge_language']      = 'handlebars';
        $message['headers']             = $this->headers->toArray();
        $message['merge_vars']          = $this->merge_vars;
        $message['images']              = $this->images;
        $message['attachments']         = $this->attachments;
        $message['global_merge_vars']   = $this->global_merge_vars->toArray();

        return $message;
    }

    protected final function        pushOnQueue()
    {
        $message                    = $this->_craft();
        $job                        = (new MailRunnerJob($this->sender_id, $message));

        $job->delay($this->delay)->onQueue(self::EMAIL_QUEUE);
        $this->dispatch($job);
    }
}