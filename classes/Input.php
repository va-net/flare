<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Input {

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
        if (isset($_POST[$item])) {
            return $_POST[$item];
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