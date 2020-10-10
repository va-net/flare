<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('Home - '.Config::get('va/name'));

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

    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php include './includes/navbar.php'; ?>
    </nav>

    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
        <div class="row m-0 p-0">
            <?php include './includes/sidebar.php'; ?>
            <div class="col-lg-9 main-content">
                <div id="loader-wrapper"><div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div></div>
                <div class="loaded">
                    <h3>Pilot Home</h3>
                    <p>Welcome to the <?= escape(Config::get('va/name')) ?> Crew Center, <?= escape($user->data()->name) ?>!</p>
                    <?php
                    if (Session::exists('errormain')) {
                        echo '<div class="alert alert-danger text-center">Error: '.Session::flash('errormain').'</div>';
                    }
                    if (Session::exists('successmain')) {
                        echo '<div class="alert alert-success text-center">'.Session::flash('successmain').'</div>';
                    }
                    ?>
                    <!-- profile -->
                    <section id="profile" class="mb-2">
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
                                                <input type="password" minlengh="8" name="newpass" id="newpass" class="form-control" required>
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
                    <section id="stats" class="mb-2">
                        <table class="table mb-0 border-bottom">
                            <tr>
                                <td class="align-middle"><b>Name</b></td>
                                <td class="align-middle"><?= escape($user->data()->name) ?></td>
                            </tr>
                            <tr>
                                <td class="align-middle"><b>IFC Profile</b></td>
                                <?php
                                $username = explode('/', $user->data()->ifc);
                                ?>
                                <td class="align-middle"><a href="<?= $user->data()->ifc ?>" target="_blank"><?= escape($username[4]) ?></td>
                            </tr>
                            <tr>
                                <td class="align-middle"><b>Callsign</b></td>
                                <td class="align-middle"><?= escape($user->data()->callsign) ?></td>
                            </tr>
                            <tr>
                                <td class="align-middle"><b>Flight Time</b></td>
                                <td class="align-middle"><?= escape(Time::secsToString($user->getFlightTime())) ?></td>
                            </tr>
                            <tr>
                                <td class="align-middle"><b>Rank</b></td>
                                <?php 
                                    $next = $user->nextrank(); 
                                    $tip = "The Top Rank!";
                                    if ($next != null) {
                                        $hrs = $next->timereq / 3600;
                                        $tip = "Next Rank: {$next->name} ({$hrs}hrs)";
                                    }
                                ?>
                                <td class="align-middle" data-toggle="tooltip" title="<?= $tip ?>"><?= escape($user->rank()) ?></td>
                            </tr>
                            <tr>
                                <td class="align-middle"><b>PIREPs</b></td>
                                <td class="align-middle"><?= escape($user->numPirepsFiled()) ?></td>
                            </tr>
                        </table>
                    </section>  
                    <!-- news -->
                    <section id="news" class="mb-3">
                        <h3>News Feed</h3>
                        <?php
                        $news = News::get();

                        if ($news === array()) {
                            echo 'No News';
                        } else {
                            foreach ($news as $article) {
                                echo '<div class="card mb-3">';
                                echo '<div class="card-body">';
                                echo '<h5 class="card-title"><u>'.$article['title'].'</u></h5>';
                                echo '<p><small><i class="fa fa-user"></i> '.$article['author'].'&nbsp;&nbsp;&nbsp;<i class="fa fa-clock"></i> '.date_format(date_create($article['dateposted']), 'Y-m-d').'</small></p>';
                                echo '<p class="card-text">'.$article['content'].'</p>';
                                echo '</div></div>';
                            }
                        }
                        ?>
                    </section>
                    <!-- pireps -->
                    <section id="pireps" class="mb-3">
                        <h3>Your Recent PIREPs</h3>
                        <?php $pireps = $user->recentPireps(); ?>
                        <?php if ($pireps): ?>
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
                        <?php else: ?>
                            <?= 'No Recent PIREPs' ?>
                        <?php endif; ?>
                    </section>  
                    <?php if ($IS_GOLD): ?>
                        <!-- events -->
                        <section id="events" class="mb-3">
                            <h3>Upcoming Events</h3>
                            <table class="table table-striped text-center">
                                <thead class="bg-custom">
                                    <tr>
                                        <th>Name</th>
                                        <th>Airport</th>
                                        <th>View</th>
                                    </tr>
                                </thead>
                                <tbody id="events-table">
                                    <tr><td colspan="3">Loading...</td></tr>
                                </tbody>
                            </table>
                            <script>
                                $.post("vanet.php", {
                                    "method": "events-table"
                                }, function (data, status) {
                                    $("#events-table").html(data);
                                });
                            </script>
                        </section>
                    <?php endif; ?>
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