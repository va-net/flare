<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Staff Admin - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('staffmanage') || !$user->hasPermission('admin')) {
    Redirect::to('/home.php');
}

$ACTIVE_CATEGORY = 'user-management';
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
                        <h3>Manage Staff</h3>
                        <p>Here you can manage staff members, and their permissions. Be sure to select the correct permissions, as setting the wrong permissions can give them access to sensitive information!</p>
                        <table class="table table-striped datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Callsign</th>
                                    <th class="mobile-hidden">Name</th>
                                    <th class="mobile-hidden">Email</th>
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
                        foreach ($stafflist as $staff) { ?>
                            <div class="modal fade" id="staff<?= $x ?>modal" tabindex="-1" role="dialog" aria-labelledby="staff<?= $x ?>label" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staffmodaltitle">Edit Staff Member - <?= $staff['callsign'] ?></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/update.php" method="post">
                                                <input hidden name="action" value="editstaffmember">
                                                <input hidden name="id" value="<?= $staff['id'] ?>">
                                                <div class="form-group">
                                                    <label for="usermodal-callsign">Callsign</label>
                                                    <input required type="text" value="<?= $staff['callsign'] ?>" class="form-control" name="callsign" id="usermodal-callsign">
                                                </div>
                                                <div class="form-group">
                                                    <label for="usermodal-name">Name</label>
                                                    <input required type="text" value="<?= $staff['name'] ?>" class="form-control" name="name" id="usermodal-name">
                                                </div>
                                                <div class="form-group">
                                                    <label for="usermodal-email">Email</label>
                                                    <input required type="text" value="<?= $staff['email'] ?>" class="form-control" name="email" id="usermodal-email">
                                                </div>
                                                <div class="form-group">
                                                    <label for="usermodal-ifc">IFC Username</label>
                                                    <input required type="text" value="<?= $staff['ifc'] ?>" class="form-control" name="ifc" id="usermodal-ifc">
                                                </div>
                                                <div class="form-group">
                                                    <label for="usermodal-joined">Join Date</label>
                                                    <input readonly type="date" value="<?= date_format(date_create($staff['joined']), 'Y-m-d') ?>" class="form-control" name="joined" id="usermodal-joined">
                                                </div>
                                                <br>
                                                <h5>Permissions</h5>
                                                <?php
                                                    $allperms = Permissions::getAll();
                                                    $myperms = Permissions::forUser($staff['id']);
                                                    foreach ($allperms as $permission => $name) {
                                                        if ($user->hasPermission($permission, $staff['id'])): ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" value="on" id="permission-<?= $permission ?>" name="<?= $permission ?>" checked>
                                                                <label class="form-check-label" for="permission-<?= $permission ?>">
                                                                    <?= $name ?>
                                                                </label>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" value="on" id="permission-<?= $permission ?>" name="<?= $permission ?>">
                                                                <label class="form-check-label" for="permission-<?= $permission ?>">
                                                                    <?= $name ?>
                                                                </label>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php } ?>
                                                <br />
                                                <input type="submit" class="btn bg-custom" value="Save">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php $x++; ?>
                        <?php } ?>
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