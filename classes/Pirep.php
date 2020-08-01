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

        self::$_db->update('pireps', $id, 'id', array(
            'status' => 1
        ));

    }

    public static function decline($id) 
    {

        self::$_db->update('pireps', $id, 'id', array(
            'status' => 2
        ));

    }

}