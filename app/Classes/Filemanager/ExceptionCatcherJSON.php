<?php

namespace App\Classes\Filemanager;

use App\Classes\Filemanager\includes\ExceptionCatcher;

class ExceptionCatcherJSON extends ExceptionCatcher
{
    public static function draw(\Exception $oExp)
    {
        @header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        $oResponse = new Response;
        $oResponse->setData(array(
            'success' => false,
            'error' => $oExp->getMessage(),
            'errorDetail' => array(
                'type' => get_class($oExp),
                'code' => $oExp->getCode()
            )
        ));
        return $oResponse->flushJson();
    }

    public static function register()
    {
        set_exception_handler(array(__CLASS__, 'handle'));
    }
}