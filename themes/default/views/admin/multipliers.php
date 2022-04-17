<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Multipliers Admin - ' . Page::$pageData->va_name);
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
                        <h3>Manage Multipliers</h3>
                        <p>
                            Multiplier codes allow your pilots to gain multiplied flight time easily. They can simply enter their real flight time
                            when filing their PIREP, enter a multiplier code, and their flight time multiplier will be applied automatically.
                        </p>
                        <h4>Active Multipliers</h4>
                        <form id="multiarticle" action="/admin/pireps/multipliers" method="post">
                            <input hidden name="action" value="deletemulti" />
                        </form>
                        <table class="table table-striped">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Multiplication</th>
                                    <th>Min. Rank</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (Page::$pageData->multis as $m) {
                                    echo '<tr><td class="align-middle">';
                                    echo $m->code;
                                    echo '</td><td class="align-middle">';
                                    echo $m->name;
                                    echo '</td><td class="align-middle">';
                                    echo $m->multiplier . 'x';
                                    echo '</td><td class="align-middle">';
                                    echo $m->minrank ?? 'N/A';
                                    echo '</td><td class="align-middle">';
                                    echo '<button value="' . $m->id . '" form="multiarticle" type="submit" class="btn btn-danger text-light" name="delete"><i class="fa fa-trash"></i></button>';
                                    echo '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                        <br />
                        <h4>Add Multiplier</h4>
                        <form action="/admin/pireps/multipliers" method="post">
                            <input hidden name="action" value="addmulti" />
                            <div class="form-group">
                                <label for="multi-name">Name</label>
                                <input required type="text" maxlength="120" class="form-control" name="name" id="multi-name" />
                            </div>
                            <div class="form-group">
                                <label for="multi-multi">Multiplication</label>
                                <input required type="number" step="0.1" class="form-control" name="multi" id="multi-multi" />
                            </div>
                            <div class="form-group">
                                <label for="multi-minrank">Minimum Rank</label>
                                <select required type="number" step="0.01" class="form-control" name="minrank" id="multi-minrank">
                                    <option value="">None</option>
                                    <?php foreach (Page::$pageData->ranks as $r) : ?>
                                        <option value="<? $r->id ?>"><?= $r->name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="submit" class="btn bg-custom" value="Save" />
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
            $(".<?= Page::$pageData->active_dropdown ?>").collapse('show');
        });
    </script>
</body>

</html>