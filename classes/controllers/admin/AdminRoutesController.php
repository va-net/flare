<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class AdminRoutesController extends Controller
{
    public function get_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;

        $data->routes = Route::fetchAll();
        $data->fleet = Aircraft::fetchActiveAircraft()->results();
        $this->render('admin/routes', $data);
    }

    public function post_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');

        switch (Input::get('action')) {
            case 'editroute':
                $this->update();
                break;
            case 'addroute':
                $this->create();
                break;
            case 'deleteroute':
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

        $data->route = Route::find($id);
        $data->aircraft = Route::aircraft($id);

        $this->render('admin/routes_edit', $data);
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

        $this->render('admin/routes_new', $data);
    }

    public function post_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');

        $this->create();
        $this->get_index();
    }

    public function get_import()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;

        $this->render('admin/import', $data);
    }

    public function post_import()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');

        switch (Input::get('action')) {
            case 'choose':
                $this->import_choose();
            case 'import':
                $this->import_import();
            default:
                $this->get_import();
        }
    }

    private function create()
    {
        $notes = empty(Input::get('notes')) ? null : Input::get('notes');
        Route::add([
            "fltnum" => Input::get('fltnum'),
            "dep" => Input::get('dep'),
            "arr" => Input::get('arr'),
            "duration" => Time::strToSecs(Input::get('duration')),
            "notes" => $notes,
        ]);
        $id = Route::lastId();
        foreach (explode(',', Input::get('aircraft')) as $acId) {
            Route::addAircraft($id, $acId);
        }
        Session::flash('success', 'Route Added Successfully!');
    }

    private function delete()
    {
        Route::delete(Input::get('delete'));
        Session::flash('success', 'Route Removed Successfully!');
    }

    private function update()
    {
        $oldAc = array_map(function ($a) {
            return $a->id;
        }, Route::aircraft(Input::get('id')));
        $newAc = explode(',', Input::get('aircraft'));
        if ($oldAc != $newAc) {
            foreach ($oldAc as $o) {
                if (!in_array($o, $newAc)) {
                    // Been Removed
                    Route::removeAircraft(Input::get('id'), $o);
                }
            }
            foreach ($newAc as $n) {
                if (!in_array($n, $oldAc)) {
                    // Been Added
                    Route::addAircraft(Input::get('id'), $n);
                }
            }
        }

        $ret = Route::update(Input::get('id'), array(
            "fltnum" => Input::get('fltnum'),
            "dep" => Input::get('dep'),
            "arr" => Input::get('arr'),
            "duration" => Time::strToSecs(Input::get('duration')),
            "notes" => Input::get('notes'),
        ));

        if ($ret === FALSE) {
            Session::flash('error', 'Error Updating Route');
        } else {
            Session::flash('success', 'Route Updated Successfully!');
        }
    }

    private function import_choose()
    {
        $user = new User;
        $this->authenticate($user, true, 'opsmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->aircraft = Aircraft::fetchAllAircraftFromVANet();

        $file = Input::getFile('routes-upload');
        if ($file["error"] == 1 || $file["error"] === TRUE) {
            Session::flash('error', 'Upload failed. Maybe your file is too big?');
            $this->redirect('/admin/operations/routes/import');
        }

        $route_data = file_get_contents($file["tmp_name"]);
        preg_match_all('/\r?\n.*/m', $route_data, $routelines);
        $data->routes = array_map(function ($l) {
            $l = trim($l);
            $segments = str_getcsv($l);
            if (strpos($segments[10], ".") === FALSE && strpos($segments[10], ":") === FALSE) {
                $segments[10] .= ".00";
            }

            return array(
                "fltnum" => $segments[1],
                "dep" => $segments[2],
                "arr" => $segments[3],
                "duration" => Time::strToSecs(str_replace('.', ':', $segments[10])),
                "aircraftid" => $segments[5]
            );
        }, array_filter($routelines[0], function ($l) {
            return strlen(trim($l)) > 0;
        }));

        foreach ($data->routes as $r) {
            foreach ($r as $k => $v) {
                if (strlen($v) < 1) {
                    Session::flash('error', 'Invalid CSV File');
                    $this->get_import();
                }
            }
        }

        $this->render('admin/import_choose', $data);
    }

    private function import_import()
    {
        $routes = Input::get('rJson');
        $count = count(Json::decode($routes));
        $db = DB::getInstance();

        $allaircraft = Aircraft::fetchActiveAircraft()->results();
        $firstRank = $db->query("SELECT * FROM ranks ORDER BY timereq ASC LIMIT 1")->first()->id;

        for ($i = 0; $i < $count; $i++) {
            $item = Input::get('livery' . $i);
            if (empty($item)) continue;
            $aircraft = false;
            foreach ($allaircraft as $a) {
                if ($a->ifliveryid == $item) $aircraft = $a;
            }

            if ($aircraft === FALSE) {
                Aircraft::add($item, $firstRank);
                $aircraft = Aircraft::findAircraft($item);
                array_push($allaircraft, $aircraft);
            }

            $routes = str_replace(Input::get('rego' . $i), $aircraft->id, $routes);
        }

        $routes = Json::decode($routes);
        $lastId = Route::lastId();

        $sql = "INSERT INTO routes (id, fltnum, dep, arr, duration) VALUES";
        $params = array();
        $j = 0;
        foreach ($routes as $item) {
            $sql .= "\n(?, ?, ?, ?, ?),";
            array_push($params, $lastId + $j + 1);
            array_push($params, $item["fltnum"]);
            array_push($params, $item["dep"]);
            array_push($params, $item["arr"]);
            array_push($params, $item["duration"]);
            Route::addAircraft($lastId + $j + 1, $item["aircraftid"]);

            $j++;
        }

        $sql = trim($sql, ',');
        $ret = $db->query($sql, $params);
        if ($ret->error()) {
            foreach ($params as $pm) {
                $rpl = $pm;
                if (gettype($pm) == 'string') {
                    $rpl = "'{$pm}'";
                }
                $from = '/' . preg_quote('?', '/') . '/';
                $sql = preg_replace($from, $rpl, $sql, 1);
            }
            Session::flash('error', "Failed to Import Routes");
            $this->redirect('/admin/operations/routes/import');
        }

        Events::trigger('route/import');

        Session::flash('success', "Routes Imported Successfully!");
        $this->redirect('/admin/operations/routes');
    }
}
