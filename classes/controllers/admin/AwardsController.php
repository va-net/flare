<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class AwardsController extends Controller
{
    public function get_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');
        $data = new stdClass;
        $data->user = $user;

        $data->all_users = $user->getAllUsers();
        $data->awards = Awards::getAll();
        $this->render('admin/awards', $data);
    }

    public function post_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');

        switch (Input::get('action')) {
            case 'addaward':
                $this->create();
                break;
            case 'editaward':
                $this->update();
                break;
            case 'delaward':
                $this->delete();
                break;
            case 'giveaward':
                $this->give();
                break;
        }
    }

    public function get_edit($id)
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');
        $data = new stdClass;
        $data->user = $user;

        $data->award = Awards::get($id);
        $this->render('admin/awards_edit', $data);
    }

    public function post_edit($id)
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');

        switch (Input::get('action')) {
            case 'editaward':
                $this->update();
                break;
            case 'giveaward':
                $this->give();
                break;
        }

        $this->get_edit($id);
    }

    public function get_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');
        $data = new stdClass;
        $data->user = $user;

        $this->render('admin/awards_new', $data);
    }

    public function post_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');

        $this->create();
        $this->redirect('/admin/awards');
    }

    private function create()
    {
        $res = Awards::create([
            'name' => Input::get('name'),
            'description' => Input::get('description'),
            'imageurl' => Input::get('image'),
        ]);

        if ($res) {
            Session::flash('success', 'Award Created');
        } else {
            Session::flash('error', 'Failed to Create Award');
        }

        $this->get_index();
    }

    private function update()
    {
        $res = Awards::update(Input::get('id'), [
            'name' => Input::get('name'),
            'description' => Input::get('description'),
            'imageurl' => Input::get('image'),
        ]);

        if ($res) {
            Session::flash('success', 'Award Updated');
        } else {
            Session::flash('error', 'Failed to Update Award');
        }

        $this->get_index();
    }

    private function delete()
    {
        $res = Awards::delete(Input::get('id'));

        if ($res) {
            Session::flash('success', 'Award Deleted');
        } else {
            Session::flash('error', 'Failed to Delete Award');
        }

        $this->get_index();
    }

    private function give()
    {
        $res = Awards::give(Input::get('award'), Input::get('pilot'));

        if ($res) {
            Session::flash('success', 'Award Given');
        } else {
            Session::flash('error', 'Failed to Give Award');
        }

        $this->get_index();
    }
}
