<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Callsign
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
     * @return bool
     * @param int $id Pilot ID to Exclude
     * @param string $callsign Callsign to Check
     */
    public static function assigned($callsign, $id)
    {

        self::init();
        if ($result = self::$_db->query("SELECT * FROM pilots WHERE id <> {$id} AND callsign = '{$callsign}'")) {
            if ($result->count() == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public static function all()
    {
        self::init();
        $result = self::$_db->query("SELECT `callsign` FROM `pilots`")->results();
        return array_map(function ($u) {
            return $u->callsign;
        }, $result);
    }
}
