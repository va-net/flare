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
        $data->pireps = $user->recentPireps(null, null);
        $this->render('pireps_all', $data);
    }

    public function post_all()
    {
        $user = new User;
        $this->authenticate($user);

        $this->update();
        $this->redirect('/pireps');
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
        $this->authenticate($user);
        if ($user->data()->ifuserid == null) {
            $this->redirect('/pireps/setup');
        }

        $this->create();
        $this->redirect('/pireps');
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

    public function get_pirep($id)
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->pirep = Pirep::find($id, $user->hasPermission('pirepmanage') ? null : $user->data()->id);
        if ($data->pirep === FALSE) $this->notFound();
        $data->comments = Pirep::getComments($id);
        $data->aircraft = $user->getAvailableAircraft();

        $this->render('pireps_view', $data);
    }

    public function post_pirep($id)
    {
        $user = new User;
        $this->authenticate($user);

        $this->update();
        $this->get_pirep($id);
    }

    public function acars()
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        if (!$data->is_gold) $this->notFound();
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
        if (!$data->is_gold) $this->notFound();
        $data->acars = VANet::runAcars(Input::get('server'));

        if ($data->acars == null || $data->acars['status'] != 0) {
            Session::flash('error', 'We couldn\'t find you on the server. Ensure that you have filed a flight plan, and are still connected to Infinite Flight. Then, hit that button again.');
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

    private function update()
    {
        $data = [
            'flightnum' => Input::get('fnum'),
            'departure' => Input::get('dep'),
            'arrival' => Input::get('arr'),
            'date' => Input::get('date'),
            'fuelused' => Input::get('fuel'),
        ];
        if ((new User)->hasPermission('pirepmanage')) {
            if (Input::get('ftime')) $data['flighttime'] = Time::strToSecs(Input::get('ftime'));
            if (Input::get('aircraft')) $data['aircraftid'] = Input::get('aircraft');
            if (strlen(Input::get('status')) > 0) $data['status'] = Input::get('status');
            if (Input::get('multi')) $data['multi'] = Input::get('multi');
        }

        if (!Pirep::update(Input::get('id'), $data)) {
            Session::flash('error', 'There was an Error Editing the PIREP');
        } else {
            Session::flash('success', 'PIREP Edited successfully!');
        }
    }

    private function create()
    {
        $multi = "None";
        $finalFTime = Time::strToSecs(Input::get('ftime'));
        $user = new User();

        if (!empty(Input::get('multi'))) {
            $multiplier = Pirep::findMultiplier(Input::get('multi'), $user->rank(null, true));
            if (!$multiplier) {
                Session::flash('error', 'Invalid Multiplier Code');
                $this->get_new();
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
            Session::flash('error', 'You are not of a high enough rank to fly that aircraft. Your PIREP has not been filed.');
            $this->get_new();
        }

        if (!Pirep::file(array(
            'flightnum' => strtoupper(Input::get('fnum')),
            'departure' => strtoupper(Input::get('dep')),
            'arrival' => strtoupper(Input::get('arr')),
            'flighttime' => $finalFTime,
            'pilotid' => $user->data()->id,
            'date' => Input::get('date'),
            'aircraftid' => Input::get('aircraft'),
            'multi' => $multi,
            'fuelused' => Input::get('fuel'),
        ))) {
            Session::flash('error', 'There was an Error Filing the PIREP.');
        } else {
            Cache::delete('badge_pireps');
            Session::flash('success', 'PIREP Filed Successfully!');
        }
    }
}
