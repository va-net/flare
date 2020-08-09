<?php 

class Aircraft
{

    private static $_db;

    private static function init() 
    {

        self::$_db = DB::getInstance();

    }

    public static function fetchAllAircraftFromVANet()
    {

        $curl = new Curl;
        $response = $curl->get(Config::get('vanet/base_url').'/api/aircraft', array(
            'apikey' => Config::get('vanet/api_key')
        ));
        $response = Json::decode($response->body);

        $completed = array();
        foreach ($response as $aircraft) {
            if (in_array($aircraft['aircraftID'], $completed)) {
                continue;
            } else {
                $completed[$aircraft['aircraftID']] = $aircraft['aircraftName'];
            }
        }
        return $completed;

    }

    public static function fetchAircraftFromVANet($aircraft, $search = 'AircraftID') 
    {

        $curl = new Curl;
        $response = $curl->get(Config::get('vanet/base_url')."/api/aircraft/{$search}/{$aircraft}", array(
            'apikey' => Config::get('vanet/api_key')
        ));
        return Json::decode($response->body);

    }

    public static function fetchActiveAircraft()
    {

        self::init();

        if (!$results = self::$_db->get('aircraft', array('status', '=', 1), array('name', 'ASC'))) {
            return false;
        }
        return $results;

    }

    public static function getAvailableAircraft($rankid)
    {

        self::init();

        return self::$_db->get('aircraft', array('rankreq', '<=', $rankid), array('name', 'ASC'));

    }

    public static function getAircraftName($id)
    {

        self::init();

        $result = self::$_db->get('aircraft', array('id', '=', $id));
        
        return $result->first()->name;

    }

    public static function getId($name)
    {

        self::init();

        $result = self::$_db->get('aircraft', array('name', '=', $name));

        return $result->first()->id;

    }

}