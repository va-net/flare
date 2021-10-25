<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class AdminEventsController extends EventsController
{
    public function get()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->is_gold = VANet::isGold();
        if (!$data->is_gold) {
            $this->redirect('/');
        }

        $data->fleet = Aircraft::fetchActiveAircraft()->results();
        $data->active_dropdown = 'operations-management';
        $this->render('admin/events', $data);
    }

    public function post()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        switch (Input::get('action')) {
            case 'deleteevent':
                $this->delete();
                break;
            case 'addevent':
                $this->create();
                break;
            case 'editevent':
                $this->edit();
                break;
        }

        $this->get();
    }

    public function get_edit($id)
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->is_gold = VANet::isGold();
        if (!$data->is_gold) {
            $this->redirect('/');
        }

        $data->event = VANet::findEvent($id);
        if ($data->event == null) {
            $this->notFound();
        }
        $data->fleet = Aircraft::fetchActiveAircraft()->results();

        $data->active_dropdown = 'operations-management';
        $this->render('admin/events_edit', $data);
    }

    public function post_edit($id)
    {
        $this->authenticate(new User, true, 'opsmanage');

        $this->edit();
        $this->get_edit($id);
    }

    public function get_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->is_gold = VANet::isGold();
        if (!$data->is_gold) {
            $this->redirect('/');
        }

        $data->fleet = Aircraft::fetchActiveAircraft()->results();
        $data->active_dropdown = 'operations-management';
        $this->render('admin/events_new', $data);
    }

    public function post_new()
    {
        $this->authenticate(new User, true, 'opsmanage');

        $this->create();

        $this->get_new();
    }

    private function delete()
    {
        VANet::deleteEvent(Input::get('delete'));
        Session::flash('success', 'Event Deleted Successfully');
    }

    private function create()
    {
        $sentGates = explode(",", Input::get('gates'));
        $gates = [];
        foreach ($sentGates as $g) {
            $gates[] = trim($g);
        }

        $datetime = Input::get('date') . 'T' . substr(Input::get('time'), 0, 2) . ':' . substr(Input::get('time'), 2, 2) . ':00Z';

        $res = VANet::createEvent(array(
            "name" => Input::get('name'),
            "description" => Input::get('description'),
            "date" => $datetime,
            "departureIcao" => Input::get('dep'),
            "arrivalIcao" => Input::get('arr'),
            "aircraftLiveryId" => Input::get('aircraft'),
            "server" => Input::get('server'),
            "gateNames" => $gates
        ));
        if ($res) {
            Session::flash('success', 'Event Added Successfully!');
        } else {
            Session::flash('error', 'Error Creating Event');
        }
    }

    private function edit()
    {
        $datetime = Input::get('date') . 'T' . substr(Input::get('time'), 0, 2) . ':' . substr(Input::get('time'), 2, 2) . ':00Z';
        $ret = VANet::editEvent(Input::get('id'), array(
            "name" => Input::get('name'),
            "description" => Input::get('description'),
            "date" => $datetime,
            "departureIcao" => Input::get('dep'),
            "arrivalIcao" => Input::get('arr'),
            "aircraftLiveryId" => Input::get('aircraft'),
            "server" => Input::get('server')
        ));

        if (!$ret) {
            Session::flash('error', "Error Updating Event");
        } else {
            Session::flash('success', "Event Updated Successfully");
        }
    }
}
