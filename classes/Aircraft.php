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

        return self::$_db->get('aircraft', array('status', '=', 1), array('name', 'ASC'));

    }

    public static function fetchAllAircraft()
    {

        self::init();

        return self::$_db->get('aircraft', array('status', '<', 3), array('name', 'ASC'));

    }

    public static function getAvailableAircraft($rankid)
    {

        self::init();

        return self::$_db->query('SELECT * FROM aircraft WHERE rankreq <= ? AND status = 1 ORDER BY name ASC', array($rankid));

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

    public static function archive($id)
    {

        self::init();

        self::$_db->update('aircraft', $id, 'id', array(
            'status' => 0
        ));

    }

    public static function add($aircraft) 
    {

        self::init();

        self::$_db->update('aircraft', $aircraft, 'name', array(
            'status' => 1
        ));

    }

    public static function nameToId($name)
    {

        self::init();

        $result = self::$_db->get('aircraft', array('name', '=', $name));
        return $result->first()->id;

    }

}