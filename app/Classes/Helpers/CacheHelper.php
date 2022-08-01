<?php

namespace App\Classes\Helpers;


class CacheHelper
{
    /**
     * @param string $hex
     * @return array
     */
    public static function SetCacheKey($name, $user = null, $param = [])
    {
        
        $parm_name = null;
        $cachekey = $name;
        foreach ($param as $p) {
            $parm_name .= serialize($p);
        }
        if (!$user || $user->current_society_id === 1 || $user->current_society_id === 113) {
            $result = $cachekey . $parm_name;
        } else {
            $result = $user->current_society_id . $cachekey . $parm_name;
        }
        return $result;
    }
}
