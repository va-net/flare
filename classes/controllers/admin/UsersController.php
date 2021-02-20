<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class UsersController extends Controller
{
    public function get()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->users = $user->getAllUsers();
        $this->render('admin/users', $data);
    }

    public function post()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');
        switch (Input::get('action')) {
            case 'deluser':
                $this->delete();
            case 'edituser':
                $this->edit();
            case 'announce':
                $this->announce();
            default:
                $this->get();
        }
    }

    private function delete()
    {
        $user = new User;
        try {
            $user->update(array(
                'status' => 2
            ), Input::get('id'));
            Session::flash('success', 'User deleted successfully!');
        } catch (Exception $e) {
            Session::flash('error', 'There was an Error Deleting the User.');
        } finally {
            $this->redirect('/admin/users');
        }
    }

    private function edit()
    {
        $user = new User;
        $isAdmin = $user->hasPermission('admin', Input::get('id'));
        if (!$isAdmin && Input::get('admin') == 1) {
            Permissions::give(Input::get('id'), 'admin');
        } elseif ($isAdmin && Input::get('admin') == 0) {
            Permissions::revokeAll(Input::get('id'));
        }

        $statuses = [
            "Pending" => 0,
            "Active" => 1,
            "Inactive" => 2,
            "Declined" => 3,
        ];

        $user->update([
            'callsign' => Input::get('callsign'),
            'name' => Input::get('name'),
            'email' => Input::get('email'),
            'ifc' => Input::get('ifc'),
            'transhours' => Time::strToSecs(Input::get('transhours')),
            'transflights' => Input::get('transflights'),
            'status' => $statuses[Input::get('status')]
        ], Input::get('id'));
        Session::flash('success', 'User Edited Successfully');
        $this->redirect('/admin/users');
    }

    private function announce()
    {
        $title = escape(Input::get('title'));
        $content = escape(Input::get('content'));
        Notifications::notify(0, "fa-bullhorn", $title, $content);
        Session::flash('success', 'Announcement Created');
        $this->redirect('/admin/users');
    }
}
