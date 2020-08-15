<?php

class VANet
{

    public static function myInfo($key)
    {

        $curl = new Curl;
        $curl->options['CURLOPT_FRESH_CONNECT'] = true;
        $response = $curl->get(Config::get('vanet/base_url').'/api/myinfo/bykey', array(
            'apikey' => $key
        ));
        return Json::decode($response->body);

    }

    public static function isGold($key = null) 
    {

        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }

        $myinfo = self::myInfo($key);

        if ($myinfo['tier'] != 'Gold') {
            return false;
        }
        return true;

    }

    public static function isVeKey($key = null)
    {

        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }

        $myinfo = self::myInfo($key);
        if ($myinfo['type'] == null) {
            return false;
        }
        return true;

    }

}