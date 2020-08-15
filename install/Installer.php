<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../classes/DB.php';

class Installer
{

    private static $_error;

    public static function showTemplate($name) 
    {

        if (file_exists('./templates/'.$name.'.php')) {
            include_once './templates/'.$name.'.php';
            return true;
        } else {
            return false;
        }

    }

    public static function createConfig($data = array()) 
    {

        $template = file_get_contents('./templates/config.php');
        foreach ($data as $name => $val) {
            $template = str_replace($name, $val, $template);
        }

        $file = fopen('../core/config.php', 'w');

        if (!$file) {
            self::$_error = true;
        }

        fwrite($file, $template);
        fclose($file);

        return true;

    }

    public static function appendConfig($data = array()) 
    {

        $currentConf = file_get_contents('../core/config.php');
        foreach ($data as $name => $val) {
            $currentConf = str_replace($name, $val, $currentConf);
        }

        $file = fopen('../core/config.php', 'w+');

        if (!$file) {
            self::$_error = true;
        }

        fwrite($file, $currentConf);
        fclose($file);

        return true;

    }   

    public static function setupDb()
    {

        $sql = file_get_contents('./db.sql');
        $db = DB::newInstance();

        if (!$db->query($sql)) {
            self::$_error = true;
            return false;
        }
        $all = Aircraft::fetchAllAircraftFromVANet();
        foreach ($all as $id => $name) {
            $db->insert('aircraft', array(
                'ifaircraftid' => $id,
                'name' => $name
            ));
        }
        return true;

    }

    public static function error()
    {

        return self::$_error;

    }

}