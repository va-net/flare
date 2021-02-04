<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once __DIR__.'/../classes/data/DB.php';

class Installer
{

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
            return false;
        }

        fwrite($file, $template);
        fclose($file);

        return true;

    }

    public static function appendConfig($data = array()) 
    {

        $currentConf = file_get_contents(__DIR__.'/../core/config.php');
        foreach ($data as $name => $val) {
            $currentConf = str_replace($name, $val, $currentConf);
        }

        $file = fopen('../core/config.php', 'w+');

        if (!$file) {
            return false;
        }

        fwrite($file, $currentConf);
        fclose($file);

        return true;

    }   

    public static function setupDb()
    {

        $sql = file_get_contents(__DIR__.'/db.sql');
        $db = DB::getInstance();
        if (!$db->query($sql)) {
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

}