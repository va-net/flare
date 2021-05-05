<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Analytics
{
    private static $BASE = 'https://api.vanet.app/flare/v1';

    /**
     * @param Event $ev
     * @return void
     */
    public static function reportUpdate($ev)
    {
        $key = Config::get('INSTANCE_ID');
        if (empty($key)) return;

        $db = DB::getInstance();

        $inst = self::getInfo();
        $inst['flareVersion'] = $ev->params['tag'];
        $inst['phpVersion'] = phpversion();
        $inst['mysqlVersion'] = $db->query("SELECT VERSION() AS v")->first()->v;
        $inst['url'] = self::url();
        $inst['name'] = Config::get('va/name');
        $url = self::$BASE . '/instance';

        $options = array(
            'http' => array(
                'method'  => 'PUT',
                'content' => Json::encode($inst),
                'header' =>  "Content-Type: application/json\r\nX-Api-Key: {$key}\r\n",
            )
        );

        $context = stream_context_create($options);
        @file_get_contents($url, false, $context);
    }

    /**
     * @return array
     */
    public static function getInfo()
    {
        $key = Config::get('INSTANCE_ID');
        return Json::decode(HttpRequest::hacky(self::$BASE . '/instance', 'GET', '', ["X-Api-Key: {$key}\r\n"]));
    }

    /**
     * @return void
     */
    public static function register()
    {
        if (!empty(Config::get('INSTANCE_ID'))) return;

        $key = Config::get('vanet/api_key');

        $url = self::$BASE . "/instance";
        $db = DB::getInstance();
        $data = [
            "name" => Config::get('va/name'),
            "flareVersion" => Updater::getVersion()["tag"],
            "phpVersion" => phpversion(),
            "mysqlVersion" => $db->query("SELECT VERSION() AS v")->first()->v,
            "url" => self::url(),
        ];

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => Json::encode($data),
                'header' =>  "Content-Type: application/json\r\nX-Api-Key: {$key}\r\n",
            )
        );

        $context = stream_context_create($options);
        $res = Json::decode(file_get_contents($url, false, $context));
        Config::replace('INSTANCE_ID', $res['result']);
    }

    /**
     * @return void
     */
    public static function unregister()
    {
        $key = Config::get('INSTANCE_ID');
        if (empty($key)) return;

        $url = self::$BASE . "/instance";

        $options = array(
            'http' => array(
                'method'  => 'DELETE',
                'header' => "X-Api-Key: {$key}\r\n"
            )
        );

        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
        Config::replace('INSTANCE_ID', 0);
    }

    /**
     * @return bool
     */
    public static function isRegistered()
    {
        return !empty(Config::get('INSTANCE_ID'));
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
}
