<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('PIREPs Admin - '.Config::get('va/name'));
Page::excludeAsset('chartjs');

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('pirepmanage') || !$user->hasPermission('admin')) {
    Redirect::to('/home.php');
}

$ACTIVE_CATEGORY = 'pirep-management';
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
                        if (file_exists(__DIR__.'/../install/install.php') && !file_exists(__DIR__.'/../.development')) {
                            echo '<div class="alert alert-danger text-center">The Install Folder still Exists! Please delete it immediately, it poses a severe security risk.</div>';
                        }
                        
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                        }

                        $tab = "pending";
                        if (!empty(Input::get('tab'))) {
                            $tab = Input::get('tab');
                        }
                        $ACTIVE_CATEGORY = 'site-management'; 
                        ?>
                        <script>
                            $(document).ready(function() {
                                $("#<?= $tab; ?>link").click();
                            });
                        </script>
                        <h3>Manage PIREPs</h3>
                        <ul class="nav nav-tabs nav-dark justify-content-center">
                            <li class="nav-item">
                                <a class="nav-link" id="pendinglink" data-toggle="tab" href="#pending">Pending PIREPs</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="alllink" data-toggle="tab" href="#all">All PIREPs</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div id="pending" class="tab-pane container-fluid p-3 fade">
                                <form id="acceptpirep" action="/update.php" method="post">
                                    <input hidden name="action" value="acceptpirep">
                                </form>
                                <form id="declinepirep" action="/update.php" method="post">
                                    <input hidden name="action" value="declinepirep">
                                </form>
                                <table class="table table-striped">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th class="mobile-hidden">Callsign</th>
                                            <th class="mobile-hidden">Flight Number</th>
                                            <th>Dep<span class="mobile-hidden">arture</span></th>
                                            <th>Arr<span class="mobile-hidden">ival</span></th>
                                            <th>Flight Time</th>
                                            <th class="mobile-hidden">Multiplier</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $x = 0;
                                        $pireps = Pirep::fetchPending();
                                        foreach ($pireps as $pirep) {
                                            echo '<tr><td class="align-middle mobile-hidden">';
                                            $callsign = $user->idToCallsign($pirep['pilotid']);
                                            echo $callsign;
                                            echo '</td><td class="align-middle mobile-hidden">';
                                            echo $pirep['flightnum'];
                                            echo '</td><td class="align-middle">';
                                            echo $pirep['departure'];
                                            echo '</td><td class="align-middle">';
                                            echo $pirep['arrival'];
                                            echo '</td><td class="align-middle">';
                                            echo Time::secsToString($pirep["flighttime"]);
                                            echo '</td><td class="align-middle mobile-hidden">';
                                            echo $pirep["multi"];
                                            echo '</td><td class="align-middle">';
                                            echo '<button class="btn btn-success text-light" value="'.$pirep['id'].'" form="acceptpirep" type="submit" name="accept"><i class="fa fa-check"></i></button>';
                                            echo '&nbsp;<button value="'.$pirep['id'].'" form="declinepirep" type="submit" class="btn btn-danger text-light" name="decline"><i class="fa fa-times"></i></button>';
                                            echo '</td>';
                                            $x++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="all" class="tab-pane container-fluid p-3 fade">
                                <table class="table table-striped datatable">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th>Date</th>
                                            <th class="mobile-hidden">Pilot</th>
                                            <th>Dep<span class="mobile-hidden">arture</span></th>
                                            <th>Arr<span class="mobile-hidden">ival</span></th>
                                            <th class="mobile-hidden">Aircraft</th>
                                            <th><span class="mobile-hidden">Actions</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $allpireps = Pirep::fetchAll();
                                            $statuses = [
                                                [
                                                    "badge" => "warning",
                                                    "text" => "Pending",
                                                ],
                                                [
                                                    "badge" => "success",
                                                    "text" => "Accepted",
                                                ],
                                                [
                                                    "badge" => "danger",
                                                    "text" => "Denied"
                                                ],
                                            ];
                                            foreach ($allpireps as $a) {
                                                echo '<tr><td class="align-middle">';
                                                echo $a['date'];
                                                echo '</td><td class="align-middle mobile-hidden">';
                                                echo $a['pilotname'];
                                                echo '</td><td class="align-middle">';
                                                echo $a['departure'];
                                                echo '</td><td class="align-middle">';
                                                echo $a['arrival'];
                                                echo '</td><td class="align-middle mobile-hidden">';
                                                echo $a['aircraftname'];
                                                echo '</td><td class="align-middle">';
                                                echo 'SOON';
                                                echo '</td></tr>';
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .nav-tabs .nav-link {
                    color: #000!important;
                }
            </style>
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