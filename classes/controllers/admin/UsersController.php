<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class UsersController extends Controller
{
    public function get_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->users = $user->getAllUsers();
        $data->active_dropdown = 'user-management';
        $this->render('admin/users', $data);
    }

    public function post_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');
        switch (Input::get('action')) {
            case 'deluser':
                $this->delete();
                break;
            case 'edituser':
                $this->update();
                break;
            case 'announce':
                $this->announce();
                break;
        }

        $this->get_index();
    }

    public function get_pending()
    {
        $user = new User;
        $this->authenticate($user, true, 'recruitment');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->users = $user->getAllPendingUsers();
        $data->active_dropdown = 'user-management';
        $this->render('admin/recruitment', $data);
    }

    public function post_pending()
    {
        $user = new User;
        $this->authenticate($user, true, 'recruitment');
        switch (Input::get('action')) {
            case 'acceptapplication':
                $this->accept();
                break;
            case 'declineapplication':
                $this->decline();
                break;
        }

        $this->get_pending();
    }

    public function get_staff()
    {
        $user = new User;
        $this->authenticate($user, true, 'staffmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->staff = $user->getAllStaff();
        $data->active_dropdown = 'user-management';
        $this->render('admin/staff', $data);
    }

    public function post_staff()
    {
        $user = new User;
        $this->authenticate($user, true, 'staffmanage');
        $myperms = Permissions::forUser(Input::get('id'));
        $permissions = array_keys(Permissions::getAll());
        foreach ($permissions as $permission) {
            if (Input::get($permission) == 'on' && !in_array($permission, $myperms)) {
                Permissions::give(Input::get('id'), $permission);
            } elseif (Input::get($permission) != 'on' && in_array($permission, $myperms)) {
                Permissions::revoke(Input::get('id'), $permission);
            }
        }

        try {
            $user->update(array(
                'callsign' => Input::get('callsign'),
                'name' => Input::get('name'),
                'email' => Input::get('email'),
                'ifc' => Input::get('ifc')
            ), Input::get('id'));
        } catch (Exception $e) {
            Session::flash('error', 'There was an Error Editing the Staff Member.');
            $this->redirect('/admin/users/staff');
        }
        Session::flash('success', 'Staff Member Edited Successfully!');
        $this->redirect('/admin/users/staff');
    }

    public function get_edit($id)
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');
        $data = new stdClass;
        $data->user = $user;

        $data->edit_user = $user->getUser($id);
        $data->pireps = $user->fetchPireps($id)->results();
        $data->user_awards = $user->getAwards($id);
        $data->all_awards = Awards::getAll();
        $data->permissions = array_unique(Permissions::forUser($id));
        $data->active_dropdown = 'user-management';
        $this->render('admin/users_edit', $data);
    }

    public function post_edit($id)
    {
        $user = new User;
        $this->authenticate($user, true, 'usermanage');

        $this->update();
        $this->get_edit($id);
    }

    public function get_lookup($value)
    {
        $gold = VANet::isGold();
        if (!$gold || !VANet::featureEnabled('airline-userlookup')) $this->notFound();

        $user = new User;
        $this->authenticate($user, true, 'usermanage');
        $data = new stdClass;
        $data->user = $user;
        $data->is_gold = $gold;
        $data->lookup = VANet::lookupUser($value, !empty(Input::get('ifc')));
        if ($data->lookup == null) {
            $this->notFound(
                'User Not Found. If searching by IFC Username, the pilot must have Show Username enabled in Infinite Flight.'
            );
        }

        $data->active_dropdown = 'user-management';
        $this->render('admin/user_lookup', $data);
    }

    private function accept()
    {
        $user = new User;
        try {
            $user->update(array(
                'status' => 1
            ), Input::get('accept'));
        } catch (Exception $e) {
            Session::flash('error', 'There was an Error Accepting the Application.');
            return;
        }

        Events::trigger('user/accepted', [Input::get('accept')]);

        Cache::delete('badge_recruitment');
        Session::flash('success', 'Application Accepted Successfully!');
    }

    private function decline()
    {
        $user = new User;
        try {
            $user->update(array(
                'status' => 3
            ), Input::get('id'));
        } catch (Exception $e) {
            Session::flash('error', 'There was an error Declining the Application.');
            $this->redirect('/admin/users/pending');
        }

        Events::trigger('user/declined', ['id' => Input::get('id'), 'reason' => Input::get('declinereason')]);

        Cache::delete('badge_recruitment');
        Session::flash('success', 'Application Declined Successfully');
    }

    private function delete()
    {
        $user = new User;

        if (empty(Input::get('permanent'))) {
            try {
                $user->update(array(
                    'status' => 2
                ), Input::get('id'));
                Session::flash('success', 'User Marked Inactive');
            } catch (Exception $e) {
                Session::flash('error', 'Failed to Mark User Inactive');
            }
        } else {
            $this->authenticate($user, true, 'staffmanage');
            $user->delete(Input::get('id'));
            Session::flash('success', 'User Deleted');
        }
    }

    private function update()
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
    }

    private function announce()
    {
        $title = escape(Input::get('title'));
        $content = escape(Input::get('content'));
        Notifications::notify(0, "fa-bullhorn", $title, $content);
        Session::flash('success', 'Announcement Created');
    }
}
