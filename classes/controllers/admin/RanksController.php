<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class RanksController extends Controller
{
    public function get_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->ranks = Rank::fetchAllNames()->results();
        $data->active_dropdown = 'operations-management';
        $this->render('admin/ranks', $data);
    }

    public function post_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        switch (Input::get('action')) {
            case 'addrank':
                $this->create();
                break;
            case 'editrank':
                $this->update();
                break;
            case 'delrank':
                $this->delete();
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

        $rank = Rank::find($id);
        if ($rank === false) {
            $this->notFound();
        }

        $data->rank = $rank;
        $data->active_dropdown = 'operations-management';
        $this->render('admin/ranks_edit', $data);
    }

    public function post_edit($id)
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');

        $this->update();
        $this->get_edit($id);
    }

    public function get_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->active_dropdown = 'operations-management';

        $this->render('admin/ranks_new', $data);
    }

    public function post_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');

        $this->create();
        $this->redirect('/admin/ranks');
    }

    private function create()
    {
        Rank::add(Input::get('name'), Time::hrsToSecs(Input::get('time')));
        Session::flash('success', 'Rank Added Successfully!');
    }

    private function update()
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
    }

    private function delete()
    {
        $ranks = Rank::fetchAllNames()->count();
        if ($ranks <= 1) {
            Session::flash('error', 'You cannot delete the one remaining rank!');
            $this->redirect('/admin/operations/ranks');
        }
        $aircraft = Aircraft::unlockedAtRank(Input::get('id'));
        if (count($aircraft) > 0) {
            Session::flash('error', 'You cannot delete this rank as it is currently assigned to ' . count($aircraft) . ' aircraft!');
            $this->redirect('/admin/operations/ranks');
        }

        $ret = Rank::delete(Input::get('delete'));
        if (!$ret) {
            Session::flash('error', 'There was an Error Deleting the Rank');
        } else {
            Session::flash('success', 'Rank Deleted Successfully');
        }
    }
}
