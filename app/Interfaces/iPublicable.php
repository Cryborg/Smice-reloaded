<?php

namespace App\Interfaces;

interface iPublicable
{
    /**
     * Implementing this interface will add a SQL condition when retrieving
     * multiple records.
     * The condition is:
     *
     *  OR WHERE public = true
     *
     */
}