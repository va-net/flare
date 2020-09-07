<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('Admin Panel - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
} elseif (!$user->hasPermission('admin')) {
    Session::flash('errormain', 'You don\'t have permission to access this!');
    Redirect::to('home.php');
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

    <nav class="navbar navbar-expand-lg navbar-dark bg-custom">
        <?php include './includes/navbar.php'; ?>
    </nav>

    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
        <div class="row m-0 p-0">
            <?php include './includes/sidebar.php'; ?>
            <div class="col-lg-9 p-3 main-content">
                <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    <div class="tab-content" id="tc">
                        <div class="tab-pane container active" id="home" style="display: none;">
                            <?php
                            if (file_exists('./install/install.php')) {
                                Session::flash('error', '<b>The Install Folder still exists! Please delete this <u>immediately</u>, as this poses a Severe Security Risk!</b>');
                            }
                            if (Session::exists('error')) {
                                echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                            }
                            if (Session::exists('success')) {
                                echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                            }
                            ?>
                            <?php if (Input::get('page') == ''): ?>
                                <h3>Admin Panel</h3>
                                <p>Welcome to the Admin Panel. Here you can find the administration tools required to manage <?= escape(Config::get('va/name')) ?></p>
                                <p>Looks like no page was specified. Make sure you use the buttons in the navbar/sidebar!</p>
                            <?php endif; ?>
                            <?php if (Input::get('page') === 'usermanage'): ?>
                                <script>
                                    $(document).ready(function() {
                                        $(".usrCollapse").each(function() {
                                            $(this).collapse("show");
                                        });
                                    });
                                </script>
                                <h3>Manage Users</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    <p>Here you can view all users, active and inactive. Click on a user to view/edit the information.</p>
                                    <table class="table table-striped">
                                        <thead class="bg-custom">
                                            <tr>
                                                <th>Callsign</th>
                                                <th class="mobile-hidden">Name</th>
                                                <th class="mobile-hidden">Email</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $users = $user->getAllUsers();
                                        $x = 0;
                                        foreach ($users as $user) {
                                            echo '<tr><td class="align-middle">';
                                            echo $user["callsign"];
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            echo $user["name"];
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            echo $user["email"];
                                            echo '</td><td class="align-middle">';
                                            echo $user["status"];
                                            echo '</td><td class="align-middle">';
                                            echo '<button class="btn btn-primary text-light userEdit" data-callsign="'.$user['callsign'].'" 
                                            data-name="'.$user['name'].'" data-email="'.$user['email'].'" data-ifc="'.$user['ifc'].'" 
                                            data-joined="'.date_format(date_create($user['joined']), 'Y-m-d').'" data-status="'.$user['status'].'" 
                                            data-id="'.$user['id'].'" data-thrs="'.Time::secsToString($user["transhours"]).'" 
                                            data-admin="'.$user['isAdmin'].'" data-tflts="'.$user["transflights"].'"><i class="fa fa-edit"></i>
                                            </button>';
                                            echo '&nbsp;<button id="delconfirmbtn" class="btn text-light btn-danger" 
                                            data-toggle="modal" data-target="#delconfirmmodal" 
                                            data-callsign="'.$user['callsign'].'" data-id="'.$user['id'].'">
                                            <i class="fa fa-trash"></i></button>';
                                            echo '</td>';
                                            $x++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <div class="modal fade" id="delconfirmmodal" tabindex="-1" role="dialog" aria-labelledby="delconfirmmodallabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Confirm</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p id="delconfirmmessage"></p>
                                                <form action="update.php" method="post">
                                                    <input hidden name="action" value="deluser">
                                                    <input hidden value="" name="id" id="delconfirmuserid">
                                                    <input type="submit" class="btn bg-danger text-light" value="Mark as Inactive">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="usermodal" tabindex="-1" role="dialog" aria-labelledby="pirep'.$x.'label" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="usermodal-title"></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="update.php" method="post">
                                                    <input hidden name="action" value="edituser">
                                                    <input hidden name="id" id="usermodal-id" value="">
                                                    <div class="form-group">
                                                        <label for="usermodal-callsign">Callsign</label>
                                                        <input required type="text" value="" class="form-control" name="callsign" id="usermodal-callsign">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-name">Name</label>
                                                        <input required type="text" value="" class="form-control" name="name" id="usermodal-name">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-email">Email</label>
                                                        <input required type="text" value="" class="form-control" name="email" id="usermodal-email">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-ifc">IFC Profile URL</label>
                                                        <input required type="url" value="" class="form-control" name="ifc" id="usermodal-ifc">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-thrs">Transfer Flight Time</label>
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <input required type="number" min="0" id="flightTimeHrs" class="form-control" placeholder="Hours" />
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <input required type="number" min="0" id="flightTimeMins" class="form-control" placeholder="Minutes" />
                                                            </div>
                                                        </div>
                                                        <input hidden name="transhours" id="usermodal-thrs" class="form-control" value="<?= escape(Input::get('ftime')) ?>" required />
                                                        <script>
                                                            function formatFlightTime() {
                                                                var hrs = $("#flightTimeHrs").val();
                                                                var mins = $("#flightTimeMins").val();
                                                                $("#usermodal-thrs").val(hrs + ":" + mins);
                                                            }

                                                            function reverseFormatFlightTime() {
                                                                var formatted = $("#usermodal-thrs").val();
                                                                if (formatted != '') {
                                                                    var split = formatted.split(":");
                                                                    var hrs = split[0];
                                                                    var mins = split[1];
                                                                    $("#flightTimeHrs").val(hrs);
                                                                    $("#flightTimeMins").val(mins);
                                                                }
                                                            }

                                                            $(document).ready(function() {
                                                                $("#flightTimeHrs").keyup(function() {
                                                                    formatFlightTime();
                                                                });
                                                                $("#flightTimeMins").keyup(function() {
                                                                    formatFlightTime();
                                                                });
                                                                reverseFormatFlightTime();
                                                            });
                                                        </script>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-tflts"># Transfer Flights</label>
                                                        <input required type="number" min="0" value="" class="form-control" name="transflights" id="usermodal-tflts">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-joined">Join date</label>
                                                        <input readonly type="date" value="" class="form-control" name="joined" id="usermodal-joined">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-status">Status</label>
                                                        <input readonly type="text" value="" class="form-control" name="status" id="usermodal-status">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-admin">Admin Status</label>
                                                        <select required class="form-control" name="admin" id="usermodal-admin">
                                                            <option value>Select</option>
                                                            <option value="0" id="usermodal-admin-0">Pilot</option>
                                                            <option value="1" id="usermodal-admin-1">Staff Member</option>
                                                        </select>
                                                    </div>
                                                    <input type="submit" class="btn bg-custom" value="Save">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Populate modal fields -->
                                <script>
                                    $(document).ready(function() {
                                        $('.userEdit').click(function(e) {
                                            var userCallsign = $(this).data("callsign");
                                            var userName = $(this).data("name");
                                            var userEmail = $(this).data("email");
                                            var userIfc = $(this).data("ifc");
                                            var userJoined = $(this).data("joined");
                                            var userStatus = $(this).data("status");
                                            var userThrs = $(this).data("thrs");
                                            var userTflts = $(this).data("tflts");
                                            var userId = $(this).data("id");
                                            var userAdmin = $(this).data("admin");

                                            $("#usermodal-callsign").val(userCallsign);
                                            $("#usermodal-name").val(userName);
                                            $("#usermodal-email").val(userEmail);
                                            $("#usermodal-ifc").val(userIfc);
                                            $("#usermodal-joined").val(userJoined);
                                            $("#usermodal-status").val(userStatus);
                                            $("#usermodal-thrs").val(userThrs);
                                            $("#usermodal-tflts").val(userTflts);
                                            $("#usermodal-id").val(userId);
                                            $("#usermodal-admin-" + userAdmin).attr("selected", true);

                                            $("#usermodal-title").text("Edit User - " + userCallsign);
                                            reverseFormatFlightTime();

                                            $("#usermodal").modal("show");
                                        });
                                    });
                                </script>
                                <!-- Confirm delete modal -->
                                <script>
                                    $(document).ready(function() {
                                        $('#delconfirmmodal').on('show.bs.modal', function(e) {
                                            var userCallsign = $(e.relatedTarget).data('callsign');
                                            var userId = $(e.relatedTarget).data('id');

                                            var message = 'Are you sure you want to mark the user ' + userCallsign + ' as inactive?'
                                            $("#delconfirmmessage").text(message);
                                            $("#delconfirmuserid").val(userId);
                                        });
                                    });
                                </script>
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'staffmanage'): ?>
                                <script>
                                    $(document).ready(function() {
                                        $(".usrCollapse").each(function() {
                                            $(this).collapse("show");
                                        });
                                    });
                                </script>
                                <h3>Manage Staff</h3>
                                <?php if (!$user->hasPermission('staffmanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    <p>Here you can manage staff members, and their permissions. Be sure to select the correct permissions, as setting the wrong permissions can give them access to sensitive information!</p>
                                    <table class="table table-striped">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th>Callsign</th>
                                            <th class="mobile-hidden">Name</th>
                                            <th class="mobile-hidden">Email</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stafflist = $user->getAllStaff();
                                        $x = 0;
                                        foreach ($stafflist as $staff) {
                                            echo '<tr><td class="align-middle">';
                                            echo $staff["callsign"];
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            echo $staff["name"];
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            echo $staff["email"];
                                            echo '</td><td class="align-middle">';
                                            echo $staff["status"];
                                            echo '</td><td class="align-middle">';
                                            echo '<button class="btn text-light btn-primary" data-toggle="modal" data-target="#staff'.$x.'modal" data-callsign="'.$staff['callsign'].'" data-name="'.$staff['name'].'" data-email="'.$staff['email'].'" data-ifc="'.$staff['ifc'].'" data-joined="'.date_format(date_create($staff['joined']), 'Y-m-d').'" data-status="'.$staff['status'].'" data-id="'.$staff['id'].'"><i class="fa fa-edit"></i></button>';
                                            echo '&nbsp;<button id="delconfirmbtn" class="btn text-light btn-danger" data-toggle="modal" data-target="#delconfirmmodal" data-callsign="'.$staff['callsign'].'"><i class="fa fa-trash"></i></button>';
                                            echo '</td>';
                                            $x++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <?php
                                $x = 0;
                                foreach ($stafflist as $staff) {
                                    echo 
                                    '
                                    <div class="modal fade" id="staff'.$x.'modal" tabindex="-1" role="dialog" aria-labelledby="staff'.$x.'label" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="staffmodaltitle">Edit Staff Member - '.$staff['callsign'].'</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="update.php" method="post">
                                                        <input hidden name="action" value="editstaffmember">
                                                        <input hidden name="id" value="'.$staff['id'].'">
                                                        <div class="form-group">
                                                            <label for="usermodal-callsign">Callsign</label>
                                                            <input required type="text" value="'.$staff['callsign'].'" class="form-control" name="callsign" id="usermodal-callsign">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-name">Name</label>
                                                            <input required type="text" value="'.$staff['name'].'" class="form-control" name="name" id="usermodal-name">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-email">Email</label>
                                                            <input required type="text" value="'.$staff['email'].'" class="form-control" name="email" id="usermodal-email">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-ifc">IFC Username</label>
                                                            <input required type="text" value="'.$staff['ifc'].'" class="form-control" name="ifc" id="usermodal-ifc">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-joined">Join date</label>
                                                            <input readonly type="date" value="'.$staff['joined'].'" class="form-control" name="joined" id="usermodal-joined">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-status">Status</label>
                                                            <input readonly type="text" value="'.$staff['status'].'" class="form-control" name="status" id="usermodal-status">
                                                        </div>
                                                        <br>
                                                        <h5>Permissions</h5>
                                                        ';
                                                        $permissions = Permissions::getAll();

                                                        foreach ($permissions as $permission => $data) {
                                                            if ($user->hasPermission($permission, $staff['id'])) {
                                                                echo
                                                                '
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" value="on" id="permission'.$permission.'" name="'.$permission.'" checked>
                                                                    <label class="form-check-label" for="defaultCheck1">
                                                                        '.$data['name'].'
                                                                    </label>
                                                                </div>
                                                                ';
                                                            } else {
                                                                echo
                                                                '
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" value="on" id="permission'.$permission.'" name="'.$permission.'">
                                                                    <label class="form-check-label" for="defaultCheck1">
                                                                        '.$data['name'].'
                                                                    </label>
                                                                </div>
                                                                ';
                                                            }
                                                        }
                                                        echo 
                                                        '
                                                        <br>
                                                        <input type="submit" class="btn bg-custom" value="Save">
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    ';
                                    $x++;
                                }

                                ?>
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'site'): ?>
                                <?php
                                    if (!$user->hasPermission('opsmanage')) {
                                        Redirect::to('home.php');
                                        die();
                                    }
                                ?>
                                <h3>Configure Flare</h3>
                                <p>Here you may configure Flare to be your own.</p>
                                <ul class="nav nav-tabs nav-dark justify-content-center">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#colors">Color Theme</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#settings">VA Settings</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#vanet">VANet Settings</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#updates">Updates</a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <div id="colors" class="tab-pane container-fluid p-3 active">
                                        <h4>Colour Theme</h4>
                                        <form action="update.php" method="post">
                                            <input hidden name="action" value="setcolour">
                                            <div class="form-group">
                                                <label for="">Main Colour (hex, without #)</label>
                                                <input required type="text" class="form-control" name="hexcol" value="<?= str_replace('#', '', Config::get('site/colour_main_hex')) ?>">
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Save">
                                        </form>
                                    </div>
                                    <div id="settings" class="tab-pane container-fluid p-3 fade">
                                        <h4>VA Settings</h4>
                                        <form action="update.php" method="post">
                                            <input hidden name="action" value="vasettingsupdate">
                                            <div class="form-group">
                                                <label for="">VA Full Name</label>
                                                <input required type="text" class="form-control" name="vaname" value="<?= Config::get('va/name') ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="">VA Callsign Identifier</label>
                                                <input required type="text" class="form-control" name="vaident" value="<?= Config::get('va/identifier') ?>">
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Save">
                                        </form>
                                    </div>
                                    <div id="vanet" class="tab-pane container-fluid p-3 fade">
                                        <h4>VANet Settings</h4>
                                        <form action="update.php" method="post">
                                            <input hidden name="action" value="vanetupdate">
                                            <div class="form-group">
                                                <label for="">VANet API Key</label>
                                                <input required type="text" class="form-control" name="vanetkey" value="<?= Config::get('vanet/api_key') ?>">
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Save">
                                        </form>
                                    </div>
                                    <div id="updates" class="tab-pane container-fluid p-3 fade">
                                        <h4>Flare Updates</h4>
                                        <p>
                                            <?php $ver = Updater::getVersion(); ?>
                                            <b>You are on Flare <?php echo $ver["tag"]; ?></b>
                                            <br />
                                            <?php
                                                $update = Updater::checkUpdate();
                                                if ($ver["prerelease"]) {
                                                    $update = Updater::checkUpdate(true);
                                                }
                                                if (!$update) {
                                                    echo "Flare is Up-to-Date!";
                                                } else {
                                                    echo "An update to Flare ".$update["tag"]." is available<br />";
                                                    echo '<button class="btn bg-custom" id="updateNow">Update Now</button>';
                                                    echo '<p id="updateResult"></p>';
                                                }
                                            ?>
                                        </p>
                                        <script>
                                            $(document).ready(function() {
                                                $("#updateNow").click(function() {
                                                    $("#updateResult").html('<div class="spinner-grow spinner-custom"></div>');
                                                    $.get("updater.php", function(data, status) {
                                                        $("#updateResult").html(data);
                                                    });
                                                });
                                            });
                                        </script>
                                    </div>
                                </div>

                                <style>
                                    .nav-tabs .nav-link {
                                        color: #000!important;
                                    }
                                </style>
                            <?php elseif (Input::get('page') === 'recruitment'): ?>
                                <script>
                                    $(document).ready(function() {
                                        $(".usrCollapse").each(function() {
                                            $(this).collapse("show");
                                        });
                                    });
                                </script>
                                <h3>Recruitment</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    <p>Here you can manage any Pending Applications</p>
                                    <form id="accept" action="update.php" method="post">
                                        <input hidden name="action" value="acceptapplication">
                                    </form>
                                    <table class="table table-striped">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th class="mobile-hidden">Callsign</th>
                                            <th>Name</th>
                                            <th class="mobile-hidden">Email</th>
                                            <th class="mobile-hidden">Grade</th>
                                            <th class="mobile-hidden">IFC Username</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $users = $user->getAllPendingUsers();
                                        $x = 0;
                                        foreach ($users as $user) {
                                            echo '<tr><td class="mobile-hidden align-middle">';
                                            echo $user["callsign"];
                                            echo '</td><td class="align-middle">';
                                            echo $user["name"];
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            echo $user["email"];
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            echo $user["grade"];
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            $username = explode('/', $user['ifc']);
                                            echo '<a href="'.$user['ifc'].'" target="_blank">'.$username[4].'</a>';
                                            echo '</td><td class="align-middle">';
                                            echo '<button class="btn btn-success text-light" value="'.$user['id'].'" form="accept" type="submit" name="accept"><i class="fa fa-check"></i></button>';
                                            echo '&nbsp;<button value="'.$user['id'].'" id="delconfirmbtn" data-toggle="modal" data-target="#user'.$x.'declinemodal" class="btn btn-danger text-light" name="decline"><i class="fa fa-times"></i></button>';
                                            echo '&nbsp;<button id="delconfirmbtn" class="btn btn-primary text-light" data-toggle="modal" data-target="#user'.$x.'modal"><i class="fa fa-plus"></i></button>';
                                            echo '</td>';
                                            $x++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <?php
                                $x = 0;
                                foreach ($users as $user) {
                                    echo 
                                    '
                                    <div class="modal fade" id="user'.$x.'modal" tabindex="-1" role="dialog" aria-labelledby="user'.$x.'label" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="usermodal-title"></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="update.php" method="post">
                                                        <div class="form-group">
                                                            <label for="usermodal-callsign">Callsign</label>
                                                            <input readonly type="text" value="'.$user["callsign"].'" class="form-control" name="callsign">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-name">Name</label>
                                                            <input readonly type="text" value="'.$user["name"].'" class="form-control" name="name">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-email">Email</label>
                                                            <input readonly type="text" value="'.$user["email"].'" class="form-control" name="email">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-ifc">IFC Username</label>
                                                            <a href="'.$user['ifc'].'" target="_blank"><input readonly type="text" style="cursor:pointer" value="'.$username[4].'" class="form-control" name="ifc"></a>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-joined">Grade</label>
                                                            <input readonly type="text" value="'.$user["grade"].'" class="form-control" name="grade">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-status">Violations to landings</label>
                                                            <input readonly type="text" value="'.$user["viol"].'" class="form-control" name="viol">
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn bg-custom" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="user'.$x.'declinemodal" tabindex="-1" role="dialog" aria-labelledby="user'.$x.'label" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="usermodal-title"></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="update.php" method="post" id="declinemodal">
                                                        <input hidden class="form-control" name="action" value="declineapplication">
                                                        <input hidden class="form-control" name="id" value="'.$user['id'].'">
                                                        <div class="form-group">
                                                            <label for="usermodal-status">Reason for decline of application</label>
                                                            <input required type="text" class="form-control" name="declinereason">
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn bg-custom" form="declinemodal" type="submit">Decline</button>
                                                    <button type="button" class="btn bg-secondary" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    ';
                                    $x++;
                                }

                                ?>
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'pirepmanage'): ?>
                                <h3>Manage PIREPs</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    <form id="acceptpirep" action="update.php" method="post">
                                        <input hidden name="action" value="acceptpirep">
                                    </form>
                                    <form id="declinepirep" action="update.php" method="post">
                                        <input hidden name="action" value="declinepirep">
                                    </form>
                                    <table class="table table-striped">
                                        <thead class="bg-custom">
                                            <tr>
                                                <th>Callsign</th>
                                                <th>Flight Number</th>
                                                <th class="mobile-hidden">Departure</th>
                                                <th class="mobile-hidden">Arrival</th>
                                                <th class="mobile-hidden">Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $x = 0;
                                            $pireps = Pirep::fetchPending();
                                            foreach ($pireps as $pirep) {
                                                echo '<tr><td class="align-middle">';
                                                $callsign = $user->idToCallsign($pirep['pilotid']);
                                                echo $callsign;
                                                echo '</td><td class="align-middle">';
                                                echo $pirep['flightnum'];
                                                echo '</td><td class="align-middle mobile-hidden">';
                                                echo $pirep['departure'];
                                                echo '</td><td class="align-middle mobile-hidden">';
                                                echo $pirep['arrival'];
                                                echo '</td><td class="align-middle mobile-hidden">';
                                                echo $pirep['date'];
                                                echo '</td><td class="align-middle">';
                                                echo '<button class="btn btn-success text-light" value="'.$pirep['id'].'" form="acceptpirep" type="submit" name="accept"><i class="fa fa-check"></i></button>';
                                                echo '&nbsp;<button value="'.$pirep['id'].'" form="declinepirep" type="submit" class="btn btn-danger text-light" name="decline"><i class="fa fa-times"></i></button>';
                                                echo '</td>';
                                                $x++;
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'newsmanage'): ?>
                                <h3>Manage News</h3>
                                <br>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    <h4>Active News Articles</h4>
                                    <form id="deletearticle" action="update.php" method="post">
                                        <input hidden name="action" value="deletearticle">
                                    </form>
                                    <table class="table table-striped">
                                        <thead class="bg-custom">
                                            <tr>
                                                <th>Title</th>
                                                <th class="mobile-hidden">Date Posted</th>
                                                <th class="mobile-hidden">Author</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $x = 0; 
                                            $news = News::get();
                                            foreach ($news as $article) {
                                                echo '<tr><td class="align-middle">';
                                                echo $article['title'];
                                                echo '</td><td class="align-middle mobile-hidden">';
                                                echo $article['dateposted'];
                                                echo '</td><td class="align-middle mobile-hidden">';
                                                echo $article['author'];
                                                echo '</td><td class="align-middle">';
                                                echo '&nbsp;<button value="'.$article['id'].'" id="articleedit" data-toggle="modal" data-target="#article'.$x.'editmodal" class="btn btn-primary text-light" name="edit"><i class="fa fa-edit"></i></button>';
                                                echo '&nbsp;<button value="'.$article['id'].'" form="deletearticle" type="submit" class="btn btn-danger text-light" name="delete"><i class="fa fa-trash"></i></button>';
                                                echo '</td>';
                                                $x++;
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <?php
                                $x = 0;
                                foreach ($news as $article) {
                                    echo 
                                    '
                                    <div class="modal fade" id="article'.$x.'editmodal" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit News Article</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="update.php" method="post">
                                                        <input hidden name="action" value="editarticle">
                                                        <input hidden name="id" value="'.$article['id'].'">
                                                        <div class="form-group">
                                                            <label>Title</label>
                                                            <input type="text" value="'.$article["title"].'" class="form-control" name="title">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Content</label>
                                                            <textarea class="form-control" name="content">'.$article["content"].'</textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Author</label>
                                                            <input readonly type="text" value="'.$article["author"].'" class="form-control" name="author">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="usermodal-ifc">Date Posted</label>
                                                            <input readonly type="text" value="'.$article["dateposted"].'" class="form-control" name="dateposted">
                                                        </div>
                                                        <input type="submit" class="btn bg-success" value="Save">
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn bg-danger" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    ';
                                    $x++;
                                }

                                ?>
                                <br>
                                <h4>New Article</h4>
                                <form action="update.php" method="post">
                                    <input hidden name="action" value="newarticle">
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" class="form-control" name="title">
                                    </div>
                                    <div class="form-group">
                                        <label>Content</label>
                                        <textarea class="form-control" name="content"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Author</label>
                                        <input readonly type="text" value="<?= escape($user->data()->name) ?>" class="form-control" name="author">
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Save">
                                </form>
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'statsviewing'): ?>
                                <?php if (!$user->hasPermission('statsviewing')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    <h3>VA Statistics</h3>
                                    <table class="table">
                                        <tr><td><b>Total Hours</b></td><td><?php echo Time::secsToString(Stats::totalHours()); ?></td></tr>
                                        <tr><td><b>Total Flights</b></td><td><?php echo Stats::totalFlights(); ?></td></tr>
                                        <tr><td><b>Total Pilots</b></td><td><?php echo Stats::numPilots(); ?></td></tr>
                                        <tr><td><b>Total Routes</b></td><td><?php echo Stats::numRoutes(); ?></td></tr>
                                    </table>
                                    <hr />
                                    <?php if (!VANet::isGold()): ?>
                                        <p>Sign Up to VANet Gold in order to get access to VANet Stats.</p>
                                    <?php else: ?>
                                        <h4>VANet Statistics</h4>
                                        <?php $stats = VANet::getStats(); ?>
                                        <table class="table">
                                            <tr><td><b>Total Distance</b></td><td><?php echo $stats["totalDistance"]; ?>NM</td></tr>
                                            <tr><td><b>Total Revenue</b></td><td>$<?php echo $stats["totalRevenue"]; ?></td></tr>
                                        </table>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'opsmanage'): ?>
                                <?php if (!$user->hasPermission('opsmanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    <script>
                                        $(document).ready(function() {
                                            $(".opsCollapse").each(function() {
                                                $(this).collapse("show");
                                            });
                                        });
                                    </script>
                                    <?php if (Input::get('section') === 'fleet'): ?>
                                        <h3>Fleet</h3>
                                        <button type="button" class="btn bg-custom mb-2" data-toggle="modal" data-target="#addAircraft">Add Aircraft</button>
                                        <div id="addAircraft" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Add Aircraft</h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="update.php" method="post">
                                                            <input hidden name="action" value="addaircraft">
                                                            <div class="form-group">
                                                                <label for="aircraft">Type</label>
                                                                <select class="form-control" name="aircraftselect" id="aircraftselect" required>
                                                                    <option value>Select</option>
                                                                    <?php
                                                                    $all = Aircraft::fetchAllAircraft();
                                                                    $x = 0;
                                                                    while ($all->count() > $x) {
                                                                        echo '<option>'.$all->results()[$x]->name.'</option>';
                                                                        $x++;
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="rank">Livery</label>
                                                                <select class="form-control" name="livery" id="liveriesselect" required>
                                                                    <option disabled>Loading...</option>
                                                                </select>
                                                            </div>
                                                            <script>
                                                                $("#aircraftselect").change(function() {
                                                                    $.ajax({
                                                                        url: "update.php",
                                                                        type: "POST",
                                                                        data: { action: "getliveriesforaircraft", aircraft: $(this).val() },
                                                                        success: function(html){
                                                                            $("#liveriesselect").empty()
                                                                            $("#liveriesselect").append("<option>Select</option>");
                                                                            $("#liveriesselect").append(html);
                                                                        }
                                                                        });
                                                                    });
                                                            </script>
                                                            <div class="form-group">
                                                                <label for="rank">Rank required</label>
                                                                <select class="form-control" name="rank" required>
                                                                    <option value>Select</option>
                                                                    <?php
                                                                    $all = Rank::fetchAllNames();
                                                                    $x = 0;
                                                                    while ($all->count() > $x) {
                                                                        echo '<option>'.$all->results()[$x]->name.'</option>';
                                                                        $x++;
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <input type="submit" class="btn bg-custom" value="Add Aircraft">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <form id="deleteaircraft" method="post" action="update.php">
                                            <input hidden value="deleteaircraft" name="action">
                                        </form>
                                        <table class="table table-striped">
                                            <thead class="bg-custom">
                                                <tr>
                                                    <th>Name</th>
                                                    <th class="mobile-hidden">Livery</th>
                                                    <th class="mobile-hidden">Min. Rank</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $all = Aircraft::fetchActiveAircraft()->results();
                                                foreach ($all as $aircraft) {
                                                    echo '<tr><td class="align-middle">';
                                                    echo $aircraft->name;
                                                    echo '</td><td class="align-middle mobile-hidden">';
                                                    echo $aircraft->liveryname;
                                                    echo '</td><td class="align-middle mobile-hidden">';
                                                    echo Rank::idToName($aircraft->rankreq);
                                                    echo '</td><td class="align-middle">';
                                                    echo '&nbsp;<button value="'.$aircraft->id.'" form="deleteaircraft" type="submit" class="btn btn-danger text-light" name="delete"><i class="fa fa-trash"></i></button>';
                                                    echo '&nbsp;<button class="btn btn-primary editFleet" data-acName="'.$aircraft->name.' ('.$aircraft->liveryname.')'.'" 
                                                    data-rankReq="'.$aircraft->rankreq.'" data-id="'.$aircraft->id.'"><i class="fa fa-edit"></i></button>';
                                                    echo '</td>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>

                                        <div class="modal" id="fleetedit">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="fleetedit-title"></h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="update.php" method="post">
                                                        <input hidden name="action" value="editfleet" />
                                                        <input hidden name="id" id="fleetedit-id" />
                                                        <div class="form-group">
                                                            <label for="fleetedit-rank">Minimum Rank</label>
                                                            <select class="form-control" name="rank" id="fleetedit-rank">
                                                                <?php
                                                                    $ranks = Rank::fetchAllNames()->results();
                                                                    foreach ($ranks as $r) {
                                                                        echo '<option id="fleetedot-rank-'.$r->id.'" value="'.$r->id.'">'.$r->name.'</option>';
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <input type="submit" class="btn bg-custom" value="Save" />
                                                    </form>
                                                </div>
                                                </div>
                                            </div>
                                        </div>

                                        <script>
                                            $(document).ready(function() {
                                                $(".editFleet").click(function() {
                                                    var acName = $(this).data('acname');
                                                    var acRank = $(this).data('rankreq');
                                                    var acId = $(this).data('id');
                                                    
                                                    $("#fleetedit-title").text("Edit Aircraft: " + acName);
                                                    $("#fleetedit-id").val(acId);
                                                    $("#fleetedit-rank-" + acRank).attr('selected', true);

                                                    $("#fleetedit").modal('show');
                                                });
                                            });
                                        </script>
                                    <?php elseif (Input::get('section') === 'routes'): ?>
                                        <h3>Route Management</h3>
                                        <p>Here you can Manage your VA's Routes.</p>
                                        <button type="button" class="btn bg-custom mb-2" data-toggle="modal" data-target="#addRoute">Add Route</button>
                                        <div id="addRoute" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Add Route</h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="update.php" method="post">
                                                            <input hidden name="action" value="addroute">
                                                            <div class="form-group">
                                                                <label for="aircraft">Departure Airport</label>
                                                                <input type="text" name="dep" class="form-control" placeholder="ICAO" required />
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="aircraft">Arrival Airport</label>
                                                                <input type="text" name="arr" class="form-control" placeholder="ICAO" required />
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="aircraft">Flight Number</label>
                                                                <input min="1" type="number" name="fltnum" class="form-control" required />
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="aircraft">Flight Duration</label>
                                                                <div class="row">
                                                                    <div class="col-sm-6">
                                                                        <input required type="number" min="0" id="flightTimeHrs" class="form-control" placeholder="Hours" />
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <input required type="number" min="0" id="flightTimeMins" class="form-control" placeholder="Minutes" />
                                                                    </div>
                                                                </div>
                                                                <input hidden name="duration" id="flightTimeFormatted" class="form-control" required />
                                                                <script>
                                                                    function formatFlightTime() {
                                                                        var hrs = $("#flightTimeHrs").val();
                                                                        var mins = $("#flightTimeMins").val();
                                                                        $("#flightTimeFormatted").val(hrs + ":" + mins);
                                                                    }

                                                                    function reverseFormatFlightTime() {
                                                                        var formatted = $("#flightTimeFormatted").val();
                                                                        if (formatted != '') {
                                                                            var split = formatted.split(":");
                                                                            var hrs = split[0];
                                                                            var mins = split[1];
                                                                            $("#flightTimeHrs").val(hrs);
                                                                            $("#flightTimeMins").val(mins);
                                                                        }
                                                                    }

                                                                    $(document).ready(function() {
                                                                        $("#flightTimeHrs").keyup(function() {
                                                                            formatFlightTime();
                                                                        });
                                                                        $("#flightTimeMins").keyup(function() {
                                                                            formatFlightTime();
                                                                        });
                                                                        reverseFormatFlightTime();
                                                                    });
                                                                </script>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="aircraft">Aircraft</label>
                                                                <select class="form-control" name="aircraft" required>
                                                                    <option value>Select</option>
                                                                    <?php
                                                                    $all = Aircraft::fetchActiveAircraft();

                                                                    $x = 0;

                                                                    while ($all->count() > $x) {
                                                                        echo '<option>'.$all->results()[$x]->name.'</option>';
                                                                        $x++;
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <input type="submit" class="btn bg-custom" value="Add Route" />
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <table class="table table-striped">
                                            <thead class="bg-custom">
                                                <tr>
                                                    <th class="mobile-hidden">Flight Number</th>
                                                    <th>Departure</th>
                                                    <th>Arrival</th>
                                                    <th class="mobile-hidden">Aircraft</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $all = Route::fetchAll()->results();

                                                foreach ($all as $route) {
                                                    echo '<tr><td class="align-middle mobile-hidden">';
                                                    echo $route->fltnum;
                                                    echo '</td><td class="align-middle">';
                                                    echo $route->dep;
                                                    echo '</td><td class="align-middle">';
                                                    echo $route->arr;
                                                    echo '</td><td class="align-middle mobile-hidden">';
                                                    echo Aircraft::idToName($route->aircraftid);
                                                    echo '</td><td class="align-middle">';
                                                    echo '&nbsp;<button value="'.$route->id.'" form="deleteroute" type="submit" class="btn btn-danger text-light" name="delete"><i class="fa fa-trash"></i></button>';
                                                    echo '</td></tr>';
                                                    $x++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <form id="deleteroute" method="post" action="update.php">
                                            <input hidden value="deleteroute" name="action">
                                        </form>
                                    <?php elseif (Input::get('section') === 'ranks'): ?>
                                        <h3>Manage Ranks</h3>
                                        <p>Here you can Manage the Ranks that your pilots can be Awarded.</p>
                                        <button type="button" class="btn bg-custom mb-2" data-toggle="modal" data-target="#addRank">Add Rank</button>
                                        <div id="addRank" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Add Rank</h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="update.php" method="post">
                                                            <input hidden name="action" value="addrank">
                                                            <div class="form-group">
                                                                <label for="name">Name</label>
                                                                <input type="text" name="name" class="form-control" placeholder="Second Officer" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="time">Flight time required (<b>in hours</b>)</label>
                                                                <input type="number" name="time" class="form-control" placeholder="50" required>
                                                            </div>
                                                            <input type="submit" class="btn bg-custom" value="Add Rank">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <table class="table table-striped">
                                            <thead class="bg-custom">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Min. Hours</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $all = Rank::fetchAllNames()->results();
                                                foreach ($all as $rank) {
                                                    echo '<tr><td class="align-middle">';
                                                    echo $rank->name;
                                                    echo '</td><td class="align-middle">';
                                                    echo $rank->timereq;
                                                    echo '</td><td class="align-middle">';
                                                    echo '<button class="btn btn-primary text-light editRank" 
                                                    data-id="'.$rank->id.'" data-name="'.$rank->name.'" 
                                                    data-minhrs="'.$rank->timereq.'">
                                                    <i class="fa fa-edit"></i></button>';
                                                    echo '</td></tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <div id="rankmodal" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="rankmodal-title"></h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="update.php" method="post">
                                                            <input hidden name="action" value="editrank">
                                                            <input hidden name="id" id="rankmodal-id">
                                                            <div class="form-group">
                                                                <label for="name">Name</label>
                                                                <input type="text" name="name" class="form-control" id="rankmodal-name" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="time">Flight Time Required (<b>in hours</b>)</label>
                                                                <input type="number" min="0" name="time" class="form-control" id="rankmodal-hours" required>
                                                            </div>
                                                            <input type="submit" class="btn bg-custom" value="Save">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <script>
                                            $(document).ready(function() {
                                                $('.editRank').click(function(e) {
                                                    var rankId = $(this).data("id");
                                                    var rankName = $(this).data("name");
                                                    var rankHrs = $(this).data("minhrs");

                                                    $("#rankmodal-id").val(rankId);
                                                    $("#rankmodal-name").val(rankName);
                                                    $("#rankmodal-hours").val(rankHrs);
                                                    $("#rankmodal-title").text("Edit Rank - " + rankName);

                                                    $("#rankmodal").modal("show");
                                                });
                                            });
                                        </script>
                                    <?php endif; ?>
                                <?php endif; ?>
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