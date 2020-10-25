<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Stats {
    /**
     * @var DB
     */
    private static $_db;

    private static function init() 
    {
        self::$_db = DB::getInstance();
    }

    /**
     * @return int
     * @param int $days Time Period
     */
    public static function totalHours($days = null) 
    {
        self::init();
        $transhours;
        $transflights;

        if ($days == null) {
            $transhours = self::$_db->query('SELECT SUM(transhours) AS trans FROM pilots')->first()->trans;
            $filedhours = self::$_db->query('SELECT SUM(flighttime) AS filed FROM pireps WHERE status=1')->first()->filed;
        } else {
            $transhours = 0;
            $filedhours = self::$_db->query('SELECT SUM(flighttime) AS filed FROM pireps WHERE status=1 AND DATEDIFF(NOW(), date) <= ?', [$days])->first()->filed;
        }

        if ($filedhours == null) $filedhours = 0;
        
        return $transhours + $filedhours;
    }

    /**
     * @return int
     * @param int $days Time Period
     */
    public static function totalFlights($days = null) 
    {
        self::init();

        if ($days == null) {
            $transflights = self::$_db->query('SELECT SUM(pilots.transflights) AS trans FROM pilots')->first()->trans;
            $filedflights = self::$_db->query('SELECT COUNT(pireps.id) AS pireps FROM pireps WHERE status=1')->first()->pireps;
        } else {
            $transflights = self::$_db->query('SELECT SUM(pilots.transflights) AS trans FROM pilots')->first()->trans;
            $filedflights = self::$_db->query('SELECT COUNT(pireps.id) AS pireps FROM pireps WHERE status=1 AND DATEDIFF(NOW(), date) <= ?', [$days])->first()->pireps;
        }
        
        return $transflights + $filedflights;
    }

    /**
     * @return int
     * @param int $days Time Period
     */
    public static function pilotsApplied($days)
    {
        self::init();

        $pilots = self::$_db->query("SELECT COUNT(id) AS applied FROM pilots WHERE DATEDIFF(NOW(), joined) < ?", [$days])->first()->applied;
        
        return $pilots;
    }

    /**
     * @return int
     */
    public static function numPilots() 
    {
        self::init();

        return self::$_db->query('SELECT COUNT(*) AS total FROM pilots WHERE status=1')->first()->total;
    }

    /**
     * @return int
     */
    public static function numRoutes() 
    {
        self::init();

        return self::$_db->query('SELECT COUNT(routes.id) AS total FROM routes')->first()->total;
    }

    /**
     * @return array
     * @param string $field Field to Sort By
     * @param string $order Sort Order
     * @param int $limit Records to Return
     */
    public static function pilotLeaderboard($limit, $field, $order = 'DESC')
    {
        self::init();

        $sql = "SELECT u.*, (SELECT SUM(flighttime) FROM pireps p WHERE p.pilotid=u.id AND status=1) AS flighttime FROM pilots u WHERE status=1 ORDER BY {$field} {$order} LIMIT {$limit}";
        return self::$_db->query($sql)->results();
    }
}