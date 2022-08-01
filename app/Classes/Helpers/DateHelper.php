<?php

namespace App\Classes\Helpers;

use DateTime;
use DateTimeZone;

class DateHelper
{
    /**
     * Convert given age to timestamp
     *
     * @param $date
     * @return bool|string
     */
    public static function setDate($date)
    {
        $date = date('Y') - intval($date);
        $date .= '-01-01';

        return $date;
    }

    /**
     * return str, du xx/xx/xxxx au xx/xx/xxxx
     * from all waves passed
     * @param $waves
     * @return string
     */
    public static function getDateFromWave($waves)
    {
        $date_wave = [];
        foreach ($waves as $wave) {
            $date_wave['date_start'][] = $wave['date_start'];
            $date_wave['date_end'][] = $wave['date_end'];
        }
        $min_date = new DateTime(min($date_wave['date_start']), new DateTimeZone('Europe/Paris'));
        $min_date = $min_date->format('d/m/Y');
        $max_date = new DateTime(max($date_wave['date_end']), new DateTimeZone('Europe/Paris'));
        $max_date = $max_date->format('d/m/Y');

        $date = 'Du ' . $min_date . ' au ' . $max_date;

        return $date;
    }
}