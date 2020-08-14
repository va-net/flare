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

    public static function fetchAll()
    {

        self::init();

        $result = self::$_db->getAll('pireps');

        $x = 0;
        $pireps = array();

        while ($x < $result->count()) {
            $newdata = array(
                'id' => $result->results()[$x]->id,
                'flightnum' => $result->results()[$x]->flightnum,
                'departure' => $result->results()[$x]->departure,
                'arrival' => $result->results()[$x]->arrival,
                'flighttime' => $result->results()[$x]->flighttime,
                'pilotid' => $result->results()[$x]->pilotid,
                'date' => $result->results()[$x]->date,
                'aircraftid' => $result->results()[$x]->flighttime,
                'multi' => $result->results()[$x]->multi,
                'status' => $result->results()[$x]->status,
            );
            $pireps[$x] = $newdata;
            $x++;
        }
        return $pireps;

    }

    public static function fetchPending()
    {

        self::init();

        $result = self::$_db->get('pireps', array('status', '=', 0));

        $x = 0;
        $pireps = array();

        while ($x < $result->count()) {
            $newdata = array(
                'id' => $result->results()[$x]->id,
                'flightnum' => $result->results()[$x]->flightnum,
                'departure' => $result->results()[$x]->departure,
                'arrival' => $result->results()[$x]->arrival,
                'flighttime' => $result->results()[$x]->flighttime,
                'pilotid' => $result->results()[$x]->pilotid,
                'date' => $result->results()[$x]->date,
                'aircraftid' => $result->results()[$x]->flighttime,
                'multi' => $result->results()[$x]->multi,
                'status' => $result->results()[$x]->status,
            );
            $pireps[$x] = $newdata;
            $x++;
        }
        return $pireps;

    }

    public static function accept($id) 
    {

        self::init();

        self::$_db->update('pireps', $id, 'id', array(
            'status' => 1
        ));

    }

    public static function decline($id) 
    {

        self::init();

        self::$_db->update('pireps', $id, 'id', array(
            'status' => 2
        ));

    }

    public static function setup($callsign, $id) 
    {

        self::init();

        $curl = new Curl;
        $request = $curl->get(Config::get('vanet/base_url').'/api/userid', array(
            'apikey' => Config::get('vanet/api_key'),
            'callsign' => $callsign,
            'server' => 'casual'
        ));
        $response = Json::decode($request->body);
        if (array_key_exists('status', $response)) {
            if ($response['status'] == 404) {
                return false;
            }
        }

        if (!self::$_db->update('pilots', $id, 'id', array(
            'ifuserid' => $response['data']
        ))) {
            return false;
        }
        
        return true;

    }

}