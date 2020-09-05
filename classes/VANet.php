<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class VANet
{

    public static function myInfo($key = null)
    {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }

        $curl = new Curl;
        $curl->options['CURLOPT_FRESH_CONNECT'] = true;
        $response = $curl->get('https://vanet.app/api/myinfo/bykey', array(
            'apikey' => $key
        ));
        return Json::decode($response->body);
    }

    public static function isGold($key = null) 
    {

        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }

        $myinfo = self::myInfo($key);

        if ($myinfo['tier'] != 'Gold') {
            return false;
        }
        return true;

    }

    public static function isVeKey($key = null)
    {

        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }

        $myinfo = self::myInfo($key);
        if ($myinfo['type'] == null) {
            return false;
        }
        return true;

    }

    public static function getStats($key = null) 
    {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }

        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $request = $curl->get(Config::get('vanet/base_url') . "/api/airline", array(
            "apikey" => $key,
        ));
        return Json::decode($request->body);
    }
}