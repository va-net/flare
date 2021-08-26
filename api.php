<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

$IS_API = true;
require_once './core/init.php';
header('Content-Type: application/json');

abstract class AuthType
{
    const NoAuth = 0;
    const Session = 1; // All scopes
    const Cookie = 2; // Reserved for Future Use, All Scopes
    const ApiKey = 3; // Read Only
    const BasicHttp = 4; // All Scopes
}

abstract class ErrorCode
{
    const NoError = 0;
    const Unauthorized = 1;
    const NotFound = 2;
    const AccessDenied = 3;
    const InternalServerError = 4;
    const CallsignNotValid = 5;
    const CallsignTaken = 6;
    const MultiplierNotFound = 9;
    const RankNotSufficent = 10;
    const VaNotGold = 11;
    const MethodNotAllowed = 12;
    const NoIfUid = 13;
    const NoGateAvailable = 14;
}

function unauthorized()
{
    http_response_code(401);
    echo Json::encode([
        "status" => ErrorCode::Unauthorized,
        "result" => null
    ]);
    die();
}

function internalError()
{
    http_response_code(500);
    echo Json::encode([
        "status" => ErrorCode::InternalServerError,
        "result" => null
    ]);
    die();
}

function badReq($status)
{
    http_response_code(400);
    echo Json::encode([
        "status" => $status,
        "result" => null
    ]);
    die();
}

function notFound()
{
    http_response_code(404);
    echo Json::encode([
        "status" => ErrorCode::NotFound,
        "result" => null
    ]);
    die();
}

function accessDenied()
{
    http_response_code(403);
    echo Json::encode([
        "status" => ErrorCode::AccessDenied,
        "result" => null
    ]);
    die();
}

$user = new User();

$_authType = AuthType::NoAuth;
$_apiUser = new stdClass;
$_headers = getallheaders();

if ($user->isLoggedIn()) {
    // Session Auth
    $_authType = AuthType::Session;
    $_apiUser = $user->data();
} elseif (array_key_exists('Authorization', $_headers) && explode(' ', $_headers['Authorization'])[0] == 'Bearer') {
    // Bearer Auth (Header)
    $check = Api::processKey(explode(' ', $_headers['Authorization'])[1]);
    if ($check === FALSE) {
        unauthorized();
    }

    $_authType = AuthType::ApiKey;
    $_apiUser = $check;
    $user->find($_apiUser->id);
} elseif (array_key_exists('apikey', $_GET)) {
    // Bearer Auth (Query String)
    $check = Api::processKey($_GET['apikey']);
    if ($check === FALSE) {
        unauthorized();
    }


    $_authType = AuthType::ApiKey;
    $_apiUser = $check;
    $user->find($_apiUser->id);
} elseif (array_key_exists('Authorization', $_headers) && explode(' ', $_headers['Authorization'])[0] == 'Basic') {
    // Basic HTTP Auth
    $check = Api::processBasic(explode(' ', $_headers['Authorization'])[1]);
    if ($check === FALSE) {
        unauthorized();
    }

    $_authType = AuthType::BasicHttp;
    $_apiUser = $check;
    $pass = explode(':', base64_decode(explode(' ', $_headers['Authorization'])[1]))[1];
    if (!$user->login($_apiUser->email, $pass)) {
        unauthorized();
    }
} else {
    unauthorized();
}

$guid = '([0-9a-zA-z]{8}-[0-9a-zA-z]{4}-[0-9a-zA-z]{4}-[0-9a-zA-z]{4}-[0-9a-zA-z]{12})';

// Not Found
Router::pathNotFound('notFound');

// Method Not Allowed
Router::methodNotAllowed(function () {
    http_response_code(405);
    echo Json::encode([
        "status" => ErrorCode::MethodNotAllowed,
        "result" => null
    ]);
    die();
});

// View All PIREPs for User
Router::add('/pireps', function () {
    global $user, $_apiUser;
    $res = [
        "status" => ErrorCode::NoError,
        "result" => $user->fetchPireps($_apiUser->id)->results(),
    ];

    $i = 0;
    foreach ($res["result"] as $p) {
        $p = (array)$p;
        foreach ($p as $key => $val) {
            if (is_numeric($val) && $key != 'flightnum') {
                $res["result"][$i]->$key = intval($val);
            }
        }
        unset($res["result"][$i]->pilotid);
        $i++;
    }

    echo Json::encode($res);
});

