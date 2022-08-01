<?php

namespace App\Classes\Auth;

use App\Hooks\Hook;
use App\Hooks\HookSignUp;
use App\Models\Society;
use App\Models\User;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Validator;
use App\Classes\AvatarService;


class SignUp implements SelfHandling
{
    /**
     * @var Society|null
     */
    private $society = null;
    /**
     * @var User|null
     */
    private $user = null;
    /**
     * @var array
     */
    private $params = [];

    public function __construct(array $params, Society $society)
    {
        $this->user = new User();
        $this->society = $society;
        $this->params = $params;

        Validator::make(
            [
                'email' => array_get($params, 'email'),
                'password' => array_get($params, 'password'),
                'first_name' => array_get($params, 'first_name'),
                'last_name' => array_get($params, 'last_name'),
                'gender' => array_get($params, 'gender'),
                'password_confirmation' => array_get($params, 'password_confirmation'),
                'society_id' => $this->society->getKey(),
                'phone' => array_get($params, 'phone'),
                'birth_date' => array_get($params, 'birth_date'),
                'street' => array_get($params, 'street'),
                'city' => array_get($params, 'city'),
                'postal_code' => array_get($params, 'postal_code'),
                'country_name' => array_get($params, 'country_name'),
            ],
            [
                'email' => 'email|required|unique_with:user,society_id',
                'password' => 'string|min:6|required|confirmed',
                'first_name' => 'string|required',
                'last_name' => 'string|required',
                'gender' => 'string|required|in:male,female',
                'phone' => 'string|required',
                'birth_date' => 'date|required',
                'street' => 'string|required',
                'city' => 'string|required',
                'postal_code' => 'string|required',
                'country_name' => 'string|required',
            ]
        )->passOrDie();

        $email_to_lowercase = strtolower(array_get($params, 'email'));

        $avatar = new AvatarService();
        $picture_url = $avatar->create(array_get($params, 'first_name'), array_get($params, 'last_name'));

        $this->user->setAttribute('email', $email_to_lowercase);
        $this->user->setAttribute('password', array_get($params, 'password'));
        $this->user->setAttribute('password_confirmation', array_get($params, 'password_confirmation'));
        $this->user->setAttribute('first_name', array_get($params, 'first_name'));
        $this->user->setAttribute('last_name', array_get($params, 'last_name'));
        $this->user->setAttribute('gender', array_get($params, 'gender'));
        $this->user->setAttribute('phone', array_get($params, 'phone'));
        $this->user->setAttribute('birth_date', array_get($params, 'birth_date'));
        $this->user->setAttribute('street', array_get($params, 'street'));
        $this->user->setAttribute('city', array_get($params, 'city'));
        $this->user->setAttribute('postal_code', array_get($params, 'postal_code'));
        $this->user->setAttribute('country_name', array_get($params, 'country_name'));
        $this->user->setAttribute('language_id', 5); // default french
        $this->user->setAttribute('picture', $picture_url);
        $this->user->society()->associate($this->society);
        $this->user->currentSociety()->associate($this->society);
        
        // role smicer by default
        // for testing adding permissions on create account
        //$this->user->setAttribute('permissions', '{"alias": {"manage": true, "consult": true}, "gains": {"manage": true, "consult": true}, "roles": {"manage": true}, "shops": {"manage": true, "consult": true}, "users": {"manage": true, "consult": true}, "waves": {"administer": true}, "alerts": {"manage": true}, "surveys": {"manage": true}, "missions": {"manage": true, "consult": true, "administer": true, "manage models": true}, "payments": {"manage": true, "consult": true}, "societies": {"manage": true, "consult": true}} ');
        //$this->user->setAttribute('advanced_permissions', '{"axes": {"read": 2, "delete": true, "create/update": true}, "jobs": {"read": 2, "delete": true, "create/update": true}, "alias": {"read": 3, "delete": true, "create/update": true}, "gains": {"read": 3, "delete": true, "create/update": true}, "mails": {"read": 3}, "roles": {"read": 2, "delete": true, "create/update": true}, "shops": {"read": 2, "delete": true, "create/update": true}, "users": {"read": 2, "delete": true, "create/update": true}, "waves": {"read": 2, "delete": true, "create/update": true}, "alerts": {"read": 2, "delete": true, "create/update": true}, "graphs": {"read": 3, "delete": true, "create/update": true}, "themes": {"read": 2, "delete": true, "create/update": true}, "surveys": {"read": 3, "delete": true, "create/update": true}, "targets": {"read": false, "delete": false, "create/update": false}, "missions": {"read": 2, "delete": true, "create/update": true}, "payments": {"read": 3, "delete": true, "create/update": true}, "programs": {"read": 2, "delete": true, "create/update": true}, "briefings": {"read": 2, "delete": true, "create/update": true}, "criterion": {"read": 2, "delete": true, "create/update": true}, "questions": {"read": 2, "delete": true, "create/update": true}, "scenarios": {"read": 2, "delete": true, "create/update": true}, "sequences": {"read": 2, "delete": true, "create/update": true}, "societies": {"read": 3, "delete": true, "create/update": true}, "criterionA": {"read": 2, "delete": true, "create/update": true}, "criterionB": {"read": 2, "delete": true, "create/update": true}, "dashboards": {"read": 3, "delete": true, "create/update": true}, "answer_images": {"manage": false, "consult": false}, "axeDirectories": {"read": 2, "delete": true, "create/update": true}, "mail_templates": {"read": 3, "delete": true, "create/update": true}, "graph_templates": {"read": 3, "delete": true, "create/update": true}} ');
    }

    public function handle()
    {
        $this->user->save();

        $user = $this->user->retrieve();
        $user->token = (new CreateJWTToken($user))->handle();

        Hook::launch(HookSignUp::class, function (HookSignUp $hook) use ($user) {
            $hook->user = $user;
        });

        return $user;
    }
}