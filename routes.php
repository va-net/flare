<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('Routes - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './includes/header.php'; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-custom">
        <?php include './includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <?php include './includes/sidebar.php'; ?>
            <div class="col-lg-9 p-3 main-content">
                <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    <div class="tab-content" id="tc">
                        <div class="tab-pane container active" id="home" style="display: none;">
                            <h3>Route Database</h3>
                            <p>Please note that importing from a CSV is not yet supported, and will be coming in a later build.</p>
                            <table class="table table-striped">
                                <thead class="bg-custom">
                                    <tr>
                                        <th>Flight Number</th>
                                        <th>Departure</th>
                                        <th>Arrival</th>
                                        <th>Aircraft</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $all = Route::fetchAll();
                                    $x = 0;

                                    while ($all->count() > $x) {
                                        echo '<tr><td class="align-middle">';
                                        echo $all->results()[$x]->fltnum;
                                        echo '</td><td class="align-middle">';
                                        echo $all->results()[$x]->dep;
                                        echo '</td><td class="align-middle">';
                                        echo $all->results()[$x]->arr;
                                        echo '</td><td class="align-middle">';
                                        echo Aircraft::idToName($all->results()[$x]->aircraftid);
                                        echo '</td></tr>';
                                        $x++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php include './includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>
</html>