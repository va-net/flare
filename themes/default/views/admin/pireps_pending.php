<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Pending PIREPs - ' . Page::$pageData->va_name);
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
                        <h3>Pending PIREPs</h3>
                        <form id="acceptpirep" action="/admin/pireps" method="post">
                            <input hidden name="action" value="acceptpirep">
                        </form>
                        <form id="declinepirep" action="/admin/pireps" method="post">
                            <input hidden name="action" value="declinepirep">
                        </form>
                        <table class="table table-striped">
                            <thead class="bg-custom">
                                <tr>
                                    <th class="mobile-hidden">Callsign</th>
                                    <th class="mobile-hidden">Flight Number</th>
                                    <th>Dep<span class="mobile-hidden">arture</span></th>
                                    <th>Arr<span class="mobile-hidden">ival</span></th>
                                    <th>Flight Time</th>
                                    <th class="mobile-hidden">Multiplier</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $x = 0;
                                foreach (Page::$pageData->pending as $pirep) {
                                    echo '<tr><td class="align-middle mobile-hidden">';
                                    echo $pirep['pilotcallsign'];
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo $pirep['flightnum'];
                                    echo '</td><td class="align-middle">';
                                    echo $pirep['departure'];
                                    echo '</td><td class="align-middle">';
                                    echo $pirep['arrival'];
                                    echo '</td><td class="align-middle">';
                                    echo Time::secsToString($pirep["flighttime"]);
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo $pirep["multi"];
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn btn-success text-light" value="' . $pirep['id'] . '" form="acceptpirep" type="submit" name="accept"><i class="fa fa-check"></i></button>';
                                    echo '&nbsp;<button value="' . $pirep['id'] . '" form="declinepirep" type="submit" class="btn btn-danger text-light" name="decline"><i class="fa fa-times"></i></button>';
                                    echo '</td>';
                                    $x++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <style>
                .nav-tabs .nav-link {
                    color: #000 !important;
                }
            </style>
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