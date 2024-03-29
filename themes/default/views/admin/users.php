<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Users Admin - ' . Page::$pageData->va_name);
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once __DIR__ . '/../../includes/header.php'; ?>
</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="mt-4 text-center container-fluid" style="overflow: auto;">
            <div class="p-0 m-0 row">
                <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <?php
                        if (file_exists(__DIR__ . '/../install/install.php') && !file_exists(__DIR__ . '/../.development')) {
                            echo '<div class="text-center alert alert-danger">The Install Folder still Exists! Please delete it immediately, it poses a severe security risk.</div>';
                        }

                        if (Session::exists('error')) {
                            echo '<div class="text-center alert alert-danger">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="text-center alert alert-success">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <h3>Manage Users</h3>
                        <p>Here you can view all users, active and inactive. Click on a user to view/edit the information.</p>
                        <p>
                            <span class="cursor-pointer text-primary" id="toggle-inactive" data-state="1">Show Inactive Users</span>
                        </p>
                        <table class="table datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Callsign</th>
                                    <th class="mobile-hidden">Name</th>
                                    <th class="mobile-hidden">Flight Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (Page::$pageData->users as $user) {
                                    $rowClassName = '';
                                    $rowStyle = '';
                                    if ($user['status'] == 'Inactive' || $user['status'] == 'Declined') {
                                        $rowClassName = 'inactive-row';
                                        $rowStyle = 'display: none;';
                                    }
                                    $username = explode('/', $user['ifc']);
                                    if ($username === FALSE || count($username) < 5) {
                                        $username = '';
                                    } else {
                                        $username = $username[4];
                                    }

                                    echo '<tr class="' . $rowClassName . '" style="' . $rowStyle . '"><td class="align-middle">';
                                    echo $user["callsign"];
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo $user["name"];
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo Time::secsToString($user["flighttime"] + $user["transhours"]);
                                    echo '</td><td class="align-middle">';
                                    echo $user["status"];
                                    echo '</td><td class="align-middle">';
                                    if (Page::$pageData->is_gold && VANet::featureEnabled('airline-userlookup')) {
                                        echo '<a href="/admin/users/lookup/' . (empty($user['ifuserid']) ? $username . '?ifc=true' : $user['ifuserid']) . '" class="btn bg-custom">
                                        <i class="fa fa-search"></i></a>&nbsp;';
                                    }
                                    echo '<button class="btn btn-primary text-light userEdit" data-callsign="' . $user['callsign'] . '" 
                                    data-name="' . $user['name'] . '" data-email="' . $user['email'] . '" data-ifc="' . $user['ifc'] . '" 
                                    data-joined="' . date_format(date_create($user['joined']), 'Y-m-d') . '" data-status="' . $user['status'] . '" 
                                    data-id="' . $user['id'] . '" data-thrs="' . Time::secsToString($user["transhours"]) . '" 
                                    data-admin="' . $user['isAdmin'] . '" data-tflts="' . $user["transflights"] . '" data-notes="' . $user['notes'] . '"><i class="fa fa-edit"></i>
                                    </button>';
                                    echo '&nbsp;<button id="delconfirmbtn" class="btn text-light btn-danger" 
                                    data-toggle="modal" data-target="#delconfirmmodal" 
                                    data-callsign="' . $user['callsign'] . '" data-id="' . $user['id'] . '">
                                    <i class="fa fa-trash"></i></button>';
                                    echo '</td>';
                                }
                                ?>
                            </tbody>
                        </table>
                        <p>
                            <span class="cursor-pointer text-primary font-weight-bold" data-toggle="modal" data-target="#broadcastmodal">Make Announcement</span>
                        </p>
                        <div class="modal fade" id="delconfirmmodal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Confirm</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            Marking a user as <b>Inactive</b> prevents them from logging in but does not remove their data.
                                            This action is reversible and can be performed by anyone. <b>Deleting a user</b> permanently
                                            deletes all their data and cannot be reversed. Permanent deletion can only be done by users
                                            who can manage staff member permissions.
                                        </p>
                                        <p id="delconfirmmessage"></p>
                                        <form action="/admin/users" method="post">
                                            <input hidden name="action" value="deluser" />
                                            <input hidden value="" name="id" class="delconfirmuserid" />
                                            <input type="submit" class="btn bg-custom text-light" value="Mark as Inactive" />
                                        </form>
                                        <?php if (Page::$pageData->user->hasPermission('staffmanage')) : ?>
                                            <form action="/admin/users" method="post">
                                                <input hidden name="action" value="deluser" />
                                                <input hidden value="" name="id" class="delconfirmuserid" />
                                                <input hidden name="permanent" value="1" />
                                                <input type="submit" class="btn btn-danger text-light" value="Permanently Delete" />
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="usermodal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="usermodal-title"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="/admin/users" method="post">
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
                                                        $("#flightTimeHrs").on('change', function() {
                                                            formatFlightTime();
                                                        });
                                                        $("#flightTimeMins").on('change', function() {
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
                                                <select class="form-control" name="status" id="usermodal-status">
                                                    <option>Pending</option>
                                                    <option>Active</option>
                                                    <option>Inactive</option>
                                                    <option>Declined</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="usermodal-admin">Admin Status</label>
                                                <select required class="form-control" name="admin" id="usermodal-admin">
                                                    <option value>Select</option>
                                                    <option value="0" id="usermodal-admin-0">Pilot</option>
                                                    <option value="1" id="usermodal-admin-1">Staff Member</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="usermodal-notes">Notes</label>
                                                <textarea class="form-control" name="notes" id="usermodal-notes"></textarea>
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Save">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="broadcastmodal" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Make Announcement</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Your announcement will be sent as a notification to all users.</p>
                                        <form action="/admin/users" method="post">
                                            <input hidden name="action" value="announce" />
                                            <div class="form-group">
                                                <label for="">Title</label>
                                                <input required type="text" class="form-control" name="title" maxlength="20" />
                                            </div>
                                            <div class="form-group">
                                                <label for="">Content</label>
                                                <input required type="text" class="form-control" name="content" maxlength="60" />
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Submit">
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
                                var userNotes = $(this).data("notes");

                                $("#usermodal-callsign").val(userCallsign);
                                $("#usermodal-name").val(userName);
                                $("#usermodal-email").val(userEmail);
                                $("#usermodal-ifc").val(userIfc);
                                $("#usermodal-joined").val(userJoined);
                                $("#usermodal-status").val(userStatus);
                                $("#usermodal-thrs").val(userThrs);
                                $("#usermodal-tflts").val(userTflts);
                                $("#usermodal-id").val(userId);
                                $("#usermodal-admin").val(userAdmin);
                                $("#usermodal-notes").text(userNotes);

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

                                $("#delconfirmmessage").text(`User: ${userCallsign}`);
                                $(".delconfirmuserid").val(userId);
                            });
                        </script>
                        <!-- Hide Inactive Users -->
                        <script>
                            $(document).ready(function() {
                                $("#toggle-inactive").click(function() {
                                    var state = $("#toggle-inactive").attr('data-state');
                                    if (state == '0') {
                                        $(".inactive-row").attr('style', 'display: none;');
                                        $("#toggle-inactive").attr('data-state', '1');
                                        $(this).text('Show Inactive Users');
                                    } else {
                                        $(".inactive-row").attr('style', 'display: auto;');
                                        $("#toggle-inactive").attr('data-state', '0');
                                        $(this).text('Hide Inactive Users');
                                    }
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
            <footer class="text-center container-fluid">
                <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $(".<?= Page::$pageData->active_dropdown ?>").collapse('show');
        });
    </script>
</body>

</html>