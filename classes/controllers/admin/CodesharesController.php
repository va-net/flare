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

        $allroutes = Route::fetchAll();
        $aircraftentries = Route::fetchAllAircraftJoins();
        foreach (Input::get('routes') as $rid) {
            $r = null;
            foreach ($allroutes as $route) {
                if ($route['id'] == $rid) {
                    $r = $route;
                    break;
                }
            }
            if ($r == null) {
                Session::flash('error', "Invalid Route ID - {$rid}");
                $this->get();
            }

            $aircraft = [];
            foreach ($aircraftentries as $entry) {
                if ($entry->routeid == $r['id']) {
                    $aircraft[] = $entry;
                }
            }
            if (empty($aircraft)) {
                Session::flash('error', "Route #{$rid} has no aircraft attached");
                $this->get();
            } elseif (count($aircraft) > 1) {
                Session::flash('error', "Route #{$rid} has more than one aircraft attached");
                $this->get();
            }

            $routes[] = [
                "flightNumber" => $r['fltnum'],
                "departureIcao" => $r['dep'],
                "arrivalIcao" => $r['arr'],
                "AircraftLiveryID" => $aircraft[0]->aircraftliveryid,
                "flightTime" => $r['duration']
            ];
        }

        $ret = VANet::sendCodeshare(array(
            "recipientId" => Input::get('recipient'),
            "message" => Input::get('message'),
            "routes" => $routes
        ));
        if (!$ret) {
            Session::flash('error', "Error Connnecting to VANet");
            $this->get();
            die();
        } else {
            Session::flash('success', "Codeshare Sent Successfully!");
            $this->get();
        }
    }

    private function delete()
    {
        $ret = VANet::deleteCodeshare(Input::get('delete'));
        if (!$ret) {
            Session::flash('error', "Error Connnecting to VANet");
            $this->get();
        }
        Cache::delete('badge_codeshares');
        Session::flash('success', "Codeshare Deleted Successfully!");
        $this->get();
    }

    private function import()
    {
        $codeshare = VANet::findCodeshare(Input::get('id'));
        if ($codeshare === FALSE) {
            Session::flash('error', "Codeshare Not Found");
            $this->get();
            die();
        }

        $dbac = Aircraft::fetchAllAircraft();
        $dbaircraft = [];
        foreach ($dbac as $d) {
            $dbaircraft[$d->ifliveryid] = $d;
        }

        $lowrank = Rank::getFirstRank();
        foreach ($codeshare["routes"] as $route) {
            $ac = -1;
            if (!array_key_exists($route['aircraftLiveryId'], $dbaircraft)) {
                Aircraft::add($route['aircraftLiveryId'], $lowrank->id);
                $ac = Aircraft::lastId();
            } else {
                $ac = $dbaircraft[$route['aircraftLiveryId']]->id;
            }
            Route::add([
                'fltnum' => $route['flightNumber'],
                'dep' => $route['departureIcao'],
                'arr' => $route['arrivalIcao'],
                'duration' => $route['flightTime'],
            ]);
            Route::addAircraft(Route::lastId(), $ac);
        }
        VANet::deleteCodeshare($codeshare["id"]);
        Cache::delete('badge_codeshares');
        Session::flash('success', "Codeshare Routes Imported Successfully!");
        $this->redirect('/admin/routes');
    }
}