// View Specific PIREP
Router::add('/pireps/([0-9]+)', function ($pirepId) {
    global $user;

    $pirep = Pirep::find($pirepId, $user->hasPermission('pirepmanage') ? null : $user->data()->id);
    if ($pirep === FALSE) {
        notFound();
    }

    $pirep = (array)$pirep;

    unset($pirep['pilotid']);
    $pirep['id'] = intval($pirep['id']);
    $pirep['flighttime'] = intval($pirep['flighttime']);
    $pirep['aircraftid'] = intval($pirep['aircraftid']);
    $pirep['status'] = intval($pirep['status']);

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $pirep,
    ]);
});

// Edit PIREP
Router::add('/pireps/([0-9]+)', function ($pirepId) {
    global $_authType;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }

    $pirep = [];

    $pirep = (array)$pirep;
    if (!empty(Input::get('flightnum'))) $pirep["flightnum"] = Input::get('flightnum');
    if (!empty(Input::get('departure'))) $pirep["departure"] = Input::get('departure');
    if (!empty(Input::get('arrival'))) $pirep["arrival"] = Input::get('arrival');
    if (!empty(Input::get('date'))) $pirep["date"] = Input::get('date');

    $res = Pirep::update($pirepId, $pirep) ? ErrorCode::NoError : ErrorCode::InternalServerError;
    echo Json::encode([
        "status" => $res,
        "result" => null,
    ]);
}, 'put');

// View PIREP Comments
Router::add('/pireps/([0-9]+)/comments', function ($pirepId) {
    global $user;

    $pirep = Pirep::find($pirepId, $user->hasPermission('pirepmanage') ? null : $user->data()->id);
    if ($pirep === FALSE) {
        notFound();
    }

    $comments = Pirep::getComments($pirepId);
    if ($comments === NULL) internalError();

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $comments,
    ]);
});

// Add PIREP Comment
Router::add('/pireps/([0-9]+)/comments', function ($pirepId) {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }

    $pirep = Pirep::find($pirepId, $user->hasPermission('pirepmanage') ? null : $user->data()->id);
    if ($pirep === FALSE) {
        notFound();
    }

    $res = Pirep::addComment([
        'content' => Input::get('content'),
        'pirepid' => $pirepId,
        'userid' => $user->data()->id,
    ]);
    if (!$res) internalError();

    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => null,
    ]);
}, 'post');

// Get PIREP Comment
Router::add('/pireps/([0-9]+)/comments/([0-9]+)', function ($pirepId, $commentId) {
    global $user;

    $pirep = Pirep::find($pirepId, $user->hasPermission('pirepmanage') ? null : $user->data()->id);
    if ($pirep === FALSE) {
        notFound();
    }

    try {
        $comment = Pirep::findComment($commentId);
    } catch (Exception $e) {
        internalError();
    }

    if (!$comment) notFound();

    return Json::encode([
        'status' => ErrorCode::NoError,
        'result' => $comment,
    ]);
});

// Delete PIREP Comment
Router::add('/pireps/([0-9]+)/comments/([0-9]+)', function ($pirepId, $commentId) {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey || !$user->hasPermission('pirepmanage')) {
        accessDenied();
    }

    $res = Pirep::deleteComment($commentId);
    if (!$res) internalError();

    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => null,
    ]);
}, 'delete');

// File PIREP
Router::add('/pireps', function () {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }

    $multi = "None";
    $finalFTime = Time::strToSecs(Input::get('flighttime'));

    if (!empty(Input::get('multi'))) {
        $multiplier = Pirep::findMultiplier(Input::get('multi'));
        if (!$multiplier) {
            badReq(ErrorCode::MultiplierNotFound);
        }

        $multi = $multiplier->name;
        $finalFTime *= $multiplier->multiplier;
    }

    $allowedaircraft = $user->getAvailableAircraft();
    $allowed = false;
    foreach ($allowedaircraft as $a) {
        if ($a["id"] == Input::get('aircraft')) {
            $allowed = true;
        }
    }

    if (!$allowed) {
        badReq(ErrorCode::RankNotSufficent);
    }

    if (!Pirep::file(array(
        'flightnum' => Input::get('flightnum'),
        'departure' => Input::get('departure'),
        'arrival' => Input::get('arrival'),
        'flighttime' => $finalFTime,
        'pilotid' => $user->data()->id,
        'date' => Input::get('date'),
        'aircraftid' => Input::get('aircraft'),
        'multi' => $multi,
        'fuelused' => Input::get('fuel'),
    ))) {
        internalError();
    } else {
        echo Json::encode([
            "status" => ErrorCode::NoError,
            "result" => null,
        ]);
    }
}, 'post');

