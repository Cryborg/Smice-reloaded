<?php

namespace App\Classes\Helpers;

class GeoHelper
{

    public static function getRoute($lat1, $lon1, $lat2, $lon2, $unit = 'K')
    {

        $accesToken = env('MAPBOX_TOKEN');
        $coordinates = $lon1 . ',' . $lat1 . ';' . $lon2 . ',' . $lat2;
        $route = 'https://api.mapbox.com/directions/v5/mapbox/driving/' . $coordinates . '?access_token=' . $accesToken;
        $res = json_decode(file_get_contents($route), true);
        if ($unit == 'K') {
            return ($res["routes"][0]["distance"] / 1000);
        } else if ($unit == 'N') {
            return ($res["routes"][0]["distance"] / 1000);
        } else {
            return $res["routes"][0]["distance"];
        }
    }

    /**
     * @param $target
     * @param $city
     * @return array
     */
    public static function getLatLonByStreet($target, $city)
    {
        $places = \GooglePlaces::textSearch($target . ' ' . $city);
        $places = $places->toArray();
        $coordinates = ['lat' => '0', 'lon' => '0'];
        foreach ($places as $kp => $place) {
            if ($kp == 'results') {
                $coordinates['lat'] = array_get($place, '0.geometry.location.lat', 0);
                $coordinates['lon'] = array_get($place, '0.geometry.location.lng', 0);
            }
        }

        return $coordinates;
    }

    /**
     * Get distance
     * $unit, 'M' - miles, 'K' - kilometers, 'N' - Nautical Miles
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     * @return int
     */
    public static function distance($lat1, $lon1, $lat2, $lon2, $unit = 'K')
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;

            $unit = strtoupper($unit);

            if ($unit == 'K') {
                return ($miles * 1.609344);
            } else if ($unit == 'N') {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }
}
