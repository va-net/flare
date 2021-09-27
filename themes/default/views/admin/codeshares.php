<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Codeshares Admin - ' . Page::$pageData->va_name);
$ACTIVE_CATEGORY = 'operations-management';
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
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <?php
                        if (file_exists(__DIR__ . '/../install/install.php') && !file_exists(__DIR__ . '/../.development')) {
                            echo '<div class="alert alert-danger text-center">The Install Folder still Exists! Please delete it immediately, it poses a severe security risk.</div>';
                        }

                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <h3>Codeshares Dashboard</h3>
                        <p>
                            Here you can see active codeshare requests from other VAs.
                            You can also make codeshare requests to send a selection of
                            your routes to other VAs.
                        </p>
                        <p>
                            Please note only one aircraft from any given route will be sent and any extra aircraft will need to be added by the other VA.
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
                                        <form id="confirmShareDelete" action="/admin/codeshares" method="post">
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

                        <form id="importcodeshare" action="/admin/codeshares" method="post">
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
                                <tr>
                                    <td colspan="4">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                        <script>
                            $.get("/api.php/codeshares", function(data, status) {
                                var html = data.result.map(c => {
                                    c.message = c.message.replace(/[\u00A0-\u9999<>\&]/g, function(i) {
                                        return '&#' + i.charCodeAt(0) + ';';
                                    });
                                    return `
                                    <tr><td class="align-middle">
                                    ${c.senderName}
                                    </td><td class="align-middle mobile-hidden">
                                    ${c.message}
                                    </td><td class="align-middle">
                                    ${c.routes.length}
                                    </td><td class="align-middle">
                                    <button value="${c.id}" form="importcodeshare" type="submit" class="btn bg-custom text-light" name="id"><i class="fa fa-file-download"></i></button>
                                    &nbsp;<button class="btn btn-danger deleteCodeshare" data-id="${c.id}"><i class="fa fa-trash"></i></button>
                                    </tr>
                                    `;
                                });
                                $("#codeshares-table").html(html);
                                $(".deleteCodeshare").click(function() {
                                    var id = $(this).data('id');
                                    $("#confirmShareDelete-id").val(id);
                                    $("#confirmShareDelete").modal('show');
                                });
                            });
                        </script>
                        <hr />
                        <h4>Make Codeshare Request</h4>
                        <form action="/admin/codeshares" method="post">
                            <input hidden name="action" value="newcodeshare" />
                            <div class="form-group">
                                <label for="codeshare-recipid">Recipient Codeshare ID</label>
                                <input required type="number" class="form-control" min="1" name="recipient" id="codeshare-recipid" />
                            </div>
                            <div class="form-group">
                                <label for="codeshare-routes">Routes</label>
                                <select multiple class="form-control selectpicker" data-live-search="true" id="codeshare-routes-select" required>
                                    <option value>Select</option>
                                    <?php
                                    foreach (Page::$pageData->routes as $r) {
                                        echo '<option value="' . $r['id'] . '">' . $r['fltnum'] . ' (' . $r['dep'] . ' - ' . $r['arr'] . ')</option>';
                                    }
                                    ?>
                                </select>
                                <div id="routes-inputs"></div>
                                <script>
                                    $("#codeshare-routes-select").on('changed.bs.select', function() {
                                        var routes = $("#codeshare-routes-select").val();
                                        var html = '';
                                        for (var i = 0; i < routes.length; i++) {
                                            html += `
                                                <input type="hidden" name="routes[]" value="${routes[i]}" />
                                            `.trim();
                                        }
                                        $("#routes-inputs").html(html);
                                    });
                                </script>
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
                <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
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