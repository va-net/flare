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
        $result = self::$_db->query("SELECT * FROM pilots WHERE id <> {$id} AND callsign = '{$callsign}' AND `status` < 2");
        return $result->count() != 0;
    }

    /**
     * @return array
     */
    public static function all()
    {
        self::init();
        $result = self::$_db->query("SELECT `callsign` FROM `pilots` WHERE `status` < 2")->results();
        return array_map(function ($u) {
            return $u->callsign;
        }, $result);
    }
}
