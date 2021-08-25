<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';
$user = new User();

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}

if (Input::get('method') === 'acars' && !empty(Input::get('server'))) {
    if (Input::get('server') == 'casual') {
        echo '<div class="alert alert-dabger">ACARS is not currently available on the Casual Server</div>';
        die();
    }
    $response = VANet::runAcars(Input::get('server'));
    if ($response['status'] != 0) {
        echo '<div class="alert alert-warning">We couldn\'t find you on the server. Ensure that you have filed a flight plan, 
        and are still connected to Infinite Flight. Then, reload the page and hit that button again.</div>';
        die();
    }
    echo '<p>Nice! We\'ve found you. If you\'ve finished your flight and at the gate, go ahead and fill out the details below. 
    If not, reload the page once you\'re done and click that button again.</p>';


    $aircraft = Aircraft::findAircraft($response['result']["aircraftLiveryId"]);
    if (!$aircraft) {
        echo '<div class="alert alert-warning">You\'re Flying an Aircraft that isn\'t in this VA\'s Fleet!</div>';
        die();
    }
    echo '<hr />';
    echo '<form action="update.php" method="post">';
    echo '
    <input hidden value="filepirep" name="action" />
    <input hidden value="' . date("Y-m-d") . '" name="date" />
    <input hidden value="' . Time::secsToString($response['result']["flightTime"]) . '" name="ftime" />
    <input hidden value="' . $aircraft->id . '" name="aircraft" />
    ';

    // Check VANet was able to determine departure ICAO
    if ($response['result']["departure"] != null) {
        echo '<input hidden value="' . $response['result']["departure"] . '" name="dep" />';
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
    if ($response['result']["arrival"] != null) {
        echo '<input hidden value="' . $response['result']["arrival"] . '" name="arr" />';
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
        <input required type="text" class="form-control" name="fnum" />
    </div>

    <div class="form-group">
        <label for="fuel">Fuel Used (kg)</label>
        <input required type="number" class="form-control" name="fuel" />
    </div>

    <div class="form-group">
        <label for="multi">Multiplier Code (if applicable)</label>
        <input type="number" class="form-control" maxlength="6" minlength="6" id="multi" name="multi">
    </div>

    <input type="submit" class="btn bg-custom" value="File PIREP" />
    ';

    echo '</form>';
} elseif (Input::get('method') === 'liveriesforaircraft' && !empty(Input::get('data'))) {
    $all = Aircraft::fetchLiveryIdsForAircraft(Input::get('data'));
    foreach ($all as $name => $id) {
        echo '<option value="' . $id . '">' . $name . '</option>';
    }
} elseif (Input::get('method') === 'codeshares' && $user->hasPermission('opsmanage')) {
    $all = VANet::getCodeshares();
    foreach ($all as $codeshare) {
        echo '<tr><td class="align-middle">';
        echo $codeshare["senderName"];
        echo '</td><td class="align-middle mobile-hidden">';
        echo $codeshare["message"];
        echo '</td><td class="align-middle">';
        echo count($codeshare["routes"]);
        echo '</td><td class="align-middle">';
        echo '<button value="' . $codeshare['id'] . '" form="importcodeshare" type="submit" class="btn bg-custom text-light" name="id"><i class="fa fa-file-download"></i></button>';
        echo '&nbsp;<button class="btn btn-danger deleteCodeshare" data-id="' . $codeshare["id"] . '"><i class="fa fa-trash"></i></button>';
    }
}
