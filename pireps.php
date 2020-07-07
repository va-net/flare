<?php
require_once './core/init.php';

$user = new User();

Page::setTitle('Home - ' . $user->data()->callsign);

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
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #E4181E;">
        <?php include './includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <div class="col-lg-3 p-3 bg-light text-left mobile-hidden" id="desktopMenu" style="height: 100%;">
                    <h3>Pilot Panel - <?= $user->data()->callsign ?></h3>
                    <hr class="mt-0 divider" />
                    <a href="home.php" id="homelink" class="panel-link"><i class="fa fa-home"></i>&nbsp;Pilot Home</a><br>
                    <a href="pireps.php#filepirep" id="filepireplink" class="panel-link"><i class="fa fa-plane"></i>&nbsp;File PIREP</a><br>
                    <a href="pireps.php#recents" id="mypirepslink" class="panel-link"><i class="fa fa-folder"></i>&nbsp;My PIREPs</a><br>
                    <a href="routes.php" id="routeslink" class="panel-link"><i class="fa fa-database"></i>&nbsp;Route Database</a><br>
                    <a href="acars.php" id="acarslink" class="panel-link"><i class="fa fa-sync"></i>&nbsp;ACARS</a><br>
                    <?php
                    $permissions = Permissions::getAll();

                    if ($user->hasPermission('admin')) {
                        echo '<br>';
                        echo '<h3>Admin Panel</h3>';
                        echo '<hr class="mt-0 divider">';
                        foreach ($permissions as $permission => $data) {
                            if ($user->hasPermission($permission)) {
                                echo '<a href="admin.php?page=' . $permission . '" id="userslink" class="panel-link"><i class="fa ' . $data['icon'] . '"></i>&nbsp;' . $data['name'] . '</a><br>';
                            }
                        }
                    }
                    ?>
                    <br>
                    <a href="logout.php" class="panel-link"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
                </div>
                <div class="col-lg-9 p-3 main-content">
                    <div id="loader" class="spinner-border spinner-border-sm text-danger"></div>
                    <div class="tab-content" id="tc">
                        <div class="tab-pane container active" id="home" style="display: none;">
                            <section id="recents">
                                <h4>My Recent PIREPs</h4>
                                <p>Showing your 30 most recent PIREPs</p>
                                <?php
                                if (Session::exists('errorrecent')) {
                                    echo '<div class="alert alert-danger text-center">Error: '.Session::flash('errorrecent').'</div>';
                                }
                                if (Session::exists('successrecent')) {
                                    echo '<div class="alert alert-success text-center">'.Session::flash('successrecent').'</div>';
                                }
                                ?>
                                <br>
                                <table class="table table-striped">
                                    <thead class="bg-virgin">
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
                                        $pireps = $user->recentPireps($user->data()->id, 30);
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
                                            echo '<button class="btn text-light" style="background-color: #E4181E;" data-toggle="modal" data-target="#pirep'.$x.'"><i class="fa fa-edit"></i></button>';
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
                                                            <input type="number" min="1" class="form-control" name="fnum" value="'.$pirep['number'].'">
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
                                                            <label for="hrs">Flight Time</label>
                                                            <input type="time" class="form-control" name="ftime" value="'.Time::secsToString($pirep['flighttime']).'">
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
                                                            <input type="number" class="form-control" maxlength="6" minlength="6" id="multi" name="multi" value="'.$pirep['multi'].'">
                                                        </div>
                                                        <input type="submit" class="btn bg-virgin" value="Save">    
                                                    </form>                                      
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    ';
                                    $x++;
                                }
                                ?>
                            </section>
                            <br>
                            <section id="filepirep">
                                <h4>File PIREP</h4>
                                <br>
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
                                        <input type="number" min="1" class="form-control" name="fnum" value="<?= Input::get('fnum') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="hrs">Flight Time</label>
                                        <input type="time" class="form-control" name="ftime" value="<?= Input::get('ftime') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="dep">Departure</label>
                                        <input required type="text" class="form-control" maxlength="4" placeholder="ICAO" maxlength="4" minlength="4" name="dep" value="<?= Input::get('dep') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="arr">Arrival</label>
                                        <input required type="text" class="form-control" maxlength="4" placeholder="ICAO" maxlength="4" minlength="4" name="arr" value="<?= Input::get('arr') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="aircraft">Aircraft</label>
                                        <select class="form-control" name="aircraft" required>
                                            <option value>Select</option>
                                            <?php
                                            $aircraftlist = $user->getAvailableAircraft();

                                            foreach($aircraftlist as $aircraft) {
                                                echo '<option>'.$aircraft['name'].'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="multi">Multiplier number (if applicable)</label>
                                        <input type="number" class="form-control" maxlength="6" minlength="6" id="multi" name="multi" value="0">
                                    </div>
                                    <input type="submit" class="btn text-light" style="background-color: #E4181E;" value="Submit">
                                </form>
                            </section>
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