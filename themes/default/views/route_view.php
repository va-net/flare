<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Route - ' . Page::$pageData->va_name);
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
                        <h3>View Route</h3>
                        <hr />

                        <?php if (Page::$pageData->route !== FALSE) : ?>
                            <div class="row text-left">
                                <div class="col-md">
                                    <h4>Basic Info</h4>
                                    <ul>
                                        <li>
                                            <b>Flight Number:</b> <?= Page::$pageData->route->fltnum ?>
                                        </li>
                                        <li>
                                            <b>Departure:</b> <?= Page::$pageData->route->dep ?>
                                        </li>
                                        <li>
                                            <b>Arrival:</b> <?= Page::$pageData->route->arr ?>
                                        </li>
                                        <li>
                                            <b>Approx. Duration:</b> <?= Time::secsToString(Page::$pageData->route->duration) ?>
                                        </li>
                                        <li>
                                            <b>Notes:</b> <?= empty(Page::$pageData->route->notes) ? 'N/A' : escape(Page::$pageData->route->notes) ?>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md">
                                    <h4>Aircraft</h4>
                                    <ul>
                                        <?php
                                        foreach (Page::$pageData->aircraft as $a) {
                                            echo "<li>{$a->name} ({$a->liveryname})</li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                            <hr />
                            <h4>Previous PIREPs</h4>
                            <table class="table table-striped datatable-nosearch">
                                <thead class="bg-custom">
                                    <tr>
                                        <th>Date</th>
                                        <th>Pilot</th>
                                        <th>Aircraft</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach (Page::$pageData->pireps as $p) {
                                        echo '<tr><td>';
                                        echo $p->date;
                                        echo '</td><td>';
                                        echo $p->pilotname;
                                        echo '</td><td>';
                                        echo $p->aircraftname;
                                        echo '</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        <?php else : ?>
                            <p>Route Not Found</p>
                        <?php endif; ?>
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