// Accept PIREP
Router::add('/pireps/accept/([0-9]+)', function ($pirepId) {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }

    if (!$user->hasPermission('pirepmanage') || !$user->hasPermission('admin')) {
        accessDenied();
    }

    Pirep::accept($pirepId);
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => null,
    ]);
});

// Deny PIREP
Router::add('/pireps/deny/([0-9]+)', function ($pirepId) {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }

    if (!$user->hasPermission('pirepmanage') || !$user->hasPermission('admin')) {
        accessDenied();
    }

    Pirep::decline($pirepId);
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => null,
    ]);
});

// View User Info
Router::add('/about', function () {
    global $_apiUser, $_authType;
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => [
            "id" => intval($_apiUser->id),
            "callsign" => $_apiUser->callsign,
            "name" => $_apiUser->name,
            "email" => $_authType != AuthType::ApiKey ? $_apiUser->email : null,
            "ifc" => $_apiUser->ifc,
            "transfer_hours" => intval($_apiUser->transhours),
            "transfer_flights" => intval($_apiUser->transflights),
            "violation_landing" => $_apiUser->violand,
            "grade" => $_apiUser->grade,
            "joined" => date_format(date_create($_apiUser->joined), 'Y-m-d'),
        ],
    ]);
});

// Update User Info
Router::add('/about', function () {
    global $_authType, $user, $_apiUser;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }

    if (!empty(Input::get('callsign'))) {
        $csPattern = Config::get('VA_CALLSIGN_FORMAT');
        if (!Regex::match($csPattern, Input::get('callsign'))) {
            badReq(ErrorCode::CallsignNotValid);
        }

        if (Callsign::assigned(Input::get('callsign'), $_apiUser->id)) {
            badReq(ErrorCode::CallsignTaken);
        }
    }

    $updUser = [];
    if (!empty(Input::get('callsign'))) $updUser['callsign'] = Input::get('callsign');
    if (!empty(Input::get('name'))) $updUser['name'] = Input::get('name');
    if (!empty(Input::get('email'))) $updUser['email'] = Input::get('email');
    if (!empty(Input::get('ifc'))) $updUser['ifc'] = Input::get('ifc');

    try {
        $user->update($updUser);
        echo Json::encode([
            "status" => ErrorCode::NoError,
            "result" => null,
        ]);
    } catch (Exception $e) {
        internalError();
    }
}, 'put');

// View All Events
Router::add('/events', function () {
    $events = VANet::getEvents();
    if ($events === null) internalError();

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $events,
    ]);
});

// View Specific Event
Router::add('/events/' . $guid, function ($eventId) {
    $event = VANet::findEvent($eventId);
    if ($event === FALSE) notFound();

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $event,
    ]);
});

// Sign up to/pull out of Event
Router::add('/events/' . $guid, function ($eventId) {
    global $_authType, $_apiUser;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }
    if ($_apiUser->ifuserid == null) badReq(ErrorCode::NoIfUid);

    $event = VANet::findEvent($eventId);
    if ($event === FALSE) notFound();

    $pilots = array_values(array_filter(array_map(function ($s) {
        return $s['pilotId'];
    }, $event['slots']), function ($uid) {
        return $uid != null;
    }));
    $slots = array_values(array_map(function ($s) {
        return $s['id'];
    }, array_filter($event['slots'], function ($s) {
        return $s['pilotId'] != null;
    })));
    $signups = array_combine($pilots, $slots);

    $firstavail = array_values(array_filter($event['slots'], function ($s) {
        return $s['pilotId'] == null;
    }));
    if (count($firstavail) < 1) {
        $firstavail = false;
    } else {
        $firstavail = $firstavail[0];
    }

    if (array_key_exists($_apiUser->ifuserid, $signups)) {
        VANet::eventPullOut($signups[$_apiUser->ifuserid], $eventId, $_apiUser->ifuserid);
    } else {
        if ($firstavail === FALSE) {
            badReq(ErrorCode::NoGateAvailable);
        }
        VANet::eventSignUp($_apiUser->ifuserid, $firstavail['id']);
    }

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => null,
    ]);
}, 'put');

