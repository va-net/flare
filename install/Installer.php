<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once __DIR__ . '/../classes/data/DB.php';

class Installer
{

    public static function showTemplate($name)
    {

        if (file_exists('./templates/' . $name . '.php')) {
            include_once './templates/' . $name . '.php';
            return true;
        } else {
            return false;
        }
    }

    public static function createConfig($data = array())
    {

        $template = file_get_contents(__DIR__ . '/templates/config.php');
        foreach ($data as $name => $val) {
            $template = str_replace("'{$name}'", "'{$val}'", $template);
        }

        return file_put_contents(__DIR__ . '/../core/config.new.php', $template);
    }

    public static function appendConfig($data = array())
    {

        $currentConf = file_get_contents(__DIR__ . '/../core/config.new.php');
        foreach ($data as $name => $val) {
            $currentConf = str_replace("'{$name}'", "'{$val}'", $currentConf);
        }

        return file_put_contents(__DIR__ . '/../core/config.new.php', $currentConf);
    }

    public static function setupDb()
    {

        $sql = file_get_contents(__DIR__ . '/db.sql');
        $db = DB::getInstance();
        if (!$db->query($sql)) {
            return false;
        }
        return true;
    }
}
