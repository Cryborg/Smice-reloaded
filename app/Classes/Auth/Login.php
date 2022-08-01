<?php

namespace App\Classes\Auth;

use App\Exceptions\SmiceException;
use App\Models\User;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Login implements SelfHandling
{
    /**
     * @var mixed|null
     */
    private $email           = null;
    /**
     * @var mixed|null
     */
    private $password         = null;

    public function         __construct(array $params)
    {
        $this->email        = array_get($params, 'email');
        $this->email        = strtolower($this->email);
        $this->password     = array_get($params, 'password');

        Validator::make(
            [
                'email' => $this->email,
                'password' => $this->password
            ],
            [
                'email' => 'required|email',
                'password' => 'required|string|min:6'
            ]
        )->passOrDie();
    }

    public function handle()
    {
        $user = User::relations()->where('email', $this->email)->first();

        if (!$user)
            throw new SmiceException(
                SmiceException::HTTP_UNAUTHORIZED,
                SmiceException::E_CREDENTIALS,
                'Incorrect login / password combination.'
            );
        if (!Hash::check($user->secret_key . $this->password, $user->password))
            throw new SmiceException(
                SmiceException::HTTP_UNAUTHORIZED,
                SmiceException::E_CREDENTIALS,
                'Incorrect login / password combination.'
            );

        $user->reloadPermissions();
        $user->reloadCurrentSociety();
        $user->token = (new CreateJWTToken($user))->handle();
        return $user;
    }

}