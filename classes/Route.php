<?php

class Route
{

    private static $_db;
    
    private static function init()
    {

        self::$_db = DB::getInstance();

    }

    public static function add($fields) 
    {

        self::init();

        self::$_db->insert('routes', array(
            'fltnum' => $fields[0], 
            'dep' => $fields[1],
            'arr' => $fields[2],
            'duration' => $fields[3],
            'aircraftid' => $fields[4]
        ));
        
    }

    public static function fetchAll()
    {

        self::init();

        return self::$_db->getAll('routes');

    }

    public static function delete($id)
    {

        self::init();

        self::$_db->delete('routes', array('id', '=', $id));

    }

}