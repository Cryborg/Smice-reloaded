<?php

namespace App\Classes\Auth;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Config;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Webpatser\Uuid\Uuid;
use App\Models\User;

class CreateJWTToken implements SelfHandling
{
    /**
     * @var User|null
     */
    private $user           = null;
    /**
     * @var bool
     */
    private $remember_me    = false;
    /**
     * @var Builder|null
     */
    private $builder        = null;
    /**
     * @var Sha256|null
     */
    private $signer         = null;

    const SHORT_EXPIRE      = 60 * 60 * 24;

    const LONG_EXPIRE       = 60 * 60 * 24 * 365;

    public function         __construct($user, $remember_me = false)
    {
        $this->builder      = new Builder();
        $this->signer       = new Sha256();
        $this->user         = $user;
        $this->remember_me  = $remember_me;
    }

    public function         handle()
    {
        $this->builder
            ->setIssuer('http://api.smice.com')
            ->setAudience('http://smice.com')
            ->setId(Uuid::generate(4))
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->set('society', $this->user->society->getKey())
            ->setSubject($this->user->getKey());

        if ($this->remember_me) {
            $this->builder->setExpiration(time() + self::LONG_EXPIRE);
        } else {
            $this->builder->setExpiration(time() + self::SHORT_EXPIRE);
        }

        $this->builder->sign($this->signer, Config::get('app.key'));

        return (string) $this->builder->getToken();
    }
}