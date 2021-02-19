<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class RanksController extends Controller
{

    public function get()
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

    public function post()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        switch (Input::get('action')) {
            case 'addrank':
                $this->add();
            case 'editrank':
                $this->edit();
            case 'delrank':
                $this->delete();
            default:
                $this->get();
        }
    }

    private function add()
    {
        Rank::add(Input::get('name'), Time::hrsToSecs(Input::get('time')));
        Session::flash('success', 'Rank Added Successfully!');
        $this->redirect('/admin/operations/ranks');
    }

    private function edit()
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

    private function delete()
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
}
