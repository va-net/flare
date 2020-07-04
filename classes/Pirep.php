<?php

class Pirep
{

    private static $_db;

    private static function init()
    {

        self::$_db = DB::newInstance();

    }

    public static function totalFiled($id = null)
    {

        self::init();

        if ($id) {
            $result = self::$_db->query('SELECT * FROM pireps WHERE pilotid = ? AND status = ?', array($id, 1));
            $count = $result->count();
            $user = self::$_db->get('pilots', array('id', '=', $id));
            $total = $user->first()->transflights;

            $x = 0;

            while ($x < $count) {
                $total++;
                $x++;
            }

            return $total;

        } 

    }

    public static function fetchApprovedPireps($id = null)
    {

        self::init();
        if (!$id) {
            return self::$_db->getAll('pireps');
        }

        return self::$_db->query('SELECT * FROM pireps WHERE pilotid = ? AND status = ?', array($id, 1));

    }

}