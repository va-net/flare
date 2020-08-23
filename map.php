<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('Live Map - '.Config::get('va/name'));

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
    <nav class="navbar navbar-expand-lg navbar-dark bg-custom">
        <?php include './includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <div class="col-lg-3 p-3 bg-light text-left mobile-hidden" id="desktopMenu" style="height: 100%;">
                    <h3>Pilot Panel - <?= $user->data()->callsign ?></h3>
                    <hr class="mt-0 divider" />
                    <a href="home.php" id="homelink" class="panel-link"><i class="fa fa-home"></i>&nbsp;Pilot Home</a><br>
                    <a href="pireps.php?page=new" id="filepireplink" class="panel-link"><i class="fa fa-plane"></i>&nbsp;File PIREP</a><br>
                    <a href="pireps.php?page=recents" id="mypirepslink" class="panel-link"><i class="fa fa-folder"></i>&nbsp;My PIREPs</a><br>
                    <a href="routes.php" id="routeslink" class="panel-link"><i class="fa fa-database"></i>&nbsp;Route Database</a><br>
                    <a href="acars.php" id="acarslink" class="panel-link"><i class="fa fa-sync"></i>&nbsp;ACARS</a><br>
                    <?php
                    $permissions = Permissions::getAll();

                    if ($user->hasPermission('admin')) {
                        echo '<br>';
                        echo '<h3>Admin Panel</h3>';
                        echo '<hr class="mt-0 divider">';
                        foreach ($permissions as $permission => $data) {
                            if ($user->hasPermission($permission)) {
                                if ($permission == 'opsmanage') {
                                    echo '
                                    <a href="#" data-toggle="collapse" data-target="#demo" class="panel-link"><i class="fa fa-caret-down"></i>&nbsp;Operations Management</a><br>
                                    <div id="demo" class="collapse">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-medal"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=ranks" class="panel-link">Manage Ranks</a><br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plane"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=fleet" class="panel-link">Manage Fleet</a><br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plane-departure"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=routes" class="panel-link">Manage Routes</a><br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-globe"></i>&nbsp;<a href="./admin.php?page=site" class="panel-link">Manage Site</a><br>
                                    </div>
                                    ';
                                } else {
                                    echo '<a href="admin.php?page='.$permission.'" id="userslink" class="panel-link"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$data['name'].'</a><br>';
                                }
                            }
                        }
                    }
                    ?>
                    <br>
                    <a href="logout.php" class="panel-link"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
                </div>
                <div class="col-lg-9 p-3 main-content">
                <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    <div class="tab-content" id="tc">
                        <div class="tab-pane container active" id="home" style="display: none;">
                            <h3>Live Map</h3>
                            <p>Here you can view all the pilots that are currently flying through the Infinite Flight skies, using their <?= Config::get('va/name') ?> callsign.</p>
                            <iframe src='https://ifvarb.com/liveflightmap.php?callsign=<?= Config::get('va/identifier') ?>&color=red&apikey=b48e66-79248c-d11549-090d5f-b39ea4' width='100%;' height='600px;' frameborder='0' scrolling='no'></iframe>
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