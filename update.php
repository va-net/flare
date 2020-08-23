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
        Session::flash('error', 'Callsign is already taken!');
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
        Session::flash('success', 'Password changed successfully!');
        Redirect::to('home.php');
    } else {
        Session::flash('error', 'Your current password was incorrect!');
        Redirect::to('home.php');
    }
} elseif (Input::get('action') === 'filepirep') {
    $multi = 0;

    if (Input::get('multi') != 0) {
        $multi = Input::get('multi');
    }

    $curl = new Curl;

    $response = $curl->post(Config::get('vanet/base_url').'/api/flights/new?apikey='.Config::get('vanet/api_key'), array(
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
        Session::flash('error', 'There was an error connecting to VANet.');
        Redirect::to('pireps.php?page=new');
    }

    if (!Pirep::file(array(
        'flightnum' => Input::get('fnum'),
        'departure' => Input::get('dep'),
        'arrival' => Input::get('arr'),
        'flighttime' => Time::strToSecs(Input::get('ftime')),
        'pilotid' => $user->data()->id,
        'date' => Input::get('date'),
        'fuel' => Input::get('fuel'),
        'aircraftid' => Aircraft::getId(Input::get('aircraft')),
        'multi' => $multi
    ))) {
        Session::flash('error', 'There was an error filing the PIREP.');
        Redirect::to('pireps.php?page=new');
    } else {
        Session::flash('success', 'PIREP filed successfully!');
        Redirect::to('pireps.php?page=new');
    }
} elseif (Input::get('action') === 'editpirep') {
    if (!Pirep::update(Input::get('id'), array(
        'flightnum' => Input::get('fnum'),
        'departure' => Input::get('dep'),
        'arrival' => Input::get('arr'),
        'flighttime' => Time::strToSecs(Input::get('ftime')),
        'pilotid' => $user->data()->id,
        'date' => Input::get('date'),
        'aircraftid' => Aircraft::getId(Input::get('aircraft')),
        'multi' => Input::get('multi')
    ))) {
        Session::flash('errorrecent', 'There was an error editing the PIREP.');
        Redirect::to('pireps.php');
    } else {
        Session::flash('successrecent', 'PIREP edited successfully!');
        Redirect::to('pireps.php');
    }
} elseif (Input::get('action') === 'edituser') {

    try {
        $user->update(array(
            'callsign' => Input::get('callsign'),
            'name' => Input::get('name'),
            'email' => Input::get('email'),
            'ifc' => Input::get('ifc')
        ), Input::get('id'));
    } catch (Exception $e) {
        Session::flash('error', 'There was an error editing the user.');
        Redirect::to('admin.php?page=usermanage');
    }
    Session::flash('success', 'User edited successfully!');
    Redirect::to('admin.php?page=usermanage');
} elseif (Input::get('action') === 'deluser') {

    try {
        $user->update(array(
            'status' => 2
        ), Input::get('id'));
    } catch (Exception $e) {
        Session::flash('error', 'There was an error deleting the user.');
        Redirect::to('admin.php?page=usermanage');
    }
    Session::flash('success', 'User deleted successfully!');
    Redirect::to('admin.php?page=usermanage');
} elseif (Input::get('action') === 'editstaffmember') {
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
        Session::flash('error', 'There was an error editing the staff member.');
        Redirect::to('admin.php?page=staffmanage');
    }
    Session::flash('success', 'Staff member edited successfully!');
    Redirect::to('admin.php?page=staffmanage');
} elseif (Input::get('action') === 'declineapplication') {
    try {
        $user->update(array(
            'status' => 3,
            'declinereason' => Input::get('declinereason')
        ), Input::get('id'));
    } catch (Exception $e) {
        Session::flash('error', 'There was an error declining the application.');
        Redirect::to('admin.php?page=recruitment');
    }
    Session::flash('success', 'Application declined successfully!');
    Redirect::to('admin.php?page=recruitment');
} elseif (Input::get('action') === 'acceptapplication') {
    try {
        $user->update(array(
            'status' => 1
        ), Input::get('accept'));
    } catch (Exception $e) {
        Session::flash('error', 'There was an error accepting the application.');
        Redirect::to('admin.php?page=recruitment');
    }
    Session::flash('success', 'Application accepted successfully!');
    Redirect::to('admin.php?page=recruitment');
} elseif (Input::get('action') === 'acceptpirep') {
    Pirep::accept(Input::get('accept'));
    Session::flash('success', 'PIREP accepted successfully!');
    Redirect::to('admin.php?page=pirepmanage');
} elseif (Input::get('action') === 'declinepirep') {
    Pirep::decline(Input::get('decline'));
    Session::flash('success', 'PIREP declined successfully! ');
    Redirect::to('admin.php?page=pirepmanage');
} elseif (Input::get('action') === 'deletearticle') {
    News::archive(Input::get('delete'));
    Session::flash('success', 'News archived successfully! ');
    Redirect::to('admin.php?page=newsmanage');
} elseif (Input::get('action') === 'editarticle') {
    News::edit(Input::get('id'), array(
        'subject' => Input::get('title'),
        'content' => Input::get('content')
    ));
    Session::flash('success', 'News article edited successfully! ');
    Redirect::to('admin.php?page=newsmanage');
} elseif (Input::get('action') === 'newarticle') {
    News::add(array(
        'subject' => Input::get('title'),
        'content' => Input::get('content'),
        'author' => Input::get('author')
    ));
    Session::flash('success', 'News article added successfully! ');
    Redirect::to('admin.php?page=newsmanage');
} elseif (Input::get('action') === 'deleteaircraft') {
    Aircraft::archive(Input::get('delete'));
    Session::flash('success', 'Aircraft archived successfully! ');
    Redirect::to('admin.php?page=opsmanage&section=fleet');
} elseif (Input::get('action') === 'addaircraft') {
    Aircraft::add(Input::get('aircraftselect'), Rank::nameToId(Input::get('rank')), Input::get('livery'));
    Session::flash('success', 'Aircraft added successfully! ');
    Redirect::to('admin.php?page=opsmanage&section=fleet');
} elseif (Input::get('action') === 'setuppireps') {
    if (!Pirep::setup(Input::get('callsign'), $user->data()->id)) {
        Session::flash('errorrecent', 'There was an error connecting to Infinite Flight. Ensure you are spawned in on the <b>Casual Server, and have set your callsign to \''.$user->data()->callsign.'\'</b>!');
        Redirect::to('pireps.php?page=new');
    }
    Session::flash('successrecent', 'PIREPs setup successfully! You can now file PIREPs.');
    Redirect::to('pireps.php?page=new');
} elseif (Input::get('action') === 'addroute') {
    Route::add(array(Input::get('fltnum'), Input::get('dep'), Input::get('arr'), Time::strToSecs(Input::get('duration')), Aircraft::nameToId(Input::get('aircraft'))));
    Session::flash('success', 'Route added successfully! ');
    Redirect::to('admin.php?page=opsmanage&section=routes');
} elseif (Input::get('action') === 'deleteroute') {
    Route::delete(Input::get('deleteroute'));
    Session::flash('success', 'Route removed successfully!');
    Redirect::to('admin.php?page=opsmanage&section=routes');
} elseif (Input::get('action') === 'addrank') {
    Rank::add(Input::get('name'), Time::hrsToSecs(Input::get('time')));
    Session::flash('success', 'Rank added successfully!');
    Redirect::to('admin.php?page=opsmanage&section=ranks');
} elseif (Input::get('action') == 'getliveriesforaircraft') {
    $all = Aircraft::fetchLiveryIdsForAircraft(Aircraft::nameToAircraftId(Input::get('aircraft')));
    foreach ($all as $name => $id) {
        echo '<option>'.$name.'</option>';
    }
} elseif (Input::get('action') === 'setcolour') {
    if (!Config::replaceColour(Input::get('hexcol'))) {
        Session::flash('error', 'There was an error updating the colour theme!');
        Redirect::to('admin.php?page=site');
        die();
    }
    Session::flash('success', 'Colour theme updated successfully! You may need to reload the page or clear your cache in order for it to show.');
    Redirect::to('admin.php?page=site');
} elseif (Input::get('action') === 'vasettingsupdate') {
    if (!Config::replace('name', Input::get('vaname')) || !Config::replace('identifier', Input::get('vaident'))) {
        Session::flash('error', 'There was an error updating the config file!');
        Redirect::to('admin.php?page=site');
        die();
    }
    Session::flash('success', 'VA details changed successfully. You may need to reload the page a few times or clear your cache in order for it to show.');
    Redirect::to('admin.php?page=site');
} elseif (Input::get('action') === 'vanetupdate') {
    if (!Config::replace('api_key', Input::get('vanetkey'))) {
        Session::flash('error', 'There was an error updating the config file!');
        Redirect::to('admin.php?page=site');
        die();
    }
    Session::flash('success', 'VANet API key changed successfully.');
    Redirect::to('admin.php?page=site');
}



