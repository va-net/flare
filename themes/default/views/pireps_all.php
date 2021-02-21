<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('All PIREPs - ' . Page::$pageData->va_name);
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
                        <?php
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <h3>My Recent PIREPs</h3>
                        <p>Showing your 30 Most Recent PIREPs</p>
                        <br />
                        <table class="table table-striped datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th class="mobile-hidden">Flight Number</th>
                                    <th>Route</th>
                                    <th class="mobile-hidden">Date</th>
                                    <th class="mobile-hidden">Aircraft</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $x = 0;
                                foreach (Page::$pageData->pireps as $pirep) {
                                    echo '<tr><td class="mobile-hidden align-middle">';
                                    echo $pirep["number"];
                                    echo '</td><td class="align-middle">';
                                    echo $pirep["departure"] . '-' . $pirep["arrival"];
                                    echo '</td><td class="mobile-hidden align-middle">';
                                    echo date_format(date_create($pirep['date']), 'Y-m-d');
                                    echo '</td><td class="mobile-hidden align-middle">';
                                    echo $pirep["aircraft"];
                                    echo '</td><td class="align-middle">';
                                    echo $pirep["status"];
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn text-light btn-primary" data-toggle="modal" data-target="#pirep' . $x . '"><i class="fa fa-edit"></i></button>';
                                    echo '</td></tr>';
                                    $x++;
                                }
                                ?>
                            </tbody>
                        </table>

                        <?php
                        $x = 0;
                        foreach (Page::$pageData->pireps as $pirep) {
                            echo
                            '
                            <div class="modal fade" id="pirep' . $x . '" tabindex="-1" role="dialog" aria-labelledby="pirep' . $x . 'label" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="pirep' . $x . 'title">Edit PIREP</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/pireps" method="post">
                                                <input hidden name="action" value="editpirep">
                                                <input hidden name="id" value="' . $pirep['id'] . '">
                                                <div class="form-group">
                                                    <label for="date">Date of Flight</label>
                                                    <input required type="date" value="' . date_format(date_create($pirep['date']), 'Y-m-d') . '" class="form-control" name="date">
                                                </div>
                                                <div class="form-group">
                                                    <label for="fnum">Flight Number</label>
                                                    <input required type="text" class="form-control" name="fnum" value="' . $pirep['number'] . '">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dep">Departure</label>
                                                    <input required maxlength="4" minlength="4" type="text" value="' . $pirep['departure'] . '" class="form-control" name="dep">
                                                </div>
                                                <div class="form-group">
                                                    <label for="arr">Arrival</label>
                                                    <input required maxlength="4" minlength="4" type="text" value="' . $pirep['arrival'] . '" class="form-control" name="arr">
                                                </div>
                                                <input type="submit" class="btn bg-custom" value="Save">    
                                            </form>                                      
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ';
                            $x++;
                        }
                        ?>
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