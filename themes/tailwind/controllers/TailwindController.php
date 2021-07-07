<?php

use RegRev\RegRev;

class TailwindController extends Controller
{
    public function get_pirep($id)
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->pirep = Pirep::find($id, $user->hasPermission('pirepmanage') ? null : $user->data()->id);
        if ($data->pirep === FALSE) $this->notFound();
        $data->aircraft = $user->getAvailableAircraft();

        $this->render('pireps_view', $data);
    }

    public function get_profile()
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $this->render('profile', $data);
    }

    public function post_profile()
    {
        $user = new User;
        $csPattern = Config::get('VA_CALLSIGN_FORMAT');
        $trimmedPattern = preg_replace("/\/[a-z]*$/", '', preg_replace("/^\//", '', $csPattern));

        if (Callsign::assigned(Input::get('callsign'), $user->data()->id)) {
            Session::flash('error', 'Callsign is Already Taken!');
            $this->get_profile();
        } elseif (!Regex::match($csPattern, Input::get('callsign'))) {
            Session::flash('error', 'Callsign does not match the required format! Try <b>' . RegRev::generate($trimmedPattern) . '</b> instead.');
            $this->get_profile();
        } else {
            try {
                if (Config::get('AUTO_CALLSIGNS') == 1) {
                    $user->update(array(
                        'name' => Input::get('name'),
                        'email' => Input::get('email'),
                        'ifc' => Input::get('ifc')
                    ));
                } else {
                    $user->update(array(
                        'name' => Input::get('name'),
                        'callsign' => Input::get('callsign'),
                        'email' => Input::get('email'),
                        'ifc' => Input::get('ifc')
                    ));
                }
            } catch (Exception $e) {
                Session::flash('error', $e->getMessage());
                $this->get_profile();
            }
            Session::flash('success', 'Profile updated successfully!');
            $this->get_profile();
        }
    }
}
