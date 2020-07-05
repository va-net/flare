<?php

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
        Session::flash('error', 'There was an error filing the PIREP.');
        Redirect::to('pireps.php');
    } else {
        Session::flash('success', 'PIREP filed successfully!');
        Redirect::to('pireps.php');
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
}


