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

    /**
     * @var array
     */
    private static $_cache = null;

    private static function init()
    {
        self::$_db = DB::getInstance();
        if (self::$_cache == null) {
            $res = self::$_db->query("SELECT * FROM `cache` WHERE `expiry` > NOW() OR `expiry` = null", [], true)->results();
            $keys = array_map(function ($row) {
                return $row->name;
            }, $res);
            $vals = array_map(function ($row) {
                return $row->value;
            }, $res);
            self::$_cache = array_combine($keys, $vals);
        }
    }

    /**
     * @param string $key Item Key
     * @return string
     */
    public static function get($key)
    {
        self::init();
        if (!isset(self::$_cache[$key])) return '';

        return self::$_cache[$key];
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
        self::$_db->query($sql, [$key, $val, $expiry, $val, $expiry], true);
        if (self::$_cache != null) self::$_cache[$key] = $val;
    }

    /**
     * @return bool
     * @param string $key Item Key
     */
    public static function exists($key)
    {
        self::init();
        return isset(self::$_cache[$key]);
    }

    /**
     * @return null
     * @param string $key Item Key
     */
    public static function delete($key)
    {
        self::init();
        self::$_db->delete('cache', ['name', '=', $key], true);
        if (isset(self::$_cache[$key])) unset(self::$_cache[$key]);
    }

    /**
     * @return null
     */
    public static function clean()
    {
        self::init();
        self::$_db->query("DELETE FROM `cache` WHERE `expiry` < NOW() AND `expiry`!=null", [], true);
    }

    /**
     * @return null
     */
    public static function clear()
    {
        self::init();
        self::$_db->delete('cache', ['1', '=', '1'], true);
    }
}
