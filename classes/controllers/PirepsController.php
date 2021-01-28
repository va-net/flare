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
            'AircraftID' => Aircraft::idToLiveryId(Input::get('aircraft')),
            'Arrival' => Input::get('arr'),
            'DateTime' => Input::get('date'),
            'Departure' => Input::get('dep'),
            'FlightTime' => Time::strToSecs(Input::get('ftime')),
            'FuelUsed' => Input::get('fuel'),
            'PilotId' => $user->data()->ifuserid
        ));

        $response = Json::decode($response->body);
        if (!isset($response['success']) || $response['success'] != true) {
            Session::flash('error', 'There was an Error Connecting to VANet.');
            $this->get_new();
            die();
        }

        if (!Pirep::file(array(
            'flightnum' => Input::get('fnum'),
            'departure' => Input::get('dep'),
            'arrival' => Input::get('arr'),
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
        $data->server = 'casual';
        $force = Config::get('FORCE_SERVER');
        if ($force != 0 && $force != 'casual') $data->server = $force;
        $this->render('pireps_setup', $data);
    }

    public function post_setup()
    {
        $user = new User;
        $this->authenticate($user);
        if (!VANet::setupPireps(Input::get('callsign'), $user->data()->id)) {
            $server = 'casual';
            $force = Config::get('FORCE_SERVER');
            if ($force != 0 && $force != 'casual') $server = $force;
            Session::flash('errorrecent', 'There was an Error Connecting to Infinite Flight. Ensure you are spawned in on the <b>' . ucfirst($server) . ' Server, and have set your callsign to \'' . $user->data()->callsign . '\'</b>!');
            $this->get_setup();
        }

        Session::flash('success', 'PIREPs Setup Successfully! You can now File PIREPs.');
        $this->redirect('/pireps/new');
    }
}
