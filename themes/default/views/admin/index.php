<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Site Dashboard - ' . Page::$pageData->va_name);
$days = Page::$pageData->days;
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
        <div class="mt-4 text-center container-fluid" style="overflow: auto;">
            <div class="p-0 m-0 row">
                <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <?php
                        if (file_exists(__DIR__ . '/../install/install.php') && !file_exists(__DIR__ . '/../.development')) {
                            echo '<div class="text-center alert alert-danger">The Install Folder still Exists! Please delete it immediately, it poses a severe security risk.</div>';
                        }

                        if (Session::exists('error')) {
                            echo '<div class="text-center alert alert-danger">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="text-center alert alert-success">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <h3>Admin Dashboard</h3>
                        <form class="form-inline" method="get">
                            <label for="days">Time Period (days)</label>
                            <input type="number" class="mx-2 form-control" id="days" name="days" value="<?= $days ?>">
                            <button type="submit" class="btn bg-custom">View</button>
                        </form>
                        <div class="row">
                            <div class="p-3 col-lg">
                                <div class="p-3 shadow card h-100">
                                    <h5 class="font-weight-bold">PIREPs (<?= $days ?> Days)</h5>
                                    <p><?= Page::$pageData->pireps ?></p>
                                </div>
                            </div>
                            <div class="p-3 col-lg">
                                <div class="p-3 shadow card h-100">
                                    <h5 class="font-weight-bold">Hours (<?= $days ?> Days)</h5>
                                    <p><?= Page::$pageData->hrs ?></p>
                                </div>
                            </div>
                            <div class="p-3 col-lg">
                                <div class="p-3 shadow card h-100">
                                    <h5 class="font-weight-bold">Pilot Applications (<?= $days ?> Days)</h5>
                                    <p><?= Page::$pageData->pilots ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="p-3 col-lg">
                                <div class="p-3 shadow card h-100">
                                    <h5 class="font-weight-bold">PIREPs (<?= $days ?> Days)</h5>
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
                            <div class="p-3 col-lg">
                                <div class="p-3 shadow card h-100">
                                    <h5 class="font-weight-bold">Pilot Applications (<?= $days ?> Days)</h5>
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
                            <div class="p-3 col-lg">
                                <div class="p-3 shadow card h-100">
                                    <h5 class="font-weight-bold">Pilot Leaderboard</h5>
                                    <p>Past <?= $days ?> days.</p>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Pilot</th>
                                                <th>Hours</th>
                                                <th>PIREPs</th>
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
                                                echo '</td><td>';
                                                echo $t->flightcount;
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
            <footer class="text-center container-fluid">
                <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $(".<?= Page::$pageData->active_dropdown ?>").collapse('show');
        });
    </script>
</body>

</html>