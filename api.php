<?php 

require 'mysql.php';

if (!isset($_POST["command"]) || !isset($_POST["text"]) || !isset($_POST["token"])) {http_response_code(500); die();}



if ($_POST["command"] == 'approvepirep' && is_numeric($_POST["text"]) && $_POST["token"] == '95d296df-44ea1nb5e-d5f2a59') {

    $query = $conn->prepare("UPDATE pireps SET status=1 WHERE id=?;");

    $query->bind_param("i", $_POST["text"]);

    $ret = $query->execute();

    if ($ret === TRUE) {

        echo true;

    } else {

        http_response_code(500);

    }

} elseif ($_POST["command"] == 'denypirep' && is_numeric($_POST["text"]) && $_POST["token"] == '95d296df-44ea1nb5e-d5f2a59') {

    $query = $conn->prepare("UPDATE pireps SET status=2 WHERE id=?;");

    $query->bind_param("i", $_POST["text"]);

    $ret = $query->execute();

    if ($ret === TRUE) {

        echo true;

    } else {

        http_response_code(500);

    }

} elseif ($_POST["command"] == 'holdpirep' && is_numeric($_POST["text"]) && $_POST["token"] == '95d296df-44ea1nb5e-d5f2a59') {

    $query = $conn->prepare("UPDATE pireps SET status=3 WHERE id=?;");

    $query->bind_param("i", $_POST["text"]);

    $ret = $query->execute();

    if ($ret === TRUE) {

        echo true;

    } else {

        http_response_code(500);

    }

} elseif ($_POST["command"] == 'delnews' && is_numeric($_POST["text"]) && $_POST["token"] == '2b116ed0-86ca417d8-57f41ad') {

    $query = $conn->prepare("DELETE FROM news WHERE id=?;");

    $query->bind_param("i", $_POST["text"]);

    $ret = $query->execute();

    if ($ret === TRUE) {

        echo true;

    } else {

        http_response_code(500);

    }

} elseif ($_POST["command"] == 'delroute' && is_numeric($_POST["text"]) && $_POST["token"] == 'b8bd10dc-d3f1a5fb9-05e0b91') {

    $query = $conn->prepare("DELETE FROM routes WHERE id=?;");

    $query->bind_param("i", $_POST["text"]);

    $ret = $query->execute();

    if ($ret === TRUE) {

        echo true;

    } else {

        http_response_code(500);

    }

} elseif ($_POST["command"] == 'delrank' && is_numeric($_POST["text"]) && $_POST["token"] == 'b8bd10dc-d3f1a5fb9-05e0b91') {

    $query = $conn->prepare("DELETE FROM ranks WHERE id=?;");

    $query->bind_param("i", $_POST["text"]);

    $ret = $query->execute();

    if ($ret === TRUE) {

        echo true;

    } else {

        http_response_code(500);

    }

} elseif ($_POST["command"] == 'delftype' && is_numeric($_POST["text"]) && $_POST["token"] == 'b8bd10dc-d3f1a5fb9-05e0b91') {

    $query = $conn->prepare("DELETE FROM flighttypes WHERE id=?;");

    $query->bind_param("i", $_POST["text"]);

    $ret = $query->execute();

    if ($ret === TRUE) {

        echo true;

    } else {

        http_response_code(500);

    }

} elseif ($_POST["command"] == 'delmulti' && is_numeric($_POST["text"]) && $_POST["token"] == 'b8bd10dc-d3f1a5fb9-05e0b91') {

    $query = $conn->prepare("DELETE FROM multipliers WHERE id=?;");

    $query->bind_param("i", $_POST["text"]);

    $ret = $query->execute();

    if ($ret === TRUE) {

        echo true;

    } else {

        http_response_code(500);

    }

} elseif ($_POST["command"] == 'delevent' && is_numeric($_POST["text"]) && $_POST["token"] == 'b8bd10dc-d3f1a5fb9-05e0b91') {

    $query = $conn->prepare("DELETE FROM events WHERE id=?;");

    $query->bind_param("i", $_POST["text"]);

    $ret = $query->execute();

    if ($ret === FALSE) {

        http_response_code(500);

    } else {

        echo true;

    }

} elseif ($_POST["command"] == 'runacars') {

    $allflights = json_decode(file_get_contents('https://atcegserverside4--webpage.repl.co/flights'), true);

    $foundthem = false;

    $fplfound = false;

    foreach ($allflights as $fItem) {

        if ($fItem["CallSign"] == $_SESSION["pilotinfo"]["callsign"] && $fItem["UserID"] == $_SESSION["pilotinfo"]["ifuserid"]) {

            $foundthem = true;

            // Get All Flight Plans on Expert

            $fpls = json_decode(file_get_contents("http://infinite-flight-public-api.cloudapp.net/v1/GetFlightPlans.aspx?apikey=e1a46fce-700d-41bc-99f2-f2812dc8cfc4&sessionid=7e5dcd44-1fb5-49cc-bc2c-a9aab1f6a856"), true);

            // Get Spped and Alt records for flight so we can grab Flight Time

            $finfo = json_decode(file_get_contents("http://infinite-flight-public-api.cloudapp.net/v1/FlightDetails.aspx?apikey=e1a46fce-700d-41bc-99f2-f2812dc8cfc4&flightid={$fItem['FlightID']}"), true);

            // Get departure airport altitude

            $daptAlt = floor($finfo[0]["Altitude"]);

            // Find the first report where pilot was significantly above the dep airport alt and grab the timestamp

            foreach ($finfo as $spot) {

                if (floor($spot["Altitude"]) - $daptAlt >= 10 && !isset($uptime)) {

                    $uptime = $spot["Time"];

                }

            }

            //$uptime = $finfo[0]["Time"];

            // Convert dep timestamp to unix and subtract last report time also in unix - finds flight time in secs

            $flighttime = floor($finfo[count($finfo) - 1]["Time"] / 10000000) - floor($uptime / 10000000);

            echo 'Flight Time HH:MM: '.secsToHrMin($flighttime).'<br />';

            echo 'Flight Time S: '.$flighttime.'<br />';

            $t = floor($uptime / 10000000) - 11644473600;

            echo 'Departure Timestamp - UNIX: '.$t.'<br />';

            $t = floor($finfo[count($finfo) - 1]["Time"] / 10000000) - 11644473600;

            echo 'Arrival Timestamp - UNIX: '.$t.'<br />';

            // Loop through Flight plans to find the one we want 

            foreach ($fpls as $fplItem) {

                if ($fplItem["FlightID"] == $fItem["FlightID"]) { 

                    // Show form so pilot can add info and update departure and/or arrival if req

                    $fplfound = true;

                    ?>

                    <form action="?page=acars" method="post">

                        <input hidden name="action" value="acarspirep">

                        <p>Please input the following details that we were unable to grab from Infinite Flight.</p>

                        <?php if (strlen($fplItem["DepartureAirportCode"]) != 4) { ?>

                        <div class="form-group">

                            <label for="depicao">Departure</label>

                            <input required type="text" class="form-control" maxlength="4" minlength="4" name="depicao" id="depicao" value="<?php echo $fplItem["DepartureAirportCode"]; ?>">

                        </div>

                        <?php } else {

                            echo '<input hidden name="depicao" value="'.$fplItem["DepartureAirportCode"].'">';

                        } 

                        if (strlen($fplItem["DestinationAirportCode"]) !== 4) { ?>

                            <div class="form-group">

                                <label for="arricao">Arrival</label>

                                <input required type="text" class="form-control" maxlength="4" minlength="4" name="arricao" id="arricao" value="<?php echo $fplItem["DestinationAirportCode"]; ?>">

                            </div>

                        <?php } else {

                            echo '<input hidden name="arricao" value="'.$fplItem["DestinationAirportCode"].'">';

                        }

                        ?>

                        <div class="form-group">

                            <label for="ftype">Flight Type</label>

                            <select class="form-control" name="ftype" id="ftype" required>

                            <option value>Select</option>

                                <?php

                                    $theftypes = selectMultiple("SELECT * FROM flighttypes;");

                                    while ($ftItem= $theftypes->fetch_assoc()) {

                                        echo '<option value="'.$ftItem["code"].'">'.$ftItem["name"].'</option>';

                                    }

                                ?>

                            </select>

                        </div>

                        <div class="form-group">

                            <label for="fnum">Flight Number</label>

                            <input required type="text" class="form-control" name="fnum" id="fnum">

                        </div>

                        <input hidden name="ftime" value="<?php echo $flighttime; ?>">

                        <input hidden name="aircraft" value="<?php echo $fItem["LiveryID"]; ?>">

                        <div class="form-group">

                            <label for="multicode">Multiplier Code (if applicable)</label>

                            <input type="number" class="form-control" name="multicode" id="multicode">

                        </div>

                        <input type="submit" class="btn bg-virgin" value="Validate & Submit">

                    </form>

                <?php }

            }

        }

    }

    if ($foundthem == false) {

        echo '<div class="alert alert-warning text-center">Could Not Find your Flight. Please ensure you are on Expert Server.</div>';

    } elseif ($fplfound == false) {

        echo '<div class="alert alert-warning text-center">Could not find your Flight Plan. Please ensure you have filed a flight plan.</div>';

    }

} elseif ($_POST["command"] == 'getEvent' && is_numeric($_POST["text"])) {

    $query = $conn->prepare("SELECT eventslots.*, events.name, events.description, events.type, events.date, events.time, events.depart, events.arrive, events.server, aircraft.name AS aircraftname, events.id AS eid FROM ((eventslots INNER JOIN events ON eventslots.eventid=events.id) INNER JOIN aircraft ON aircraft.id=events.aircraftentity) WHERE eventslots.eventid=?;");

    $query->bind_param("i", $_POST["text"]);

    $query->execute();

    $res = array();

    $query = $query->get_result();

    while ($x = $query->fetch_assoc()) {

        array_push($res, $x);

    } ?>

    

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

    <div class="modal-content">

        <div class="modal-header">

            <h4 class="modal-title"><?php echo $res[0]["name"]; ?></h4>

            <button type="button" class="close" data-dismiss="modal">&times;</button>

        </div>

        <div class="modal-body">

            <?php echo $res[0]["description"]; ?>

            <p>
                <b>Date & Time: </b><?php echo $res[0]["date"].' '.$res[0]["time"].'Z'; ?><br />
                
                <b>Departure: </b><?php echo $res[0]["depart"]; ?><br />

                <b>Arrival: </b><?php echo $res[0]["arrive"]; ?><br />

                <b>Aircraft: </b><?php echo $res[0]["aircraftname"]; ?><br />

                <b>Server: </b><?php echo $res[0]["server"]; ?><br />

            </p>

            <table class="table table-striped"><thead class="bg-virgin"><tr><th>Gate</th><th>Pilot</th></tr></thead><tbody id="eventInfoGates">

            <?php

                $isPilotComing = false;

                $gatesAvail = false;

                foreach ($res as $item) {

                    echo '<tr><td>';

                    echo $item["gate"];

                    echo '</td><td>';

                    if ($item["pilotentity"] == '') {

                        echo 'Vacant';

                        $gatesAvail = true;

                    } else {

                        if (is_numeric($item["pilotentity"])) {

                            $usr = getUserInfo($item["pilotentity"]);

                            echo $usr["name"]." ({$usr['callsign']})";

                        } else {

                            echo $item["pilotentity"];

                        }

                    }

                    echo '</td></tr>';



                    if ($_SESSION["pilotinfo"]["id"] == $item["pilotentity"]) {

                        $isPilotComing = $item["id"];

                    }

                }

            ?>

            </tbody></table>

            <?php

                if ($isPilotComing == false && $gatesAvail == true) {

                    echo '<form action="?page=events" method="post"><input hidden name="action" value="addESignup"><input hidden name="eid" value="'.$res[0]["eid"].'"><input type="submit" class="btn bg-virgin" value="Sign Up"></form>';

                } elseif ($isPilotComing != false) {

                    echo '<form action="?page=events" method="post"><input hidden name="action" value="remESignup"><input hidden name="eid" value="'.$res[0]["eid"].'"><input type="submit" class="btn btn-danger" value="Remove My Signup"></form>';                    

                } else {

                    echo 'No Free Gates &#128542;';

                }

            ?>

        </div>

    </div>

    </div>



<?php 
} elseif ($_POST["command"] == '/handbook' && $_POST["token"] == 'F37wB46luGv8Q9PSwGPRpm6v') {
    echo '*Pilot Handbook*
https://bit.ly/VGVA-SOP';
    http_response_code(200);
} elseif ($_POST["command"] == '/ranks' && $_POST["token"] == 'F37wB46luGv8Q9PSwGPRpm6v') {
    $query = selectMultiple("SELECT aircraft.name AS aircraft, ranks.hoursreq AS hrs, ranks.name AS rank FROM aircraft INNER JOIN ranks ON aircraft.rankreq=ranks.id ORDER BY hoursreq ASC;");
    $ranks = array();
    while ($q = $query->fetch_assoc()) {
        if (!isset($ranks[$q["rank"].','.$q["hrs"]])) {
            $ranks[$q["rank"].','.$q["hrs"]] = array();
        }
        array_push($ranks[$q["rank"].','.$q["hrs"]], $q["aircraft"]);
    }
    foreach ($ranks as $rank => $aircraft) {
       $rank = explode(",", $rank);
       echo '*'.$rank[0].'*
> ';
       echo 'Requires '.secsToHrMin($rank[1]).' Hours
> ';
       echo 'Can Fly ';
       $i = 0;
       foreach ($aircraft as $ac) {
           if ($i == 0) {
               echo $ac;
           } else {
               echo ', '.$ac;
           }
           $i = $i + 1;
       }
       echo '
       
';
    }
    http_response_code(200);
} elseif ($_POST["command"] == '/findrouteto' && $_POST["token"] == 'F37wB46luGv8Q9PSwGPRpm6v') {
    $query = selectMultiple("SELECT routes.*, aircraft.name AS aircraftname FROM routes INNER JOIN aircraft ON routes.aircraftid=aircraft.id WHERE arr='".mysqli_real_escape_string($conn, $_POST["text"])."';");
    if ($query === FALSE) {
        echo 'No Routes Found';
    } else {
        while ($q = $query->fetch_assoc()) {
            echo $q["typecode"].$q["fltnum"].': '.$q["dep"].'-'.$q["arr"].' - '.$q["aircraftname"].' ('.secsToHrMin($q["duration"]).')
';
        }
    }
    http_response_code(200);
} elseif ($_POST["command"] == '/findroutefrom' && $_POST["token"] == 'F37wB46luGv8Q9PSwGPRpm6v') {
    $query = selectMultiple("SELECT routes.*, aircraft.name AS aircraftname FROM routes INNER JOIN aircraft ON routes.aircraftid=aircraft.id WHERE dep='".mysqli_real_escape_string($conn, $_POST["text"])."';");
    if ($query === FALSE) {
        echo 'No Routes Found';
    } else {
        while ($q = $query->fetch_assoc()) {
            echo $q["typecode"].$q["fltnum"].': '.$q["dep"].'-'.$q["arr"].' - '.$q["aircraftname"].' ('.secsToHrMin($q["duration"]).')
';
        }
    }
    http_response_code(200);
} elseif ($_POST["command"] == 'toggledarkmode') {
    if (!isset($_SESSION["darkmode"]) || $_SESSION["darkmode"] == false) {
        $_SESSION["darkmode"] = true;
    } else {
        $_SESSION["darkmode"] = false;
    }
    http_response_code(200);
    echo true;
} else {

    http_response_code(500);

}