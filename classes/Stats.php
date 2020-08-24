<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Stats {
    private static $_db;

    private static function init() 
    {
        self::$_db = DB::getInstance();
    }

    public static function totalHours() {
        self::init();
        
        $transhours = self::$_db->query('SELECT SUM(transhours) AS trans FROM pilots')->first()->trans;
        $filedhours = self::$_db->query('SELECT SUM(flighttime) AS filed FROM pireps WHERE status=1')->first()->filed;
        return $transhours = $filedhours;
    }

    public static function totalFlights() {
        self::init();

        $transflights = self::$_db->query('SELECT SUM(pilots.transflights) AS trans FROM pilots')->first()->trans;
        $filedflights = self::$_db->query('SELECT COUNT(pireps.id) AS pireps FROM pireps WHERE pireps.status=1')->first()->pireps;
        return $transflights + $filedflights;
    }

    public static function numPilots() {
        self::init();

        return self::$_db->query('SELECT COUNT(*) AS total FROM pilots WHERE status=1')->first()->total;
    }

    public static function numRoutes() {
        self::init();

        return self::$_db->query('SELECT COUNT(routes.id) AS total FROM routes')->first()->total;
    }
}