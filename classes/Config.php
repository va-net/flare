<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Config 
{

    /**
     * @return mixed
     * @param string $path Config Key, Config File Categories Separated by Slashes
     */
    public static function get($path = null) {

        if ($path) {
            $config = $GLOBALS['config'];
            $path = explode('/', $path);

            foreach ($path as $bit) {
                if(isset($config[$bit])) {
                    $config = $config[$bit];
                }
            }

            // Check if the Key was Invalid. If so, fall back on the Database
            if ($config === $GLOBALS['config']) {
                $db = DB::getInstance();
                $ret = $db->get('options', array('name', '=', $path[0]));
                if ($ret->count() === 0) {
                    return false;
                }

                return $ret->first()->value;
            }

            return $config;

        }

        return false;

    }

    /**
     * @return bool
     * @param string $main Main Theme Colour, No Hash
     * @param string $text Text Colour, No Hash
     */
    public static function replaceColour($main, $text)
    {

        $currentConf = file_get_contents(__DIR__.'/../core/config.php');
        preg_match("/#([a-f0-9]{3}){1,2}\b/i", $currentConf, $matches);
        $currentConf = str_replace($matches[0], '#'.$main, $currentConf);

        $file = fopen(__DIR__.'/../core/config.php', 'w+');

        if (!$file) {
            return false;
        }

        fwrite($file, $currentConf);
        fclose($file);

        self::replace("TEXT_COLOUR", '#'.$text);

        Events::trigger('config/updated', ['item' => 'TEXT_COLOUR']);
        Events::trigger('config/updated', ['item' => 'site/colour_main_hex']);

        return true;

    }

    /**
     * @return bool
     * @param string $where Config Key
     * @param mixed $new New Value
     */
    public static function replace($where, $new)
    {

        $currentConfFile = file_get_contents(__DIR__.'/../core/config.php');
        $regex = '/\''.$where.'\' => .*/m';
        preg_match($regex, $currentConfFile, $matches);

        if (count($matches) === 0) {
            $db = DB::getInstance();
            $ret = $db->update('options', '\''.$where.'\'', 'name', array(
                'value' => $new
            ));
            return !($ret->error());
        }

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

        Events::trigger('config/updated', ['item' => $where]);

        return true;

    }

    /**
     * @return bool
     * @param string $key Config Key
     * @param mixed $value Value
     */
    public static function add($key, $value) {
        $db = DB::getInstance();
        $ret = $db->insert('options', array(
            'name' => $key,
            'value' => $value
        ));

        Events::trigger('config/added', ['item' => $where]);

        return !($ret->error());
    }

}