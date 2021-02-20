<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();
if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}

if (Input::get('action') === 'editpirep') {
    $pirep = Pirep::find(Input::get('id'));
    if ($pirep === FALSE) {
        Session::flash('error', 'PIREP Not Found');
        Redirect::to('pireps.php?page=recents');
    }
    if ($pirep->pilotid != $user->data()->id) {
        Session::flash('error', 'There was an Error Editing the PIREP.');
        Redirect::to('pireps.php?page=recents');
    }

    $data = array(
        'flightnum' => Input::get('fnum'),
        'departure' => Input::get('dep'),
        'arrival' => Input::get('arr'),
        'date' => Input::get('date'),
    );
    if (!Pirep::update(Input::get('id'), $data)) {
        Session::flash('error', 'There was an Error Editing the PIREP.');
        Redirect::to('pireps.php?page=recents');
    } else {
        Session::flash('success', 'PIREP Edited successfully!');
        Redirect::to('pireps.php?page=recents');
    }
} elseif (Input::get('action') === 'edituser') {
    if (!$user->hasPermission('usermanage')) {
        Redirect::to('home.php');
        die();
    }

    $isAdmin = $user->hasPermission('admin', Input::get('id'));
    if (!$isAdmin && Input::get('admin') == 1) {
        Permissions::give(Input::get('id'), 'admin');
    } elseif ($isAdmin && Input::get('admin') == 0) {
        Permissions::revokeAll(Input::get('id'));
    }

    $statuses = [
        "Pending" => 0,
        "Active" => 1,
        "Inactive" => 2,
        "Declined" => 3,
    ];

    $user->update(array(
        'callsign' => Input::get('callsign'),
        'name' => Input::get('name'),
        'email' => Input::get('email'),
        'ifc' => Input::get('ifc'),
        'transhours' => Time::strToSecs(Input::get('transhours')),
        'transflights' => Input::get('transflights'),
        'status' => $statuses[Input::get('status')]
    ), Input::get('id'));
    Session::flash('success', 'User Edited Successfully!');
    Redirect::to('/admin/users.php');
} elseif (Input::get('action') === 'deluser') {
    if (!$user->hasPermission('usermanage')) {
        Redirect::to('home.php');
        die();
    }

    try {
        $user->update(array(
            'status' => 2
        ), Input::get('id'));
    } catch (Exception $e) {
        Session::flash('error', 'There was an Error Deleting the User.');
        Redirect::to('/admin/users.php');
    }
    Session::flash('success', 'User deleted successfully!');
    Redirect::to('/admin/users.php');
} elseif (Input::get('action') === 'editstaffmember') {
    if (!$user->hasPermission('staffmanage')) {
        Redirect::to('home.php');
        die();
    }

    $myperms = Permissions::forUser(Input::get('id'));
    $permissions = Permissions::getAll();
    foreach ($permissions as $permission => $name) {
        if (Input::get($permission) == 'on' && !in_array($permission, $myperms)) {
            Permissions::give(Input::get('id'), $permission);
        } elseif (Input::get($permission) != 'on' && in_array($permission, $myperms)) {
            Permissions::revoke(Input::get('id'), $permission);
        }
    }

    try {
        $user->update(array(
            'callsign' => Input::get('callsign'),
            'name' => Input::get('name'),
            'email' => Input::get('email'),
            'ifc' => Input::get('ifc')
        ), Input::get('id'));
    } catch (Exception $e) {
        Session::flash('error', 'There was an Error Editing the Staff Member.');
        Redirect::to('/admin/staff.php');
    }
    Session::flash('success', 'Staff Member Edited Successfully!');
    Redirect::to('/admin/staff.php');
} elseif (Input::get('action') === 'declineapplication') {
    if (!$user->hasPermission('recruitment')) {
        Redirect::to('home.php');
        die();
    }

    try {
        $user->update(array(
            'status' => 3
        ), Input::get('id'));
    } catch (Exception $e) {
        Session::flash('error', 'There was an error Declining the Application.');
        Redirect::to('/admin/recruitment.php');
    }

    Events::trigger('user/declined', ['id' => Input::get('id'), 'reason' => Input::get('declinereason')]);

    Cache::delete('badge_recruitment');
    Session::flash('success', 'Application Declined Successfully');
    Redirect::to('/admin/recruitment.php');
} elseif (Input::get('action') === 'acceptapplication') {
    if (!$user->hasPermission('recruitment')) {
        Redirect::to('home.php');
        die();
    }

    try {
        $user->update(array(
            'status' => 1
        ), Input::get('accept'));
    } catch (Exception $e) {
        Session::flash('error', 'There was an Error Accepting the Application.');
        Redirect::to('/admin/recruitment.php');
    }

    Events::trigger('user/accepted', [Input::get('accept')]);

    Cache::delete('badge_recruitment');
    Session::flash('success', 'Application Accepted Successfully!');
    Redirect::to('/admin/recruitment.php');
} elseif (Input::get('action') === 'acceptpirep') {
    if (!$user->hasPermission('pirepmanage')) {
        Redirect::to('home.php');
        die();
    }

    Pirep::accept(Input::get('accept'));
    Cache::delete('badge_pireps');
    Session::flash('success', 'PIREP Accepted Successfully!');
    Redirect::to('/admin/pireps.php');
} elseif (Input::get('action') === 'declinepirep') {
    if (!$user->hasPermission('pirepmanage')) {
        Redirect::to('home.php');
        die();
    }

    Pirep::decline(Input::get('decline'));
    Cache::delete('badge_pireps');
    Session::flash('success', 'PIREP Declined Successfully');
    Redirect::to('/admin/pireps.php');
} elseif (Input::get('action') === 'deletemulti') {
    if (!$user->hasPermission('pirepmanage')) {
        Redirect::to('home.php');
        die();
    }

    Pirep::deleteMultiplier(Input::get('delete'));
    Session::flash('success', 'Multiplier Deleted Successfully!');
    Redirect::to('/admin/multipliers.php');
} elseif (Input::get('action') === 'addmulti') {
    if (!$user->hasPermission('pirepmanage')) {
        Redirect::to('home.php');
        die();
    }

    Pirep::addMultiplier(array(
        "code" => Pirep::generateMultiCode(),
        "name" => Input::get("name"),
        "multiplier" => Input::get("multi")
    ));
    Session::flash('success', 'Multiplier Added Successfully!');
    Redirect::to('/admin/multipliers.php');
} elseif (Input::get('action') === 'vasettingsupdate') {
    if (!$user->hasPermission('site')) {
        Redirect::to('home.php');
        die();
    }

    if (
        !Config::replace('name', Input::get('vaname'))
        || !Config::replace('identifier', Input::get('vaabbrv'))
        || !Config::replace("FORCE_SERVER", Input::get('forceserv'))
        || !Config::replace("CHECK_PRERELEASE", Input::get('checkpre'))
        || !Config::replace("VA_CALLSIGN_FORMAT", Input::get('vaident'))
        || !Config::replace("VA_LOGO_URL", Input::get('valogo'))
        || !Config::replace("AUTO_CALLSIGNS", Input::get('autocallsign'))
    ) {
        Session::flash('error', 'There was an error updating your settings');
        Redirect::to('/admin/site.php?tab=settings');
        die();
    }
    Session::flash('success', 'VA Settings Changed Successfully!');
    Redirect::to('/admin/site.php?tab=settings');
} elseif (Input::get('action') === 'eventsignup') {
    $uData = $user->data();
    if (VANet::isSignedUp($uData->ifuserid, Input::get('event')) != false) {
        Redirect::to('events.php?page=view&event=' . urlencode(Input::get('event')));
        die();
    }

    $ret = VANet::eventSignUp($uData->ifuserid, Input::get('gate'));
    if ($ret === 400) {
        Session::flash("error", "Event is Corrupted. Please contact your VA.");
        Redirect::to('events.php?page=view&event=' . urlencode(Input::get('event')));
        die();
    } elseif ($ret === 404) {
        Session::flash('error', 'Slot Not Found. Are you messing with us? :/');
        Redirect::to('events.php?page=view&event=' . urlencode(Input::get('event')));
        die();
    } elseif ($ret === 409) {
        Session::flash("error", "Rats! Someone got to that gate before you. Please try again.");
        Redirect::to('events.php');
        die();
    } elseif ($ret === true) {
        Session::flash('success', 'Gate Reserved Successfully!');
        Redirect::to('events.php?page=view&event=' . urlencode(Input::get('event')));
        die();
    }
} elseif (Input::get('action') === 'vacateslot') {
    $uData = $user->data();

    $ret = VANet::eventPullOut(Input::get('gate'), Input::get('event'), $uData->ifuserid);

    if ($ret === 400) {
        Redirect::to('events.php?page=view&event=' . urlencode(Input::get('event')));
        die();
    } elseif ($ret === 404) {
        Session::flash('error', 'Slot Not Found. Are you messing with us? :/');
        Redirect::to('events.php?page=view&event=' . urlencode(Input::get('event')));
        die();
    } elseif ($ret === 409) {
        Session::flash("error", "Event is Corrupted. Please contact your VA.");
        Redirect::to('events.php');
        die();
    } elseif ($ret === true) {
        Session::flash('success', 'Slot Vacated Successfully!');
        Redirect::to('events.php?page=view&event=' . urlencode(Input::get('event')));
        die();
    }
} elseif (Input::get('action') === 'installplugin') {
    if (!$user->hasPermission('site')) {
        Redirect::to('home.php');
    }

    $slash = "/";
    if (strpos(strtolower(php_uname('s')), "window") !== FALSE) {
        $slash = "\\";
    }

    $GH_BRANCH = "master";

    $url = "https://raw.githubusercontent.com/va-net/flare-plugins/{$GH_BRANCH}/plugins.tsv";
    $opts = array(
        'http' => array(
            'method' => "GET",
            'header' => "User-Agent: va-net\r\n"
        )
    );
    $context = stream_context_create($opts);
    $plugins = file_get_contents($url, false, $context);
    $pluginbasic = null;
    preg_match_all('/\n.*/m', $plugins, $lines);
    foreach ($lines[0] as $l) {
        $l = trim($l);
        $l = explode("\t", $l);
        if ($pluginbasic == null && $l[1] == Input::get('plugin')) {
            $pluginbasic = array(
                "name" => $l[0],
                "slug" => $l[1],
                "author" => $l[2],
                "version" => $l[3],
                "update-date" => $l[4],
                "tags" => explode(",", $l[5])
            );
            break;
        }
    }

    $pluginbasic["slug"] = strtolower($pluginbasic["slug"]);

    $version = Updater::getVersion();
    $pluginadv = Json::decode(file_get_contents("https://raw.githubusercontent.com/va-net/flare-plugins/{$GH_BRANCH}/" . $pluginbasic["slug"] . "/plugin.json", false, $context));
    // Removed for now while we troubleshoot. Couldn't get a consistent repro.
    // if (!in_array($version["tag"], $pluginadv["compatability"]) && $version["prerelease"] == false) {
    //     Session::flash('error', 'This plugin does not support this version of Flare.');
    //     Redirect::to('/admin/plugins.php');
    // }

    foreach ($pluginadv["installation"]["files"] as $f) {
        $f = str_replace("/", $slash, $f);
        if (file_exists(__DIR__ . $slash . $f)) {
            if (unlink(__DIR__ . $slash . $f) !== TRUE) {
                Session::flash('error', 'File "' . $f . '" already exists, failed to delete it.');
                Redirect::to('/admin/plugins.php');
            }

            Logger::log('File "' . __DIR__ . $slash . $f . '" was deleted while installing plugin ' . $pluginbasic["name"]);
        }
    }
    foreach ($pluginadv["installation"]["files"] as $f) {
        $data = file_get_contents("https://raw.githubusercontent.com/va-net/flare-plugins/master/" . $pluginbasic["slug"] . "/" . $f, false, $context);
        $f = str_replace("/", $slash, $f);
        file_put_contents(__DIR__ . $slash . $f, $data);
    }

    $db = DB::getInstance();
    foreach ($pluginadv["installation"]["queries"] as $q) {
        $db->query($q);
    }

    $currentplugins = Json::decode(file_get_contents('./plugins.json'));
    array_push($currentplugins, $pluginadv);
    file_put_contents('./plugins.json', Json::encode($currentplugins, true));

    Session::flash('success', 'Plugin Installed!');
    Redirect::to('/admin/plugins.php?tab=installed');
} elseif (Input::get('action') === 'removeplugin') {
    if (!$user->hasPermission('site')) {
        Redirect::to('home.php');
    }

    $slash = "/";
    if (strpos(strtolower(php_uname('s')), "window") !== FALSE) {
        $slash = "\\";
    }

    $theplugin = null;
    foreach ($INSTALLED_PLUGINS as $p) {
        if ($theplugin == null && $p["name"] == Input::get('plugin')) {
            $theplugin = $p;
            $INSTALLED_PLUGINS = array_filter($INSTALLED_PLUGINS, function ($item) {
                global $theplugin;
                if ($item == $theplugin) {
                    return false;
                }

                return true;
            });
            file_put_contents('./plugins.json', Json::encode($INSTALLED_PLUGINS, true));
            break;
        }
    }

    foreach ($theplugin["installation"]["files"] as $file) {
        $file = str_replace("/", $slash, $file);
        $path = __DIR__ . $slash . $file;
        if (unlink($path) === FALSE) {
            Session::flash('error', 'Failed to remove file - ' . $path);
            Redirect::to('/admin/plugins.php?tab=installed');
        }
    }

    Session::flash('success', 'Plugin Removed');
    Redirect::to('/admin/plugins.php?tab=installed');
} elseif (Input::get('action') === 'announce') {
    if (!$user->hasPermission('usermanage')) {
        Redirect::to('home.php');
        die();
    }

    $title = escape(Input::get('title'));
    $content = escape(Input::get('content'));
    Notifications::notify(0, "fa-bullhorn", $title, $content);
    Session::flash('sucess', 'Announcement Made');
    Redirect::to('/admin/users.php');
} elseif (Input::get('action') === 'editpirepadmin') {
    if (!$user->hasPermission('pirepmanage')) {
        Redirect::to('home.php');
        die();
    }

    $data = array(
        'flightnum' => Input::get('fnum'),
        'departure' => Input::get('dep'),
        'arrival' => Input::get('arr'),
        'date' => Input::get('date'),
        'flighttime' => Time::strToSecs(Input::get('ftime')),
        'aircraftid' => Input::get('aircraft'),
        'status' => Input::get('status'),
    );
    if (!Pirep::update(Input::get('id'), $data)) {
        Session::flash('error', 'There was an Error Editing the PIREP');
        Redirect::to('/admin/pireps.php?tab=all');
    } else {
        Cache::delete('badge_pireps');
        Session::flash('success', 'PIREP Edited Successfully!');
        Redirect::to('/admin/pireps.php?tab=all');
    }
}
