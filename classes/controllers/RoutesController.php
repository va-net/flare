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
        $orderby = 'fltnum';
        $orderdirection = 'ASC';
        if (!empty(Input::get('sortby'))) {
            $sortby = explode('_', Input::get('sortby'));
            $orderby = $sortby[0];
            $orderdirection = strtoupper($sortby[1]);

            if ($orderdirection != 'ASC' && $orderdirection != 'DESC') {
                $orderdirection = 'ASC';
            }
            if (!in_array($orderby, ['id', 'fltnum', 'dep', 'arr', 'duration', 'notes'])) {
                $orderby = 'fltnum';
            }
        }
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
            } elseif (Input::get('duration') == 14) {
                array_push($searchwhere, 'duration >= ?');
                array_push($stmts, 60 * 60 * 14);
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

        $query .= " ORDER BY {$orderby} {$orderdirection}";

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
        $data->pireps = Route::pireps($data->route->fltnum, $data->route->dep, $data->route->arr);
        $this->render('route_view', $data);
    }
}