// Get All Codeshare Requests
Router::add('/codeshares', function () {
    global $user;
    if (!$user->hasPermission('opsmanage')) accessDenied();

    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => VANet::getCodeshares(),
    ]);
});

// Send Codeshare Request
Router::add('/codeshares', function () {
    $routes = [];
    $inputRoutes = explode(",", Input::get('routes'));

    $dbRoutes = Route::fetchAll();
    foreach ($inputRoutes as $input) {
        if (!array_key_exists($input, $dbRoutes)) {
            Session::flash('error', 'Could not Find Route ' . $input);
            $this->redirect('/admin/operations/codeshares');
        }
        $r = $dbRoutes[$input];
        if (count($r['aircraft']) < 1) {
            Session::flash('error', 'This route does not have any aircraft attached - ' . $input);
            $this->redirect('/admin/operations/codeshares');
        }
        array_push($routes, array(
            "flightNumber" => $r['fltnum'],
            "departureIcao" => $r['dep'],
            "arrivalIcao" => $r['arr'],
            "aircraftLiveryId" => $r['aircraft'][0]['liveryid'],
            "flightTime" => $r['duration']
        ));
    }

    $ret = VANet::sendCodeshare(array(
        "recipientId" => Input::get('recipient'),
        "message" => Input::get('message'),
        "routes" => $routes
    ));
    if (!$ret) {
        Session::flash('error', "Error Connnecting to VANet");
        $this->redirect('/admin/operations/codeshares');
        die();
    } else {
        Session::flash('success', "Codeshare Sent Successfully!");
        $this->redirect('/admin/operations/codeshares');
    }
}, 'post');

// Get Codeshare Request
Router::add('/codeshares/' . $guid, function ($id) {
    global $user;
    if (!$user->hasPermission('opsmanage')) accessDenied();

    $codeshare = VANet::findCodeshare($id);
    if (!$codeshare) notFound();

    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => $codeshare,
    ]);
});

// Import Codeshare
Router::add('/codeshares/' . $guid, function ($id) {
    global $user;
    if (!$user->hasPermission('opsmanage')) accessDenied();

    $codeshare = VANet::findCodeshare($id);
    if ($codeshare === FALSE) notFound();

    $dbac = Aircraft::fetchAllAircraft();
    $dbaircraft = [];
    foreach ($dbac as $d) {
        $dbaircraft[$d->ifliveryid] = $d;
    }

    $lowrank = Rank::getFirstRank();
    foreach ($codeshare["routes"] as $route) {
        $ac = -1;
        if (!array_key_exists($route['aircraftLiveryId'], $dbaircraft)) {
            Aircraft::add($route['aircraftLiveryId'], $lowrank->id);
            $ac = Aircraft::lastId();
        } else {
            $ac = $dbaircraft[$route['aircraftLiveryId']]->id;
        }
        Route::add([
            'fltnum' => $route['flightNumber'],
            'dep' => $route['departureIcao'],
            'arr' => $route['arrivalIcao'],
            'duration' => $route['flightTime'],
        ]);
        Route::addAircraft(Route::lastId(), $ac);
    }
    VANet::deleteCodeshare($codeshare["id"]);
    Cache::delete('badge_codeshares');

    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => null,
    ]);
}, 'put');

// Delete Codeshare
Router::add('/codeshares/' . $guid, function ($id) {
    global $user;
    if (!$user->hasPermission('opsmanage')) accessDenied();

    $codeshare = VANet::findCodeshare($id);
    if (!$codeshare) notFound();

    $ret = VANet::deleteCodeshare($id);
    if (!$ret) internalError();

    Cache::delete('badge_codeshares');
    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => null,
    ]);
}, 'delete');

