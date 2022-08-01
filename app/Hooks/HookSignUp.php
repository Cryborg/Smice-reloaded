<?php

namespace App\Hooks;

use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Models\User;
use App\Models\Zapier;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

/**
 * The Hook called when someone creates an account on Smice
 *
 * Class HookSignUp
 * @package App\Hooks
 */
class HookSignUp extends Hook
{
    protected static $action = 'Nouvel utilisateur';

    /**
     * @var User
     */
    public $user = null;

    protected $system = true;

    public final function run()
    {
        $this->sendWelcomeEmail();
        $this->triggerZapier();

        return true;
    }

    private final function sendWelcomeEmail()
    {
        SmiceMailSystem::send(SmiceMailSystem::WELCOME, function (SmiceMailSystem $message) {
            $message->to([$this->user->getKey()]);
            $message->subject('Bienvenue sur Smice');
            $message->addMergeVars([
                $this->user->id => [
                    'name'  =>      $this->user->first_name,
                    'date_limit'        => Carbon::now()->addDays(30)->format('d/m/Y'),
                    'content' => Crypt::encrypt($this->user->email),
                ],
            ]);
        }, $this->user->language->code);   

        return true;
    }

    private final function triggerZapier()
    {
        Zapier::send('new_user', $this->user->society->getKey(), $this->user->toArray());
    }
}