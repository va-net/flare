<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('ACARS - '.$user->data()->callsign);

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
                                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plane"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=fleet" class="panel-link">Manage Fleet</a><br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plane-departure"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=routes" class="panel-link">Manage Routes</a><br>
                                    
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
                            <h3>ACARS</h3>
                            <?php if (VANet::isGold()): ?>
                                <?php if ($user->data()->ifuserid != null): ?>
                                    <?php
                                    $curl = new Curl;
                                    $request = $curl->get(Config::get('vanet/base_url').'/api/acars', array(
                                        'callsign' => $user->data()->callsign,
                                        'userid' => $user->data()->ifuserid,
                                        'server' => 'expert',
                                        'apikey' => Config::get('vanet/api_key')
                                    ));
                                    $response = Json::decode($request->body);
                                    if (array_key_exists('status', $response)) {
                                        if ($response['status'] == 404 || $response['status'] == 409) {
                                            echo 'Hmm, looks like we couldn\'t find you on the server. Ensure that you have filed a flightplan, and are still connected to Infinite Flight. Then, refresh the page.';
                                        }
                                    } else {
                                        echo 'Nice! We\'ve found you. Once you\'ve completed your flight, come back here and click the button below.';
                                    }
                                    ?>
                                <?php else: ?>
                                    <p>Looks like you haven't yet setup PIREPs! Go to <a href="./pireps.php">pireps</a>, set them up, and then come back here and try again.</p>
                                <?php endif ?> 
                            <?php else: ?>
                                <p>Hmm. In order to run ACARS, <?= Config::get('va/name') ?> needs to sign up to VANet Gold. You can take a look at it <a href="https://vanet.app/getstarted" target="_blank">here</a>!</p>
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