// View All News
Router::add('/news', function () {
    $news = News::get();

    $i = 0;
    foreach ($news as $n) {
        foreach ($n as $key => $val) {
            if (is_numeric($val)) $news[$i][$key] = intval($val);
            if ($key == 'dateposted') $news[$i][$key] = date_format(date_create($val), 'Y-m-d');
        }
        $i++;
    }
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $news,
    ]);
});

// View Specific News Item
Router::add('/news/([0-9]+)', function ($newsId) {
    $article = News::find($newsId);
    if ($article === FALSE) notFound();

    $article->dateposted = date_format(date_create($article->dateposted), "Y-m-d");
    foreach ($article as $key => $val) {
        if (is_numeric($val)) $article->$key = intval($val);
        if ($key == 'dateposted') $article->$key = date_format(date_create($val), "Y-m-d");
    }

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $article,
    ]);
});

// Add News Item
Router::add('/news', function () {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }
    if (!$user->hasPermission('newsmanage') || !$user->hasPermission('admin')) {
        accessDenied();
    }

    News::add([
        "subject" => Input::get('subject'),
        "content" => Input::get('content'),
        "author" => $user->data()->name,
    ]);
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => null,
    ]);
}, 'post');

// Edit News Item
Router::add('/news/([0-9]+)', function ($newsId) {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }
    if (!$user->hasPermission('newsmanage') || !$user->hasPermission('admin')) {
        accessDenied();
    }

    $updNews = [];
    if (!empty(Input::get('subject'))) $updNews["subject"] = Input::get('subject');
    if (!empty(Input::get('content'))) $updNews["content"] = Input::get('content');

    News::edit($newsId, $updNews);
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => null,
    ]);
}, 'put');

// Delete News Item
Router::add('/news/([0-9]+)', function ($newsId) {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }
    if (!$user->hasPermission('newsmanage') || !$user->hasPermission('admin')) {
        accessDenied();
    }

    News::archive($newsId);
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => null,
    ]);
}, 'delete');

// View All Routes
Router::add('/routes', function () {
    $data = Route::fetchAll();
    $routes = [];
    foreach ($data as $id => $r) {
        $r['aircraft'] = array_map(function ($a) {
            unset($a['liveryid']);
            $a['id'] = intval($a['id']);
            return $a;
        }, $r['aircraft']);

        $r = array_reverse($r);
        $r['id'] = $id;
        foreach ($r as $key => $val) {
            if (is_numeric($val) && $key != 'fltnum') $r[$key] = intval($val);
        }

        $routes[] = array_reverse($r);
    }
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $routes,
    ]);
});

// View Specific Route
Router::add('/routes/([0-9]+)', function ($routeId) {
    $route = Route::find($routeId);
    if ($route === FALSE) notFound();

    $route->aircraft = array_map(function ($a) {
        return [
            "id" => $a->id,
            "name" => $a->name,
            "livery" => $a->liveryname,
        ];
    }, Route::aircraft($routeId));
    $route->duration = intval($route->duration);
    $route->id = intval($route->id);
    unset($route->aircraftid);

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $route,
    ]);
});

// Add Route
Router::add('/routes', function () {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }
    if (!$user->hasPermission('opsmanage') || !$user->hasPermission('admin')) {
        accessDenied();
    }

    Route::add([
        "fltnum" => Input::get('fltnum'),
        "dep" => Input::get('dep'),
        "arr" => Input::get('arr'),
        "duration" => Input::get('duration'),
    ]);
    $routeId = Route::lastId();
    foreach (Input::get('aircraft') as $a) {
        Route::addAircraft($routeId, $a);
    }
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => null,
    ]);
}, 'post');

// Edit Route
Router::add('/routes/([0-9]+)', function ($routeId) {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }
    if (!$user->hasPermission('opsmanage') || !$user->hasPermission('admin')) {
        accessDenied();
    }

    $updRoute = [];
    if (!empty(Input::get('fltnum'))) $updRoute['fltnum'] = Input::get('fltnum');
    if (!empty(Input::get('arr'))) $updRoute['arr'] = Input::get('arr');
    if (!empty(Input::get('dep'))) $updRoute['dep'] = Input::get('dep');
    if (!empty(Input::get('duration'))) $updRoute['duration'] = Input::get('duration');

    Route::update($routeId, $updRoute);
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => null,
    ]);
}, 'put');

