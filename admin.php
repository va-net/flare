<?php
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

    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #E4181E;">
        <?php include './includes/navbar.php'; ?>
    </nav>

    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
        <div class="row m-0 p-0">
            <div class="col-lg-3 p-3 bg-light text-left mobile-hidden" id="desktopMenu" style="height: 100%;">
                <h3>Pilot Panel - <?= $user->data()->callsign ?></h3>
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
                            echo '<a href="admin.php?page='.$permission.'" id="userslink" class="panel-link"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$data['name'].'</a><br>';
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
                            <h3>Admin Panel</h3>
                            <p>Welcome to the Admin Panel. Here you can find the administration tools required to manage <?= Config::get('va/name') ?></p>
                            <?php if (Input::get('page') === 'usermanage'): ?>
                                <h3>Manage Users</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    <p>Here you can view all users, active and inactive. Click on a user to view/edit the information.</p>
                                    <table class="table table-striped">
                                    <thead class="bg-virgin">
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
                                            echo '<button class="btn text-light" style="background-color: #E4181E;" data-toggle="modal" data-target="#usermodal" data-callsign="'.$user['callsign'].'" data-name="'.$user['name'].'" data-email="'.$user['email'].'" data-ifc="'.$user['ifc'].'" data-joined="'.date_format(date_create($user['joined']), 'Y-m-d').'" data-status="'.$user['status'].'" data-id="'.$user['id'].'"><i class="fa fa-edit"></i></button>';
                                            echo '&nbsp;<button id="delconfirmbtn" class="btn text-light" style="background-color: #E4181E;" data-toggle="modal" data-target="#delconfirmmodal" data-callsign="'.$user['callsign'].'"><i class="fa fa-trash"></i></button>';
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
                                                <form>
                                                    <input type="hidden" value="" name="user" id="delconfirmuserid">
                                                    <input type="submit" class="btn bg-virgin" value="Mark as inactive">
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
                                                        <input required type="text" value="" class="form-control" name="name" id="usermodal-email">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-ifc">IFC Username</label>
                                                        <input required type="text" value="" class="form-control" name="name" id="usermodal-ifc">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-joined">Join date</label>
                                                        <input readonly type="date" value="" class="form-control" name="name" id="usermodal-joined">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usermodal-status">Status</label>
                                                        <input type="text" value="" class="form-control" name="name" id="usermodal-status">
                                                    </div>
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
                                    
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'recruitment'): ?>
                                <h3>Recruitment</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'pirepmanage'): ?>
                                <h3>Manage PIREPS</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'newsmanage'): ?>
                                <h3>Manage News</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'staffmanage'): ?>
                                <h3>Email Pilots</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'opsmanage'): ?>
                                <h3>Operations Management</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    
                                <?php endif; ?>
                            <?php elseif (Input::get('page') === 'statsviewing'): ?>
                                <h3>VA Statistics</h3>
                                <?php if (!$user->hasPermission('usermanage')): ?>
                                    <div class="alert alert-danger text-center">Whoops! You don't have the necessary permissions to access this.</div>
                                <?php else: ?>
                                    
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