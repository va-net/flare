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
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->fleet = Aircraft::fetchActiveAircraft()->results();
        $this->render('admin/events', $data);
    }

    public function post()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        switch (Input::get('action')) {
            case 'deleteevent':
                $this->delete();
            case 'addevent':
                $this->add();
            case 'editevent':
                $this->edit();
            default:
                $this->get();
        }
    }

    private function delete()
    {
        VANet::deleteEvent(Input::get('delete'));
        Session::flash('success', 'Event Deleted Successfully');
        $this->redirect('/admin/operations/events');
    }

    private function add()
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
        $this->redirect('/admin/operations/events');
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
            $this->redirect('/admin/operations/events');
        } else {
            Session::flash('success', "Event Updated Successfully");
            $this->redirect('/admin/operations/events');
        }
    }
}