// Delete Route
Router::add('/routes/([0-9]+)', function ($routeId) {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }
    if (!$user->hasPermission('opsmanage') || !$user->hasPermission('admin')) {
        accessDenied();
    }

    Route::delete($routeId);
    Route::removeAircraft($routeId);
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => null,
    ]);
}, 'delete');

// Get All Aircraft
Router::add('/aircraft', function () {
    $allaircraft = array_map(function ($a) {
        unset($a->status);
        foreach ($a as $key => $val) {
            if (is_numeric($val)) {
                $a->$key = intval($val);
            }
        }

        return $a;
    }, Aircraft::fetchActiveAircraft()->results());

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $allaircraft,
    ]);
});

// Get Aircraft
Router::add('/aircraft/([0-9]+)', function ($aircraftId) {
    $a = Aircraft::fetch($aircraftId);
    if ($a === FALSE) {
        notFound();
    }

    unset($a->status);
    foreach ($a as $key => $val) {
        if (is_numeric($val)) {
            $a->$key = intval($val);
        }
    }
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $a,
    ]);
});

// Get Notifications
Router::add('/notifications', function () {
    global $_apiUser;
    $notifications = array_map(function ($n) {
        $n->datetime = $n->formattedDate;
        unset($n->formattedDate);
        unset($n->pilotid);
        foreach ($n as $key => $val) {
            if (is_numeric($val)) {
                $n->$key = intval($val);
            }
        }

        return $n;
    }, Notifications::mine($_apiUser->id));
    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $notifications,
    ]);
});

// Get All Ranks
Router::add('/ranks', function () {
    $ranks = array_map(function ($r) {
        foreach ($r as $key => $val) {
            if (is_numeric($val)) {
                $r->$key = intval($val);
            }
        }

        return $r;
    }, Rank::fetchAllNames()->results());

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $ranks,
    ]);
});

// Get Rank
Router::add('/ranks/([0-9]+)', function ($rankId) {
    $r = Rank::find($rankId);
    if ($r === FALSE) {
        notFound();
    }

    foreach ($r as $key => $val) {
        if (is_numeric($val)) {
            $r->$key = intval($val);
        }
    }

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $r,
    ]);
});

// Get Log
Router::add('/logs/(.+)', function ($logName) {
    global $_authType, $user;
    if ($_authType == AuthType::ApiKey) {
        accessDenied();
    }
    if (!$user->hasPermission('site')) {
        accessDenied();
    }

    header('Content-Type: text/plain');
    if (!file_exists("core/logs/{$logName}.log")) {
        http_response_code(404);
        echo 'Not Found';
        die();
    }
    echo file_get_contents("core/logs/{$logName}.log");
});

// Get Menu
Router::add('/menu', function () {
    global $user;
    $IS_GOLD = VANet::isGold();
    $menu = [];
    foreach ($GLOBALS['pilot-menu'] as $name => $data) {
        if ($IS_GOLD || $data["needsGold"] == false) {
            unset($data["needsGold"]);
            $data["category"] = 'pilot';
            $menu[$name] = $data;
        }
    }
    foreach ($GLOBALS['admin-menu'] as $cName => $cData) {
        foreach ($cData as $name => $data) {
            if ($user->hasPermission($data["permission"])) {
                if ($IS_GOLD || !$data["needsGold"]) {
                    unset($data["needsGold"]);
                    $data["category"] = $cName;
                    $menu[$name] = $data;
                }
            }
        }
    }

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $menu,
    ]);
});

