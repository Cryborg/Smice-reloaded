<?php

namespace App\Classes\Helpers;


class ColorsHelper
{
    /**
     * @param string $hex
     * @return array
     */
    public static function hex2Rgb($hex)
    {
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
        return [$r, $g, $b];
    }
}