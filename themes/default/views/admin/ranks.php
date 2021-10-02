<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Ranks Admin - ' . Page::$pageData->va_name);
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
                                        <form action="/admin/ranks" method="post">
                                            <input hidden name="action" value="addrank">
                                            <div class="form-group">
                                                <label for="name">Name</label>
                                                <input type="text" name="name" class="form-control" placeholder="Second Officer" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="time">Flight time required (in hours)</label>
                                                <input type="number" name="time" class="form-control" placeholder="50" required>
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Add Rank">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form id="delrank" action="/admin/ranks" method="post">
                            <input hidden name="action" value="delrank" />
                        </form>

                        <table class="table table-striped datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Name</th>
                                    <th>Min. Hours</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (Page::$pageData->ranks as $rank) {
                                    echo '<tr><td class="align-middle">';
                                    echo $rank->name;
                                    echo '</td><td class="align-middle">';
                                    echo Time::secsToString($rank->timereq);
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn btn-primary text-light editRank" 
                                    data-id="' . $rank->id . '" data-name="' . $rank->name . '" 
                                    data-minhrs="' . ($rank->timereq / 3600) . '">
                                    <i class="fa fa-edit"></i></button>';
                                    echo '&nbsp;<button class="btn btn-danger text-light" 
                                    value="' . $rank->id . '" form="delrank" name="delete">
                                    <i class="fa fa-trash"></i></button>';
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
                                        <form action="/admin/ranks" method="post">
                                            <input hidden name="action" value="editrank">
                                            <input hidden name="id" id="rankmodal-id">
                                            <div class="form-group">
                                                <label for="name">Name</label>
                                                <input type="text" name="name" class="form-control" id="rankmodal-name" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="time">Flight Time Required (in hours)</label>
                                                <input type="number" min="0" name="time" class="form-control" id="rankmodal-hours" required>
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Save">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
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
            $(".<?= Page::$pageData->active_dropdown ?>").collapse('show');
        });
    </script>
</body>

</html>