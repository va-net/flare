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

    public static function replaceColour($new)
    {

        $currentConf = file_get_contents(__DIR__.'/../core/config.php');
        preg_match("/#([a-f0-9]{3}){1,2}\b/i", $currentConf, $matches);
        $currentConf = str_replace($matches[0], '#'.$new, $currentConf);

        $file = fopen(__DIR__.'/../core/config.php', 'w+');

        if (!$file) {
            return false;
        }

        fwrite($file, $currentConf);
        fclose($file);

        return true;

    }

    public static function replace($where, $new)
    {

        $currentConfFile = file_get_contents(__DIR__.'/../core/config.php');
        $regex = '/\''.$where.'\' => .*/m';
        preg_match($regex, $currentConfFile, $matches);

        $currentVal = explode('=>', $matches[0]);
        $currentVal = trim(str_replace("'", "", $currentVal[1]));
        $currentVal = trim(str_replace(",", "", $currentVal));

        $newConf = preg_replace('/'.$currentVal.'/', $new, $currentConfFile, 1);

        $file = fopen(__DIR__.'/../core/config.php', 'w+');

        if (!$file) {
            return false;
        }

        fwrite($file, $newConf);
        fclose($file);

        return true;

    }

}