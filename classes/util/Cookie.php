<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Cookie
{

    /**
     * @return bool
     * @param string $name Cookie Name
     */
    public static function exists($name)
    {
        return (isset($_COOKIE[$name])) ? true : false;
    }

    /**
     * @return mixed
     * @param string $name Cookie Name
     */
    public static function &get($name)
    {
        return $_COOKIE[$name];
    }

    /**
     * @return bool
     * @param string $name Cookie Name
     * @param mixed $value Cookie Value
     * @param int $expiry Cookie Expiry
     */
    public static function create($name, $value, $expiry)
    {
        if (setcookie($name, $value, time() + $expiry, '/')) {
            return true;
        }
        return false;
    }

    /**
     * @return void
     * @param $name Cookie Name
     */
    public static function delete($name)
    {
        self::create($name, '', time() - 1);
    }
}
