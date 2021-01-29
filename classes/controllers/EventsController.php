<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class EventsController extends Controller
{
    public function get()
    {
        $user = new User;
        $this->authenticate($user);
        if ($user->data()->ifuserid == null) {
            $this->redirect('/pireps/setup');
        }
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $this->render('events_list', $data);
    }

    public function view($eid)
    {
        $user = new User;
        $this->authenticate($user);
        if ($user->data()->ifuserid == null) {
            $this->redirect('/pireps/setup');
        }
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->event = VANet::findEvent($eid);
        $data->users = $user->getAllUsers();
        $this->render('events_view', $data);
    }
}
