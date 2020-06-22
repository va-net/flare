<?php

class Callsign
{

    private static $_db;

    private static function init()
    {

        self::$_db = DB::getInstance();

    }

    public static function assigned($callsign, $id)
    {

        self::init();
        if ($result = self::$_db->query("SELECT * FROM pilots WHERE id <> {$id} AND callsign = '{$callsign}'")) {
            if ($result->count() == 0) {
                return true;
            }
        }
        return false;
    }

}