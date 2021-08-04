<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Pilot Awards - ' . Page::$pageData->va_name);
$ACTIVE_CATEGORY = 'user-management';
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
    <script type="application/json" id="all-awards">
        <?= Json::encode(Page::$pageData->awards) ?>
    </script>
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
                        <h3>Pilot Awards</h3>
                        <button class="mb-2 btn bg-custom" data-toggle="modal" data-target="#addAward">Add Award</button>
                        <div id="addAward" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Add Award</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post">
                                            <input hidden name="action" value="addaward" />
                                            <div class="form-group">
                                                <label for="name">Name</label>
                                                <input required type="text" class="form-control" name="name" id="name" />
                                            </div>
                                            <div class="form-group">
                                                <label for="description">Description</label>
                                                <input type="text" class="form-control" name="description" id="description" />
                                            </div>
                                            <div class="form-group">
                                                <label for="image">Image URL</label>
                                                <input required type="url" class="form-control" name="image" id="image" />
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Add Award">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form id="delete-award" method="post">
                            <input hidden name="action" value="delaward" />
                        </form>
                        <table class="table table-striped">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (Page::$pageData->awards as $award) : ?>
                                    <tr>
                                        <td class="align-middle">
                                            <img src="<?= $award->imageurl ?>" style="height: 25px; width: auto;" />
                                        </td>
                                        <td class="align-middle">
                                            <?= $award->name ?>
                                        </td>
                                        <td class="align-middle">
                                            <button class="mr-1 btn bg-custom give-award" data-awardid="<?= $award->id ?>">
                                                <i class="fa fa-medal"></i>
                                            </button>
                                            <button class="mr-1 btn bg-custom edit-award" data-awardid="<?= $award->id ?>">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger" form="delete-award" name="id" value="<?= $award->id ?>">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div id="editAward" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Edit Award</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post">
                                            <input hidden name="action" value="editaward" />
                                            <input hidden name="id" id="edit-id" value="" />
                                            <div class="form-group">
                                                <label for="edit-name">Name</label>
                                                <input required type="text" class="form-control" name="name" id="edit-name" />
                                            </div>
                                            <div class="form-group">
                                                <label for="description">Description</label>
                                                <input type="text" class="form-control" name="description" id="edit-description" />
                                            </div>
                                            <div class="form-group">
                                                <label for="edit-image">Image URL</label>
                                                <input required type="url" class="form-control" name="image" id="edit-image" />
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Save">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="giveAward" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Give Award</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post">
                                            <input hidden name="action" value="giveaward" />
                                            <input hidden name="award" id="give-id" value="" />
                                            <div class="form-group">
                                                <label for="give-pilot">Pilot</label>
                                                <select required class="form-control" name="pilot" id="give-pilot">
                                                    <option value>Select</option>
                                                    <?php foreach (Page::$pageData->all_users as $u) : ?>
                                                        <option value="<?= $u['id'] ?>"><?= $u['name'] ?> (<?= $u['callsign'] ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Save">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
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
            $(".<?= $ACTIVE_CATEGORY ?>").collapse('show');
        });

        $(".edit-award").click(function() {
            var id = $(this).data('awardid');

            var awards = JSON.parse($("#all-awards").text());
            var award = awards.find((a) => a.id == id);
            if (!award) return;

            $("#edit-id").val(award.id);
            $("#edit-name").val(award.name);
            $("#edit-description").val(award.description);
            $("#edit-image").val(award.imageurl);
            $("#editAward").modal('show');
        });

        $(".give-award").click(function() {
            var id = $(this).data('awardid');

            $("#give-id").val(id);
            $("#giveAward").modal('show');
        });
    </script>
</body>

</html>