<?php

namespace App\Classes\Helpers;


class GlobalScoreHelper
{
    /**
     * @param $uuid
     * @return float|null
     */
    public static function getGlobalScoreOneMission($uuid)
    {

        $result = \DB::table('show_scoring')
            ->selectRaw('CASE WHEN SUM(weight) > 0 THEN
                                        SUM(score) / SUM(CAST(weight AS FLOAT))
                                    ELSE null
                                END as score')
            ->where('scoring', true)
            ->where('uuid', $uuid);

        $result = $result->first();
        if (is_null($result['score'])) {
            $result['score'] = null;
        } else {
            $result['score'] = round($result['score'], 1);
        }

        return $result['score'];
    }
}