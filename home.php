
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require './includes/header.php'; 
require_once './core/init.php';

$user = new User();
?>

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
<script>
$(document).ready(function() {
    window.setTimeout(function() {
        $("#loader").fadeOut(500, function() {
            $("#home").fadeIn(400);
        });
    }, 500);
});
</script>

<?php if (isset($_SESSION["darkmode"]) && $_SESSION["darkmode"] == true) { ?>
<script>
    $(document).ready(function() {
        $("body").addClass("bg-dark");
        $("body").addClass("text-light");
        $("#desktopMenu").removeClass("bg-light");
        $("#desktopMenu").addClass("bg-dark");
        $(".panel-link").addClass("panel-link-dark");
        $(".panel-link-dark").removeClass("panel-link");
        $("#desktopMenu").addClass("border");
        $("#desktopMenu").addClass("border-white");
        $("table").addClass("text-light");
        $(".card").addClass("bg-dark");
        $(".divider").addClass("divider-dark");
        $(".divider-dark").removeClass("divider");
        $("*").on("shown.bs.modal", function() {
            $(".modal-content").addClass("bg-dark");
            $("table").addClass("text-light");
        });
    });
</script>

<?php } ?>
<div class="container-fluid mt-4 text-center" style="overflow: auto;">
<div class="row m-0 p-0">
    <div class="col-lg-3 p-3 bg-light text-left mobile-hidden" id="desktopMenu" style="height: 100%;">
        <h3>Pilot Panel</h3>
        <hr class="mt-0 divider" />
        <a href="#home" id="homelink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-home"></i>&nbsp;Pilot Home</a><br />
        <a href="#filepirep" id="filepireplink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-plane"></i>&nbsp;File PIREP</a><br />
        <a href="#mypireps" id="mypirepslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-folder"></i>&nbsp;My PIREPs</a><br />
        <a href="#routedb" id="routeslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-database"></i>&nbsp;Route Database</a><br />
        <a href="#featured" id="featuredlink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-map-marked-alt"></i>&nbsp;Featured Routes</a><br />
        <a href="#events" id="eventslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-calendar"></i>&nbsp;Events</a><br />
        <a href="#acars" id="acarslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-sync"></i>&nbsp;ACARS</a><br />
        <a href="assets/StandardOperatingProcedures.pdf" id="soplink" class="panel-link" target="_blank"><i class="fa fa-file-download"></i>&nbsp;Handbook</a><br /><br />
        
        <?php

        $permissions = array(
            'usermanage' => array(
                'name' => 'User Management',
                'icon' =>'fa-users'
            ), 
            'staffmanage' => array(
                'name' => 'Staff Management',
                'icon' => 'fa-user-shield'
            ),
            'recruitment' => array(
                'name' => 'Recruitment',
                'icon' => 'fa-id-card'
            ), 
            'pirepmanage' => array(
                'name' => 'PIREP Management',
                'icon' => 'fa-plane'
            ), 
            'newsmanage' => array(
                'name' => 'News Management',
                'icon' => 'fa-newspaper'
            ), 
            'emailpilots' => array(
                'name' => 'Email Pilots',
                'icon' => 'fa-envelope'
            ), 
            'opsmanage' => array(
                'name' => 'Operations Management',
                'icon' => 'fa-file-alt'
            ), 
            'statsviewing' => array(
                'name' => 'VA Statistics',
                'icon' => 'fa-chart-pie'
            )
        );

        foreach ($permissions as $permission => $data) {
            if ($user->hasPermission($permission)) {
                echo '<a href="admin.php#'.$permission.'" id="userslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$data['name'].'</a><br>';
            }
        }

        ?>
        <a href="logout.php" class="panel-link"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
    </div>
    <div class="col-lg-9 p-3 main-content">
    <div id="loader" class="spinner-border spinner-border-sm text-danger"></div>
    <div class="tab-content" id="tc">
        <div class="tab-pane container active" id="home" style="display: none;">
            <h3>Pilot Home</h3>
            <p>Welcome to the <?= Config::get('va/name'); ?> crew center, <?= $user->data()->name; ?>!</p>

            <h3>Your Profile</h3>
            <?php

                if (Session::exists('error')) {
                    echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                }
                if (Session::exists('success')) {
                    echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                }

            ?>
            <button type="button" class="btn bg-virgin mb-2" data-toggle="modal" data-target="#editMyProfile">Edit Profile</button>
            <button type="button" class="btn bg-virgin mb-2" data-toggle="modal" data-target="#changePassword">Change Password</button>

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
                            <input type="text" maxlegnth="120" name="name" id="name" class="form-control" required value="<?= $user->data()->name ?>">
                        </div>
                        <div class="form-group">
                            <label for="callsign">Callsign</label>
                            <input type="text" maxlegnth="8" name="callsign" id="callsign" class="form-control" required value="<?= $user->data()->callsign ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required value="<?= $user->data()->email ?>">
                        </div>
                        <div class="form-group">
                            <label for="ifc">IFC URL</label>
                            <input type="url" name="ifc" id="ifc" class="form-control" required value="<?= $user->data()->ifc ?>">
                        </div>
                        <input type="submit" class="btn bg-virgin" value="Edit Profile">
                    </form>
                </div>
                </div>
            </div>
            </div>

            <div id="changePassword" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Change Password</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="update.php" method="post">
                        <script>
                            $(document).ready(function() {
                                $("#confpass").change(function() {
                                    if ($("#confpass").val() != $("#newpass").val()) {
                                        $("#cpError").html("Passwords Do Not Match");
                                        $("#cpSubmit").attr("disabled", true);
                                    } else {
                                        $("#cpError").html("");
                                        $("#cpSubmit").attr("disabled", false);
                                    }
                                });
                            });
                        </script>
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
                        <input type="submit" class="btn bg-virgin" id="cpSubmit" value="Change Password" disabled>
                    </form>
                </div>
                </div>
            </div>
            </div>
            <table class="table mb-0 border-bottom">
            <tr><td class="align-middle"><b>Name</b></td><td class="align-middle"><?= $user->data()->name ?></td></tr>
                <tr><td class="align-middle"><b>Callsign</b></td><td class="align-middle"><?= $user->data()->callsign ?></td></tr>
                <tr><td class="align-middle"><b>Flight Time</b></td><td class="align-middle"><?= $user->data()->transhours ?></td></tr>
                <tr><td class="align-middle"><b>Rank</b></td><td class="align-middle"><?= $user->rank() ?></td></tr>
                <tr><td class="align-middle"><b>PIREPs</b></td><td class="align-middle"><?= $user->pireps() ?></td></tr>
            </table>
            <br />

            <h3>News Feed</h3>
            <?php
            $news = News::get();

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

            <h3>Your Recent PIREPs</h3>
            <table class="table table-striped">
                <thead class="bg-virgin"><tr><th class="mobile-hidden">Flight Number</th><th>Route</th><th class="mobile-hidden">Date</th><th class="mobile-hidden">Aircraft</th><th>Status</th></tr></thead>
                <tbody>
                    <?php
                        $pireps = Pirep::recents();

                        foreach ($pireps as $pirep) {
                            echo '<tr><td class="mobile-hidden align-middle">';
                            echo $pirep["type"].$pirep["number"];
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
        </div>
<?php

include './includes/footer.php';