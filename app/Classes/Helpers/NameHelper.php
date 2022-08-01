<?php

namespace App\Classes\Helpers;


class NameHelper
{
    /**
     * @param array $array
     * @return string
     */
    public static function makeTagName($array)
    {
        $r = [];

        foreach ($array as $item) {
            foreach ($item as $it) {
                array_push($r, $it['wave_name']);
            }
        }
        $r = ArrayHelper::superUniqueArray($r);

        $a = end($r);
        $b = array_reverse($r);
        $c = end($b);
        $str = 'De ' . $c . ' à  ' . $a;

        return $str;
    }

    /**
     * @param array $item
     * @return string
     */
    public static function makeSequenceName($item)
    {
        foreach ($item as $k => $v) {
            if (!$v) {
                unset($item[$k]);
            }
        }

        $a = array_keys($item);
        $k = end($a);
        $s = array_reverse($a);
        $z = end($s);
        $y = 'De ' . $z . ' à  ' . $k;

        return $y;
    }
}