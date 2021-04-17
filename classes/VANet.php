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
     * @return array|null
     * @param string $key VANet API Key
     */
    public static function myInfo($key = null)
    {
        $wasNull = false;
        if ($key == null) {
            $wasNull = true;
            $key = Config::get('vanet/api_key');
        }

        if ($wasNull) {
            $cache = Cache::get('myinfo');
            if ($cache != '') {
                return Json::decode($cache);
            }
        }

        $req = new HttpRequest("https://api.vanet.app/airline/v1/profile");
        $req->setRequestHeaders([
            "X-Api-Key" => $key,
        ])->execute();
        $response = Json::decode($req->getResponse());

        if ($response['status'] != 0) return null;

        if ($wasNull) {
            Cache::set('myinfo', $response['result'], date("Y-m-d H:i:s", strtotime('+24 hours')));
        }

        return $response['result'];
    }

    /**
     * @return bool
     * @param string $key VANet API Key
     */
    public static function isGold($key = null)
    {
        $myinfo = self::myInfo($key);
        if ($myinfo == null) return false;

        return $myinfo['isGoldPlan'];
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
        return $myinfo != null;
    }

    /**
     * @return array|null
     */
    public static function getStats()
    {
        $key = Config::get('vanet/api_key');

        if (!self::isGold($key)) {
            return null;
        }

        $req = new HttpRequest("https://api.vanet.app/airline/v1/stats");
        $req->setRequestHeaders(["X-Api-Key" => $key])->execute();
        $response = Json::decode($req->getResponse());
        if ($response['status'] != 0) return null;

        return $response['result'];
    }

    /**
     * @return bool
     * @param string $callsign Pilot Callsign
     * @param int $id Pilot ID
     */
    public static function setupPireps($callsign, $id)
    {
        $db = DB::getInstance();
        $server = 'casual';
        $force = Config::get('FORCE_SERVER');
        if ($force != 0 && $force != 'casual') $server = $force;

        $callsign = urlencode($callsign);
        $server = urlencode($server);

        $req = new HttpRequest("https://api.vanet.app/airline/v1/user/id?callsign={$callsign}&serverName={$server}");
        $req->setRequestHeaders(["X-Api-Key" => Config::get('vanet/api_key')])->execute();
        if ($req->getHttpCode() != 200) {
            return false;
        }

        $response = Json::decode($req->getResponse());
        if ($response['status'] != 0) return false;

        if (!$db->update('pilots', $id, 'id', array(
            'ifuserid' => $response['result']
        ))) {
            return false;
        }

        Events::trigger('pirep/setup', ["pilot" => $id, "userid" => $response["result"], "method" => 0]);

        return true;
    }

    /**
     * @return bool
     * @param string $ifc Pilot IFC
     * @param int $id Pilot ID
     */
    public static function setupPirepsIfc($ifc, $id)
    {
        $db = DB::getInstance();

        $ifc = urlencode($ifc);
        $req = new HttpRequest("https://api.vanet.app/airline/v1/user/id/{$ifc}");
        $req->setRequestHeaders(["X-Api-Key" => Config::get('vanet/api_key')])->execute();
        if ($req->getHttpCode() != 200) return false;

        $response = Json::decode($req->getResponse());
        if ($response['status'] != 0) return false;

        if (!$db->update('pilots', $id, 'id', [
            'ifuserid' => $response['result']
        ])) {
            return false;
        }

        Events::trigger('pirep/setup', ["pilot" => $id, "userid" => $response["data"], "method" => 1]);

        return true;
    }

    /**
     * @return array
     * @param string $icao ICAO Code
     */
    public static function getAirport($icao)
    {
        $icao = urlencode($icao);
        $req = new HttpRequest("https://api.vanet.app/public/v1/airport/{$icao}");
        $req->setRequestHeaders(["X-Api-Key" => Config::get('vanet/api_key')])->execute();
        if ($req->getHttpCode() != 200) {
            return false;
        }

        return Json::decode($req->getResponse())['result'];
    }

    /**
     * @return bool
     * @param array $fields PIREP Fields
     */
    public static function sendPirep($fields)
    {

        $req = new HttpRequest("https://api.vanet.app/airline/v1/flights");
        $req->setRequestHeaders(["X-Api-Key" => Config::get('vanet/api_key'), "Content-Type" => "application/json"])
            ->setMethod("POST")
            ->setRequestBody(Json::encode($fields))
            ->execute();

        if ($req->getHttpCode() != 200 || Json::decode($req->getResponse())['status'] != 0) return false;

        return true;
    }

    /**
     * @return array|null
     * @param bool $future Return only Future Events
     */
    public static function getEvents($future = true)
    {
        $key = Config::get('vanet/api_key');
        if (!self::isGold($key)) {
            return null;
        }

        $url = "https://api.vanet.app/airline/v1/events";
        if ($future) {
            $url .= "/future";
        }

        $req = new HttpRequest($url);
        $req->setRequestHeaders(["X-Api-Key" => $key])->execute();
        if ($req->getHttpCode() != 200) return null;

        return Json::decode($req->getResponse())['result'];
    }

    /**
     * @return array|null
     * @param string $id Event ID
     */
    public static function findEvent($id)
    {
        $key = Config::get('vanet/api_key');
        if (!self::isGold($key)) {
            return null;
        }

        $id = urlencode($id);
        $req = new HttpRequest("https://api.vanet.app/airline/v1/events/{$id}");
        $req->setRequestHeaders(["X-Api-Key" => $key])->execute();
        if ($req->getHttpCode() != 200) {
            return null;
        }

        return Json::decode($req->getResponse())['result'];
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

        $req = new HttpRequest("https://api.vanet.app/airline/v1/events");
        $req->setRequestHeaders(["X-Api-Key" => $key, "Content-Type" => "application/json"])
            ->setMethod("POST")
            ->setRequestBody(Json::encode($fields))
            ->execute();
        $response = Json::decode($req->getResponse());
        if ($req->getHttpCode() != 200 || $response['status'] != 0) {
            return false;
        }

        Events::trigger('vanet/event/added', $fields);

        return true;
    }

    /**
     * @param string $pilotUid Pilot User ID
     * @param string $gateId Gate ID
     * @return bool
     */
    public static function eventSignUp($pilotUid, $gateId)
    {
        $key = Config::get('vanet/api_key');
        if (!self::isGold($key)) {
            return false;
        }

        $gateId = urlencode($gateId);
        $req = new HttpRequest("https://api.vanet.app/airline/v1/events/slot/{$gateId}");
        $req->setRequestHeaders(["X-Api-Key" => $key, "Content-Type" => "application/json"])
            ->setMethod("POST")
            ->setRequestBody(Json::encode(["pilotId" => $pilotUid]))
            ->execute();
        $response = Json::decode($req->getResponse());

        if ($req->getHttpCode() != 200 || $response['status'] != 0) return false;

        Events::trigger('vanet/event/signup/added', ['pilotUid' => $pilotUid, 'gateId' => $gateId]);

        return true;
    }

    /**
     * @return bool
     * @param string $slotId Slot ID
     */
    public static function eventPullOut($slotId)
    {
        $key = Config::get('vanet/api_key');
        if (!self::isGold($key)) {
            return false;
        }

        $slotId = urlencode($slotId);
        $req = new HttpRequest("https://api.vanet.app/airline/v1/events/slot/{$slotId}");
        $req->setRequestHeaders(["X-Api-Key" => $key])->setMethod("DELETE")->execute();
        $response = Json::decode($req->getResponse());

        if ($req->getHttpCode() != 200 || $response['status'] != 0) return false;

        Events::trigger('vanet/event/signup/removed', ['slot' => $slotId]);

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

        $eventId = urlencode($eventId);
        $req = new HttpRequest("https://api.vanet.app/airline/v1/events/{$eventId}");
        $req->setRequestHeaders(["X-Api-Key" => $key])->execute();
        $response = Json::decode($req->getResponse());

        foreach ($response["result"]["slots"] as $s) {
            if ($s["pilotId"] == $pilotUid) {
                return true;
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

        $eventId = urlencode($eventId);
        $req = new HttpRequest("https://api.vanet.app/airline/v1/events/{$eventId}");
        $req->setRequestHeaders(["X-Api-Key" => $key])->setMethod("DELETE")->execute();

        if ($req->getHttpCode() != 200 || Json::decode($req->getResponse())['status'] != 0) return false;

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

        $id = urlencode($id);
        $req = new HttpRequest("https://api.vanet.app/airline/v1/events/{$id}");
        $req->setRequestHeaders(["X-Api-Key" => $key, "Content-Type" => "application/json"])
            ->setMethod("PUT")
            ->setRequestBody(Json::encode($fields))
            ->execute();

        if ($req->getHttpCode() != 200 || Json::decode($req->getResponse())['status'] != 0) {
            return false;
        }

        $fields["id"] = $id;
        Events::trigger('vanet/event/updated', $fields);

        return true;
    }

    /**
     * @return array|null
     * @param string $server Server
     * @param string $callsign Pilot Callsign
     * $param string $uid Pilot User ID
     */
    public static function runAcars($server, $callsign = null, $uid = null)
    {
        $key = Config::get('vanet/api_key');
        if (!self::isGold($key)) return null;

        $user = new User();

        if ($callsign == null) $callsign = $user->data()->callsign;
        if ($uid == null) $uid = $user->data()->ifuserid;
        if ($user->data()->ifuserid == null) return null;

        $req = new HttpRequest("https://api.vanet.app/airline/v1/acars");
        $req->setRequestHeaders(["X-Api-Key" => $key, "Content-Type" => "application/json"])
            ->setMethod("POST")
            ->setRequestBody(Json::encode([
                "callsign" => $callsign,
                "userId" => $uid,
                "server" => $server,
            ]))
            ->execute();

        if ($req->getHttpCode() != 200) return null;

        return Json::decode($req->getResponse());
    }

    /**
     * @return array|null
     */
    public static function getCodeshares()
    {
        $req = new HttpRequest("https://api.vanet.app/airline/v1/codeshares");
        $req->setRequestHeaders(["X-Api-Key" => Config::get('vanet/api_key')])->execute();
        $response = Json::decode($req->getResponse());
        if ($req->getHttpCode() != 200 || $response['status'] != 0) {
            return null;
        }

        return $response['result'];
    }

    /**
     * @return bool
     * @param array $fields Codeshare Fields
     */
    public static function sendCodeshare($fields)
    {
        $req = new HttpRequest("https://api.vanet.app/airline/v1/codeshares");
        $req->setRequestHeaders(["X-Api-Key" => Config::get('vanet/api_key'), "Content-Type" => "application/json"])
            ->setMethod("POST")
            ->setRequestBody(Json::encode($fields))
            ->execute();
        $response = Json::decode($req->getResponse());
        if ($req->getHttpCode() != 200 || $response['status'] != 0) {
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
        $id = urlencode($id);
        $req = new HttpRequest("https://api.vanet.app/airline/v1/codeshares/{$id}");
        $req->setRequestHeaders(["X-Api-Key" => Config::get('vanet/api_key')])
            ->setMethod("DELETE")
            ->execute();
        $response = Json::decode($req->getResponse());

        if ($req->getHttpCode() != 200 || $response['status'] != 0) {
            return false;
        }

        Events::trigger('vanet/codeshare/deleted', ['id' => $id]);

        return true;
    }

    /**
     * @return array|null
     * @param string $id Codeshare ID
     */
    public static function findCodeshare($id)
    {
        $id = urlencode($id);
        $req = new HttpRequest("https://api.vanet.app/airline/v1/codeshares/{$id}");
        $req->setRequestHeaders(["X-Api-Key" => Config::get('vanet/api_key')])->execute();
        $response = Json::decode($req->getResponse());

        if ($req->getHttpCode() != 200 || $response['status'] != 0) {
            return null;
        }

        return $response['result'];
    }
}