// Get Menu Badges
Router::add('/menu/badges', function () {
    global $user;
    require_once __DIR__ . '/core/menus.php';
    $IS_GOLD = VANet::isGold();
    $ids = [];
    foreach ($GLOBALS['admin-menu'] as $cName => $cData) {
        foreach ($cData as $name => $data) {
            if ($user->hasPermission($data["permission"])) {
                if (($IS_GOLD || !$data["needsGold"]) && isset($data["badgeid"]) && $data["badgeid"] != null) {
                    $ids[] = $data["badgeid"];
                }
            }
        }
    }

    foreach ($GLOBALS['pilot-menu'] as $name => $data) {
        if (($IS_GOLD || $data["needsGold"] == false) && $data["badgeid"] != null) {
            $ids[] = $data["badgeid"];
        }
    }

    $badges = Page::$badges;
    $res = [];
    foreach ($badges as $id => $action) {
        $cache = Cache::get("badge_{$id}");
        if ($cache == '') {
            $ret = call_user_func($action);
            if (in_array($id, $ids)) $res[$id] = $ret;

            if (gettype($ret) == 'boolean') {
                $ret = $ret ? 'bool_1' : 'bool_0';
            }
            Cache::set("badge_{$id}", $ret, date("Y-m-d H:i:s", strtotime('+24 hours')));
        } elseif (in_array($id, $ids)) {
            if (strpos($cache, 'bool_') === 0) {
                $cache = $cache == 'bool_1';
            }
            $res[$id] = $cache;
        }
    }

    echo Json::encode([
        "status" => ErrorCode::NoError,
        "result" => $res,
    ]);
});

// Get Liveries for Aircraft
Router::add('/liveries', function () {
    global $user;
    if (!$user->hasPermission('opsmanage')) accessDenied();

    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => Aircraft::fetchLiveryIdsForAircraft(Input::get('aircraftid')),
    ]);
});

// Repair Site
Router::add('/repair', function () {
    global $user;
    if (!$user->hasPermission('admin')) accessDenied();

    // Remove Permissions Column
    $db = DB::getInstance();
    $table = $db->query("SELECT * FROM pilots")->results();
    $cols = array_keys($table);
    if (in_array('permissions', $cols)) {
        $db->query("ALTER TABLE `pilots` DROP `permissions`");
    }

    // Fix Prerelease Check
    if (empty(Config::get('CHECK_PRERELEASE'))) {
        $v = Updater::getVersion();
        Config::replace('CHECK_PRERELEASE', $v['prerelease'] ? '1' : '0');
    }

    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => null,
    ]);
});

// Get/Search Plugins
Router::add('/plugins', function () {
    global $user;
    if (!$user->hasPermission('site')) accessDenied();

    $search = empty(Input::get('search')) ? null : Input::get('search');
    $page = empty(Input::get('page')) ? 1 : Input::get('page');
    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => VANet::getPlugins($search, Input::get('prerelease') == 'true', $page),
    ]);
});

// Get Plugin Updates
Router::add('/plugins/updates', function () {
    global $user;
    if (!$user->hasPermission('site')) accessDenied();

    $res = VANet::pluginUpdates(Input::get('prerelease') == 'true');
    if ($res === null) internalError();

    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => $res,
    ]);
});

// Get Active ATC (Gold Only)
Router::add('/atc', function () {
    $res = VANet::getAtc(empty(Input::get('server')) ? 'expert' : Input::get('server'));
    if (!$res) internalError();

    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => $res
    ]);
});

Router::add('/config_migrate', function () {
    global $user;
    if (!$user->hasPermission('site')) accessDenied();

    if (file_exists(__DIR__ . '/core/config.new.php') || !file_exists(__DIR__ . '/core/config.php')) {
        badReq(ErrorCode::MethodNotAllowed);
    }

    $ignore = [
        'FLARE_DEFAULTS_FULLADMINPERMISSIONS',
        'FLARE_DEFAULTS_PERMISSIONS',
        'FLARE_VANET_BASE_URL',
        'FLARE_SESSION_TOKEN_NAME',
        'FLARE_SESSION_SESSION_NAME',
        'FLARE_REMEMBER_COOKIE_EXPIRY',
        'FLARE_REMEMBER_COOKIE_NAME'
    ];

    $res = "<?php\n\n";

    $config = $GLOBALS['config'];
    foreach ($config as $category => $items) {
        foreach ($items as $key => $value) {
            $constName = 'FLARE_' . implode('_', array_map('strtoupper', [$category, $key]));
            if (in_array($constName, $ignore)) continue;

            $res .= "define('{$constName}', '{$value}');\n";
        }
    }

    file_put_contents(__DIR__ . '/core/config.new.php', $res);
    rename(__DIR__ . '/core/config.php', __DIR__ . '/core/config.old.php');

    echo Json::encode([
        'status' => ErrorCode::NoError,
        'result' => null,
    ]);
});

Router::run('/api.php');
