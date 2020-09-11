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

if (Input::get('method') === 'events-table') {
    $all = VANet::getEvents();
    foreach ($all as $event) {
        if ($event["visible"]) {
            echo '<tr><td class="align-middle">';
            echo $event["name"];
            echo '</td><td class="align-middle">';
            echo $event["departureAirport"];
            echo '</td><td class="align-middle">';
            echo '<a href="events.php?page=view&event='.urlencode($event["id"]).'" class="btn bg-custom">View</button>';
            echo '</td></tr>';
        }
    }
} elseif (Input::get('method') === 'events-admin' && $user->hasPermission('opsmanage')) {
    $all = VANet::getEvents();
    foreach ($all as $event) {
        echo '<tr><td class="align-middle">';
        echo $event["name"];
        echo '</td><td class="align-middle">';
        echo $event["departureAirport"];
        echo '</td><td class="align-middle">';
        echo '<button class="btn btn-primary editEvent" data-name="'.$event["name"].'" data-desc="'.str_replace('"', '', $event["description"]).'" 
        data-dep="'.$event["departureAirport"].'" data-arr="'.$event["arrivalAirport"].'" data-aircraft="'.$event["aircraft"]["liveryID"].'" 
        data-vis="'.$event["visible"].'" data-server="'.$event["server"].'" data-id="'.$event["id"].'"><i class="fa fa-edit"></i></button>';
        echo '&nbsp;<button data-id="'.$event['id'].'" class="btn btn-danger text-light deleteEvent"><i class="fa fa-trash"></i></button>';
        echo '</td></tr>';
    }
} elseif (Input::get('method') === 'event' && !empty(Input::get('data'))) {
    $event = VANet::findEvent(Input::get('event'));
    header("Content-Type: application/json");
    echo Json::encode($event);
} elseif (Input::get('method') === 'acars' && !empty(Input::get('server'))) {
    $response = VANet::runAcars(Input::get('server'));
    if (array_key_exists('status', $response)) {
        if ($response['status'] == 404 || $response['status'] == 409) {
            echo '<div class="alert alert-warning">We couldn\'t find you on the server. Ensure that you have filed a flight plan, 
            and are still connected to Infinite Flight. Then, reload the page and hit that button again.</div>';
            die();
        }
    }
    echo '<p>Nice! We\'ve found you. If you\'ve finished your flight and at the gate, go ahead and fill out the details below. 
    If not, reload the page once you\'re done and click that button again.</p>';

    
    $aircraft = Aircraft::findAircraft($response["aircraft"]);
    if (!$aircraft) {
        echo '<div class="alert alert-warning">You\'re Flying an Aircraft that isn\'t in this VA\'s Fleet!</div>';
        die();
    }
    echo '<hr />';
    echo '<form action="update.php" method="post">';
    echo '
    <input hidden value="filepirep" name="action" />
    <input hidden value="'.date("Y-m-d").'" name="date" />
    <input hidden value="'.Time::secsToString($response["flightTime"]).'" name="ftime" />
    <input hidden value="'.$aircraft->id.'" name="aircraft" />
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
} elseif (Input::get('method') === 'liveriesforaircraft' && !empty(Input::get('data'))) {
    $all = Aircraft::fetchLiveryIdsForAircraft(Input::get('data'));
    foreach ($all as $name => $id) {
        echo '<option value="'.$id.'">'.$name.'</option>';
    }
}