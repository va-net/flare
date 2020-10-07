<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Users Admin - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('usermanage') || !$user->hasPermission('admin')) {
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
                        <h3>Manage Users</h3>
                        <p>Here you can view all users, active and inactive. Click on a user to view/edit the information.</p>
                        <table class="table table-striped datatable">
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
                                        <form action="/update.php" method="post">
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
                                        <form action="/update.php" method="post">
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
                        </script>
                        <!-- Confirm delete modal -->
                        <script>
                            $('#delconfirmmodal').on('show.bs.modal', function(e) {
                                var userCallsign = $(e.relatedTarget).data('callsign');
                                var userId = $(e.relatedTarget).data('id');

                                var message = 'Are you sure you want to mark the user ' + userCallsign + ' as inactive?'
                                $("#delconfirmmessage").text(message);
                                $("#delconfirmuserid").val(userId);
                            });
                        </script>
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