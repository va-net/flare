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

        $result = self::$_db->get('pireps', array('pilotid', '=', $id), array('datetime', 'DESC'));

        $x = 0;
        $pireps = array();
        $statuses = array('Pending', 'Approved', 'Denied');

        while ($x < $result->count()) {
            $aircraft = self::$_db->get('aircraft', array('id', '=', $result->results()[$x]->aircraftid));
            $aircraft = $aircraft->first()->name;
            $newdata = array(
                'type' => $result->results()[$x]->type,
                'number' => $result->results()[$x]->flightnum,
                'departure' => $result->results()[$x]->departure,
                'arrival' => $result->results()[$x]->arrival,
                'aircraft' => $aircraft,
                'date' => $result->results()[$x]->date,
                'status' => $statuses[$result->results()[$x]->status],
            );
            $pireps[$x] = $newdata;
            $x++;
        }
        return $pireps;

    }

}