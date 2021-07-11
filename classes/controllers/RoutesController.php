<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class RoutesController extends Controller
{
    public function get()
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->aircraft = $user->getAvailableAircraft();
        $this->render('route_form', $data);
    }

    public function search()
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->aircraft = $user->getAvailableAircraft();

        $searchwhere = array();
        $stmts = array();
        if (!empty(Input::get('dep'))) {
            array_push($searchwhere, 'dep = ?');
            array_push($stmts, Input::get('dep'));
        }
        if (!empty(Input::get('arr'))) {
            array_push($searchwhere, 'arr = ?');
            array_push($stmts, Input::get('arr'));
        }
        if (!empty(Input::get('fltnum'))) {
            array_push($searchwhere, 'fltnum = ?');
            array_push($stmts, Input::get('fltnum'));
        }
        if (!empty(Input::get('aircraft'))) {
            array_push($searchwhere, '? IN (SELECT aircraftid FROM route_aircraft WHERE routeid=routes.id)');
            array_push($stmts, Input::get('aircraft'));
        }
        if (!empty(Input::get('duration')) || Input::get('duration') === '0') {
            if (Input::get('duration') == 0) {
                array_push($searchwhere, 'duration <= ?');
                array_push($stmts, 3600);
            } elseif (Input::get('duration') == 10) {
                array_push($searchwhere, 'duration >= ?');
                array_push($stmts, 36000);
            } elseif (is_numeric(Input::get('duration'))) {
                array_push($searchwhere, 'duration >= ?');
                array_push($stmts, Input::get('duration') * 3600);

                array_push($searchwhere, 'duration < ?');
                array_push($stmts, (Input::get('duration') + 1) * 3600);
            }
        }
        if (count($searchwhere) == 0) {
            Session::flash('error', 'You must select at least one filter');
            $this->redirect('/routes');
        }

        $query = 'SELECT routes.fltnum, routes.dep, routes.arr, routes.duration, routes.id, routes.notes FROM routes';
        $i = 0;
        foreach ($searchwhere as $cond) {
            if ($i == 0) {
                $query = $query . ' WHERE ' . $cond;
            } else {
                $query = $query . ' AND ' . $cond;
            }
            $i++;
        }

        $db = DB::getInstance();
        $data->routes = $db->query($query, $stmts)->results();

        $this->render('route_search', $data);
    }

    public function view($id)
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->route = Route::find($id);
        if ($data->route === FALSE) $this->notFound();

        $data->aircraft = Route::aircraft($id);
        $data->pireps = Route::pireps($data->route->fltnum);
        $this->render('route_view', $data);
    }
}
