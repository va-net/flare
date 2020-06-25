<?php

class Pirep
{

    private static $_db;

    private static function init()
    {

        self::$_db = DB::getInstance();

    }

    public static function totalFiled($id = null)
    {

        self::init();

        if ($id) {
            $result = self::$_db->get('pireps', array('pilotid', '=', $id));
            $trans = self::$_db->get('pilots', array('id', '=', $id));
            
            $x = 0;
            $total = 0;

            while ($x < $result->count()) {
                $total++;
                $x++;
            }

            return $total;

        } 

    }

    public static function recents($id = null) 
    {

        self::init();

        if (!$id) {
            $user = new User();
            if ($user->isLoggedIn()) {
                $id = $user->data()->id;
            }
        }

        $results = self::$_db->get('pireps', array('pilotid', '=', $id), array('datetime', 'DESC'));

        $x = 0;
        $pireps = array();
        $statuses = array('Pending', 'Approved', 'Denied');

        while ($x < $results->count()) {
            $newdata = array(
                'type' => $results->results()[$x]->type,
                'number' => $results->results()[$x]->flightnum,
                'departure' => $results->results()[$x]->departure,
                'arrival' => $results->results()[$x]->arrival,
                'date' => $results->results()[$x]->datetime,
                'status' => $statuses[$results->results()[$x]->status],
                'aircraft' => Aircraft::getAircraftName($results->results()[$x]->aircraftid),
            );
            $pireps[$x] = $newdata;
            $x++;
        }
        return $pireps;

    }

}