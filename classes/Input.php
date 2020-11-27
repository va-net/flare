<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Input {

    /**
     * @var array
     */
    private static $_PUT = null;

    /**
     * @return bool
     * @param string $type GET or POST
     */
    public static function exists($type = 'post') {
        switch (strtolower($type)) {
            case 'post':
                return (!empty($_POST)) ? true : false;
                break;
            case 'get':
                return (!empty($_GET)) ? true : false;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * @return string
     * @param string|int $item Item Key to Get
     */
    public static function get($item) {
        if (self::$_PUT == null) {
            switch (getallheaders()['Content-Type']) {
                case 'application/json':
                    self::$_PUT = Json::decode(file_get_contents("php://input"));
                    break;
                case 'x-www-form-urlencoded':
                    self::$_PUT = parse_str(file_get_contents('php://input'));
                    break;
                default:
                    self::$_PUT = parse_str(file_get_contents('php://input'));
                    break;
            }
        }
        if (isset($_POST[$item])) {
            return $_POST[$item];
        } elseif (isset(self::$_PUT[$item])) {
            return self::$_PUT[$item];
        } elseif (isset($_GET[$item])) {
            return $_GET[$item];
        }
        return '';
    }

    /**
     * @return array|string
     * @param string $file File Name
     */
    public static function getFile($file) {
        if (isset($_FILES[$file])) {
            return $_FILES[$file];
        }
        return '';
    }

}