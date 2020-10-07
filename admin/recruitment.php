<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Recruitment Admin - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('recruitment') || !$user->hasPermission('admin')) {
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
                        <h3>Recruitment</h3>
                        <p>Here you can manage any Pending Applications</p>
                        <form id="accept" action="/update.php" method="post">
                            <input hidden name="action" value="acceptapplication">
                        </form>
                        <table class="table table-striped datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Name</th>
                                    <th class="mobile-hidden">Email</th>
                                    <th class="mobile-hidden">IFC</th>
                                    <th>Flags</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $lists = Json::decode(file_get_contents("https://ifvarb.com/watchlist_api.php?apikey=a5f2963d-29b1-40e4-8867-a4fbb384002c"));
                                $watchlist = array();
                                $blacklist = array();
                                foreach ($lists as $l) {
                                    if ($l["type"] == "Watchlist") {
                                        $watchlist[strtolower($l["ifc"])] = $l["notes"];
                                    } else {
                                        $blacklist[strtolower($l["ifc"])] = $l["notes"];
                                    }
                                }

                                $users = $user->getAllPendingUsers();
                                $x = 0;
                                foreach ($users as $user) {
                                    echo '<tr><td class="mobile-hidden align-middle">';
                                    echo $user["name"];
                                    echo '</td><td class="mobile-hidden align-middle">';
                                    echo $user["email"];
                                    echo '</td><td class="mobile-hidden align-middle">';
                                    $username = explode('/', $user['ifc'])[4];
                                    echo '<a href="'.$user['ifc'].'" target="_blank">'.$username.'</a>';
                                    echo '</td><td class="align-middle">';
                                    if (array_key_exists(strtolower($username), $blacklist)) {
                                        echo '<span class="badge badge-danger" data-toggle="tooltip" title="'.$blacklist[strtolower($username)].'">Blacklisted</span>';
                                    } elseif (array_key_exists(strtolower($username), $watchlist)) {
                                        echo '<span class="badge badge-warning" data-toggle="tooltip" title="'.$watchlist[strtolower($username)].'">Watchlisted</span>';
                                    } else {
                                        echo '<span class="badge badge-success">None</span>';
                                    }
                                    echo '</td><td class="align-middle">&nbsp;';
                                    if (!array_key_exists(strtolower($username), $blacklist)) echo '<button class="btn btn-success text-light" value="'.$user['id'].'" form="accept" type="submit" name="accept"><i class="fa fa-check"></i></button>&nbsp;';
                                    echo '<button value="'.$user['id'].'" id="delconfirmbtn" data-toggle="modal" data-target="#user'.$x.'declinemodal" class="btn btn-danger text-light" name="decline"><i class="fa fa-times"></i></button>&nbsp;';
                                    echo '<button id="delconfirmbtn" class="btn btn-primary text-light" data-toggle="modal" data-target="#user'.$x.'modal"><i class="fa fa-plus"></i></button>';
                                    echo '</td>';
                                    $x++;
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                        $x = 0;
                        foreach ($users as $user) { ?>
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
                                            <form action="/update.php" method="post">
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
                                                    <a href="'.$user['ifc'].'" target="_blank"><input readonly type="text" style="cursor:pointer" value="'.$username.'" class="form-control" name="ifc"></a>
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
                                            <form action="/update.php" method="post" id="declinemodal">
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
                        <?php $x++; } ?>
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