<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Site Dashboard - ' . Page::$pageData->va_name);
$ACTIVE_CATEGORY = 'site-management';
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once __DIR__ . '/../../includes/header.php'; ?>
</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <?php
                        if (file_exists(__DIR__ . '/../install/install.php') && !file_exists(__DIR__ . '/../.development')) {
                            echo '<div class="alert alert-danger text-center">The Install Folder still Exists! Please delete it immediately, it poses a severe security risk.</div>';
                        }

                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <h3>Admin Dashboard</h3>
                        <div class="row">
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">PIREPs (90 Days)</h5>
                                    <p><?= Page::$pageData->pireps_90 ?></p>
                                </div>
                            </div>
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">Hours (90 Days)</h5>
                                    <p><?= Page::$pageData->hrs_90 ?></p>
                                </div>
                            </div>
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">Pilot Applications (90 Days)</h5>
                                    <p><?= Page::$pageData->pilots_90 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">PIREPs (30 Days)</h5>
                                    <canvas id="pireps-chart"></canvas>
                                    <script>
                                        var ctx = document.getElementById('pireps-chart').getContext('2d');
                                        var chart = new Chart(ctx, {
                                            type: 'line',
                                            data: {
                                                labels: <?= Json::encode(Page::$pageData->pireps_chart_labels) ?>,
                                                datasets: [{
                                                    label: 'PIREPs',
                                                    borderColor: '<?= Page::$pageData->va_color ?>',
                                                    data: <?= Json::encode(Page::$pageData->pireps_chart_data) ?>
                                                }]
                                            },
                                            options: {}
                                        });
                                    </script>
                                </div>
                            </div>
                            <div class="col-lg p-3">
                                <div class="card p-3 shadow h-100">
                                    <h5 class="font-weight-bold">Pilot Applications (30 Days)</h5>
                                    <canvas id="pilots-chart"></canvas>
                                    <script>
                                        var ctx = document.getElementById('pilots-chart').getContext('2d');
                                        var chart = new Chart(ctx, {
                                            type: 'line',
                                            data: {
                                                labels: <?= Json::encode(Page::$pageData->pilots_chart_labels) ?>,
                                                datasets: [{
                                                    label: 'Pilot Applications',
                                                    borderColor: '<?= Page::$pageData->va_color ?>',
                                                    data: <?= Json::encode(Page::$pageData->pilots_chart_data) ?>
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
                                            $i = 1;
                                            foreach (Page::$pageData->leaderboard as $t) {
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
                <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
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