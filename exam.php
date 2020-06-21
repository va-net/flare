<?php require 'header.php';
if (!isset($_SESSION["authed"]) || $_SESSION["authed"] != true) {
    echo '<script>window.location.href="/";</script>';
    die();
} elseif ($_SESSION["pilotinfo"]["recruitstage"] != 1) {
    echo '<script>window.location.href="pilotpanel.php";</script>';
    die();
}
?>

<div class="container-fluid my-2 px-2 text-center">
<h2>Virgin Virtual Group - Pilot Exam</h2>

<?php
if (isset($_POST["1"])) {
    $answers = ["b", "d", "d", "d", "a", "c", "a", "b", "a", "a", "b", "c", "b", "b"];
    $right = 0;
    for ($i=0;$i<14;$i++) {
        if ($_POST[strval($i + 1)] == $answers[$i]) {
            $right = $right + 1;
        }
    }
    $mark = $right / 14 * 100;
    if ($mark < 65) {
        echo '<div class="alert alert-danger text-center">Your mark is '.floor($mark).'%. This is not a pass. Your application has been denied. If you wish to dispute this, please contact our staff.</div>';
        runQ("UPDATE pilots SET recruitstage=2 WHERE id={$_SESSION['pilotinfo']['id']}");
    } else {
        echo '<div class="alert alert-success text-center">You have passed with a mark of '.round($mark).'%. <br /><a href="pilotpanel.php">Click here to continue</a></div>';
        $stmt = runQ("UPDATE pilots SET recruitstage=3 WHERE id={$_SESSION['pilotinfo']['id']};");
        if ($stmt === FALSE) {
            echo '<div class="alert alert-danger text-center">MySQL Error</div>';
        } else {
            reloadPilotInfo();
            $mark = floor($mark);
            $mes = urlencode("*New Pilot to Invite*\r\n{$_SESSION['pilotinfo']['email']} - {$_SESSION['pilotinfo']['name']}\r\nPassed Exam with {$mark}%");
            $res = file_get_contents("https://VGVA-Webhook-Server--webpage.repl.co?token=f280edb5-f685-4810-bb9f-19661f1389be&channel=recruitment&message=$mes");
            if ($res != 'ok') {
                sendEmail("virginvirtual0@gmail.com", "Pilot Passed Exam", str_replace("\r\n", "<br />", urldecode($mes)));
            }
        }
    }
} else {
?>

<p>
    <u>Rules</u><br />
    There is no time limit<br />
    You must complete the exam in one sitting<br />
    Please attempt all questions<br />
</p>

<form method="post">
    <div class="card mb-3">
        <div class="card-header"><h4>Where can you tune into a VOR?</h4></div>
        <div class="card-body">
            <select required name="1" class="form-control">
                <option value>Select</option>
                <option value="a">The NAV Popup</option>
                <option value="b">The Map</option>
                <option value="c">The ATC Popup</option>
                <option value="d">The System Popup</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>What units are used for Altitude, Speed and Distance respectively?</h4></div>
        <div class="card-body">
            <select required name="2" class="form-control">
                <option value>Select</option>
                <option value="a">Meters, Kilometers per Hour, Kilometers</option>
                <option value="b">Feet, Miles per Hour, Miles</option>
                <option value="c">Feet, Knots, Miles</option>
                <option value="d">Feet, Knots, Nautical Miles</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>Should you encounter a stall warning, which of these options should you NOT TAKE?</h4></div>
        <div class="card-body">
            <select required name="3" class="form-control">
                <option value>Select</option>
                <option value="a">Increase Power</option>
                <option value="b">Pitch Down</option>
                <option value="c">Go Faster</option>
                <option value="d">Bank Left</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>On takeoff, there are three speeds you should have in mind. What are they?</h4></div>
        <div class="card-body">
            <select required name="4" class="form-control">
                <option value>Select</option>
                <option value="a">V1, V2, V3</option>
                <option value="b">VQ, VR, VS</option>
                <option value="c">V1, VR, V3</option>
                <option value="d">V1, VR, V2</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>Which of these IS NOT a use of the rudder?</h4></div>
        <div class="card-body">
            <select required name="5" class="form-control">
                <option value>Select</option>
                <option value="a">Turning Once Airborne</option>
                <option value="b">Maintaining Attitude when Banking</option>
                <option value="c">Manoeuvring on the Ground</option>
                <option value="d">"Kicking it Central" during a Crosswind Landing</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>At what altitude does speed begin to be measured in Mach speeds?</h4></div>
        <div class="card-body">
            <select required name="6" class="form-control">
                <option value>Select</option>
                <option value="a">FL100</option>
                <option value="b">FL250</option>
                <option value="c">FL280</option>
                <option value="d">FL300</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>When calling inbound on Tower with no Approach Frequency and intending to fly an ILS, what should you say?</h4></div>
        <div class="card-body">
            <select required name="7" class="form-control">
                <option value>Select</option>
                <option value="a">Inbound for Landing</option>
                <option value="b">Inbound on the ILS</option>
                <option value="c">Inbound on the Visual</option>
                <option value="d">Inbound on the GPS</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>If you receive only a Clearance on Tower after being handed off from approach, what type of approach are you flying?</h4></div>
        <div class="card-body">
            <select required name="8" class="form-control">
                <option value>Select</option>
                <option value="a">Visual Approach</option>
                <option value="b">ILS Approach</option>
                <option value="c">Radar Vector</option>
                <option value="d">Flight Following</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>When given a sequence such as <i>"VGVA123, number 2, traffic to follow is on final"</i>, who must ensure that you maintain a safe distance from said traffic?</h4></div>
        <div class="card-body">
            <select required name="9" class="form-control">
                <option value>Select</option>
                <option value="a">You, the Pilot</option>
                <option value="b">The Pilot on Final</option>
                <option value="c">The Pilot Behind You</option>
                <option value="d">The Controller</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>True or False; both Tower and Ground can instruct you to Cross a Runway?</h4></div>
        <div class="card-body">
            <select required name="10" class="form-control">
                <option value>Select</option>
                <option value="a">True</option>
                <option value="b">False</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>In which of these situations should you NOT have Strobe Lights on?</h4></div>
        <div class="card-body">
            <select required name="11" class="form-control">
                <option value>Select</option>
                <option value="a">Crossing a Runway</option>
                <option value="b">Taxiing to the Runway</option>
                <option value="c">Climbing and Below 10,000ft</option>
                <option value="d">Descending and Above 10,000ft</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>Which of these DOES NOT warrant the use of the 'Heavy' Callsign Prefix?</h4></div>
        <div class="card-body">
            <select required name="12" class="form-control">
                <option value>Select</option>
                <option value="a">Airbus A330</option>
                <option value="b">Boeing 747</option>
                <option value="c">Airbus A321</option>
                <option value="d">Boeing 787</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>True of False; 100% Power is Recommended on Takeoff.</h4></div>
        <div class="card-body">
            <select required name="13" class="form-control">
                <option value>Select</option>
                <option value="a">True</option>
                <option value="b">False</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>What is the Pattern Altitude for Airliners in Infinite Flight?</h4></div>
        <div class="card-body">
            <select required name="14" class="form-control">
                <option value>Select</option>
                <option value="a">1000ft AGL</option>
                <option value="b">1500ft AGL</option>
                <option value="c">1500ft MSL</option>
                <option value="d">2000ft AGL</option>
            </select>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>Do you want to Join VGVA? (Unmarked)</h4></div>
        <div class="card-body">
            <select required name="15" class="form-control">
                <option value>Select</option>
                <option value="a">Yes</option>
                <option value="b">Of Course</option>
            </select>
        </div>
    </div>
    <input type="submit" class="btn bg-virgin btn-lg" value="Finish & View Results">
</form>
</div>

<?php } ?>

<?php require 'footer.php'; ?>