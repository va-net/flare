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
     * @return string
     */
    public static function baseUrl()
    {
        if (!empty(Config::get('vanet/base_url')) && Config::get('vanet/base_url') != 'https://vanet.app') {
            return Config::get('vanet/base_url');
        }
        return "https://api.vanet.app";
    }

    /**
     * @var array|null
     */
    private static $_profile = null;

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
            if (self::$_profile !== null) return self::$_profile;

            $cache = Cache::get('myinfo');
            if ($cache != '') {
                self::$_profile = Json::decode($cache);
                return self::$_profile;
            }
        }

        $req = new HttpRequest(self::baseUrl() . "/airline/v1/profile");
        $req->setRequestHeaders([
            "X-Api-Key: {$key}",
        ])->execute();
        $response = Json::decode($req->getResponse());

        if ($response['status'] != 0) return null;

        if ($wasNull) {
            Cache::set('myinfo', Json::encode($response['result']), date("Y-m-d H:i:s", strtotime('+24 hours')));
            self::$_profile = $response['result'];
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
     * @return bool
     * @param string $id Feature ID
     */
    public static function featureEnabled($id)
    {
        $info = self::myInfo();
        if (!$info) return false;

        return !(isset($info['activeFeatures'][$id]) && $info['activeFeatures'][$id] === FALSE);
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

        $req = new HttpRequest(self::baseUrl() . "/airline/v1/stats");
        $req->setRequestHeaders(["X-Api-Key: {$key}"])->execute();
        $response = Json::decode($req->getResponse());
        if ($response['status'] != 0) return null;

        return $response['result'];
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
        $key = Config::get('vanet/api_key');
        $req = new HttpRequest(self::baseUrl() . "/airline/v1/user/id/{$ifc}");
        $req->setRequestHeaders(["X-Api-Key: {$key}"])->execute();
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
     * @return array|null
     * @param string $icao ICAO Code
     * @param bool $noCache Whether to bypass cache
     */
    public static function getAirport($icao, $noCache = false)
    {
        if (!$noCache) {
            $cache = Cache::get("airport_{$icao}");
            if (!empty($cache)) return Json::decode($cache);
        }

        $icao = urlencode($icao);
        $key = Config::get('vanet/api_key');
        $req = new HttpRequest(self::baseUrl() . "/public/v1/airport/{$icao}");
        $req->setRequestHeaders(["X-Api-Key: {$key}"])->execute();
        if ($req->getHttpCode() != 200) {
            return null;
        }

        $res = Json::decode($req->getResponse())['result'];
        if (!$noCache) {
            Cache::set("airport_{$icao}", Json::encode($res), date("Y-m-d H:i:s", strtotime('+7 days')));
        }

        return $res;
    }

    /**
     * @return void
     * @param Event $ev
     */
    public static function sendPirep($ev)
    {
        $key = Config::get('vanet/api_key');

        $data = Json::encode([
            'aircraftLiveryId' => Aircraft::idToLiveryId($ev->params['aircraftid']),
            'arrivalIcao' => $ev->params['arrival'],
            'date' => $ev->params['date'],
            'departureIcao' => $ev->params['departure'],
            'flightTime' => $ev->params['flighttime'],
            'fuelUsed' => $ev->params['fuelused'],
            'pilotId' => (new User)->getUser($ev->params['pilotid'])->ifuserid,
        ]);

        $response = Json::decode(@HttpRequest::hacky(self::baseUrl() . "/airline/v1/flights", "POST", $data, ["X-Api-Key: {$key}", "Content-Type: application/json"]));
        if (!$response || $response['status'] != 0) {
            var_dump($response);
            die('Error Connecting to VANet');
        }
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

        $url = self::baseUrl() . "/airline/v1/events";
        if ($future) {
            $url .= "/future";
        }

        $req = new HttpRequest($url);
        $req->setRequestHeaders(["X-Api-Key: {$key}"])->execute();
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
        $req = new HttpRequest(self::baseUrl() . "/airline/v1/events/{$id}");
        $req->setRequestHeaders(["X-Api-Key: {$key}"])->execute();
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

        $data = Json::encode($fields);
        $response = Json::decode(@HttpRequest::hacky(self::baseUrl() . "/airline/v1/events", "POST", $data, ["X-Api-Key: {$key}", "Content-Type: application/json"]));
        if (!$response || $response['status'] != 0) {
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
        $data = Json::encode(["pilotId" => $pilotUid]);
        $response = Json::decode(@HttpRequest::hacky(self::baseUrl() . "/airline/v1/events/slot/{$gateId}", "POST", $data, ["X-Api-Key: {$key}", "Content-Type: application/json"]));

        if (!$response || $response['status'] != 0) return false;

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
        $req = new HttpRequest(self::baseUrl() . "/airline/v1/events/slot/{$slotId}");
        $req->setRequestHeaders(["X-Api-Key: {$key}"])->setMethod("DELETE")->execute();
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
        $req = new HttpRequest(self::baseUrl() . "/airline/v1/events/{$eventId}");
        $req->setRequestHeaders(["X-Api-Key: {$key}"])->execute();
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
        $req = new HttpRequest(self::baseUrl() . "/airline/v1/events/{$eventId}");
        $req->setRequestHeaders(["X-Api-Key: {$key}"])->setMethod("DELETE")->execute();

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
        $data = Json::encode($fields);
        $response = Json::decode(@HttpRequest::hacky(self::baseUrl() . "/airline/v1/events/{$id}", "PUT", $data, ["X-Api-Key: {$key}", "Content-Type: application/json"]));

        if (!$response || $response['status'] != 0) {
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

        $data = Json::encode([
            "callsign" => $callsign,
            "userId" => $uid,
            "server" => $server,
        ]);
        $response = Json::decode(HttpRequest::hacky(self::baseUrl() . "/airline/v1/acars", "POST", $data, ["X-Api-Key: {$key}", "Content-Type: application/json"]));

        if (!$response) return null;

        return $response;
    }

    /**
     * @return array|null
     */
    public static function getCodeshares()
    {
        $key = Config::get('vanet/api_key');
        $req = new HttpRequest(self::baseUrl() . "/airline/v1/codeshares");
        $req->setRequestHeaders(["X-Api-Key: {$key}"])->execute();
        $response = Json::decode($req->getResponse());
        if ($req->getHttpCode() != 200 || $response['status'] != 0) {
            return null;
        }

        return $response['result'];
    }

    /**
     * @return int
     */
    public static function getCodeshareCount()
    {
        return strval(count(self::getCodeshares()));
    }

    /**
     * @return bool
     * @param array $fields Codeshare Fields
     */
    public static function sendCodeshare($fields)
    {
        $key = Config::get('vanet/api_key');
        $data = Json::encode($fields);
        $response = Json::decode(@HttpRequest::hacky(self::baseUrl() . "/airline/v1/codeshares", "POST", $data, ["X-Api-Key: {$key}", "Content-Type: application/json"]));
        if (!$response || $response['status'] != 0) {
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
        $key = Config::get('vanet/api_key');
        $req = new HttpRequest(self::baseUrl() . "/airline/v1/codeshares/{$id}");
        $req->setRequestHeaders(["X-Api-Key: {$key}"])
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
        $key = Config::get('vanet/api_key');
        $req = new HttpRequest(self::baseUrl() . "/airline/v1/codeshares/{$id}");
        $req->setRequestHeaders(["X-Api-Key: {$key}"])->execute();
        $response = Json::decode($req->getResponse());

        if ($req->getHttpCode() != 200 || $response['status'] != 0) {
            return null;
        }

        return $response['result'];
    }

    /**
     * @return array
     * @param string|null $search Search Term
     * @param bool $prerelease Whether to include prereleased plugins
     * @param int $page Page Number
     */
    public static function getPlugins($search = null, $prerelease = false, $page = 1)
    {
        if ($search != null) $search = urlencode($search);

        $search = empty($search) ? '' : "search={$search}&";
        $page = 'page=' . urlencode($page) . '&';
        $prerelease = 'prerelease=' . ($prerelease ? 'true' : 'false');

        $req = new HttpRequest(self::baseUrl() . "/flare/v1/plugins?{$search}{$page}{$prerelease}");
        $req->execute();
        $response = Json::decode($req->getResponse());

        if ($req->getHttpCode() != 200 || !$response || $response['status'] != 0) {
            return [];
        }

        if (isset($response['result']['data'])) {
            $response['result']['data'] = array_map(function ($x) {
                $x['installed'] = false;
                foreach ($GLOBALS['INSTALLED_PLUGINS'] as $i) {
                    if ($i['pluginInfo']['id'] == $x['id']) $x['installed'] = true;
                }

                return $x;
            }, $response['result']['data']);
        }

        return $response['result'];
    }

    /**
     * @return array|null
     * @param string $id Plugin ID
     */
    public static function getPlugin($id)
    {
        $req = new HttpRequest(self::baseUrl() . "/flare/v1/plugins/" . urlencode($id));
        $req->execute();
        $response = Json::decode($req->getResponse());

        if ($req->getHttpCode() != 200 || !$response || $response['status'] != 0) {
            return null;
        }

        return $response['result'];
    }

    /**
     * @return array|null
     * @param string $id Plugin ID
     * @param bool $prerelease Whether to install prerelease version
     */
    public static function pluginInstallDetails($id, $prerelease = false)
    {
        $req = new HttpRequest(self::baseUrl() . '/flare/v1/plugins/' . urlencode($id) . ($prerelease ? '?prerelease=true' : ''));

        $req->setMethod('POST')
            ->execute();

        $response = Json::decode($req->getResponse());
        if ($req->getHttpCode() != 200 || !$response || $response['status'] != 0) {
            return null;
        }

        return $response['result'];
    }

    /**
     * @return array|null
     * @param bool $prerelease Whether Check for Prerelease Updates
     */
    public static function manualPluginUpdates($prerelease = false)
    {
        $body = [];
        foreach ($GLOBALS['INSTALLED_PLUGINS'] as $p) {
            $match = Regex::match("/^v\d+.\d+.\d+-[a-z]+.\d+$/", $p['versionTag']);
            $body[] = [
                'pluginId' => $p['pluginInfo']['id'],
                'versionTag' => $p['versionTag'],
                'checkPrerelease' => $prerelease || $match,
            ];
        }

        $data = HttpRequest::hacky(
            self::baseUrl() . '/flare/v1/plugins/updates',
            'POST',
            Json::encode($body),
            ['Content-Type: application/json']
        );
        $res = Json::decode($data);

        if (!$res || $res['status'] != 0) {
            var_dump($res);
            return null;
        }

        return $res['result'];
    }

    /**
     * @return bool
     */
    public static function pluginUpdatesAvailable()
    {
        return count(self::manualPluginUpdates()) > 0;
    }

    /**
     * @return array|null
     * @param string $id Plugin ID
     * @param bool $prerelease Whether to install prerelease version
     */
    public static function pluginUpdateDetails($id, $prerelease = false)
    {
        $req = new HttpRequest(self::baseUrl() . '/flare/v1/plugins/updates/' . urlencode($id) . ($prerelease ? '?prerelease=true' : ''));

        $req->setMethod('POST')
            ->execute();

        $response = Json::decode($req->getResponse());
        if ($req->getHttpCode() != 200 || !$response || $response['status'] != 0) {
            return null;
        }

        return $response['result'];
    }

    /**
     * @return array|null
     * @param string $server `casual`, `training`, or `expert`
     */
    public static function getAtc($server = 'expert')
    {
        if (!self::isGold()) return null;

        $key = Config::get('vanet/api_key');

        $req = new HttpRequest(self::baseUrl() . '/airline/v1/atc/' . urlencode($server));
        $req->setRequestHeaders(["X-Api-Key: {$key}"])
            ->execute();

        $data = Json::decode($req->getResponse());
        if (!$data || $data['status'] != 0) return null;

        return $data['result'];
    }

    /**
     * @return string|null
     * @param string $icao Airport ICAO
     * @param bool $noCache Whether to bypass cache
     */
    public static function getAtis($icao, $noCache = false)
    {
        if (!$noCache) {
            $cache = Cache::get('atis_' . $icao);
            if (!empty($cache)) return $cache;
        }

        $key = Config::get('vanet/api_key');

        $req = new HttpRequest(self::baseUrl() . '/public/v1/airport/' . urlencode($icao) . '/atis');
        $req->setRequestHeaders(["X-Api-Key: {$key}"])
            ->execute();

        $data = Json::decode($req->getResponse());
        if (!$data || $data['status'] != 0) return null;

        $res = $data['result'];
        if (!$noCache) {
            Cache::set('atis_' . $icao, $res, date("Y-m-d H:i:s", strtotime('+15 minutes')));
        }

        return $res;
    }

    /**
     * @return array|null
     * @param string $search User ID or IFC
     * @param bool $isIfc Whether the search value is an IFC Username
     */
    public static function lookupUser($search, $isIfc = false)
    {
        if (!self::isGold()) return null;

        $key = Config::get('vanet/api_key');

        if ($isIfc) {
            $req = new HttpRequest(self::baseUrl() . '/airline/v1/user?ifc=' . urlencode($search));
            $req->setRequestHeaders([
                "X-Api-Key: {$key}"
            ])->execute();
            $res = Json::decode($req->getResponse());
            if (!$res || $res['status'] != 0) return null;

            return $res['result'];
        }

        $req = new HttpRequest(self::baseUrl() . '/airline/v1/user/' . urlencode($search));
        $req->setRequestHeaders([
            "X-Api-Key: {$key}"
        ])->execute();
        $res = Json::decode($req->getResponse());
        if (!$res || $res['status'] != 0) return null;

        return $res['result'];
    }

    /**
     * @return array|null
     * @param string $code Authorization Code
     * @param string $redirect_uri Redirect URL
     */
    public static function tokenExchange($code, $redirect_uri)
    {
        $client_id = Config::get('oauth/client_id');
        $client_secret = Config::get('oauth/client_secret');
        $res = HttpRequest::hacky(
            self::baseUrl() . '/public/v1/oauth/token',
            'POST',
            'grant_type=authorization_code&code=' . urlencode($code) . '&redirect_uri=' . urlencode($redirect_uri),
            [
                'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
                'Content-Type: application/x-www-form-urlencoded'
            ]
        );
        return Json::decode($res);
    }

    /**
     * @return array|null
     * @param string $accessToken
     */
    public static function getUserProfile($accessToken)
    {
        $res = HttpRequest::hacky(self::baseUrl() . '/public/v1/oauth/userinfo', 'GET', '', [
            'Authorization: Bearer ' . $accessToken,
        ]);
        $data = Json::decode($res);
        if (!$data || isset($data['error'])) return null;

        return $data;
    }

    /**
     * @return array|null
     */
    public static function registerApp()
    {
        $key = Config::get('vanet/api_key');

        $req = new HttpRequest(self::baseUrl() . '/airline/v1/app');
        $req->setRequestHeaders(["X-Api-Key: {$key}"])
            ->setMethod('POST')
            ->execute();

        $data = Json::decode($req->getResponse());
        if (!$data || $data['status'] != 0) return null;

        return $data['result'];
    }

    /**
     * @return bool
     * @param array $redirects
     */
    public static function updateAppRedirects($redirects)
    {
        $key = Config::get('vanet/api_key');

        $res = HttpRequest::hacky(
            self::baseUrl() . '/airline/v1/app/redirects',
            'PUT',
            Json::encode($redirects),
            [
                "X-Api-Key: {$key}",
                'Content-Type: application/json'
            ]
        );
        $data = Json::decode($res);

        if (!$data || $data['status'] != 0) return false;

        return true;
    }

    /**
     * @return bool
     * @param int $uid
     * @param string $ifId
     * @param int[] $permissions
     */
    public static function registerMembership($uid, $ifId, $permissions = [])
    {
        $key = Config::get('vanet/api_key');

        $req = HttpRequest::hacky(self::baseUrl() . '/airline/v1/members', 'POST', Json::encode([
            'infiniteFlightId' => $ifId,
            'permissions' => $permissions
        ]), [
            "X-Api-Key: {$key}",
            'Content-Type: application/json'
        ]);
        $data = Json::decode($req);

        if (!$data || $data['status'] != 0) return false;

        (new User)->update([
            'vanet_id' => $data['result']['userId'],
        ], $uid);

        return true;
    }

    /**
     * @return void
     * @param User $user
     */
    public static function refreshMembership($user)
    {
        if (!$user->data()->vanet_id || !$user->data()->ifuserid) return;

        $key = Config::get('vanet/api_key');
        $req = HttpRequest::hacky(self::baseUrl() . '/airline/v1/members', 'GET', '', [
            "X-Api-Key: {$key}"
        ]);
        $data = Json::decode($req);

        if (!$data || $data['status'] != 0) return;

        $membership = null;
        foreach ($data['result'] as $m) {
            if ($m['userId'] == $user->data()->vanet_id) {
                $membership = $m;
                break;
            }
        }

        if ($membership == null) {
            VANet::registerMembership($user->data()->id, $user->data()->ifuserid, $user->hasPermission('pirepmanage') ? [0] : []);
            return;
        }

        $user->update([
            'vanet_memberid' => $membership['id'],
        ], $user->data()->id);
    }
}
