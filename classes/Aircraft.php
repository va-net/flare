<?php 

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

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

    public static function fetchLiveryIdsForAircraft($aircraftid)
    {

        self::init();
        $curl = new Curl;
        $response = $curl->get(Config::get('vanet/base_url')."/api/aircraft/AircraftID/{$aircraftid}", array(
            'apikey' => Config::get('vanet/api_key')
        ));
        $all = Json::decode($response->body);
        $final = array();
        foreach ($all as $aircraft) {
            $final[$aircraft['liveryName']] = $aircraft['liveryID'];
        }

        return $final;

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

        return self::$_db->query("SELECT aircraft.*, ranks.name AS rank FROM aircraft INNER JOIN ranks ON aircraft.rankreq=ranks.id WHERE status=1 ORDER BY aircraft.name ASC;");

    }

    public static function findAircraft($liveryId)
    {
        self::init();
        $result = self::$_db->get('aircraft', array('ifliveryid', '=', $liveryId));
        if ($result->count() === 0) return false;
        return $result->first();
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

    public static function liveryNameToId($livery, $aircraftid) 
    {

        $response = self::fetchAircraftFromVANet(rawurlencode($livery), 'LiveryName');

        foreach ($response as $aircraft) {
            if ($aircraftid == $aircraft['aircraftID']) {
                continue;
            }
            $fin = $aircraft['liveryID'];
        }
        return $fin;

    }

    public static function add($liveryId, $rank) 
    {

        self::init();
        
        $details = self::fetchAircraftFromVANet($liveryId, 'LiveryID')[0];

        $x = self::$_db->insert('aircraft', array(
            'status' => 1,
            'rankreq' => $rank,
            'ifliveryid' => $liveryId,
            'liveryname' => $details["liveryName"],
            'name' => $details["aircraftName"],
            'ifaircraftid' => $details["aircraftID"]
        ));
    }

    public static function updateRank($rankId, $aircraftId) 
    {
        self::init();
        
        $fields = array(
            'rankreq' => $rankId,
        );

        if (!self::$_db->update('aircraft', $aircraftId, 'id', $fields)) {
            throw new Exception('There was a problem updating the user.');
        }
    }

    public static function nameToId($name)
    {

        self::init();

        $result = self::$_db->get('aircraft', array('name', '=', $name));
        return $result->first()->id;

    }

    public static function nameToAircraftId($name)
    {

        self::init();

        $result = self::$_db->get('aircraft', array('name', '=', $name));
        return $result->first()->ifaircraftid;

    }

    public static function nameToLiveryId($name)
    {

        self::init();
        $result = self::$_db->get('aircraft', array('name', '=', $name));
        return $result->first()->ifliveryid;

    }

    public static function idToName($id)
    {

        self::init();
        $result = self::$_db->get('aircraft', array('id', '=', $id));
        return $result->first()->name;

    }

    public static function exists($liveryId) 
    {

        self::init();
        $result = self::$_db->get('aircraft', array('ifliveryid', '=', $liveryId));
        return !($result->count() == 0);
    }

}