<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class CodesharesController extends Controller
{
    public function get()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->routes = Route::fetchAll();
        $this->render('admin/codeshares', $data);
    }

    public function post()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        switch (Input::get('action')) {
            case 'newcodeshare':
                $this->new();
            case 'deletecodeshare':
                $this->delete();
            case 'importcodeshare':
                $this->import();
            default:
                $this->get();
        }
    }

    private function new()
    {
        $routes = [];
        $inputRoutes = explode(",", Input::get('routes'));

        $dbRoutes = Route::fetchAll();
        foreach ($inputRoutes as $input) {
            if (!array_key_exists($input, $dbRoutes)) {
                Session::flash('error', 'Could not Find Route ' . $input);
                $this->redirect('/admin/operations/codeshares');
            }
            $r = $dbRoutes[$input];
            if (count($r['aircraft']) < 1) {
                Session::flash('error', 'This route does not have any aircraft attached - ' . $input);
                $this->redirect('/admin/operations/codeshares');
            }
            array_push($routes, array(
                "flightNum" => $r['fltnum'],
                "departure" => $r['dep'],
                "arrival" => $r['arr'],
                "aircraftID" => $r['aircraft'][0]['liveryid'],
                "flightTime" => $r['duration']
            ));
        }

        $ret = VANet::sendCodeshare(array(
            "VEToID" => Input::get('recipient'),
            "Message" => Input::get('message'),
            "Routes" => $routes
        ));
        if (!$ret) {
            Session::flash('error', "Error Connnecting to VANet");
            $this->redirect('/admin/operations/codeshares');
            die();
        } else {
            Session::flash('success', "Codeshare Sent Successfully!");
            $this->redirect('/admin/operations/codeshares');
        }
    }

    private function delete()
    {
        $ret = VANet::deleteCodeshare(Input::get('delete'));
        if (!$ret) {
            Session::flash('error', "Error Connnecting to VANet");
            $this->redirect('/admin/operations/codeshares');
        }
        Cache::delete('badge_codeshares');
        Session::flash('success', "Codeshare Deleted Successfully!");
        $this->redirect('/admin/operations/codeshares');
    }

    private function import()
    {
        $codeshare = VANet::findCodeshare(Input::get('id'));
        if ($codeshare === FALSE) {
            Session::flash('error', "Codeshare Not Found");
            $this->redirect('/admin/operations/codeshares');
            die();
        }

        $dbac = Aircraft::fetchAllAircraft();
        $dbaircraft = [];
        foreach ($dbac as $d) {
            $dbaircraft[$d['ifliveryid']] = $d;
        }

        $lowrank = Rank::getFirstRank();
        foreach ($codeshare["routes"] as $route) {
            $ac = -1;
            if (!array_key_exists($route['aircraftLiveryID'], $dbaircraft)) {
                Aircraft::add($route['aircraftLiveryID'], $lowrank->id);
                $ac = Aircraft::lastId();
            } else {
                $ac = $dbaircraft[$route['aircraftLiveryID']]->id;
            }
            Route::add([
                'fltnum' => $route['flightNum'],
                'dep' => $route['departure'],
                'arr' => $route['arrival'],
                'duration' => $route['flightTime'],
            ]);
            Route::addAircraft(Route::lastId(), $ac);
        }
        VANet::deleteCodeshare($codeshare["id"]);
        Cache::delete('badge_codeshares');
        Session::flash('success', "Codeshare Routes Imported Successfully!");
        $this->redirect('/admin/operations/routes');
    }
}
