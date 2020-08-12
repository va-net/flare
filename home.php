<?php
require_once './core/init.php';

$user = new User();

Page::setTitle('Home - '.$user->data()->callsign);

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
    <style>
        #loader {
        position: absolute;
        left: 50%;
        top: 50%;
        z-index: 1;
        width: 150px;
        height: 150px;
        margin: -75px 0 0 -75px;
        width: 120px;
        height: 120px;
        }
    </style>

    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #E4181E;">
        <?php include './includes/navbar.php'; ?>
    </nav>

    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
        <div class="row m-0 p-0">
            <div class="col-lg-3 p-3 bg-light text-left mobile-hidden" id="desktopMenu" style="height: 100%;">
                <h3>Pilot Panel - <?= escape($user->data()->callsign) ?></h3>
                <hr class="mt-0 divider" />
                <a href="home.php" id="homelink" class="panel-link"><i class="fa fa-home"></i>&nbsp;Pilot Home</a><br>
                <a href="pireps.php#filepirep" id="filepireplink" class="panel-link"><i class="fa fa-plane"></i>&nbsp;File PIREP</a><br>
                <a href="pireps.php#mypireps" id="mypirepslink" class="panel-link"><i class="fa fa-folder"></i>&nbsp;My PIREPs</a><br>
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
                                &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-globe"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=site" class="panel-link">Manage Site</a>
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
                <div id="loader" class="spinner-border spinner-border-sm text-danger"></div>
                    <div class="tab-content" id="tc">
                        <div class="tab-pane container active" id="home" style="display: none;">
                            <h3>Pilot Home</h3>
                            <p>Welcome to the <?= escape(Config::get('va/name')) ?> crew center, <?= escape($user->data()->name) ?>!</p>
                            <?php
                            if (Session::exists('errormain')) {
                                echo '<div class="alert alert-danger text-center">Error: '.Session::flash('errormain').'</div>';
                            }
                            if (Session::exists('successmain')) {
                                echo '<div class="alert alert-success text-center">'.Session::flash('successmain').'</div>';
                            }
                            ?>
                            <!-- profile -->
                            <section id="profile">
                                <h3>Your Profile</h3>
                                <?php
                                if (Session::exists('error')) {
                                    echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                                }
                                if (Session::exists('success')) {
                                    echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                                }
                                ?>
                                <button type="button" class="btn bg-custom mb-2" data-toggle="modal" data-target="#editMyProfile">Edit Profile</button>
                                <button type="button" class="btn bg-custom mb-2" data-toggle="modal" data-target="#changePassword">Change Password</button>
                                <!-- edit profile form -->
                                <div id="editMyProfile" class="modal fade" role="dialog">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Edit Profile</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="update.php" method="post">
                                                    <input type="hidden" name="action" value="editprofile">
                                                    <div class="form-group">
                                                        <label for="name">Name</label>
                                                        <input type="text" maxlegnth="120" name="name" id="name" class="form-control" required value="<?= escape($user->data()->name) ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="callsign">Callsign</label>
                                                        <input type="text" maxlegnth="8" name="callsign" id="callsign" class="form-control" required value="<?= escape($user->data()->callsign) ?>"> 
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="email">Email</label>
                                                        <input type="email" name="email" id="email" class="form-control" required value="<?= escape($user->data()->email) ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="ifc">IFC URL</label>
                                                        <input type="url" name="ifc" id="ifc" class="form-control" required value="<?= escape($user->data()->ifc) ?>">
                                                    </div>
                                                    <input type="submit" class="btn bg-custom" value="Edit Profile">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- change password form -->
                                <div id="changePassword" class="modal fade" role="dialog">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Change Password</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="update.php" method="post">
                                                    <input type="hidden" name="action" value="changepass">
                                                    <div class="form-group">
                                                        <label for="oldpass">Old Password</label>
                                                        <input type="password" name="oldpass" id="oldpass" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="newpass">New Password</label>
                                                        <input type="password" minlenght="8" name="newpass" id="newpass" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="confpass">Confirm New Password</label>
                                                        <input type="password" name="confpass" id="confpass" class="form-control" required>
                                                    </div>
                                                    <p id="cpError" class="text-danger"></p>
                                                    <input type="submit" class="btn bg-custom" id="cpSubmit" value="Change Password">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <!-- stats table -->
                            <section id="stats">
                                <table class="table mb-0 border-bottom">
                                    <tr>
                                        <td class="align-middle"><b>Name</b></td>
                                        <td class="align-middle"><?= escape($user->data()->name) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle"><b>Callsign</b></td>
                                        <td class="align-middle"><?= escape($user->data()->callsign) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle"><b>Flight Time</b></td>
                                        <td class="align-middle"><?= escape($user->getFlightTime()) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle"><b>Rank</b></td>
                                        <td class="align-middle"><?= escape($user->rank()) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle"><b>PIREPs</b></td>
                                        <td class="align-middle"><?= escape($user->numPirepsFiled()) ?></td>
                                    </tr>
                                </table>
                                <br />
                            </section>  
                            <!-- news -->
                            <section id="news">
                                <h3>News Feed</h3>
                                <br>
                                <?php
                                $news = News::get();

                                if ($news === array()) {
                                    echo 'No news';
                                }

                                foreach ($news as $article) {
                                    echo '<div class="card mb-3">';
                                    echo '<div class="card-body">';
                                    echo '<h5 class="card-title"><u>'.$article['title'].'</u></h5>';
                                    echo '<p><small><i class="fa fa-user"></i> '.$article['author'].'&nbsp;&nbsp;&nbsp;<i class="fa fa-clock"></i> '.date_format(date_create($article['dateposted']), 'Y-m-d').'</small></p>';
                                    echo '<p class="card-text">'.$article['content'].'</p>';
                                    echo '</div></div>';
                                }
                                ?>
                                <br>
                                <br>
                            </section>
                            <!-- pireps -->
                            <section id="pireps">
                                <h3>Your Recent PIREPs</h3>
                                <br>
                                <table class="table table-striped">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th class="mobile-hidden">Flight Number</th>
                                            <th>Route</th>
                                            <th class="mobile-hidden">Date</th>
                                            <th class="mobile-hidden">Aircraft</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $pireps = $user->recentPireps();

                                        foreach ($pireps as $pirep) {
                                            echo '<tr><td class="mobile-hidden align-middle">';
                                            echo $pirep["number"];
                                            echo '</td><td class="align-middle">';
                                            echo $pirep["departure"].'-'.$pirep["arrival"];
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            echo date_format(date_create($pirep['date']), 'Y-m-d');
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            echo $pirep["aircraft"];
                                            echo '</td><td class="align-middle">';
                                            echo $pirep["status"];
                                            echo '</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </section>
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