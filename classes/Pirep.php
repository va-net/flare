<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Pirep
{

    /**
     * @var DB
     */
    private static $_db;

    private static function init()
    {

        self::$_db = DB::newInstance();

    }

    /**
     * @return bool
     * @param array $fields PIREP Fields
     */
    public static function file($fields = array()) 
    {

        self::init();
        if (self::$_db->insert('pireps', $fields)->error()) {
            return false;
        }
        Events::trigger('pirep/filed', $fields);
        return true;

    }

    /**
     * @return bool
     * @param int $id PIREP ID
     * @param array $fields Updated PIREP Fields
     */
    public static function update($id, $fields = array())
    {

        self::init();

        if(self::$_db->update('pireps', $id, 'id', $fields)->error()) {
            return false;
        }
        $fields["id"] = $id;
        Events::trigger('pirep/updated', $fields);
        return true;

    }

    /**
     * @return array
     */
    public static function fetchAll()
    {

        self::init();

        $sql = "SELECT pireps.*, pilots.name AS pilotname, aircraft.name AS aircraftname FROM (pireps INNER JOIN pilots ON pireps.pilotid=pilots.id) INNER JOIN aircraft ON pireps.aircraftid=aircraft.id";
        $result = self::$_db->query($sql)->results();
        $pireps = array_map(function($p) {
            return (array)$p;
        }, $result);
        return $pireps;

    }

    /**
     * @return array
     */
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

    /**
     * @return null
     * @param int $id PIREP ID
     */
    public static function accept($id) 
    {

        self::init();

        $usr = new User;
        $pirep = Pirep::find($id);
        if (!$pirep) return;

        $beforeRank = $usr->rank($pirep->pilotid, true);
        self::$_db->update('pireps', $id, 'id', array(
            'status' => 1
        ));
        $afterRank = $usr->rank($pirep->pilotid, true);

        if ($beforeRank != $afterRank) {
            Events::trigger('user/promoted', ["pilot" => $pirep->pilotid, "rank" => $afterRank]);
        }
        Events::trigger('pirep/accepted', (array)$pirep);

    }

    /**
     * @return null
     * @param int $id PIREP ID
     */
    public static function decline($id) 
    {

        self::init();

        self::$_db->update('pireps', $id, 'id', array(
            'status' => 2
        ));
        Events::trigger('pirep/denied', ["id" => $id]);

    }

    /**
     * @return array
     */
    public static function fetchMultipliers() 
    {
        self::init();

        return self::$_db->getAll('multipliers')->results();
    }

    /**
     * @return int
     * @param array $multis Array of Multipliers
     */
    public static function generateMultiCode($multis = null) 
    {
        if ($multis == null) {
            $multis = self::fetchMultipliers();
        }
        $codes = array();
        foreach ($multis as $m) {
            array_push($codes, $m->code);
        }

        $code = mt_rand(111111, 999999);
        while (in_array($code, $codes)) {
            $code = mt_rand(111111, 999999);
        }

        return $code;
    }

    /**
     * @return null
     * @param int $id Multiplier ID
     */
    public static function deleteMultiplier($id) 
    {
        self::init();

        self::$_db->delete('multipliers', array('id', '=', $id));
        Events::trigger('pirep/multipliers/deleted', ["id" => $id]);
    }

    /**
     * @return null
     * @param array $fields Multiplier Fields
     */
    public static function addMultiplier($fields = array()) 
    {
        self::init();

        self::$_db->insert('multipliers', $fields);
        Events::trigger('pirep/multipliers/added', $fields);
    }

    /**
     * @return bool|object
     * @param int $code Multiplier Code
     */
    public static function findMultiplier($code) 
    {
        self::init();
        $ret = self::$_db->get('multipliers', array('code', '=', $code));
        if ($ret->count() == 0) {
            return false;
        }

        return $ret->first();
    }

    /**
     * @return object|bool
     * @param int $id PIREP ID
     * @param int $pilot Pilot ID
     */
    public static function find($id, $pilot = null) 
    {
        self::init();

        $pirep = self::$_db->get('pireps', ['id', '=', $id]);
        if ($pirep->count() == 0) {
            return false;
        }

        if ($pilot == null) {
            return $pirep->first();
        }

        if ($pirep->first()->pilotid == $pilot) {
            return $pirep->first();
        }

        return false;
    }
}