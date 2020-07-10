<?php

class Pirep
{

    private static $_db;

    private static function init()
    {

        self::$_db = DB::newInstance();

    }

    public static function file($fields = array()) 
    {

        self::init();
        if (!self::$_db->insert('pireps', $fields)) {
            return false;
        }
        return true;

    }

    public static function update($id, $fields = array())
    {

        self::init();

        if(!self::$_db->update('pireps', $id, 'id', $fields)) {
            return false;
        }
        return true;

    }

}