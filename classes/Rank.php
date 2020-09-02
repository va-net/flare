<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Rank
{

    private static $_db;

    private static function init()
    {

        self::$_db = DB::newInstance();

    }

    public static function calc($hours, $returnid = false)
    {

        self::init();

        $ranks = self::$_db->get('ranks', array('id', '>', '0'), array('timereq', 'desc'))->results();

        foreach ($ranks as $item) {
            if ($item->timereq * 3600 <= $hours) {
                if ($returnid) {
                    return $item->id;
                } else {
                    return $item->name;
                }
            }
        }

    }

    public static function getId($hours)
    {

        self::init();

        return self::calc($hours, true);

    }

    public static function getName($hours)
    {

        self::init();

        return self::calc($hours, false);

    }

    public static function add($name, $timereq) 
    {

        self::init();

        self::$_db->insert('ranks', array(
            'name' => $name,
            'timereq' => $timereq
        ));

    }

    public static function update($id, $fields = array()) 
    {
        self::init();
        
        if (!self::$_db->update('ranks', $id, 'id', $fields)) {
            throw new Exception('There was a problem updating the user.');
        }
    }

    public static function getFirstRank()
    {

        self::init();
        $rank = self::$_db->get('ranks', array('id', '>', '0'), array('timereq', 'asc'));
        return $rank->first()->name;

    }

    public static function idToName($id) 
    {

        self::init();
        $rank = self::$_db->get('ranks', array('id', '=', $id));
        return $rank->first()->name;

    }
    
    public static function nameToId($name) 
    {

        self::init();
        $rank = self::$_db->get('ranks', array('name', '=', $name));
        return $rank->first()->id;

    }

    public static function fetchAllNames() 
    {

        self::init();
        return self::$_db->getAll('ranks');

    }

}