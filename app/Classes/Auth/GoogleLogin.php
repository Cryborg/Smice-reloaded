<?php

namespace App\Classes\Auth;

use App\Hooks\Hook;
use App\Hooks\HookSignUp;
use App\Http\User\Models\UserLogin;
use App\Models\Society;
use App\Models\User;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Validator;

class GoogleLogin implements SelfHandling
{
    CONST CONFIG_DIR    = __DIR__. '/../../../config/api_configurations/';

    public function     __construct(array $params, Society $society)
    {
        $client         = new \Google_Client();
        $access_token   = array_get($params, 'code');

        Validator::make(['code' => $access_token], ['code' => 'required|string'])->passOrDie();
        $client->setAuthConfigFile(self::CONFIG_DIR. 'google_sign_in.json');
        $client->setScopes([
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile'
        ]);
        $client->authenticate($access_token);

        $plus = new \Google_Service_Oauth2($client);
        $user_info = $plus->userinfo->get();

        if (!($this->user = $this->_getGoogleUser($user_info->id))) {
            $this->_createGoogleUser($user_info, $society);
        }

        $this->params = $params;
    }

    /**
     * @return mixed|User
     */
    public function     handle()
    {
        if (!$this->user->getKey()) {
            $this->user->save();
            $user = $this->user->relations()->find($this->user->getKey());

            Hook::launch(HookSignUp::class, function(HookSignUp $hook) use ($user)
            {
                $hook->user = $user;
            });
        } else {
            $user = $this->user;
        }

        $user->reloadCurrentSociety();
        $user->token = (new CreateJWTToken($user))->handle();

        return $user;
    }

    private function    _getGoogleUser($google_user_id)
    {
        $user           = User::relations()->where('user_login.google_user_id', $google_user_id)->first();

        return $user;
    }

    private function    _createGoogleUser(\Google_Service_Oauth2_Userinfoplus $user_info, $society)
    {
        $user           = new User();

        $user->first_name = $user_info->getGivenName();
        $user->last_name = $user_info->getFamilyName();
        $user->email = $user_info->getEmail();
        $user->gender = ($user_info->getGender() == 'male' || $user_info->getGender() == 'female') ? $user_info->getGender() : null;

        # Todo: remplir ces champs avec les vérifications nécessaires
        # $user->country_id = $user_info->getLocale();
        # $user->picture = $user_info->getPicture()
        $user->country_id = 1;
        $user->language_id = 1;

        $user->password = 'GoogleAPIUserAccount';
        $user->password_confirmation = 'GoogleAPIUserAccount';

        $user->society()->associate($society);
        $user->created_by = null;
        if ($user->save()) {
            $userLogin = new UserLogin();
            $userLogin->google_user_id = $user_info->getId();
            $userLogin->save();
        }

        $this->user = $user;
    }
}
