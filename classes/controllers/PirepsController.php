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
        if (!$user->isLoggedIn()) {
            $this->redirect('/');
        }
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
}
