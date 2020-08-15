<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Config 
{

    public static function get($path = null) {

        if ($path) {
            $config = $GLOBALS['config'];
            $path = explode('/', $path);

            foreach ($path as $bit) {
                if(isset($config[$bit])) {
                    $config = $config[$bit];
                }
            }

            return $config;

        }

        return false;

    }

}