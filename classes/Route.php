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

    public static function add($fields) 
    {

        self::init();

        self::$_db->insert('routes', array(
            'fltnum' => $fields[0], 
            'dep' => $fields[1],
            'arr' => $fields[2],
            'duration' => $fields[3],
            'aircraftid' => $fields[4]
        ));
        
    }

    public static function fetchAll()
    {

        self::init();

        return self::$_db->getAll('routes');

    }

    public static function delete($id)
    {

        self::init();

        self::$_db->delete('routes', array('id', '=', $id));

    }

    public static function find($id)
    {
        self::init();

        $query = 'SELECT routes.fltnum, routes.dep, routes.arr, routes.duration, routes.id, routes.aircraftid, 
        aircraft.name AS aircraft FROM routes INNER JOIN aircraft ON aircraft.id = routes.aircraftid WHERE routed.id=?';
        
        return $db->query($query, array($id));
    }

}