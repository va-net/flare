<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('ACARS - '.Config::get('va/name'));

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
            <div class="row m-0 p-0">
                <?php include './includes/sidebar.php'; ?>
                <div class="col-lg-9 p-3 main-content">
                <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    <div class="tab-content" id="tc">
                        <div class="tab-pane container active" id="home" style="display: none;">
                            <h3>ACARS</h3>
                            <?php if (VANet::isGold()): ?>
                                <?php if ($user->data()->ifuserid != null): ?>
                                    <?php
                                    $curl = new Curl;
                                    $request = $curl->get(Config::get('vanet/base_url').'/api/acars', array(
                                        'callsign' => $user->data()->callsign,
                                        'userid' => $user->data()->ifuserid,
                                        'server' => 'expert',
                                        'apikey' => Config::get('vanet/api_key')
                                    ));
                                    $response = Json::decode($request->body);
                                    if (array_key_exists('status', $response)) {
                                        if ($response['status'] == 404 || $response['status'] == 409) {
                                            echo 'Hmm, looks like we couldn\'t find you on the server. Ensure that you have filed a flight plan, and are still connected to Infinite Flight. Then, refresh the page.';
                                        }
                                    } else {
                                        echo 'Nice! We\'ve found you. 
                                        Make sure you\'ve finished your flight (don\'t leave this page open, you\'ll need
                                        to refresh it) then go ahead and fill out these details.';

                                        $aircraft = Aircraft::fetchAircraftFromVANet($response["aircraft"], "LiveryID");
                                        if (count($aircraft) == 0) {
                                            echo '
                                            <div class="alert alert-warning">
                                            Are you a beta tester? Because we can\'t tell what aircraft you\'re flying. Try a manual PIREP instead.
                                            </div>';
                                        } else {
                                            $aircraft = $aircraft[0];
                                            if (!Aircraft::exists($aircraft["liveryID"])) {
                                                echo '<div class="alert alert-danger">Error: You\'re Flying an Aircraft that isn\'t in this VA\'s Fleet</div>';
                                            } else {
                                                echo '<hr />';
                                                echo '<form action="update.php" method="post">';
                                                echo '
                                                <input hidden value="filepirep" name="action" />
                                                <input hidden value="'.date("Y-m-d").'" name="date" />
                                                <input hidden value="'.Time::secsToString($response["flightTime"]).'" name="ftime" />
                                                <input hidden value="'.$aircraft["aircraftName"].'" name="aircraft" />
                                                ';
    
                                                // Check VANet was able to determine departure ICAO
                                                if ($response["departure"] != null) {
                                                    echo '<input hidden value="'.$response["departure"].'" name="dep" />';
                                                } else {
                                                    // ICAO could not be determined. Show UI for input
                                                    echo '
                                                    <div class="form-group">
                                                        <label for="dep">Departure</label>
                                                        <input requried class="form-control" type="text" minlength="4" maxlength="4" name="dep" id="dep" placeholder="ICAO" />
                                                    </div>
                                                    ';
                                                }
    
                                                // Check VANet was able to determine arrival ICAO
                                                if ($response["arrival"] != null) {
                                                    echo '<input hidden value="'.$response["arrival"].'" name="arr" />';
                                                } else {
                                                    // ICAO could not be determined. Show UI for input
                                                    echo '
                                                    <div class="form-group">
                                                        <label for="arr">Arrival</label>
                                                        <input requried class="form-control" type="text" minlength="4" maxlength="4" name="arr" id="arr" placeholder="ICAO" />
                                                    </div>
                                                    ';
                                                }
    
                                                echo '
                                                <div class="form-group">
                                                    <label for="fnum">Flight Number</label>
                                                    <input required type="number" min="1" class="form-control" name="fnum" />
                                                </div>
    
                                                <div class="form-group">
                                                    <label for="fuel">Fuel Used (kg)</label>
                                                    <input required type="number" class="form-control" name="fuel" />
                                                </div>
    
                                                <div class="form-group">
                                                    <label for="multi">Multiplier Number (if applicable)</label>
                                                    <input type="number" class="form-control" maxlength="6" minlength="6" id="multi" name="multi">
                                                </div>
    
                                                <input type="submit" class="btn bg-custom" value="File PIREP" />
                                                ';
    
                                                echo '</form>';
                                            }
                                        }
                                    }
                                    ?>
                                <?php else: ?>
                                    <p>Looks like you haven't yet setup PIREPs! Go to <a href="./pireps.php">pireps</a>, set them up, and then come back here and try again.</p>
                                <?php endif ?> 
                            <?php else: ?>
                                <p>In order to run ACARS, <?= Config::get('va/name') ?> needs to sign up to VANet Gold. You can take a look at it <a href="https://vanet.app/getstarted" target="_blank">here</a>!</p>
                            <?php endif; ?>
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