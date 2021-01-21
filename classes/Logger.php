<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Logger
{

    private static $_logDir = __DIR__ . '/../core/logs';

    /**
     * @return null
     * @param Event $event
     */
    public static function logEvent($event)
    {
        $data = Json::encode($event->params);
        $text = "Event {$event->event} Fired with Data {$data}";
        self::log($text);
    }

    /**
     * @return null
     * @param string $log Log Text
     */
    public static function log($log)
    {
        $log = '[' . date("d/m/Y:H:i:s O") . '] ' . $log;
        $filename = self::$_logDir . '/' . date("Y-m-d") . '.log';
        if (file_exists($filename)) {
            $file = fopen($filename, 'a');
            fwrite($file, "\r\n" . $log);
            fclose($file);
            return;
        }

        @file_put_contents($filename, $log);
    }

    /**
     * @return string
     */
    public static function latestLog()
    {
        $filename = self::$_logDir . '/' . date("Y-m-d") . '.log';
        $data = file_get_contents($filename);
        $lines = preg_match_all("/.*\r?\n/", $data);
        return;
    }

    /**
     * @return null
     * @param int $days Maximum Log Age
     */
    public static function clearOld($days = 30)
    {
        $now = new DateTime();
        $logs = scandir(self::$_logDir);
        foreach ($logs as $l) {
            $path = self::$_logDir . '/' . $l;
            if (strpos($l, '.') == 0) continue;
            $lDate = new DateTime(str_replace('.log', '', $l));
            $diff = $lDate->diff($now);
            if ($diff->days > $days) {
                unlink($path);
            }
        }

        Events::trigger('logs/cleared', ["period" => $days]);
    }

    /**
     * @return null
     */
    public static function clearAll()
    {
        $logs = scandir(self::$_logDir);
        foreach ($logs as $l) {
            $path = self::$_logDir . '/' . $l;
            if (strpos($l, '.') == 0) continue;
            if (strpos(strtolower(php_uname('s')), "window") !== FALSE) {
                $path = str_replace('/', '\\', $path);
            }
            unlink($path);
        }

        Events::trigger('logs/cleared', ["period" => "*"]);
    }
}
