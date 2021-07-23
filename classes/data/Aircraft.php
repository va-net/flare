<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Aircraft
{

    /**
     * @var DB
     */
    private static $_db;

    private static function init()
    {
        self::$_db = DB::getInstance();
    }

    /**
     * @return array
     */
    public static function fetchAllAircraftFromVANet()
    {
        $key = Config::get('vanet/api_key');
        $response = HttpRequest::hacky(VANet::$BASE . '/public/v1/aircraft', 'GET', '', ["X-Api-Key: {$key}"]);
        $response = Json::decode($response);

        if ($response['status'] != 0) return [];

        $completed = [];
        foreach ($response['result'] as $aircraft) {
            if (array_key_exists($aircraft['aircraftID'], $completed)) {
                continue;
            } else {
                $completed[$aircraft['aircraftID']] = $aircraft['aircraftName'];
            }
        }
        asort($completed);
        return $completed;
    }

    /**
     * @return array
     */
    public static function fetchAllLiveriesFromVANet()
    {
        $key = Config::get('vanet/api_key');
        $response = HttpRequest::hacky(VANet::$BASE . '/public/v1/aircraft', 'GET', '', ["X-Api-Key: {$key}"]);
        return Json::decode($response)['result'];
    }

    /**
     * @return array
     * @param string $aircraftid Aircraft ID
     */
    public static function fetchLiveryIdsForAircraft($aircraftid)
    {
        $key = Config::get('vanet/api_key');
        $response = HttpRequest::hacky(VANet::$BASE . '/public/v1/aircraft/' . urlencode($aircraftid), 'GET', '', ["X-Api-Key: {$key}"]);
        $all = Json::decode($response)['result'];
        foreach ($all as $aircraft) {
            $final[$aircraft['liveryName']] = $aircraft['liveryID'];
        }

        ksort($final);
        return $final;
    }

    /**
     * @return array
     * @param string $liveryId Aircraft Livery ID
     */
    public static function fetchAircraftFromVANet($liveryId)
    {
        $key = Config::get('vanet/api_key');
        $response = HttpRequest::hacky(VANet::$BASE . '/public/v1/aircraft/livery/' . urlencode($liveryId), 'GET', '', ["X-Api-Key: {$key}"]);
        return Json::decode($response)['result'];
    }

    /**
     * @return DB
     */
    public static function fetchActiveAircraft()
    {
        self::init();

        return self::$_db->query("SELECT aircraft.*, ranks.name AS `rank` FROM aircraft INNER JOIN ranks ON aircraft.rankreq=ranks.id WHERE `status`=1 ORDER BY aircraft.name ASC;", [], true);
    }

    /**
     * @return object
     * @param string $liveryId Aircraft Livery ID
     */
    public static function findAircraft($liveryId)
    {
        self::init();
        $result = self::$_db->query("SELECT * FROM aircraft WHERE `ifliveryid`=? AND `status`=1", [$liveryId]);
        if ($result->count() === 0) return false;
        return $result->first();
    }

    /**
     * @return DB
     */
    public static function fetchAllAircraft()
    {

        self::init();

        return self::$_db->get('aircraft', array('status', '<', 3), array('name', 'ASC'))->results();
    }

    /**
     * @return DB
     * @param int $rankid The Rank ID to Get Aircraft For
     */
    public static function getAvailableAircraft($rankid)
    {
        self::init();

        $sql = 'SELECT aircraft.*, ranks.timereq FROM aircraft 
        INNER JOIN ranks ON ranks.id=aircraft.rankreq WHERE timereq <= (SELECT timereq FROM ranks WHERE id=?) 
        AND `status` = 1 ORDER BY `name` ASC';
        return self::$_db->query($sql, [$rankid], true);
    }

    /**
     * @return string
     * @param int $id Aircraft ID
     */
    public static function getAircraftName($id)
    {
        self::init();

        $result = self::$_db->get('aircraft', array('id', '=', $id));

        return $result->first()->name;
    }

    /**
     * @return int
     * @param string $name Aircraft Name
     */
    public static function getId($name)
    {
        self::init();

        $result = self::$_db->get('aircraft', array('name', '=', $name));

        return $result->first()->id;
    }

    /**
     * @return void
     * @param int $id Aircraft ID
     */
    public static function archive($id)
    {
        self::init();

        self::$_db->update('aircraft', $id, 'id', array(
            'status' => 0
        ));

        Events::trigger('aircraft/archived', ['id' => $id]);
    }

    /**
     * @return void
     * @param string $liveryId Livery ID
     * @param int $rank Rank ID
     * @param string $notes Notes
     */
    public static function add($liveryId, $rank, $notes = null)
    {
        self::init();

        $details = self::fetchAircraftFromVANet($liveryId);
        $data = array(
            'status' => 1,
            'rankreq' => $rank,
            'ifliveryid' => $liveryId,
            'liveryname' => $details["liveryName"],
            'name' => $details["aircraftName"],
            'ifaircraftid' => $details["aircraftID"],
            'notes' => $notes
        );
        self::$_db->insert('aircraft', $data, true);
        Events::trigger('aircraft/added', $data);
    }

    /**
     * @return void
     * @param int $rankId Updated Rank ID
     * @param string $notes Updated Notes
     * @param int $aircraftId Aircraft ID
     */
    public static function update($rankId, $notes, $aircraftId)
    {
        self::init();

        $fields = array(
            'rankreq' => $rankId,
            'notes' => $notes,
        );

        if (!self::$_db->update('aircraft', $aircraftId, 'id', $fields, true)) {
            throw new Exception('There was a problem updating the user.');
        }

        $fields['id'] = $aircraftId;
        Events::trigger('aircraft/updated', $fields);
    }

    /**
     * @return int
     * @param string $name Aircraft Name
     */
    public static function nameToId($name)
    {
        self::init();

        $result = self::$_db->get('aircraft', array('name', '=', $name));
        return $result->first()->id;
    }

    /**
     * @return string
     * @param string $name Aircraft Name
     */
    public static function nameToAircraftId($name)
    {
        self::init();

        $result = self::$_db->get('aircraft', array('name', '=', $name));
        return $result->first()->ifaircraftid;
    }

    /**
     * @return string
     * @param string $name Aircraft Name
     */
    public static function nameToLiveryId($name)
    {

        self::init();
        $result = self::$_db->get('aircraft', array('name', '=', $name));
        return $result->first()->ifliveryid;
    }

    /**
     * @return string
     * @param int $id Internal Aircraft ID
     */
    public static function idToLiveryId($id)
    {
        self::init();
        $result = self::$_db->get('aircraft', array('id', '=', $id));
        return $result->first()->ifliveryid;
    }

    /**
     * @return object|bool
     * @param int $id Aircraft ID
     */
    public static function fetch($id)
    {
        self::init();
        $result = self::$_db->get('aircraft', array('id', '=', $id), false, true);
        if ($result->count() == 0) return false;
        return $result->first();
    }

    /**
     * @return bool
     * @param string $liveryId Livery ID
     */
    public static function exists($liveryId)
    {
        self::init();
        $result = self::$_db->get('aircraft', array('ifliveryid', '=', $liveryId));
        return !($result->count() == 0);
    }

    /**
     * @return int
     */
    public static function lastId()
    {
        self::init();
        $res = self::$_db->query("SELECT MAX(id) AS res FROM aircraft")->first();
        if ($res === FALSE) return 0;

        return $res->res;
    }
}
