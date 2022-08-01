<?php

namespace App\Interfaces;

interface iProtected
{
    /**
     * Implementing this interface will enable you to protect the access
     * to your model. The PermissionChecker will be call to verify that the user has
     * the rights to access this resource.
     */

    /**
     * Returns the name of the module for which your model belongs to.
     * @return mixed
     */
    public function getModuleName();
}