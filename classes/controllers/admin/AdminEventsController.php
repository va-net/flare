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

        $vis = 'true';
        if (Input::get('visible') == 0) {
            $vis = 'false';
        }

        $datetime = Input::get('date') . ' ' . substr(Input::get('time'), 0, 2) . ':' . substr(Input::get('time'), 2, 2);

        try {
            VANet::createEvent(array(
                "Name" => Input::get('name'),
                "Description" => Input::get('description'),
                "EventTypeID" => "1",
                "DateTime" => $datetime,
                "DepartureAirport" => Input::get('dep'),
                "ArrivalAirport" => Input::get('arr'),
                "Visible" => $vis,
                "Aircraft" => Input::get('aircraft'),
                "Server" => Input::get('server'),
                "Gates" => $gates
            ));
            Session::flash('success', 'Event Added Successfully!');
        } catch (Exception $e) {
            Session::flash('error', 'Error Creating Event');
        } finally {
            $this->redirect('/admin/operations/events');
        }
    }

    private function edit()
    {
        $vis = 'true';
        if (Input::get('visible') == 0) {
            $vis = 'false';
        }
        $ret = VANet::editEvent(Input::get('id'), array(
            "Name" => Input::get('name'),
            "Description" => Input::get('description'),
            "EventTypeID" => 1,
            "DepartureAirport" => Input::get('dep'),
            "ArrivalAirport" => Input::get('arr'),
            "Visible" => $vis,
            "AircraftID" => Input::get('aircraft'),
            "Server" => Input::get('server')
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
