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

    public static function getAirport($icao, $key = null) {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }

        $curl = new Curl;
        $response = $curl->get(Config::get('vanet/base_url').'/api/airports/'.urlencode($icao), array(
            'apikey' => $key
        ));
        return Json::decode($response->body);
    }

    public static function sendPirep($fields) 
    {
        $curl = new Curl;
        return $curl->post(Config::get('vanet/base_url').'/api/flights/new?apikey='.Config::get('vanet/api_key'), $fields);
    }

    public static function getEvents($key = null, $future = true)
    {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }
        if (!self::isGold($key)) {
            return false;
        }
        
        $url = Config::get('vanet/base_url').'/api/events';
        if ($future) {
            $url = Config::get('vanet/base_url').'/api/events/future';
        }

        $curl = new Curl;
        $response = $curl->get($url, array(
            'apikey' => $key
        ));
        return Json::decode($response->body);
    }

    public static function findEvent($id, $key = null) 
    {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }
        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $response = $curl->get(Config::get('vanet/base_url').'/api/events/'.urlencode($id), array(
            'apikey' => $key
        ));
        return Json::decode($response->body);
    }

    public static function createEvent($fields, $key = null) 
    {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }
        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $request = $curl->post(Config::get('vanet/base_url').'/api/events/new?apikey='.urlencode($key), $fields);
        $response = Json::decode($request->body);
        if (array_key_exists("status", $response) || !$response["success"]) {
            throw new Exception("Error Creating Event");
        }
    }

    public static function eventSignUp($pilotUid, $gateId, $key = null) 
    {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }
        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $request = $curl->get(Config::get('vanet/base_url').'/api/events/signup/'.$gateId.'/'.$pilotUid, array(
            'apikey' => $key
        ));
        $response = Json::decode($request->body);

        if (array_key_exists("status", $response)) {
            return $response["status"];
        }

        if (!$response["success"]) {
            throw new Exception("Error Connecting to VANet");
        }

        return true;
    }

    public static function eventPullOut($slotId, $eventId, $pilotUid, $key = null) 
    {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }
        if (!self::isGold($key)) {
            return false;
        }
        $already = self::isSignedUp($pilotUid, $eventId);
        if (!$already || $already != $slotId) {
            return true;
        }

        $curl = new Curl;
        $request = $curl->get(Config::get('vanet/base_url').'/api/events/signup/vacate/'.urlencode($slotId), array(
            'apikey' => $key,
        ));
        $response = Json::decode($request->body);

        if (array_key_exists("status", $response)) {
            return $response["status"];
        }

        if (!$response["success"]) {
            throw new Exception("Error Connecting to VANet");
        }

        return true;
    }

    public static function isSignedUp($pilotUid, $eventId, $key = null) 
    {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }
        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $request = $curl->get(Config::get('vanet/base_url').'/api/events/'.urlencode($eventId), array(
            'apikey' => $key,
        ));
        $response = Json::decode($request->body)["signups"];
        foreach ($response as $r) {
            if ($r["pilotId"] == $pilotUid) {
                return $r["id"];
            }
        }
        
        return false;
    }

    public static function deleteEvent($eventId, $key = null) 
    {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }
        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $curl->delete(Config::get('vanet/base_url').'/api/events/delete/'.urlencode($eventId).'?apikey='.urlencode($key));
    }

    public static function editEvent($id, $fields, $key = null) {
        if ($key == null) {
            $key = Config::get('vanet/api_key');
        }
        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $response = $curl->put(Config::get('vanet/base_url').'/api/events/update/'.urlencode($id).'?apikey='.urlencode($key), $fields);
        if (array_key_exists("status", Json::decode($response->body))) {
            return false;
        }

        return true;
    }
}