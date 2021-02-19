<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Fleet Admin - ' . Page::$pageData->va_name);
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
                                        <form action="/admin/operations/fleet" method="post">
                                            <input hidden name="action" value="addaircraft">
                                            <div class="form-group">
                                                <label for="aircraft">Type</label>
                                                <select required class="form-control" name="aircraftselect" id="aircraftselect" required>
                                                    <option value>Select</option>
                                                    <?php
                                                    $allac = Aircraft::fetchAllAircraftFromVANet();
                                                    foreach ($allac as $id => $name) {
                                                        echo '<option value="' . $id . '">' . $name . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="rank">Livery</label>
                                                <select required class="form-control" name="livery" id="liveriesselect" required>
                                                    <option disabled>Loading...</option>
                                                </select>
                                            </div>
                                            <script>
                                                $("#aircraftselect").change(function() {
                                                    $.ajax({
                                                        url: "/vanet.php",
                                                        type: "POST",
                                                        data: {
                                                            method: "liveriesforaircraft",
                                                            data: $(this).val()
                                                        },
                                                        success: function(html) {
                                                            $("#liveriesselect").empty();
                                                            $("#liveriesselect").append("<option>Select</option>");
                                                            $("#liveriesselect").append(html);
                                                        }
                                                    });
                                                });
                                            </script>
                                            <div class="form-group">
                                                <label for="rank">Minimum Rank</label>
                                                <select required class="form-control" name="rank" required>
                                                    <option value>Select</option>
                                                    <?php
                                                    foreach (Page::$pageData->ranks as $rank) {
                                                        echo '<option value="' . $rank->id . '">' . $rank->name . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="notes">Notes</label>
                                                <input type="text" class="form-control" maxlength="12" name="notes" id="notes" />
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Add Aircraft">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="confirmFleetDelete">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h4 class="modal-title">Are You Sure?</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>

                                    <div class="modal-body">
                                        Are you sure you want to delete this Aircraft?
                                        <form id="deleteaircraft" action="/admin/operations/fleet" method="post">
                                            <input hidden name="action" value="deleteaircraft" />
                                            <input hidden name="delete" id="confirmFleetDelete-id" />
                                            <input type="submit" class="btn btn-danger" value="Delete" />
                                        </form>
                                    </div>

                                    <div class="modal-footer text-center justify-content-center">
                                        <button type="button" class="btn bg-custom" data-dismiss="modal">Cancel</button>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <table class="table table-striped datatable">
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
                                foreach (Page::$pageData->fleet as $aircraft) {
                                    echo '<tr><td class="align-middle">';
                                    echo $aircraft->name;
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo $aircraft->liveryname;
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo $aircraft->rank;
                                    echo '</td><td class="align-middle">';
                                    echo '&nbsp;<button data-id="' . $aircraft->id . '" class="btn btn-danger text-light deleteFleet"><i class="fa fa-trash"></i></button>';
                                    echo '&nbsp;<button class="btn btn-primary editFleet" data-acName="' . $aircraft->name . ' (' . $aircraft->liveryname . ')' . '" 
                                    data-rankReq="' . $aircraft->rankreq . '" data-id="' . $aircraft->id . '" data-notes="' . $aircraft->notes . '">
                                    <i class="fa fa-edit"></i></button>';
                                    echo '</td>';
                                }
                                ?>
                            </tbody>
                        </table>

                        <script>
                            $(".deleteFleet").click(function() {
                                var id = $(this).data('id');
                                $("#confirmFleetDelete-id").val(id);
                                $("#confirmFleetDelete").modal('show');
                            });
                        </script>

                        <div class="modal fade" id="fleetedit">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="fleetedit-title"></h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="/admin/operations/fleet" method="post">
                                            <input hidden name="action" value="editfleet" />
                                            <input hidden name="id" id="fleetedit-id" />
                                            <div class="form-group">
                                                <label for="fleetedit-rank">Minimum Rank</label>
                                                <select required class="form-control" name="rank" id="fleetedit-rank">
                                                    <?php
                                                    foreach (Page::$pageData->ranks as $r) {
                                                        echo '<option value="' . $r->id . '">' . $r->name . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="fleetedit-notes">Notes</label>
                                                <input type="text" class="form-control" maxlength="12" name="notes" id="fleetedit-notes" />
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Save" />
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            $(".editFleet").click(function() {
                                var acName = $(this).data('acname');
                                var acRank = $(this).data('rankreq');
                                var acId = $(this).data('id');
                                var acNotes = $(this).data('notes');

                                $("#fleetedit-title").text("Edit Aircraft: " + acName);
                                $("#fleetedit-id").val(acId);
                                $("#fleetedit-rank").val(acRank);
                                $("#fleetedit-notes").val(acNotes);

                                $("#fleetedit").modal('show');
                            });
                        </script>
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