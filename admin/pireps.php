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
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                        }
                        ?>
                        <h3>Manage PIREPs</h3>
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