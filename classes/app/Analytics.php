<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Analytics
{
    private static $BASE = 'https://flare.vanet.app/api/public';

    /**
     * @param Event $ev
     * @return void
     */
    public static function reportDbError($ev)
    {
        $key = Config::get('MASTER_API_KEY');
        if ($key == '') return;

        $url = self::$BASE . "/errors?site=" . $key;
        $ev->params["version"] = Updater::getVersion()["tag"];
        $data = [
            "type" => 2,
            "message" => "DB Query Failed",
            "data" => $ev->params,
        ];

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => Json::encode($data),
                'header' =>  "Content-Type: application/json\r\n",
            )
        );

        $context = stream_context_create($options);
        @file_get_contents($url, false, $context);
    }

    /**
     * @param Exception $ex
     * @return void
     */
    public static function reportException($ex)
    {
        $key = Config::get('MASTER_API_KEY');
        if ($key == '') return;

        $url = self::$BASE . "/errors?site=" . $key;
        $excluded = self::excludedFiles();
        $fname = explode('/', $ex->getFile());
        if (in_array($fname[count($fname) - 1], $excluded)) {
            return;
        }

        $data = [
            "type" => 1,
            "code" => $ex->getCode(),
            "message" => $ex->getMessage(),
            "file" => $ex->getFile(),
            "line" => $ex->getLine(),
            "data" => [
                "version" => Updater::getVersion()["tag"]
            ],
        ];

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => Json::encode($data),
                'header' =>  "Content-Type: application/json\r\n",
            )
        );

        $context = stream_context_create($options);
        @file_get_contents($url, false, $context);
    }

    /**
     * @param int $eCode
     * @param string $eMessage
     * @param string $eFile
     * @param int $eLine
     */
    public static function reportError($eCode, $eMessage, $eFile, $eLine)
    {
        $key = Config::get('MASTER_API_KEY');
        if ($key == '') return;

        $excluded = self::excludedFiles();
        $fname = explode('/', $eFile);
        if (in_array($fname[count($fname) - 1], $excluded)) {
            return;
        }

        $url = self::$BASE . "/errors?site=" . $key;
        $data = [
            "type" => 0,
            "message" => "DB Query Failed",
            "code" => $eCode,
            "message" => $eMessage,
            "file" => $eFile,
            "line" => $eLine,
            "data" => [
                "version" => Updater::getVersion()["tag"],
            ],
        ];

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => Json::encode($data),
                'header' =>  "Content-Type: application/json\r\n",
            )
        );

        $context = stream_context_create($options);
        @file_get_contents($url, false, $context);
        return false;
    }

    /**
     * @param Event $ev
     * @return void
     */
    public static function reportUpdate($ev)
    {
        $key = Config::get('MASTER_API_KEY');
        if ($key == '') return;

        $url = self::$BASE . "/instance?site=" . $key;
        $data = [
            "version" => $ev->params['tag']
        ];

        $options = array(
            'http' => array(
                'method'  => 'PUT',
                'content' => Json::encode($data),
                'header' =>  "Content-Type: application/json\r\n",
            )
        );

        $context = stream_context_create($options);
        @file_get_contents($url, false, $context);
    }

    /**
     * @return void
     */
    public static function register()
    {
        if (Config::get('MASTER_API_KEY') != '') return;

        $url = self::$BASE . "/instance";
        $data = [
            "name" => Config::get('va/name'),
            "version" => Updater::getVersion()["tag"],
            "url" => self::url(),
        ];

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => Json::encode($data),
                'header' =>  "Content-Type: application/json\r\n",
            )
        );

        $context = stream_context_create($options);
        $res = Json::decode(file_get_contents($url, false, $context));
        Config::replace('MASTER_API_KEY', $res['result']);
    }

    /**
     * @return void
     */
    public static function unregister()
    {
        $key = Config::get('MASTER_API_KEY');
        if ($key == '') return;

        $url = self::$BASE . "/instance?site={$key}";

        $options = array(
            'http' => array(
                'method'  => 'DELETE',
            )
        );

        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
        Config::replace('MASTER_API_KEY', '');
    }

    /**
     * @return string
     */
    private static function url()
    {
        return sprintf(
            "%s://%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME']
        );
    }

    /**
     * @return string[]
     */
    private static function excludedFiles()
    {
        $plugins = Json::decode(file_get_contents(__DIR__ . '/../plugins.json'));
        $files = [];
        foreach ($plugins as $p) {
            foreach ($p['installation']['files'] as $f) {
                $name = explode('/', $f);
                $files[] = $name[count($name) - 1];
            }
        }

        return $files;
    }
}
