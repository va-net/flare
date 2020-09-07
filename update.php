<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

if (Input::get('action') === 'editprofile') {
    if (!Callsign::assigned(Input::get('callsign'), $user->data()->id)) {
        Session::flash('error', 'Callsign is Already Taken!');
    } else {
        try {
            $user->update(array(
                'name' => Input::get('name'),
                'callsign' => Input::get('callsign'),
                'email' => Input::get('email'),
                'ifc' => Input::get('ifc')
            ));
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
            Redirect::to('home.php');
        }
        Session::flash('success', 'Profile updated successfully!');
        Redirect::to('home.php');
    }
} elseif (Input::get('action') === 'changepass') {
    if (Hash::check(Input::get('oldpass'), $user->data()->password)) {
        try {
            $user->update(array(
                'password' => Hash::make(Input::get('newpass'))
            ));
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
            Redirect::to('home.php');
        }
        Session::flash('success', 'Password Changed Successfully!');
        Redirect::to('home.php');
    } else {
        Session::flash('error', 'Your Current Password was Incorrect!');
        Redirect::to('home.php');
    }
} elseif (Input::get('action') === 'filepirep') {
    $multi = 0;

    if (Input::get('multi') != 0) {
        $multi = Input::get('multi');
    }

    $user = new User();
    $allowedaircraft = $user->getAvailableAircraft();
    $allowed = false;
    foreach ($allowedaircraft as $a) {
        if ($a["name"] == Input::get('aircraft')) {
            $allowed = true;
        }
    }
    if (!$allowed) {
        Session::flash('error', 'You are not of a high enough rank to fly that aircraft. Your PIREP has not been filed.');
        Redirect::to('pireps.php?page=new');
    }

    $curl = new Curl;

    $response = VANet::sendPirep(array (
        'AircraftID' => Aircraft::nameToLiveryId(Input::get('aircraft')),
        'Arrival' => Input::get('arr'),
        'DateTime' => Input::get('date'),
        'Departure' => Input::get('dep'),
        'FlightTime' => Time::strToSecs(Input::get('ftime')),
        'FuelUsed' => Input::get('fuel'),
        'PilotId' => $user->data()->ifuserid
    ));

    $response = Json::decode($response->body);
    if ($response['success'] != true) {
        Session::flash('error', 'There was an Error Connecting to VANet.');
        Redirect::to('pireps.php?page=new');
    }

    if (!Pirep::file(array(
        'flightnum' => Input::get('fnum'),
        'departure' => Input::get('dep'),
        'arrival' => Input::get('arr'),
        'flighttime' => Time::strToSecs(Input::get('ftime')),
        'pilotid' => $user->data()->id,
        'date' => Input::get('date'),
        'aircraftid' => Aircraft::getId(Input::get('aircraft')),
        'multi' => $multi
    ))) {
        Session::flash('error', 'There was an Error Filing the PIREP.');
        Redirect::to('pireps.php?page=recents');
    } else {
        Session::flash('success', 'PIREP Filed Successfully!');
        Redirect::to('pireps.php?page=recents');
    }
} elseif (Input::get('action') === 'editpirep') {
    if (!Pirep::update(Input::get('id'), array(
        'flightnum' => Input::get('fnum'),
        'departure' => Input::get('dep'),
        'arrival' => Input::get('arr'),
        'pilotid' => $user->data()->id,
        'date' => Input::get('date'),
        'aircraftid' => Aircraft::getId(Input::get('aircraft')),
        'multi' => Input::get('multi')
    ))) {
        Session::flash('errorrecent', 'There was an Error Editing the PIREP.');
        Redirect::to('pireps.php');
    } else {
        Session::flash('successrecent', 'PIREP Edited successfully!');
        Redirect::to('pireps.php');
    }
} elseif (Input::get('action') === 'edituser') {
    if (!$user->hasPermission('usermanage')) {
        Redirect::to('home.php');
        die();
    }

    $perms = Json::decode($user->getUser(Input::get('id'))["permissions"]);
    $perms["admin"] = Input::get("admin");

    $user->update(array(
        'callsign' => Input::get('callsign'),
        'name' => Input::get('name'),
        'email' => Input::get('email'),
        'ifc' => Input::get('ifc'),
        'transhours' => Time::strToSecs(Input::get('transhours')),
        'transflights' => Input::get('transflights'),
        'permissions' => Json::encode($perms),
    ), Input::get('id'));
    Session::flash('success', 'User Edited Successfully!');
    Redirect::to('admin.php?page=usermanage');
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
        Redirect::to('admin.php?page=usermanage');
    }
    Session::flash('success', 'User deleted successfully!');
    Redirect::to('admin.php?page=usermanage');
} elseif (Input::get('action') === 'editstaffmember') {
    if (!$user->hasPermission('staffmanage')) {
        Redirect::to('home.php');
        die();
    }

    $permissions = Permissions::getAll();
    $finalpermissions = array('admin' => 1);
    foreach ($permissions as $permission => $data) {
        if (Input::get($permission) == 'on') {
            $finalpermissions[$permission] = 1;
        } else {
            $finalpermissions[$permission] = 0;
        }
    }

    try {
        $user->update(array(
            'callsign' => Input::get('callsign'),
            'name' => Input::get('name'),
            'email' => Input::get('email'),
            'ifc' => Input::get('ifc'),
            'permissions' => Json::encode($finalpermissions)
        ), Input::get('id'));
    } catch (Exception $e) {
        Session::flash('error', 'There was an Error Editing the Staff Member.');
        Redirect::to('admin.php?page=staffmanage');
    }
    Session::flash('success', 'Staff Member Edited Successfully!');
    Redirect::to('admin.php?page=staffmanage');
} elseif (Input::get('action') === 'declineapplication') {
    if (!$user->hasPermission('recruitment')) {
        Redirect::to('home.php');
        die();
    }

    try {
        $user->update(array(
            'status' => 3,
            'declinereason' => Input::get('declinereason')
        ), Input::get('id'));
    } catch (Exception $e) {
        Session::flash('error', 'There was an error Declining the Application.');
        Redirect::to('admin.php?page=recruitment');
    }
    Session::flash('success', 'Application Declined Successfully');
    Redirect::to('admin.php?page=recruitment');
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
        Redirect::to('admin.php?page=recruitment');
    }
    Session::flash('success', 'Application Accepted Successfully!');
    Redirect::to('admin.php?page=recruitment');
} elseif (Input::get('action') === 'acceptpirep') {
    if (!$user->hasPermission('pirepmanage')) {
        Redirect::to('home.php');
        die();
    }

    Pirep::accept(Input::get('accept'));
    Session::flash('success', 'PIREP Accepted Successfully!');
    Redirect::to('admin.php?page=pirepmanage');
} elseif (Input::get('action') === 'declinepirep') {
    if (!$user->hasPermission('pirepmanage')) {
        Redirect::to('home.php');
        die();
    }

    Pirep::decline(Input::get('decline'));
    Session::flash('success', 'PIREP Declined Successfully');
    Redirect::to('admin.php?page=pirepmanage');
} elseif (Input::get('action') === 'deletearticle') {
    if (!$user->hasPermission('newsmanage')) {
        Redirect::to('home.php');
        die();
    }

    News::archive(Input::get('delete'));
    Session::flash('success', 'News Item Archived Successfully! ');
    Redirect::to('admin.php?page=newsmanage');
} elseif (Input::get('action') === 'editarticle') {
    if (!$user->hasPermission('newsmanage')) {
        Redirect::to('home.php');
        die();
    }

    News::edit(Input::get('id'), array(
        'subject' => Input::get('title'),
        'content' => Input::get('content')
    ));
    Session::flash('success', 'News Article Edited Successfully! ');
    Redirect::to('admin.php?page=newsmanage');
} elseif (Input::get('action') === 'newarticle') {
    if (!$user->hasPermission('newsmanage')) {
        Redirect::to('home.php');
        die();
    }

    News::add(array(
        'subject' => Input::get('title'),
        'content' => Input::get('content'),
        'author' => Input::get('author')
    ));
    Session::flash('success', 'News Article Added Successfully! ');
    Redirect::to('admin.php?page=newsmanage');
} elseif (Input::get('action') === 'deleteaircraft') {
    if (!$user->hasPermission('opsmanage')) {
        Redirect::to('home.php');
        die();
    }

    Aircraft::archive(Input::get('delete'));
    Session::flash('success', 'Aircraft Archived Successfully! ');
    Redirect::to('admin.php?page=opsmanage&section=fleet');
} elseif (Input::get('action') === 'addaircraft') {
    if (!$user->hasPermission('opsmanage')) {
        Redirect::to('home.php');
        die();
    }

    Aircraft::add(Input::get('aircraftselect'), Rank::nameToId(Input::get('rank')), Input::get('livery'));
    Session::flash('success', 'Aircraft Added Successfully! ');
    Redirect::to('admin.php?page=opsmanage&section=fleet');
} elseif (Input::get('action') == 'editfleet') {
    if (!$user->hasPermission('opsmanage')) {
        Redirect::to('home.php');
        die();
    }
    
    Aircraft::updateRank(Input::get('rank'), Input::get('id'));
    Session::flash('success', 'Aircraft Updated Successfully!');
    Redirect::to('admin.php?page=opsmanage&section=fleet');
} elseif (Input::get('action') === 'setuppireps') {
    if (!Pirep::setup(Input::get('callsign'), $user->data()->id)) {
        Session::flash('errorrecent', 'There was an Error Connecting to Infinite Flight. Ensure you are spawned in on the <b>Casual Server, and have set your callsign to \''.$user->data()->callsign.'\'</b>!');
        Redirect::to('pireps.php?page=new');
    }
    Session::flash('successrecent', 'PIREPs Setup Successfully! You can now File PIREPs.');
    Redirect::to('pireps.php?page=new');
} elseif (Input::get('action') === 'addroute') {
    if (!$user->hasPermission('opsmanage')) {
        Redirect::to('home.php');
        die();
    }

    Route::add(array(Input::get('fltnum'), Input::get('dep'), Input::get('arr'), Time::strToSecs(Input::get('duration')), Aircraft::nameToId(Input::get('aircraft'))));
    Session::flash('success', 'Route Added Successfully!');
    Redirect::to('admin.php?page=opsmanage&section=routes');
} elseif (Input::get('action') === 'deleteroute') {
    if (!$user->hasPermission('opsmanage')) {
        Redirect::to('home.php');
        die();
    }

    Route::delete(Input::get('deleteroute'));
    Session::flash('success', 'Route Removed Successfully!');
    Redirect::to('admin.php?page=opsmanage&section=routes');
} elseif (Input::get('action') === 'addrank') {
    if (!$user->hasPermission('opsmanage')) {
        Redirect::to('home.php');
        die();
    }

    Rank::add(Input::get('name'), Time::hrsToSecs(Input::get('time')));
    Session::flash('success', 'Rank Added Successfully!');
    Redirect::to('admin.php?page=opsmanage&section=ranks');
} elseif (Input::get('action') === 'editrank') {
    if (!$user->hasPermission('opsmanage')) {
        Redirect::to('home.php');
        die();
    }

    try {
        Rank::update(Input::get('id'), array(
            'name' => Input::get('name'),
            'timereq' => Input::get('time')
        ));
    } catch (Exception $e) {
        Session::flash('error', 'There was an Error Editing the Rank.');
        Redirect::to('admin.php?page=opsmanage&section=ranks');
    }
    Session::flash('success', 'Rank Edited Successfully!');
    Redirect::to('admin.php?page=opsmanage&section=ranks');
} elseif (Input::get('action') == 'getliveriesforaircraft') {
    $all = Aircraft::fetchLiveryIdsForAircraft(Aircraft::nameToAircraftId(Input::get('aircraft')));
    foreach ($all as $name => $id) {
        echo '<option>'.$name.'</option>';
    }
} elseif (Input::get('action') === 'setcolour') {
    if (!$user->hasPermission('opsmanage')) {
        Redirect::to('home.php');
        die();
    }

    if (!Config::replaceColour(Input::get('hexcol'))) {
        Session::flash('error', 'There was an Error Updating the Colour Theme!');
        Redirect::to('admin.php?page=site');
        die();
    }
    Session::flash('success', 'Colour Theme Updated Successfully! You may need to reload the page or clear your cache in order for it to show.');
    Redirect::to('admin.php?page=site');
} elseif (Input::get('action') === 'vasettingsupdate') {
    if (!$user->hasPermission('opsmanage')) {
        Redirect::to('home.php');
        die();
    }

    if (!Config::replace('name', Input::get('vaname')) || !Config::replace('identifier', Input::get('vaident'))) {
        Session::flash('error', 'There was an error updating the Config File!');
        Redirect::to('admin.php?page=site');
        die();
    }
    Session::flash('success', 'VA Details Changed Successfully. You may need to reload the page a few times or clear your cache in order for it to show.');
    Redirect::to('admin.php?page=site');
} elseif (Input::get('action') === 'vanetupdate') {
    if (!$user->hasPermission('opsmanage')) {
        Redirect::to('home.php');
        die();
    }
    
    if (!Config::replace('api_key', Input::get('vanetkey'))) {
        Session::flash('error', 'There was an error updating the config file!');
        Redirect::to('admin.php?page=site');
        die();
    }
    Session::flash('success', 'VANet API Key changed Successfully.');
    Redirect::to('admin.php?page=site');
}