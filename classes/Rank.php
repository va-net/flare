<?php

class Rank
{

    private static $_db;

    private static function init()
    {

        self::$_db = DB::getInstance();

    }

    public static function calc($hours)
    {

        self::init();

        if ($hours == 0) {
            $result = self::$_db->get('ranks', array('hoursreq', '=', 0));
            return $result->first()->name;
        }

        if ($result = self::$_db->get('ranks', array('hoursreq', '<', $hours), array('hoursreq', 'desc'))) {
            return $result->first()->name;
        }

    }

}