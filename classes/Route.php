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
     * @return null
     * @param array $fields Route Fields
     */
    public static function add($fields) 
    {

        self::init();

        self::$_db->insert('routes', $fields);
        Events::trigger('route/added', $fields);
        
    }

    /**
     * @return array
     */
    public static function fetchAll()
    {
        self::init();

        $ret = [];
        $sql = "SELECT DISTINCT r.*, a.name AS aircraft_name, a.liveryname AS aircraft_livery, a.ifaircraftid AS aircraft_liveryid, a.id AS aircraft_id
        FROM (
            route_aircraft ra INNER JOIN aircraft a ON a.id=ra.aircraftid
        ) INNER JOIN routes r ON r.id=ra.routeid";
        $data = self::$_db->query($sql)->results();
        foreach ($data as $d) {
            if (gettype($d->id) != 'string') $d->id = strval($d->id);
            if (!array_key_exists($d->id, $ret)) {
                $ret[$d->id] = [
                    "fltnum" => $d->fltnum,
                    "dep" => $d->dep,
                    "arr" => $d->arr,
                    "duration" => $d->duration,
                    "notes" => $d->notes,
                    "aircraft" => [
                        [
                            "id" => $d->aircraft_id,
                            "name" => $d->aircraft_name,
                            "livery" => $d->aircraft_livery,
                            "liveryid" => $d->aircraft_liveryid,
                        ],
                    ],
                ];
            } else {
                $ret[$d->id]['aircraft'][] = [
                    "id" => $d->aircraft_id,
                    "name" => $d->aircraft_name,
                    "livery" => $d->aircraft_livery,
                    "liveryid" => $d->aircraft_liveryid,
                ];
            }
        }

        return $ret;
    }

    /**
     * @return null
     * @param int $id Route ID
     */
    public static function delete($id)
    {

        self::init();

        self::$_db->delete('routes', array('id', '=', $id));
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
        $ret = self::$_db->update('routes', $id, 'id', $fields);

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
        $res = self::$_db->query($sql, [$id])->results();
        return $res;
    }

    /**
     * @return null
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
     * @return null
     * @param int $routeid Route ID
     * @param int $aircraft Aircraft ID
     */
    public static function addAircraft($routeid, $aircraft)
    {
        self::init();

        self::$_db->insert('route_aircraft', [
            "routeid" => $routeid,
            "aircraftid" => $aircraft,
        ]);
    }

    /**
     * @return int
     */
    public static function lastId()
    {
        self::init();
        $sql = "SELECT id FROM routes ORDER BY id DESC LIMIT 1";
        return self::$_db->query($sql)->first()->id;
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
        return self::$_db->query($sql, [$fltnum])->results();
    }

}