<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class FleetController extends Controller
{
    public function get_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;

        $data->fleet = Aircraft::fetchActiveAircraft()->results();
        $data->ranks = Rank::fetchAllNames()->results();
        $data->types = Aircraft::fetchAllAircraftFromVANet();
        $data->active_dropdown = 'operations-management';

        $this->render('admin/fleet', $data);
    }

    public function post_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');

        switch (Input::get('action')) {
            case 'addaircraft':
                $this->create();
                break;
            case 'deleteaircraft':
                $this->delete();
                break;
            case 'editfleet':
                $this->update();
                break;
        }

        $this->get_index();
    }

    public function get_edit($id)
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;

        $data->aircraft = Aircraft::fetch($id);
        $data->ranks = Rank::fetchAllNames()->results();
        if ($data->aircraft === false) {
            $this->notFound();
        }
        $data->active_dropdown = 'operations-management';

        $this->render('admin/fleet_edit', $data);
    }

    public function post_edit($id)
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');

        $this->update($id);
        $this->get_edit($id);
    }

    public function get_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;

        $data->types = Aircraft::fetchAllAircraftFromVANet();
        $data->ranks = Rank::fetchAllNames()->results();
        $data->active_dropdown = 'operations-management';

        $this->render('admin/fleet_new', $data);
    }

    public function post_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');

        $this->create();
        $this->redirect('/admin/fleet');
    }

    private function create()
    {
        Aircraft::add(Input::get('livery'), Input::get('rank'), Input::get('notes'));
        Session::flash('success', 'Aircraft Added Successfully!');
    }

    private function delete()
    {
        Aircraft::archive(Input::get('delete'));
        Session::flash('success', 'Aircraft Archived Successfully!');
    }

    private function update()
    {
        Aircraft::update(Input::get('rank'), Input::get('notes'), Input::get('id'));
        Session::flash('success', 'Aircraft Updated Successfully!');
    }
}
