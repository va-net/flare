<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('PIREPs - ' . Config::get('va/name'));

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
    <style>
        #loader {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 1;
            width: 150px;
            height: 150px;
            margin: -75px 0 0 -75px;
            width: 120px;
            height: 120px;
        }
    </style>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php include './includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php include './includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper"><div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div></div>
                    <div class="loaded">
                        <?php
                        if (Session::exists('errorrecent')) {
                            echo '<div class="alert alert-danger text-center">Error: '.Session::flash('errorrecent').'</div>';
                        }
                        if (Session::exists('successrecent')) {
                            echo '<div class="alert alert-success text-center">'.Session::flash('successrecent').'</div>';
                        }
                        ?>
                        <?php if ($user->data()->ifuserid != null): ?>
                            <?php if (Input::get('page') === 'recents'): ?>
                                <section id="recents">
                                    <h3>My Recent PIREPs</h3>
                                    <p>Showing your 30 Most Recent PIREPs</p>
                                    <br>
                                    <?php
                                    $pireps = $user->recentPireps($user->data()->id, 30);
                                    if (!$pireps) {
                                        echo '<h5><b>No recent PIREPs<b></h5>';
                                    } else { ?>
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
                                            foreach ($pireps as $pirep) {
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
                                                echo '<button class="btn text-light btn-primary" data-toggle="modal" data-target="#pirep'.$x.'"><i class="fa fa-edit"></i></button>';
                                                echo '</td></tr>';
                                                $x++;
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <!-- pirep modals -->
                                    <?php
                                    $x = 0;
                                    foreach ($pireps as $pirep) {
                                        echo
                                        '
                                        <div class="modal fade" id="pirep'.$x.'" tabindex="-1" role="dialog" aria-labelledby="pirep'.$x.'label" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="pirep'.$x.'title">Edit PIREP</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="update.php" method="post">
                                                            <input hidden name="action" value="editpirep">
                                                            <input hidden name="id" value="'.$pirep['id'].'">
                                                            <div class="form-group">
                                                                <label for="date">Date of Flight</label>
                                                                <input required type="date" value="'.date_format(date_create($pirep['date']), 'Y-m-d').'" class="form-control" name="date">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="fnum">Flight Number</label>
                                                                <input required type="number" min="1" class="form-control" name="fnum" value="'.$pirep['number'].'">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="dep">Departure</label>
                                                                <input required maxlength="4" minlength="4" type="text" value="'.$pirep['departure'].'" class="form-control" name="dep">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="arr">Arrival</label>
                                                                <input required maxlength="4" minlength="4" type="text" value="'.$pirep['arrival'].'" class="form-control" name="arr">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="aircraft">Aircraft</label>
                                                                <select class="form-control" name="aircraft" required>
                                                                    <option value>Select</option>
                                                                    ';
                                                                    $aircraftlist = $user->getAvailableAircraft();
                                                                    foreach($aircraftlist as $aircraft) {
                                                                        if ($aircraft['name'] === $pirep['aircraft']) {
                                                                            echo '<option selected="selected">'.$aircraft['name'].'</option>';
                                                                            continue;
                                                                        }
                                                                        echo '<option>'.$aircraft['name'].'</option>';
                                                                    }
                                                                    echo '
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="multi">Multiplier number (if applicable)</label>
                                                                <input required type="number" class="form-control" maxlength="6" minlength="6" id="multi" name="multi" value="'.$pirep['multi'].'">
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
                                    }
                                    ?>
                                </section>
                            <?php elseif (Input::get('page') === 'new'): ?>
                                <br>
                                <section id="filepirep">
                                    <h3>File PIREP</h3>
                                    <br />
                                    <?php
                                    if (Session::exists('error')) {
                                        echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                                    }
                                    if (Session::exists('success')) {
                                        echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                                    }
                                    ?>
                                    <form action="update.php" method="post">
                                        <input hidden name="action" value="filepirep">
                                        <div class="form-group">
                                            <label for="flightdate">Date of Flight</label>
                                            <input required type="date" value="<?php echo date("Y-m-d") ?>" class="form-control" name="date">
                                        </div>
                                        <div class="form-group">
                                            <label for="fnum">Flight Number</label>
                                            <input requried type="text" class="form-control" name="fnum" value="<?= escape(Input::get('fnum')) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="hrs">Flight Time</label>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <input required type="number" min="0" id="flightTimeHrs" class="form-control" placeholder="Hours" />
                                                </div>
                                                <div class="col-sm-6">
                                                    <input required type="number" min="0" id="flightTimeMins" class="form-control" placeholder="Minutes" />
                                                </div>
                                            </div>
                                            <input hidden name="ftime" id="flightTimeFormatted" class="form-control" value="<?= escape(Input::get('ftime')) ?>" required />
                                            <script>
                                                function formatFlightTime() {
                                                    var hrs = $("#flightTimeHrs").val();
                                                    var mins = $("#flightTimeMins").val();
                                                    $("#flightTimeFormatted").val(hrs + ":" + mins);
                                                }

                                                function reverseFormatFlightTime() {
                                                    var formatted = $("#flightTimeFormatted").val();
                                                    if (formatted != '') {
                                                        var split = formatted.split(":");
                                                        var hrs = split[0];
                                                        var mins = split[1];
                                                        $("#flightTimeHrs").val(hrs);
                                                        $("#flightTimeMins").val(mins);
                                                    }
                                                }

                                                $(document).ready(function() {
                                                    $("#flightTimeHrs").keyup(function() {
                                                        formatFlightTime();
                                                    });
                                                    $("#flightTimeMins").keyup(function() {
                                                        formatFlightTime();
                                                    });
                                                    reverseFormatFlightTime();
                                                });
                                            </script>
                                        </div>
                                        <div class="form-group">
                                            <label for="fuel">Fuel Used (kg)</label>
                                            <input required type="number" class="form-control" name="fuel" value="<?= escape(Input::get('fuel')) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="dep">Departure</label>
                                            <input required type="text" class="form-control" maxlength="4" placeholder="ICAO" maxlength="4" minlength="4" name="dep" value="<?= escape(Input::get('dep')) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="arr">Arrival</label>
                                            <input required type="text" class="form-control" maxlength="4" placeholder="ICAO" maxlength="4" minlength="4" name="arr" value="<?= escape(Input::get('arr')) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="aircraft">Aircraft</label>
                                            <select class="form-control" name="aircraft" required>
                                                <option value>Select</option>
                                                <?php
                                                $aircraftlist = $user->getAvailableAircraft();

                                                foreach($aircraftlist as $aircraft) {
                                                    $notes = $aircraft['notes'] == null ? '' : ' - '.$aircraft['notes'];
                                                    if ($aircraft["name"] == Input::get("aircraft")) {
                                                        echo '<option value="'.$aircraft['id'].'" selected>'.$aircraft['name'].' ('.$aircraft['liveryname'].')'.$notes.'</option>';
                                                    } else {
                                                        echo '<option value="'.$aircraft['id'].'">'.$aircraft['name'].' ('.$aircraft['liveryname'].')'.$notes.'</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="multi">Multiplier Code (if applicable)</label>
                                            <input type="text" class="form-control" maxlength="6" minlength="6" id="multi" name="multi" />
                                        </div>
                                        <input type="submit" class="btn text-light bg-custom" value="Submit">
                                    </form>
                                </section>
                            <?php endif; ?>
                        <?php else: ?>
                            <h3>Setup PIREPs</h3>
                            <?php
                                $server = 'casual';
                                $force = Config::get('FORCE_SERVER');
                                if ($force != 0 && $force != 'casual') $server = $force;
                            ?>
                            <p>Before you can start filing PIREPs, we need to grab a bit of data from Infinite Flight. Please spawn in on the <?= ucfirst($server); ?> Server, and ensure that you <b>set your callsign to your assigned one (<?= $user->data()->callsign ?>, if you've forgotten!).</b> Then, click the button below.</p>
                            <form method="post" action="update.php">
                                <input hidden name="action" value="setuppireps" />
                                <input hidden name="callsign" value="<?= $user->data()->callsign ?>" />
                                <input type="submit" class="btn text-light bg-custom" value="Find Me">
                            </form>
                        <?php endif; ?>
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