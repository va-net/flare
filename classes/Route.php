<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Route
{

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

        $data = array(
            'fltnum' => $fields[0], 
            'dep' => $fields[1],
            'arr' => $fields[2],
            'duration' => $fields[3],
            'aircraftid' => $fields[4]
        );
        self::$_db->insert('routes', $data);
        Events::trigger('route/added', $data);
        
    }

    /**
     * @return DB
     */
    public static function fetchAll()
    {
        self::init();

        $sql = "SELECT routes.*, aircraft.name AS aircraft, aircraft.liveryname AS livery, 
        aircraft.ifliveryid AS liveryid FROM routes 
        INNER JOIN aircraft ON routes.aircraftid=aircraft.id;";
        return self::$_db->query($sql);

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