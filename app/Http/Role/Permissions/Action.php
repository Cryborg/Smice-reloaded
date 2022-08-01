<?php

namespace App\Classes\Permissions;

class Action
{
    const READ                      = 'read';
    const MODIFY                    = 'create/update';
    const DELETE                    = 'delete';

    private static $static_actions  = [
        self::MODIFY    => true,
        self::DELETE    => true
    ];

    private static $actions         = [
        self::READ      => true,
        self::MODIFY    => true,
        self::DELETE    => true
    ];

    /**
     * Find if an action is static or not.
     * @param $action
     * @return bool
     */
    public static function isStatic($action = null)
    {
        return isset(static::$static_actions[$action]);
    }

    /**
     * Determines if an action exists.
     * @param null $action
     * @return bool
     */
    public static function actionExists($action = null)
    {
        return isset(static::$actions[$action]);
    }
}