<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('VA Stats - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('statsviewing') || !$user->hasPermission('admin')) {
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
                        <h3>VA Statistics</h3>
                        <table class="table">
                            <tr><td><b>Total Hours</b></td><td><?php echo Time::secsToString(Stats::totalHours()); ?></td></tr>
                            <tr><td><b>Total Flights</b></td><td><?php echo Stats::totalFlights(); ?></td></tr>
                            <tr><td><b>Total Pilots</b></td><td><?php echo Stats::numPilots(); ?></td></tr>
                            <tr><td><b>Total Routes</b></td><td><?php echo Stats::numRoutes(); ?></td></tr>
                        </table>
                        <hr />
                        <?php if (!$IS_GOLD): ?>
                            <p>
                                View vFinance Stats on <a href="https://vanet.app/airline/finance/">VANet</a>. 
                                Sign Up to VANet Gold in order to get access to VANet Stats right here.
                            </p>
                        <?php else: ?>
                            <h4>VANet Statistics</h4>
                            <?php $stats = VANet::getStats(); ?>
                            <table class="table">
                                <tr><td><b>Total Distance</b></td><td><?php echo $stats["totalDistance"]; ?>NM</td></tr>
                                <tr><td><b>Total Revenue</b></td><td>$<?php echo $stats["totalRevenue"]; ?></td></tr>
                            </table>
                        <?php endif; ?>
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