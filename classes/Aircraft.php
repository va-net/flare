<?php 

class Aircraft
{

    private static $_list;

    private static function init()
    {

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

        foreach (self::$_list as $item) {
            if ($item["LiveryId"] == $id) {
                return $item;
                break;
              }
        }

    }

}