<?php

namespace App\Interfaces;

interface iREST
{
    /**
     * Implementing this interface will enable you to use your model
     * as a REST resource and take advantage of the CRUD group middleware
     */

    /**
     * Returns the URI for the model
     * @var null|string
     */
    public static function  getURI();

    /**
     * Returns the name of the model (singular)
     * @return null|string
     */
    public static function  getName();
}