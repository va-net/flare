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

    public static function getAllAircraftIF()
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

    public static function getAllAircraft()
    {

        self::init();

        return self::$_db->get('aircraft', array('id', '>', '0'));

    }

    public static function getAvailableAircraft($rankid)
    {

        self::init();

        return self::$_db->get('aircraft', array('rankreq', '<=', $rankid));

    }
}