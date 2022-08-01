<?php

namespace App\Classes\Helpers;

use App\Exceptions\SmiceException;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingHelper
{

        function setting($key, $default = null)
        {
            if (is_null($key)) {
                return new Setting();
            }
    
            if (is_array($key)) {
                return Setting::set($key[0], $key[1]);
            }
    
            $value = Setting::get($key);
    
            return is_null($value) ? value($default) : $value;
        }

}