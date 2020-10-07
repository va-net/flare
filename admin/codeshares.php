<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Codeshares Admin - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('opsmanage') || !$user->hasPermission('admin')) {
    Redirect::to('/home.php');
}

$ACTIVE_CATEGORY = 'operations-management';
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
                        <h3>Codeshares Dashboard</h3>
                        <p>
                            Here you can see active codeshare requests from other VAs. 
                            You can also make codeshare requests to share routes with other VAs.
                        </p>
                        <!-- Delete Codeshare Confirmation Modal -->
                        <div class="modal fade" id="confirmShareDelete">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">

                            <div class="modal-header">
                                <h4 class="modal-title">Are You Sure?</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>

                            <div class="modal-body">
                                Are you sure you want to delete (and hence deny) this Codeshare Request?
                                <form id="confirmShareDelete" action="/update.php" method="post">
                                    <input hidden name="action" value="deletecodeshare" />
                                    <input hidden name="delete" id="confirmShareDelete-id" />
                                    <input type="submit" class="btn btn-danger" value="Delete" />
                                </form>
                            </div>

                            <div class="modal-footer text-center justify-content-center">
                                <button type="button" class="btn bg-custom" data-dismiss="modal">Cancel</button>
                            </div>

                            </div>
                        </div>
                        </div>

                        <form id="importcodeshare" action="/update.php" method="post">
                            <input hidden name="action" value="importcodeshare" />
                        </form>

                        <h4>Pending Codeshare Requests</h4>
                        <table class="table table-striped">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Sender</th>
                                    <th class="mobile-hidden">Message</th>
                                    <th># Routes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="codeshares-table">
                                <tr><td colspan="4">Loading...</td></tr>
                            </tbody>
                        </table>
                        <script>
                            $.post("/vanet.php", {
                                "method": "codeshares"
                            }, function(data, status) {
                                $("#codeshares-table").html(data);
                                $(".deleteCodeshare").click(function() {
                                    var id = $(this).data('id');
                                    $("#confirmShareDelete-id").val(id);
                                    $("#confirmShareDelete").modal('show');
                                })
                            });
                        </script>
                        <hr />
                        <h4>Make Codeshare Request</h4>
                        <form action="/update.php" method="post">
                            <input hidden name="action" value="newcodeshare" />
                            <div class="form-group">
                                <label for="codeshare-recipid">Recipient Codeshare ID</label>
                                <input required type="number" class="form-control" min="1" name="recipient" id="codeshare-recipid" />
                            </div>
                            <div class="form-group">
                                <label for="codeshare-routes">Routes</label>
                                <input required type="text" class="form-control" name="routes" id="codeshare-routes" />
                                <small class="text-muted">Comma-Separated List of Flight Numbers</small>
                            </div>
                            <div class="form-group">
                                <label for="codeshare-msg">Optional Message</label>
                                <input type="text" class="form-control" name="message" id="codeshare-msg" />
                            </div>
                            <input type="submit" class="btn bg-custom" value="Send Request" />
                        </form>
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