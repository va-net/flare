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
}
