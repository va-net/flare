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

        return self::$_db->query("SELECT routes.*, aircraft.name AS aircraft, aircraft.liveryname AS livery, aircraft.ifliveryid AS liveryid FROM routes INNER JOIN aircraft ON routes.aircraftid=aircraft.id;");

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
     * @return DB
     * @param int $id Route ID
     */
    public static function find($id)
    {
        self::init();

        $query = 'SELECT routes.fltnum, routes.dep, routes.arr, routes.duration, routes.id, routes.aircraftid, 
        aircraft.name AS aircraft FROM routes INNER JOIN aircraft ON aircraft.id = routes.aircraftid WHERE routed.id=?';
        
        return $db->query($query, array($id));
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

}