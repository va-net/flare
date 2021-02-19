<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class OperationsController extends Controller
{
    public function ranks_get()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->ranks = Rank::fetchAllNames()->results();
        $this->render('admin/ranks', $data);
    }

    public function ranks_post()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        switch (Input::get('action')) {
            case 'addrank':
                $this->rank_add();
            case 'editrank':
                $this->rank_edit();
            case 'delrank':
                $this->rank_delete();
            default:
                $this->ranks_get();
        }
    }

    private function rank_add()
    {
        Rank::add(Input::get('name'), Time::hrsToSecs(Input::get('time')));
        Session::flash('success', 'Rank Added Successfully!');
        $this->redirect('/admin/operations/ranks');
    }

    private function rank_edit()
    {
        try {
            Rank::update(Input::get('id'), array(
                'name' => Input::get('name'),
                'timereq' => Time::hrsToSecs(Input::get('time'))
            ));
        } catch (Exception $e) {
            Session::flash('error', 'There was an Error Editing the Rank');
            $this->redirect('/admin/operations/ranks');
        }
        Session::flash('success', 'Rank Edited Successfully');
        $this->redirect('/admin/operations/ranks');
    }

    private function rank_delete()
    {
        $ranks = Rank::fetchAllNames()->count();
        if ($ranks <= 1) {
            Session::flash('error', 'You cannot delete the one remaining rank!');
            $this->redirect('/admin/operations/ranks');
        }
        $ret = Rank::delete(Input::get('delete'));
        if (!$ret) {
            Session::flash('error', 'There was an Error Deleting the Rank');
            $this->redirect('/admin/operations/ranks');
        } else {
            Session::flash('success', 'Rank Deleted Successfully');
            $this->redirect('/admin/operations/ranks');
        }
    }

    public function fleet_get()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->fleet = Aircraft::fetchActiveAircraft()->results();
        $data->ranks = Rank::fetchAllNames()->results();
        $this->render('admin/fleet', $data);
    }

    public function fleet_post()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        switch (Input::get('action')) {
            case 'addaircraft':
                $this->fleet_add();
            case 'deleteaircraft':
                $this->fleet_delete();
            case 'editfleet':
                $this->fleet_edit();
            default:
                $this->fleet_get();
        }
    }

    private function fleet_add()
    {
        Aircraft::add(Input::get('livery'), Input::get('rank'), Input::get('notes'));
        Session::flash('success', 'Aircraft Added Successfully! ');
        $this->redirect('/admin/operations/fleet');
    }

    private function fleet_delete()
    {
        Aircraft::archive(Input::get('delete'));
        Session::flash('success', 'Aircraft Archived Successfully! ');
        $this->redirect('/admin/operations/fleet');
    }

    private function fleet_edit()
    {
        Aircraft::update(Input::get('rank'), Input::get('notes'), Input::get('id'));
        Session::flash('success', 'Aircraft Updated Successfully!');
        $this->redirect('/admin/operations/fleet');
    }
}
