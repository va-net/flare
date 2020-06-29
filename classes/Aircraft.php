<?php 

class Aircraft
{

    private static $_list;
    private static $_db;

    private static function init()
    {

        self::$_db = DB::newInstance();
        self::$_list = file_get_contents('aircraft.txt');
        self::$_list = json_decode(self::$_list, true);

    }

    public static function getAllAircraft()
    {

        self::init();

        return self::$_list;

    }

    public static function getAircraftName($id)
    {

        self::init();

        $result = self::$_db->get('aircraft', array('id', '=', $id));
        
        return $result->first()->name;

    }

}