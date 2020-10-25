<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Site Dashboard - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('admin')) {
    Redirect::to('/home.php');
}

$ACTIVE_CATEGORY = 'site-management';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include '../includes/header.php'; ?>
</head>
<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php include '../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php include '../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper"><div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div></div>
                    <div class="loaded">
                        <?php
                            if (Session::exists('error')) {
                                echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                            }
                            if (Session::exists('success')) {
                                echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                            }
                        ?>
                        <h3>Admin Dashboard</h3>
                        <div class="row">
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">PIREPs (90 Days)</h5>
                                    <p><?= Stats::totalFlights(90) ?></p>
                                </div>
                            </div>
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">Hours (90 Days)</h5>
                                    <p><?= Time::secsToString(Stats::totalHours(90)) ?></p>
                                </div>
                            </div>
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">Pilot Applications (30 Days)</h5>
                                    <p><?= Stats::pilotsApplied(30) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">PIREPs Over Time</h5>
                                    <canvas id="pireps-chart"></canvas>
                                    <?php
                                        $chartdata = [];
                                        $chartlables = [];
                                        $pirepsAssoc = [];
                                        $allpireps = Pirep::fetchAll();
                                        
                                        foreach ($allpireps as $p) {
                                            if (!array_key_exists($p['date'], $pirepsAssoc)) {
                                                $pirepsAssoc[$p['date']] = 1;
                                            } else {
                                                $pirepsAssoc[$p['date']]++;
                                            }
                                        }

                                        foreach ($pirepsAssoc as $date => $count) {
                                            array_push($chartlables, $date);
                                            array_push($chartdata, $count);
                                        }
                                    ?>
                                    <script>
                                        var ctx = document.getElementById('pireps-chart').getContext('2d');
                                        var chart = new Chart(ctx, {
                                            type: 'line',
                                            data: {
                                                labels: <?= Json::encode($chartlables) ?>,
                                                datasets: [{
                                                    label: 'PIREPs',
                                                    borderColor: '<?= Config::get('site/colour_main_hex') ?>',
                                                    data: <?= Json::encode($chartdata) ?>
                                                }]
                                            },
                                            options: {}
                                        });
                                    </script>
                                </div>
                            </div>
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">Applications Over Time</h5>
                                    <canvas id="pilots-chart"></canvas>
                                    <?php
                                        $appchartdata = [];
                                        $appchartlables = [];
                                        $pilotsAssoc = [];
                                        $allpilots = $user->getAllUsers();
                                        
                                        foreach ($allpilots as $p) {
                                            if (!array_key_exists($p['joined'], $pilotsAssoc)) {
                                                $pilotsAssoc[$p['joined']] = 1;
                                            } else {
                                                $pilotsAssoc[$p['joined']]++;
                                            }
                                        }

                                        foreach ($pilotsAssoc as $date => $count) {
                                            array_push($appchartlables, $date);
                                            array_push($appchartdata, $count);
                                        }
                                    ?>
                                    <script>
                                        var ctx = document.getElementById('pilots-chart').getContext('2d');
                                        var chart = new Chart(ctx, {
                                            type: 'line',
                                            data: {
                                                labels: <?= Json::encode($appchartlables) ?>,
                                                datasets: [{
                                                    label: 'Pilot Applications',
                                                    borderColor: '<?= Config::get('site/colour_main_hex') ?>',
                                                    data: <?= Json::encode($appchartdata) ?>
                                                }]
                                            },
                                            options: {}
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">Pilot Leaderboard</h5>
                                    <p>By hours, past 7 days.</p>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Pilot</th>
                                                <th>Hours</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $top = Stats::pilotLeaderboard(5, 'flighttime');
                                                $i = 1;
                                                foreach ($top as $t) {
                                                    echo '<tr><td>';
                                                    echo $i;
                                                    echo '</td><td>';
                                                    echo $t->name;
                                                    echo '</td><td>';
                                                    echo Time::secsToString($t->flighttime);
                                                    echo '</td></tr>';
                                                    $i++;
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php include '../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $(".<?= $ACTIVE_CATEGORY ?>").collapse('show');
        });
    </script>
</body>
</html>