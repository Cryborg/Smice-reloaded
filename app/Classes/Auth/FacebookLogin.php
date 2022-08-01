<?php

namespace App\Classes\Auth;

use App\Hooks\Hook;
use App\Hooks\HookSignUp;
use App\Http\User\Models\UserLogin;
use App\Models\Society;
use App\Models\User;
use GuzzleHttp;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class FacebookLogin implements SelfHandling
{
    public function     __construct(array $params, Society $society)
    {
        $client         = new GuzzleHttp\Client();
        $code           = array_get($params, 'code');

        Validator::make(['code' => $code], ['code' => 'required|string'])->passOrDie();
        $params = [
            'code' => $code,
            'client_id' => array_get($params, 'clientId'),
            'redirect_uri' => array_get($params, 'redirectUri'),
            'client_secret' => Config::get('services.facebook.secret')
        ];
        // Step 1. Exchange authorization code for access token.
        $accessTokenResponse = $client->request('GET', 'https://graph.facebook.com/v2.5/oauth/access_token', [
            'query' => $params
        ]);
        $accessToken = json_decode($accessTokenResponse->getBody(), true);
        // Step 2. Retrieve profile information about the current user.
        $fields = 'id,first_name,location,birthday,locale,last_name,email,gender';
        $profileResponse = $client->request('GET', 'https://graph.facebook.com/v2.5/me', [
            'query' => [
                'access_token' => $accessToken['access_token'],
                'fields' => $fields
            ]
        ]);
        $profile = json_decode($profileResponse->getBody(), true);

        if (!($this->user = $this->_getFacebookUser($profile['id'])))
            $this->_createFacebookUser($profile, $society);

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

    private function    _getFacebookUser($facebook_user_id)
    {
        $user = User::relations()->where('user_login.facebook_user_id', $facebook_user_id)->first();

        return $user;
    }

    private function    _createFacebookUser($user_info, $society)
    {
        $user           = new User();

        $user->first_name = $user_info['first_name'];
        $user->last_name = $user_info['last_name'];
        $user->birth_date = date('Y-m-d', strtotime($user_info['birthday']));
        $user->email = $user_info['email'];
        $user->gender = ($user_info['gender'] == 'male' || $user_info['gender'] == 'female') ? $user_info['gender'] : null;

        # Todo: remplir ces champs
        # $user->id_pays = $user_info->getLocale();

        $user->password = 'FacebookAPIUserAccount';
        $user->password_confirmation = 'FacebookAPIUserAccount';
        $user->society()->associate($society);
        $user->created_by = null;

        if ($user->save()) {
            $userLogin = new UserLogin();
            $userLogin->facebook_user_id = $user_info['id'];
            $userLogin->save();
        }

        $this->user = $user;
    }
}
