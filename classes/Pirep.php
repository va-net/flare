<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Pirep
{

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

        if(!self::$_db->update('pireps', $id, 'id', $fields)) {
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

        self::$_db->update('pireps', $id, 'id', array(
            'status' => 1
        ));

        Events::trigger('pirep/accepted', ["id" => $id]);

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
     * @return bool
     * @param string $callsign Pilot Callsign
     * @param int $id Pilot ID
     */
    public static function setup($callsign, $id) 
    {

        self::init();

        $server = 'casual';
        $force = Config::get('FORCE_SERVER');
        if ($force != 0 && $force != 'casual') $server = $force;

        $curl = new Curl;
        $request = $curl->get(Config::get('vanet/base_url').'/api/userid', array(
            'apikey' => Config::get('vanet/api_key'),
            'callsign' => $callsign,
            'server' => $server
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

        Events::trigger('pirep/setup', ["pilot" => $id, "userid" => $response["data"]]);
        
        return true;

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
}