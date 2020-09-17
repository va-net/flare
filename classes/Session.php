<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Session {

    /**
     * @return string
     * @param string $name Variable Name
     * @param mixed $value Variable Value
     */
    public static function create($name, $value){
        $_SESSION[$name] = $value;
        return $value;
    }

    /**
     * @return bool
     * @param string $name Variable Name
     */
    public static function exists($name) {
        return isset($_SESSION[$name]) ? true : false;
    }

    /**
     * @return null
     * @param string $name Variable Name
     */
    public static function delete($name) {
        if (self::exists($name)) {
            unset($_SESSION[$name]);
        }
    }

    /**
     * @return mixed
     * @param string $name Variable Name
     */
    public static function get($name) {
        return $_SESSION[$name];
    }

    /**
     * @return string
     * @param string $name Flash Type
     * @param string $content Flash Content
     */
    public static function flash($name, $content = '') {
        if (self::exists($name)) {
            $session = self::get($name);
            self::delete($name);
            return $session;
        } else {
            self::create($name, $content);
            $session = self::get($name);
            return $session;
        }
    }

}