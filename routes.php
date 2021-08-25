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
Page::excludeAsset('chartjs');

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
                    <?php if (empty(Input::get('action'))): ?>
                        <h3>Route Search</h3>
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
                    <?php elseif (Input::get('action') == 'search'): ?>
                        <h3>Route Search</h3>
                        <a href="routes.php" class="btn bg-custom mb-2">New Search</a>
                        <table class="table table-striped datatable-nosearch">
                            <thead class="bg-custom">
                                <tr>
                                    <th class="mobile-hidden">#</th>
                                    <th>Departure</th>
                                    <th>Arrival</th>
                                    <th class="mobile-hidden">Duration</th>
                                    <th class="mobile-hidden">Notes</th>
                                    <th><span class="mobile-hidden">Actions</span></th>
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
                                    $routes = $db->query($query, $stmts)->results();
                                    foreach ($routes as $route) {
                                        echo '<tr><td class="align-middle mobile-hidden">';
                                        echo $route->fltnum;
                                        echo '</td><td class="align-middle">';
                                        echo $route->dep;
                                        echo '</td><td class="align-middle">';
                                        echo $route->arr;
                                        echo '</td><td class="align-middle mobile-hidden">';
                                        echo Time::secsToString($route->duration);
                                        echo '</td><td class="align-middle mobile-hidden">';
                                        echo $route->notes;
                                        echo '</td><td class="align-middle">';
                                        $link = "pireps.php?page=new&fnum={$route->fltnum}&dep={$route->dep}&arr={$route->arr}";
                                        echo '<a href="'.$link.'" class="btn bg-custom"><i class="fa fa-plane"></i></a>&nbsp;';
                                        echo '<a href="/routes.php?action=route&route='.$route->id.'" class="btn bg-custom"><i class="fa fa-plus"></i></a>';
                                        echo '</td></tr>';
                                    }
                                ?>
                            </tbody>
                        </table>
                    <?php elseif (Input::get('action') == 'route'): ?>
                        <h3>View Route</h3>
                        <hr />
                        <?php 
                            $route = Route::find(Input::get('route')); 
                            if ($route !== FALSE) {
                                $aircraft = Route::aircraft(Input::get('route'));
                                $pireps = Route::pireps($route->fltnum);
                            }
                        ?>

                        <?php if ($route !== FALSE): ?>
                            <div class="row text-left">
                                <div class="col-md">
                                    <h4>Basic Info</h4>
                                    <ul>
                                        <li>
                                            <b>Flight Number:</b> <?= $route->fltnum ?>
                                        </li>
                                        <li>
                                            <b>Departure:</b> <?= $route->dep ?>
                                        </li>
                                        <li>
                                            <b>Arrival:</b> <?= $route->arr ?>
                                        </li>
                                        <li>
                                            <b>Approx. Duration:</b> <?= Time::secsToString($route->duration) ?>
                                        </li>
                                        <li>
                                            <b>Notes:</b> <?= empty($route->notes) ? 'N/A' : escape($route->notes) ?>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md">
                                    <h4>Aircraft</h4>
                                    <ul>
                                        <?php
                                            foreach ($aircraft as $a) {
                                                echo "<li>{$a->name} ({$a->liveryname})</li>";
                                            }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                            <hr />
                            <h4>Previous PIREPs</h4>
                            <table class="table table-striped datatable-nosearch">
                                <thead class="bg-custom">
                                    <tr>
                                        <th>Date</th>
                                        <th>Pilot</th>
                                        <th>Aircraft</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($pireps as $p) {
                                            echo '<tr><td>';
                                            echo $p->date;
                                            echo '</td><td>';
                                            echo $p->pilotname;
                                            echo '</td><td>';
                                            echo $p->aircraftname;
                                            echo '</td></tr>';
                                        }
                                    ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>Route Not Found</p>
                        <?php endif; ?>
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