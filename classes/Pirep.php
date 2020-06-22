<?php

class Pirep
{

    private static $_db;

    private static function init()
    {

        self::$_db = DB::getInstance();

    }

    public static function totalFiled($id = null)
    {

        self::init();

        if ($id) {
            $result = self::$_db->get('pireps', array('pilotid', '=', $id));
            $trans = self::$_db->get('pilots', array('id', '=', $id));
            
            $x = 1;
            $total = 0;

            while ($x < $result->count()) {
                $total = $total + 1;
                $x++;
            }
            return $total;
        } 

    }

}