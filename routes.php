<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('Routes - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './includes/header.php'; ?>
</head>
<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php include './includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row">
            <?php include './includes/sidebar.php'; ?>
            <div class="col-lg-9 main-content">
                <div id="loader-wrapper"><div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div></div>
                <div class="loaded">
                    <h3>Route Search</h3>
                    <?php if (empty(Input::get('action'))): ?>
                        <form method="get">
                            <input hidden name="action" value="search" />
                            <div class="form-group">
                                <label for="dep">Departure ICAO</label>
                                <input type="text" class="form-control" name="dep" id="dep" placeholder="Leave Blank for Any" />
                            </div>
                            <div class="form-group">
                                <label for="arr">Arrival ICAO</label>
                                <input type="text" class="form-control" name="arr" id="arr" placeholder="Leave Blank for Any" />
                            </div>
                            <div class="form-group">
                                <label for="fltnum">Flight Number</label>
                                <input type="text" class="form-control" name="fltnum" id="fltnum" placeholder="Leave Blank for Any" />
                            </div>
                            <div class="form-group">
                                <label for="aircraft">Aircraft</label>
                                <select class="form-control" name="aircraft" id="aircraft">
                                    <option value="">Any Aircraft</option>
                                    <?php
                                        $aircraft = Aircraft::getAvailableAircraft($user->rank(null, true))->results();
                                        foreach ($aircraft as $ac) {
                                            $notes = $ac->notes == null ? '' : ' - '.$ac->notes;
                                            echo '<option value="'.$ac->id.'">'.$ac->name.' ('.$ac->liveryname.')'.$notes.'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="duration">Flight Time</label>
                                <select class="form-control" name="duration" id="duration">
                                    <option value="">Any Flight Time</option>
                                    <option value="0">&lt; 1hr</option>
                                    <option value="1">1-2hrs</option>
                                    <option value="2">2-3hrs</option>
                                    <option value="3">3-4hrs</option>
                                    <option value="4">4-5hrs</option>
                                    <option value="5">5-6hrs</option>
                                    <option value="6">6-7hrs</option>
                                    <option value="7">7-8hrs</option>
                                    <option value="9">8-9hrs</option>
                                    <option value="9">9-10hrs</option>
                                    <option value="10">10hrs+</option>
                                </select>
                            </div>
                            <input type="submit" class="btn bg-custom" value="Search" />
                        </form>
                    <?php else: ?>
                        <a href="routes.php" class="btn bg-custom mb-2">New Search</a>
                        <table class="table table-striped">
                            <thead class="bg-custom">
                                <tr>
                                    <th class="mobile-hidden">Flight Number</th>
                                    <th>Departure</th>
                                    <th>Arrival</th>
                                    <th class="mobile-hidden">Aircraft</th>
                                    <th class="mobile-hidden">Duration</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
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
                                        array_push($searchwhere, 'aircraftid = ?');
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
                                    $query = 'SELECT routes.fltnum, routes.dep, routes.arr, routes.duration, routes.id, routes.aircraftid, 
                                    aircraft.name AS aircraft, aircraft.liveryname AS livery FROM routes INNER JOIN aircraft ON aircraft.id = routes.aircraftid';
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
                                    $routes = $db->query($query, $stmts)->results();
                                    foreach ($routes as $route) {
                                        echo '<tr><td class="align-middle mobile-hidden">';
                                        echo $route->fltnum;
                                        echo '</td><td class="align-middle">';
                                        echo $route->dep;
                                        echo '</td><td class="align-middle">';
                                        echo $route->arr;
                                        echo '</td><td class="align-middle mobile-hidden">';
                                        echo $route->aircraft.' ('.$route->livery.')';
                                        echo '</td><td class="align-middle mobile-hidden">';
                                        echo Time::secsToString($route->duration);
                                        echo '</td><td class="align-middle">';
                                        $link = "pireps.php?page=new&fnum={$route->fltnum}&dep={$route->dep}&arr={$route->arr}&aircraft={$route->aircraft}";
                                        echo '<a href="'.$link.'" class="btn bg-custom"><i class="fa fa-plane"></i></a>';
                                        echo '</td></tr>';
                                    }
                                ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            </div>
            </div>
            <footer class="container-fluid text-center">
                <?php include './includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>
</html>