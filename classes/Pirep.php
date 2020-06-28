<?php

class Pirep
{

    private static $_db;

    private static function init()
    {

        self::$_db = DB::newInstance();

    }

    public static function totalFiled($id = null)
    {

        self::init();

        if ($id) {
            $result = self::$_db->get('pireps', array('pilotid', '=', $id));
            $count = $result->count();
            $user = self::$_db->get('pilots', array('id', '=', $id));
            $total = $user->first()->transflights;

            $x = 0;

            while ($x < $count) {
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
        $counter = 0;
        $pireps = array();
        $statuses = array('Pending', 'Approved', 'Denied');

        while ($x < $results->count()) {
            $newdata = array(
                'number' => $results->results()[$x]->flightnum,
                'departure' => $results->results()[$x]->departure,
                'arrival' => $results->results()[$x]->arrival,
                'date' => $results->results()[$x]->datetime,
                'status' => $statuses[$results->results()[$x]->status],
                'aircraft' => Aircraft::getAircraftName($results->results()[$x]->aircraftid),
            );
            $pireps[$x] = $newdata;
            $counter++;
            if ($counter >= 10) {
                break;
            }
            $x++;
        }
        return $pireps;

    }

    public static function fetchPireps($id = null)
    {

        self::init();
        if (!$id) {
            return self::$_db->getAll('pireps');
        }

        return self::$_db->get('pireps', array('pilotid', '=', $id));

    }

}