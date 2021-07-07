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
     * @var array
     */
    private static $_dbConfig = [];

    /**
     * @return void
     * @param bool $force Force Reload
     */
    private static function loadDbConf($force = false)
    {
        if (self::$_dbConfig != [] && !$force) {
            return;
        }
        $db = DB::newInstance(true);
        if (!$db->exists()) {
            return;
        }
        $ret = $db->getAll('options')->results();
        foreach ($ret as $c) {
            self::$_dbConfig[$c->name] = $c->value;
        }
    }

    /**
     * @return mixed
     * @param string $path Config Key, Config File Categories Separated by Slashes
     */
    public static function get($path)
    {
        $path = explode('/', $path);
        $constName = 'FLARE_' . implode('_', array_map('strtoupper', $path));
        if (defined($constName)) {
            return constant($constName);
        }

        if (!array_key_exists('config', $GLOBALS) && $path[0] != 'mysql') {
            return self::getFromDb(implode('/', $path));
        } elseif ($path[0] == 'mysql') {
            return '';
        }

        $config = $GLOBALS['config'];
        foreach ($path as $bit) {
            if (isset($config[$bit])) {
                $config = $config[$bit];
            }
        }

        // Check if the Key was Invalid. If so, fall back on the Database
        if ($config === $GLOBALS['config'] && $path != null) {
            if ($path[0] == 'mysql') {
                return '';
            }

            return self::getFromDb(implode('/', $path));
        }

        return $config;
    }

    private static function getFromDb($key)
    {
        self::loadDbConf();
        if (array_key_exists($key, self::$_dbConfig)) {
            return self::$_dbConfig[$key];
        }

        return '';
    }

    /**
     * @return bool
     * @param string $main Main Theme Colour, No Hash
     * @param string $text Text Colour, No Hash
     */
    public static function replaceColour($main, $text)
    {
        $constName = 'FLARE_SITE_COLOUR_MAIN_HEX';
        if (defined($constName)) {
            self::replace('site/colour_main_hex', '#' . $main);
            self::replace('TEXT_COLOUR', '#' . $text);
        } else {
            $currentConf = file_get_contents(__DIR__ . '/../../core/config.php');
            preg_match("/#([a-f0-9]{3}){1,2}\b/i", $currentConf, $matches);
            $currentConf = str_replace($matches[0], '#' . $main, $currentConf);

            $file = fopen(__DIR__ . '/../../core/config.php', 'w+');

            if (!$file) {
                return false;
            }

            $currentConf = file_get_contents(__DIR__ . '/../../core/config.php');
            preg_match("/#([a-f0-9]{3}){1,2}\b/i", $currentConf, $matches);
            $currentConf = str_replace($matches[0], '#' . $main, $currentConf);

            $file = fopen(__DIR__ . '/../../core/config.php', 'w+');

            if (!$file) {
                return false;
                fwrite($file, $currentConf);
                fclose($file);
            }

            self::replace("TEXT_COLOUR", '#' . $text);

            Events::trigger('config/updated', ['item' => 'TEXT_COLOUR']);
            Events::trigger('config/updated', ['item' => 'site/colour_main_hex']);
        }

        return true;
    }

    /**
     * @return bool
     * @param string $data CSS Data
     */
    public static function replaceCss($data)
    {
        $res = file_put_contents(__DIR__ . '/../../assets/custom.css', $data);
        return $res !== FALSE;
    }

    /**
     * @return string|bool
     */
    public static function getCss()
    {
        return file_get_contents(__DIR__ . '/../../assets/custom.css');
    }

    /**
     * @return bool
     * @param string $where Config Key
     * @param mixed $new New Value
     */
    public static function replace($where, $new)
    {
        $constName = 'FLARE_' . implode('_', array_map('strtoupper', explode('/', $where)));
        if (defined($constName)) {
            $file = file_get_contents(__DIR__ . '/../../core/config.new.php');
            $oldLine = "/define\('{$constName}', '.+'\);/";
            $newLine = "define('{$constName}', '{$new}');";
            $res = preg_replace($oldLine, $newLine, $file);

            Events::trigger('config/updated', ['item' => $where]);
            return file_put_contents(__DIR__ . '/../../core/config.new.php', $res) !== FALSE;
        } else {
            if (!file_exists(__DIR__ . '/../../core/config.php')) {
                return self::replaceDb($where, $new);
            }
            $currentConfFile = file_get_contents(__DIR__ . '/../../core/config.php');
            $regex = '/\'' . $where . '\' => .*/m';
            preg_match($regex, $currentConfFile, $matches);

            if (count($matches) === 0) {
                return self::replaceDb($where, $new);
            }

            $currentVal = explode('=>', $matches[0]);
            $currentVal = trim(str_replace("'", "", $currentVal[1]));
            $currentVal = trim(str_replace(",", "", $currentVal));

            $newConf = preg_replace('/' . $currentVal . '/', $new, $currentConfFile, 1);

            $file = fopen(__DIR__ . '/../../core/config.php', 'w+');

            if (!$file) {
                return false;
            }

            fwrite($file, $newConf);
            fclose($file);

            Events::trigger('config/updated', ['item' => $where]);

            return true;
        }
    }

    /**
     * @return bool
     * @param string $where Config Key
     * @param mixed $new New Value
     */
    public static function replaceDb($where, $new)
    {
        $db = DB::getInstance();
        $sql = "SELECT COUNT(name) AS results FROM options WHERE name = ?";
        $exists = $db->query($sql, [$where])->results()[0]->results;
        if ($exists > 0) {
            $res = $db->update('options', "'" . $where . "'", 'name', ["value" => $new]);
            return !($res->error());
        }

        $res = $db->insert('options', [
            "name" => $where,
            "value" => $new,
        ]);

        Events::trigger('config/updated', ['item' => $where]);
        self::loadDbConf(true);

        return !($res->error());
    }

    /**
     * @return bool
     * @param string $key Config Key
     * @param mixed $value Value
     */
    public static function add($key, $value)
    {
        $db = DB::getInstance();
        $ret = $db->insert('options', array(
            'name' => $key,
            'value' => $value
        ));

        Events::trigger('config/added', ['item' => $key, 'value' => $value]);

        return !($ret->error());
    }

    /**
     * @return bool
     */
    public static function isReady()
    {
        $old = file_exists(__DIR__ . '/../../core/config.php');
        $new = file_exists(__DIR__ . '/../../core/config.new.php');
        $db = self::get('mysql/host') != 'DB_HOST' && !empty(self::get('mysql/host'));
        return ($old || $new) && $db;
    }
}
