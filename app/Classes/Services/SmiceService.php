<?php

namespace App\Classes\Services;

use App\Models\User;

class SmiceService
{
    /**
     * @var User|null
     */
    protected $user;

    public function __construct(User $user = null)
    {
        $this->user = $user;
    }
}