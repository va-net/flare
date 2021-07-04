<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class PirepsController extends Controller
{
    public function get_all()
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->pireps = $user->recentPireps(null, 30);
        $this->render('pireps_all', $data);
    }

    public function post_all()
    {
        $user = new User;
        $this->authenticate($user);

        $pirep = Pirep::find(Input::get('id'));
        if ($pirep === FALSE || ($pirep->pilotid != $user->data()->id && !$user->hasPermission('pirepmanage'))) {
            Session::flash('error', 'PIREP Not Found');
            $this->redirect('/pireps');
        }

        $data = array(
            'flightnum' => Input::get('fnum'),
            'departure' => Input::get('dep'),
            'arrival' => Input::get('arr'),
            'date' => Input::get('date'),
        );
        if (!Pirep::update(Input::get('id'), $data)) {
            Session::flash('error', 'There was an Error Editing the PIREP');
            $this->redirect('/pireps');
        } else {
            Session::flash('success', 'PIREP Edited successfully!');
            $this->redirect('/pireps');
        }
    }

    public function get_new()
    {
        $user = new User;
        if (!$user->isLoggedIn()) {
            $this->redirect('/');
        } elseif ($user->data()->ifuserid == null) {
            $this->redirect('/pireps/setup');
        }
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->aircraft = $user->getAvailableAircraft();
        $this->render('pireps_new', $data);
    }

    public function post_new()
    {
        $user = new User;
        if (!$user->isLoggedIn()) {
            $this->redirect('/');
        } elseif ($user->data()->ifuserid == null) {
            $this->redirect('/pireps/setup');
        }
        $multi = "None";
        $finalFTime = Time::strToSecs(Input::get('ftime'));

        if (!empty(Input::get('multi'))) {
            $multiplier = Pirep::findMultiplier(Input::get('multi'));
            if (!$multiplier) {
                Session::flash('error', 'Invalid Multiplier Code');
                $this->get_new();
            }

            $multi = $multiplier->name;
            $finalFTime *= $multiplier->multiplier;
        }

        $user = new User();
        $allowedaircraft = $user->getAvailableAircraft();
        $allowed = false;
        foreach ($allowedaircraft as $a) {
            if ($a["id"] == Input::get('aircraft')) {
                $allowed = true;
            }
        }
        if (!$allowed) {
            Session::flash('error', 'You are not of a high enough rank to fly that aircraft. Your PIREP has not been filed.');
            $this->get_new();
        }

        $response = VANet::sendPirep(array(
            'aircraftLiveryId' => Aircraft::idToLiveryId(Input::get('aircraft')),
            'arrivalIcao' => strtoupper(Input::get('arr')),
            'date' => Input::get('date'),
            'departureIcao' => strtoupper(Input::get('dep')),
            'flightTime' => Time::strToSecs(Input::get('ftime')),
            'fuelUsed' => Input::get('fuel'),
            'pilotId' => $user->data()->ifuserid
        ));

        if (!$response) {
            Session::flash('error', 'There was an Error Connecting to VANet.');
            $this->get_new();
            die();
        }

        if (!Pirep::file(array(
            'flightnum' => Input::get('fnum'),
            'departure' => strtoupper(Input::get('dep')),
            'arrival' => strtoupper(Input::get('arr')),
            'flighttime' => $finalFTime,
            'pilotid' => $user->data()->id,
            'date' => Input::get('date'),
            'aircraftid' => Input::get('aircraft'),
            'multi' => $multi
        ))) {
            Session::flash('error', 'There was an Error Filing the PIREP.');
            $this->redirect('/pireps');
        } else {
            Cache::delete('badge_pireps');
            Session::flash('success', 'PIREP Filed Successfully!');
            $this->redirect('/pireps');
        }
    }

    public function get_setup()
    {
        $user = new User;
        if (!$user->isLoggedIn()) {
            $this->redirect('/');
        }
        $ifc = explode('/', $user->data()->ifc)[4];
        $setupIfc = VANet::setupPirepsIfc($ifc, $user->data()->id);
        if ($setupIfc) {
            Session::flash('success', 'PIREPs were set up using your IFC Username. No further action is required.');
            $this->redirect('/pireps/new');
        }

        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $this->render('pireps_setup', $data);
    }

    public function acars()
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->server = Config::get('FORCE_SERVER');
        $this->render('acars', $data);
    }

    public function acars_post()
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->acars = VANet::runAcars(Input::get('server'));

        if ($data->acars['status'] != 0) {
            Session::flash('error', 'We couldn\'t find you on the server. Ensure that you have filed a flight plan, 
            and are still connected to Infinite Flight. Then, hit that button again.');
            unset($data->acars);
            $this->render('acars', $data);
        }

        $data->aircraft = Aircraft::findAircraft($data->acars['result']["aircraftLiveryId"]);
        if (!$data->aircraft) {
            Session::flash('error', 'You\'re Flying an Aircraft that isn\'t in this VA\'s Fleet!');
            $this->render('acars', $data);
        }


        $this->render('acars_confirm', $data);
    }
}
