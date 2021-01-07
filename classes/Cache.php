<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Cache
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
     * @param string $key Item Key
     * @return string
     */
    public static function get($key)
    {
        self::init();
        $res = self::$_db->query("SELECT * FROM `cache` WHERE `name`=? AND (`expiry` > NOW() OR `expiry`=null)", [$key]);
        if ($res->count() < 1) return '';

        return $res->first()->value;
    }

    /**
     * @param string $key Item Key
     * @param string $val Item Value
     * @param string $expiry Item Expiry
     * @return null
     */
    public static function set($key, $val, $expiry)
    {
        self::init();
        $sql = "INSERT INTO `cache` (`name`, `value`, `expiry`) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE `value`=?, `expiry`=?";
        self::$_db->query($sql, [$key, $val, $expiry, $val, $expiry]);
    }

    /**
     * @return null
     */
    public static function clean()
    {
        self::init();
        self::$_db->query("DELETE FROM `cache` WHERE `expiry` < NOW() AND `expiry`!=null");
    }
}
