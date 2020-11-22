<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class VANet
{

    /**
     * @return array
     * @param string $key VANet API Key
     */
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

    /**
     * @return bool
     * @param string $key VANet API Key
     */
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

    /**
     * @return bool
     * @param string $key VANet API Key
     */
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

    /**
     * @return array|bool
     */
    public static function getStats() 
    {
        $key = Config::get('vanet/api_key');

        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $request = $curl->get(Config::get('vanet/base_url') . "/api/airline", array(
            "apikey" => $key,
        ));
        return Json::decode($request->body);
    }

    /**
     * @return array
     * @param string $icao ICAO Code
     */
    public static function getAirport($icao) 
    {
        $key = Config::get('vanet/api_key');

        $curl = new Curl;
        $response = $curl->get(Config::get('vanet/base_url').'/api/airports/'.urlencode($icao), array(
            'apikey' => $key
        ));
        return Json::decode($response->body);
    }

    /**
     * @return CurlResponse
     * @param array $fields PIREP Fields
     */
    public static function sendPirep($fields) 
    {
        $curl = new Curl;
        return $curl->post(Config::get('vanet/base_url').'/api/flights/new?apikey='.Config::get('vanet/api_key'), $fields);
    }

    /**
     * @return array|bool
     * @param bool $future Return Just Future Events
     */
    public static function getEvents($future = true)
    {
        $key = Config::get('vanet/api_key');
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

    /**
     * @return array|bool
     * @param string $id Event ID
     */
    public static function findEvent($id) 
    {
        $key = Config::get('vanet/api_key');
        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $response = $curl->get(Config::get('vanet/base_url').'/api/events/'.urlencode($id), array(
            'apikey' => $key
        ));
        return Json::decode($response->body);
    }

    /**
     * @return bool
     * @param array $fields Event Fields
     */
    public static function createEvent($fields) 
    {
        $key = Config::get('vanet/api_key');
        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $request = $curl->post(Config::get('vanet/base_url').'/api/events/new?apikey='.urlencode($key), $fields);
        $response = Json::decode($request->body);
        if (array_key_exists("status", $response) || !$response["success"]) {
            return false;
        }

        Events::trigger('vanet/event/added', $fields);
        
        return true;
    }

    public static function eventSignUp($pilotUid, $gateId) 
    {
        $key = Config::get('vanet/api_key');
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

        Events::trigger('vanet/event/signup/added', ['pilotUid' => $pilotUid, 'gateId' => $gateId]);

        return true;
    }

    /**
     * @return int|bool
     * @param string $slotId Slot ID
     * @param string $eventId Event ID
     * @param string $pilotUid Pilot User ID
     */
    public static function eventPullOut($slotId, $eventId, $pilotUid) 
    {
        $key = Config::get('vanet/api_key');
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

        Events::trigger('vanet/event/signup/removed', ['slot' => $slotId, 'pilot' => $pilotUid, 'event' => $eventId]);

        return true;
    }

    /**
     * @return bool
     * @param string $pilotUid Pilot User ID
     * @param string $eventId Event ID
     */
    public static function isSignedUp($pilotUid, $eventId) 
    {
        $key = Config::get('vanet/api_key');
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

    /**
     * @return bool
     * @param string $eventId Event ID
     */
    public static function deleteEvent($eventId) 
    {
        $key = Config::get('vanet/api_key');
        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $curl->delete(Config::get('vanet/base_url').'/api/events/delete/'.urlencode($eventId).'?apikey='.urlencode($key));
        Events::trigger('vanet/event/deleted', ['id' => $eventId]);
        return true;
    }

    /**
     * @return bool
     * @param string $id Event ID
     * @param array $fields Updated Event Fields
     */
    public static function editEvent($id, $fields) 
    {
        $key = Config::get('vanet/api_key');
        if (!self::isGold($key)) {
            return false;
        }

        $curl = new Curl;
        $response = $curl->put(Config::get('vanet/base_url').'/api/events/update/'.urlencode($id).'?apikey='.urlencode($key), $fields);
        if (array_key_exists("status", Json::decode($response->body))) {
            return false;
        }

        $fields["id"] = $id;
        Events::trigger('vanet/event/updated', $fields);

        return true;
    }

    /**
     * @return bool|array
     * @param string $server Server
     * @param string $callsign Pilot Callsign
     * $param string $uid Pilot User ID
     */
    public static function runAcars($server, $callsign = null, $uid = null) 
    {
        if (!self::isGold()) return false;
        
        $user = new User();

        if ($callsign == null) $callsign = $user->data()->callsign;
        if ($uid == null) $uid = $user->data()->ifuserid;
        $key = Config::get('vanet/api_key');

        $curl = new Curl;
        $request = $curl->get(Config::get('vanet/base_url').'/api/acars', array(
            'callsign' => $callsign,
            'userid' => $uid,
            'server' => $server,
            'apikey' => $key
        ));
        return Json::decode($request->body);
    }

    /**
     * @return array
     */
    public static function getCodeshares() 
    {
        $curl = new Curl;
        $response = $curl->get(Config::get('vanet/base_url').'/api/codeshares', array(
            'apikey' => Config::get('vanet/api_key')
        ));
        return Json::decode($response->body);
    }

    /**
     * @return bool
     * @param array $fields Codeshare Fields
     */
    public static function sendCodeshare($fields) 
    {
        $curl = new Curl;
        $request = $curl->post(Config::get('vanet/base_url').'/api/codeshares/new?apikey='.urlencode(Config::get('vanet/api_key')), $fields);
        $response = Json::decode($request->body);
        if (array_key_exists("status", $response) || !$response["success"]) {
            return false;
        }

        Events::trigger('vanet/codeshare/requested', $fields);

        return true;
    }

    /**
     * @return bool
     * @param string $id Codeshare ID
     */
    public static function deleteCodeshare($id) 
    {
        $curl = new Curl;
        $request = $curl->delete(Config::get('vanet/base_url').'/api/codeshares/delete/'.urlencode($id).'?apikey='.urlencode(Config::get('vanet/api_key')));
        $response = Json::decode($request->body);

        if (array_key_exists("status", $response) || !$response["success"]) {
            return false;
        }

        Events::trigger('vanet/codeshare/deleted', ['id' => $id]);

        return true;
    }

    /**
     * @return array|bool
     * @param string $id Codeshare ID
     */
    public static function findCodeshare($id) 
    {
        $curl = new Curl;
        $request = $curl->get(Config::get('vanet/base_url').'/api/codeshares/'.urlencode($id).'?apikey='.urlencode(Config::get('vanet/api_key')));
        $response = Json::decode($request->body);

        if (array_key_exists("status", $response)) {
            return false;
        }

        return $response;
    }
}