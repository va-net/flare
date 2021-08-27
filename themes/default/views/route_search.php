<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Routes - ' . Page::$pageData->va_name);
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <h3>Route Search</h3>
                        <a href="/routes" class="btn bg-custom mb-2">New Search</a>
                        <table class="table table-striped datatable-nosearch">
                            <thead class="bg-custom">
                                <tr>
                                    <th class="mobile-hidden">#</th>
                                    <th>Departure</th>
                                    <th>Arrival</th>
                                    <th class="mobile-hidden">Duration</th>
                                    <th class="mobile-hidden">Notes</th>
                                    <th><span class="mobile-hidden">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (Page::$pageData->routes as $route) {
                                    echo '<tr><td class="align-middle mobile-hidden">';
                                    echo $route->fltnum;
                                    echo '</td><td class="align-middle">';
                                    echo $route->dep;
                                    echo '</td><td class="align-middle">';
                                    echo $route->arr;
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo Time::secsToString($route->duration);
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo $route->notes;
                                    echo '</td><td class="align-middle">';
                                    $link = "/pireps/new?fnum={$route->fltnum}&dep={$route->dep}&arr={$route->arr}";
                                    echo '<a href="' . $link . '" class="btn bg-custom"><i class="fa fa-plane"></i></a>&nbsp;';
                                    echo '<a href="/routes/' . $route->id . '" class="btn bg-custom"><i class="fa fa-plus"></i></a>';
                                    echo '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>

</html>