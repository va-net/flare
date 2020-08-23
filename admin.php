<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('Admin Panel - '.$user->data()->callsign);

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
            <div class="col-lg-3 p-3 bg-light text-left mobile-hidden" id="desktopMenu" style="height: 100%;">
                <h3>Pilot Panel - <?= escape($user->data()->callsign) ?></h3>
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
                            <?php
                            if (file_exists('./install/install.php')) {
                                Session::flash('error', 'The install folder still exists! Please delete this immediately, as this poses a severe security risk!');
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
                                            <th>Action</th>
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
                                            echo '<button class="btn btn-success text-light" data-toggle="modal" data-target="#usermodal" data-callsign="'.$user['callsign'].'" data-name="'.$user['name'].'" data-email="'.$user['email'].'" data-ifc="'.$user['ifc'].'" data-joined="'.date_format(date_create($user['joined']), 'Y-m-d').'" data-status="'.$user['status'].'" data-id="'.$user['id'].'"><i class="fa fa-edit"></i></button>';
                                            echo '&nbsp;<button id="delconfirmbtn" class="btn text-light btn-danger" data-toggle="modal" data-target="#delconfirmmodal" data-callsign="'.$user['callsign'].'"><i class="fa fa-trash"></i></button>';
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
                                                    <input type="hidden" value="" name="id" id="delconfirmuserid">
                                                    <input type="submit" class="btn bg-custom" value="Mark as inactive">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="usermodal" tabindex="-1" role="dialog" aria-labelledby="pirep'.$x.'label" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="usermodaltitle"></h5>
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
                                                        <label for="usermodal-ifc">IFC Username</label>
                                                        <input required type="text" value="" class="form-control" name="ifc" id="usermodal-ifc">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-joined">Join date</label>
                                                        <input readonly type="date" value="" class="form-control" name="joined" id="usermodal-joined">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-status">Status</label>
                                                        <input readonly type="text" value="" class="form-control" name="status" id="usermodal-status">
                                                    </div>
                                                    <input type="submit" class="btn bg-custom" value="Save">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'staffmanage'): ?>
                                <h3>Manage Staff</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
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
                                            <th>Action</th>
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
                                            echo '<button class="btn text-light btn-success" data-toggle="modal" data-target="#staff'.$x.'modal" data-callsign="'.$staff['callsign'].'" data-name="'.$staff['name'].'" data-email="'.$staff['email'].'" data-ifc="'.$staff['ifc'].'" data-joined="'.date_format(date_create($staff['joined']), 'Y-m-d').'" data-status="'.$staff['status'].'" data-id="'.$staff['id'].'"><i class="fa fa-edit"></i></button>';
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
                                                    <h5 class="modal-title" id="staffmodaltitle">Edit staff member - '.$staff['callsign'].'</h5>
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
                                <h3>Configure Flare</h3>
                                <p>Here you may configure Flare to be your own.</p>
                                <br>
                                <br>
                                <h4>Colour Theme</h4>
                                <form action="update.php" method="post">
                                    <input hidden name="action" value="setcolour">
                                    <div class="form-group">
                                        <label for="">Main Colour (hex, without #)</label>
                                        <input required type="text" class="form-control" name="hexcol" value="<?= str_replace('#', '', Config::get('site/colour_main_hex')) ?>">
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Save">
                                </form>
                                <br>
                                <br>
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
                                <br>
                                <br>
                                <h4>VANet</h4>
                                <form action="update.php" method="post">
                                    <input hidden name="action" value="vanetupdate">
                                    <div class="form-group">
                                        <label for="">VANet API Key</label>
                                        <input required type="text" class="form-control" name="vanetkey" value="<?= Config::get('vanet/api_key') ?>">
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Save">
                                </form>
                            <?php elseif (Input::get('page') === 'recruitment'): ?>
                                <h3>Recruitment</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    <p>Here you can manage any pending applications</p>
                                    <form id="accept" action="update.php" method="post">
                                        <input hidden name="action" value="acceptapplication">
                                    </form>
                                    <table class="table table-striped">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th>Callsign</th>
                                            <th class="mobile-hidden">Name</th>
                                            <th class="mobile-hidden">Email</th>
                                            <th>Grade</th>
                                            <th>IFC Username</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $users = $user->getAllPendingUsers();
                                        $x = 0;
                                        foreach ($users as $user) {
                                            echo '<tr><td class="align-middle">';
                                            echo $user["callsign"];
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            echo $user["name"];
                                            echo '</td><td class="mobile-hidden align-middle">';
                                            echo $user["email"];
                                            echo '</td><td class="align-middle">';
                                            echo $user["grade"];
                                            echo '</td><td class="align-middle">';
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
                                                    <h5 class="modal-title" id="usermodaltitle"></h5>
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
                                                    <h5 class="modal-title" id="usermodaltitle"></h5>
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
                                <h3>Manage PIREPS</h3>
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
                                                <th>Action</th>
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
                                                <th>Content</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $x = 0; 
                                            $news = News::get();
                                            foreach ($news as $article) {
                                                echo '<tr><td class="align-middle">';
                                                echo $article['title'];
                                                echo '</td><td class="align-middle">';
                                                echo $article['dateposted'];
                                                echo '</td><td class="align-middle mobile-hidden">';
                                                echo $article['author'];
                                                echo '</td><td class="align-middle mobile-hidden">';
                                                echo trim(substr($article['content'], 0, 25)).'...';
                                                echo '</td><td class="align-middle">';
                                                echo '&nbsp;<button value="'.$article['id'].'" id="articleedit" data-toggle="modal" data-target="#article'.$x.'editmodal" class="btn btn-success text-light" name="edit"><i class="fa fa-edit"></i></button>';
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
                                <h4>New article</h4>
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
                                    <h3>Your VA's Stats</h3>
                                    <?php if (!VANet::isGold()): ?>
                                        <p>Hmm, you need to signup to VANet Gold in order to get access to statistics.</p>
                                    <?php else: ?>
                                        <p>Coming soon...</p>
                                    <?php endif; ?>
                            <?php elseif (Input::get('page') === 'opsmanage'): ?>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    <?php if (Input::get('section') === 'fleet'): ?>
                                        <h3>Fleet</h3>
                                        <p>Please note that importing from a CSV is not yet supported, and will be coming in a later build.</p>
                                        <br>
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
                                                                    <option>Select</option>
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
                                                                    <option>Select</option>
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
                                                                    <option>Select</option>
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
                                                            <input type="submit" class="btn bg-custom" value="Add aircraft">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <form id="deleteaircraft" method="post" action="update.php">
                                            <input hidden value="deleteaircraft" name="action">
                                        </form>
                                        <br>
                                        <table class="table table-striped">
                                            <thead class="bg-custom">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Livery</th>
                                                    <th>Rank required</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $all = Aircraft::fetchActiveAircraft();
                                                $x = 0;

                                                while ($all->count() > $x) {
                                                    echo '<tr><td class="align-middle">';
                                                    echo $all->results()[$x]->name;
                                                    echo '</td><td class="align-middle">';
                                                    echo $all->results()[$x]->liveryname;
                                                    echo '</td><td class="align-middle">';
                                                    echo Rank::idToName($all->results()[$x]->rankreq);
                                                    echo '</td><td class="align-middle">';
                                                    echo '&nbsp;<button value="'.$all->results()[$x]->id.'" form="deleteaircraft" type="submit" class="btn btn-danger text-light" name="delete"><i class="fa fa-trash"></i></button>';
                                                    echo '</td>';
                                                    $x++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    <?php elseif (Input::get('section') === 'routes'): ?>
                                        <h3>Route Management</h3>
                                        <p>Here you can manage your VA's routes, please note that importing from CSV is not yet available.</p>
                                        <br>
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
                                                                <input type="text" name="dep" class="form-control" placeholder="ICAO" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="aircraft">Arrival Airport</label>
                                                                <input type="text" name="arr" class="form-control" placeholder="ICAO" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="aircraft">Flight Number</label>
                                                                <input type="number" name="fltnum" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="aircraft">Flight Duration</label>
                                                                <input type="time" name="duration" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="aircraft">Aircraft</label>
                                                                <select class="form-control" name="aircraft" required>
                                                                    <option>Select</option>
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
                                                            <input type="submit" class="btn bg-custom" value="Add route">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <table class="table table-striped">
                                            <thead class="bg-custom">
                                                <tr>
                                                    <th>Flight Number</th>
                                                    <th>Departure</th>
                                                    <th>Arrival</th>
                                                    <th>Aircraft</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $all = Route::fetchAll();
                                                $x = 0;

                                                while ($all->count() > $x) {
                                                    echo '<tr><td class="align-middle">';
                                                    echo $all->results()[$x]->fltnum;
                                                    echo '</td><td class="align-middle">';
                                                    echo $all->results()[$x]->dep;
                                                    echo '</td><td class="align-middle">';
                                                    echo $all->results()[$x]->arr;
                                                    echo '</td><td class="align-middle">';
                                                    echo Aircraft::idToName($all->results()[$x]->aircraftid);
                                                    echo '</td><td class="align-middle">';
                                                    echo '&nbsp;<button value="'.$all->results()[$x]->id.'" form="deleteroute" type="submit" class="btn btn-danger text-light" name="delete"><i class="fa fa-trash"></i></button>';
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
                                        <p>Here you can add ranks that your pilots will be awarded.</p>
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
                                                            <input type="submit" class="btn bg-custom" value="Add rank">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
    <!-- Confirm delete modal -->
    <script>
        $(document).ready(function() {
            $('#delconfirmmodal').on('show.bs.modal', function(e) {
                var userCallsign = $(e.relatedTarget).data('callsign')
                var message = 'Are you sure you want to mark the user ' + userCallsign + ' as inactive?'
                $("#delconfirmmessage").text(message);
                $("#delconfirmuserid").val(userCallsign);
            });
        });
    </script>
    <!-- Populate user modal fields -->
    <script>
        $(document).ready(function() {
            $('#usermodal').on('show.bs.modal', function(e) {
                var userCallsign = $(e.relatedTarget).data('callsign')
                var userName = $(e.relatedTarget).data('name')
                var userEmail = $(e.relatedTarget).data('email')
                var userIfc = $(e.relatedTarget).data('ifc')
                var userJoined = $(e.relatedTarget).data('joined')
                var userStatus = $(e.relatedTarget).data('status')
                var userId = $(e.relatedTarget).data('id')
                $('#usermodaltitle').text('Edit User - ' + userCallsign)
                $('#usermodal-callsign').val(userCallsign);
                $('#usermodal-name').val(userName);
                $('#usermodal-email').val(userEmail);
                $('#usermodal-ifc').val(userIfc);
                $('#usermodal-joined').val(userJoined);
                $('#usermodal-status').val(userStatus);
                $('#usermodal-id').val(userId);
            });
        });
    </script>
</body>
</html>