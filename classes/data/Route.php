<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Route
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
     * @return void
     * @param array $fields Route Fields
     */
    public static function add($fields)
    {

        self::init();

        self::$_db->insert('routes', $fields, true);
        Events::trigger('route/added', $fields);
    }

    /**
     * @return array
     */
    public static function fetchAll()
    {
        self::init();

        $ret = [];
        $sql = "SELECT * FROM routes ORDER BY fltnum";
        $data = self::$_db->query($sql, [], true)->results();
        return array_map(function ($x) {
            return (array)$x;
        }, $data);
    }

    /**
     * @return void
     * @param int $id Route ID
     */
    public static function delete($id)
    {

        self::init();

        self::$_db->delete('routes', ['id', '=', $id]);
        self::$_db->delete('route_aircraft', ['routeid', '=', $id]);
        Events::trigger('route/deleted', ["id" => $id]);
    }

    /**
     * @return object|bool
     * @param int $id Route ID
     */
    public static function find($id)
    {
        self::init();

        return self::$_db->get('routes', ['id', '=', $id])->first();
    }

    /**
     * @return bool
     * @param int $id Route ID
     * @param array $fields Updated Route Fields
     */
    public static function update($id, $fields)
    {
        self::init();
        $ret = self::$_db->update('routes', $id, 'id', $fields, true);

        $fields["id"] = $id;
        Events::trigger('route/updated', $fields);

        return !($ret->error());
    }

    /**
     * @return array
     * @param int $id Route ID
     */
    public static function aircraft($id)
    {
        self::init();
        $sql = "SELECT * FROM aircraft WHERE id IN (SELECT aircraftid FROM route_aircraft WHERE routeid=?)";
        $res = self::$_db->query($sql, [$id], true)->results();
        return $res;
    }

    /**
     * @return void
     * @param int $routeid Route ID
     * @param int $aircraft Aircraft ID, null to remove all aircraft
     */
    public static function removeAircraft($routeid, $aircraft = null)
    {
        self::init();

        if ($aircraft != null) {
            $sql = "DELETE FROM route_aircraft WHERE routeid=? AND aircraftid=?";
            self::$_db->query($sql, [$routeid, $aircraft]);
            return;
        }

        self::$_db->delete('route_aircraft', ['routeid', '=', $routeid]);
    }

    /**
     * @return void
     * @param int $routeid Route ID
     * @param int $aircraft Aircraft ID
     */
    public static function addAircraft($routeid, $aircraft)
    {
        self::init();

        self::$_db->insert('route_aircraft', [
            "routeid" => $routeid,
            "aircraftid" => $aircraft,
        ], true);
    }

    /**
     * @return int
     */
    public static function nextId()
    {
        self::init();
        $data = self::$_db->query("SHOW TABLE STATUS")->results();
        $table = array_filter($data, function ($x) {
            return $x->Name == 'routes';
        })[0];
        return $table->Auto_increment;
    }

    /**
     * @return array
     * @param int $fltnum Flight Number
     */
    public static function pireps($fltnum)
    {
        self::init();

        $sql = "SELECT pireps.*, pilots.name AS pilotname, aircraft.name AS aircraftname FROM (
                    pireps INNER JOIN pilots ON pireps.pilotid=pilots.id
                ) INNER JOIN aircraft ON pireps.aircraftid=aircraft.id 
                WHERE pireps.flightnum=? AND pireps.status=1";
        return self::$_db->query($sql, [$fltnum], true)->results();
    }

    /**
     * @return array
     * @param string $icao Airport ICAO
     */
    public static function getByAirport($icao)
    {
        self::init();

        $sql = "SELECT * FROM routes WHERE dep=? OR arr=?";
        return self::$_db->query($sql, [$icao, $icao])->results();
    }

    /**
     * @return object[]
     */
    public static function fetchAllAircraftJoins()
    {
        self::init();

        $sql = "SELECT * FROM route_aircraft WHERE routeid IN (SELECT id FROM routes)";
        return self::$_db->query($sql, [], true)->results();
    }
}
