<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'header.php'; 
if (!isset($_SESSION["authed"]) || $_SESSION["authed"] != true) {
    echo '<script>window.location.href="/";</script>';
    die();
} elseif ($_SESSION["pilotinfo"]["recruitstage"] == 1) {
    echo '<script>window.location.href="exam.php";</script>';
    die();
}
?>

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
<?php if (isset($_SESSION["darkmode"]) && $_SESSION["darkmode"] == true) { ?>
<script>
    $(document).ready(function() {
        $("body").addClass("bg-dark");
        $("body").addClass("text-light");
        $("#desktopMenu").removeClass("bg-light");
        $("#desktopMenu").addClass("bg-dark");
        $(".panel-link").addClass("panel-link-dark");
        $(".panel-link-dark").removeClass("panel-link");
        $("#desktopMenu").addClass("border");
        $("#desktopMenu").addClass("border-white");
        $("table").addClass("text-light");
        $(".card").addClass("bg-dark");
        $(".divider").addClass("divider-dark");
        $(".divider-dark").removeClass("divider");
        $("*").on("shown.bs.modal", function() {
            $(".modal-content").addClass("bg-dark");
            $("table").addClass("text-light");
        });
    });
</script>
<?php } ?>
<div class="container-fluid mt-4 text-center" style="overflow: auto;">
<div class="row m-0 p-0">
    <div class="col-lg-3 p-3 bg-light text-left mobile-hidden" id="desktopMenu" style="height: 100%;">
        <h3>Pilot Panel</h3>
        <hr class="mt-0 divider" />
        <a href="#home" id="homelink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-home"></i>&nbsp;Pilot Home</a><br />
        <a href="#filepirep" id="filepireplink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-plane"></i>&nbsp;File PIREP</a><br />
        <a href="#mypireps" id="mypirepslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-folder"></i>&nbsp;My PIREPs</a><br />
        <a href="#routedb" id="routeslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-database"></i>&nbsp;Route Database</a><br />
        <a href="#featured" id="featuredlink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-map-marked-alt"></i>&nbsp;Featured Routes</a><br />
        <a href="#events" id="eventslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-calendar"></i>&nbsp;Events</a><br />
        <a href="#acars" id="acarslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-sync"></i>&nbsp;ACARS <?php if(!isset($_SESSION["pilotinfo"]["ifuserid"]) || $_SESSION["pilotinfo"]["ifuserid"] == ''){echo '<i class="text-danger fa fa-exclamation-circle"></i>';} ?></a><br />
        <a href="assets/StandardOperatingProcedures.pdf" id="soplink" class="panel-link" target="_blank"><i class="fa fa-file-download"></i>&nbsp;Handbook</a><br /><br />
        <?php if ($_SESSION["pilotinfo"]["recruitstage"] > 3)  { ?>
            <a href="#usermanage" id="userslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-users"></i>&nbsp;User Management</a><br />
            <a href="#recruitment" id="recruitlink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-id-card"></i>&nbsp;Recruitment</a><br />
            <a href="#pirepmanage" id="pirepmodlink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-user-shield"></i>&nbsp;PIREP Management</a><br />
            <a href="#newsmanage" id="newslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-newspaper"></i>&nbsp;News Management</a><br />
            <a href="#emailpilots" id="emaillink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-envelope"></i>&nbsp;Email Pilots</a><br />
            <a href="#wfrmanage" id="wfrlink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-shield-alt"></i>&nbsp;WFR Management</a><br />
            <a href="#eventmanage" id="eventmodlink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-plane-departure"></i>&nbsp;Event Management</a><br />
            <a href="#opsmanage" id="opslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-file-alt"></i>&nbsp;Operations Management</a><br />
            <a href="#stats" id="statslink" data-toggle="tab" onclick="clearActive()" class="panel-link"><i class="fa fa-chart-pie"></i>&nbsp;VA Statistics</a><br />
            <a href="assets/StaffOperationalGuide.pdf" id="staffguidelink" class="panel-link" target="_blank"><i class="fa fa-file-download"></i>&nbsp;Staff Guide</a><br /><br />
        <?php } ?>
        <a href="logout.php" class="panel-link"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
    </div>
    <div class="col-lg-9 p-3 main-content">
    <div id="loader" class="spinner-border spinner-border-sm text-danger"></div>
    <div class="tab-content" id="tc" style="display: none;">
        <div class="tab-pane container active" id="home">
            <h3>Pilot Home</h3>
            <p>Welcome to the Virgin Virtual Group Pilot Panel <?php echo $_SESSION["pilotinfo"]["name"]; ?>!</p>

            <h3>Your Profile</h3>
            <?php
                if (isset($_POST["action"]) && $_POST["action"] == 'editmyprofile') {
                    $callsigns = selectMultiple("SELECT callsign FROM pilots WHERE NOT id={$_SESSION['pilotinfo']['id']};");
                    $match = false;
                    while ($csItem = $callsigns->fetch_assoc()) {
                        if ($_POST["callsign"] == $csItem["callsign"]) {
                            $match = true;
                        }
                    }
                    if ($match == true) {
                        echo '<div class="alert alert-warning text-center">Could Not Update Profile: That Callsign is Already in Use by Anther Pilot</div>';
                    } else {
                        $epRet = runQ('UPDATE pilots SET name="'.mysqli_real_escape_string($conn, $_POST["name"]).'", callsign="'.mysqli_real_escape_string($conn, $_POST["callsign"]).'", email="'.mysqli_real_escape_string($conn, $_POST["email"]).'", ifc="'.mysqli_real_escape_string($conn, $_POST["ifc"]).'" WHERE id="'.mysqli_real_escape_string($conn, $_SESSION["pilotinfo"]["id"]).'"');
                        if ($epRet !== TRUE) {
                            echo '<div class="alert alert-danger text-center">MySQL Error: '.$epRet.'</div>';
                        } else {
                            echo '<div class="alert alert-success text-center">Profile Updated Successfully</div>';
                            reloadPilotInfo();
                        }
                    }
                } elseif (isset($_POST["action"]) && $_POST["action"] == 'changepass') {
                    if (password_verify($_POST["oldpass"], $_SESSION["pilotinfo"]["password"])) {
                        $hashpass = password_hash($_POST["newpass"], PASSWORD_ARGON2ID);
                        $cpRet = runQ('UPDATE pilots SET password="'.$hashpass.'" WHERE id='.$_SESSION["pilotinfo"]["id"]);
                        if ($cpRet !== TRUE) {
                            echo '<div class="alert alert-danger">MySQL Error: '.$cpRet.'</div>';
                        } else {
                            echo '<div class="alert alert-success">Password Changed Successfully</div>';
                            reloadPilotInfo();
                        }
                    } else {
                        echo '<div class="alert alert-danger">Old Password Incorrect</div>';
                    }
                }
            ?>
            <button type="button" class="btn bg-virgin mb-2" data-toggle="modal" data-target="#editMyProfile">Edit Profile</button>
            <button type="button" class="btn bg-virgin mb-2" data-toggle="modal" data-target="#changePassword">Change Password</button>

            <div id="editMyProfile" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Profile</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="?page=home" method="post">
                        <input hidden name="action" value="editmyprofile">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" maxlegnth="120" name="name" id="name" class="form-control" required value="<?php echo $_SESSION['pilotinfo']['name']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="callsign">Callsign</label>
                            <input type="text" maxlegnth="8" name="callsign" id="callsign" class="form-control" required value="<?php echo $_SESSION['pilotinfo']['callsign']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required value="<?php echo $_SESSION['pilotinfo']['email']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="ifc">IFC URL</label>
                            <input type="url" name="ifc" id="ifc" class="form-control" required value="<?php echo $_SESSION['pilotinfo']['ifc']; ?>">
                        </div>
                        <input type="submit" class="btn bg-virgin" value="Edit Profile">
                    </form>
                </div>
                </div>
            </div>
            </div>

            <div id="changePassword" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Change Password</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="?page=home" method="post">
                        <script>
                            $(document).ready(function() {
                                $("#confpass").change(function() {
                                    if ($("#confpass").val() != $("#newpass").val()) {
                                        $("#cpError").html("Passwords Do Not Match");
                                        $("#cpSubmit").attr("disabled", true);
                                    } else {
                                        $("#cpError").html("");
                                        $("#cpSubmit").attr("disabled", false);
                                    }
                                });
                            });
                        </script>
                        <div class="form-group">
                            <label for="oldpass">Old Password</label>
                            <input type="password" name="oldpass" id="oldpass" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="newpass">New Password</label>
                            <input type="password" minlenght="8" name="newpass" id="newpass" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="confpass">Confirm New Password</label>
                            <input type="password" name="confpass" id="confpass" class="form-control" required>
                        </div>
                        <input hidden name="action" value="changepass">
                        <p id="cpError" class="text-danger"></p>
                        <input type="submit" class="btn bg-virgin" id="cpSubmit" value="Change Password" disabled>
                    </form>
                </div>
                </div>
            </div>
            </div>

            <?php
                $rank = getRank($_SESSION["pilotinfo"]["id"]);
                $pireps = getFlights($_SESSION["pilotinfo"]["id"]);
            ?>
            <table class="table mb-0 border-bottom">
                <tr><td class="align-middle"><b>Name</b></td><td class="align-middle"><?php echo $_SESSION["pilotinfo"]["name"]; ?></td></tr>
                <tr><td class="align-middle"><b>Callsign</b></td><td class="align-middle"><?php echo $_SESSION["pilotinfo"]["callsign"]; ?></td></tr>
                <tr><td class="align-middle"><b>Flight Time</b></td><td class="align-middle"><?php echo getHours($_SESSION["pilotinfo"]["id"]); ?></td></tr>
                <tr><td class="align-middle"><b>Rank</b></td><td class="align-middle"><?php echo $rank; ?></td></tr>
                <tr><td class="align-middle"><b>Elevate Points</b></td><td class="align-middle"><?php echo getPoints($_SESSION["pilotinfo"]["id"]); ?></td></tr>
                <tr><td class="align-middle"><b>Elevate Tier</b></td><td class="align-middle"><?php echo getTier($_SESSION["pilotinfo"]["id"]); ?></td></tr>
                <tr><td class="align-middle"><b>PIREPs</b></td><td class="align-middle"><?php echo $pireps; ?></td></tr>
            </table>
            <br />

            <h3>News Feed</h3>
            <?php
                $news = selectMultiple("SELECT * FROM news ORDER BY id DESC LIMIT 3");
                if ($news === FALSE) {
                    echo 'No News';
                } else {
                    while ($item = $news->fetch_assoc()) {
                        echo '<div class="card mb-3">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title"><u>'.$item["subject"].'</u></h5>';
                        $date = date_format(date_create($item["dateposted"]), 'Y-m-d');
                        echo '<p><small><i class="fa fa-user"></i> '.$item["author"].'&nbsp;&nbsp;&nbsp;<i class="fa fa-clock"></i> '.$date.'</small></p>';
                        echo '<p class="card-text">'.$item["content"].'</p>';
                        echo '</div></div>';
                    }
                }
            ?><br /><br />

            <h3>Your Recent PIREPs</h3>
            <table class="table table-striped">
                <thead class="bg-virgin"><tr><th class="mobile-hidden">Flight Number</th><th>Route</th><th class="mobile-hidden">Date</th><th class="mobile-hidden">Aircraft</th><th>Status</th></tr></thead>
                <tbody>
                    <?php
                        $recentflights = selectMultiple("SELECT * FROM pireps WHERE pilotid={$_SESSION['pilotinfo']['id']} ORDER BY id DESC LIMIT 5;");
                        $statuses = array("Pending", "Approved", "Denied", "On Hold");
                        if ($recentflights === FALSE) {
                            echo '<tr><td colspan="5" class="text-center">No PIREPs<br /><a href="#filepirep" data-toggle="tab" onclick="clearActive()" class="panel-link"><u>Log One Here</u></a></td></tr>';
                        } else {
                            while ($flight = $recentflights->fetch_assoc()) {
                                echo '<tr><td class="mobile-hidden align-middle">';
                                echo $flight["type"].$flight["flightnum"];
                                echo '</td><td class="align-middle">';
                                echo $flight["departure"].'-'.$flight["arrival"];
                                echo '</td><td class="mobile-hidden align-middle">';
                                echo $flight["date"];
                                echo '</td><td class="mobile-hidden align-middle">';
                                $ac = select("SELECT code FROM aircraft WHERE id={$flight['aircraftid']};");
                                echo $ac["code"];
                                echo '</td><td class="align-middle">';
                                echo $statuses[$flight["status"]];
                                echo '</td></tr>';
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="tab-pane container" id="filepirep">
            <h3>File PIREP</h3>
            <?php if (isset($_POST["action"]) && $_POST["action"] == 'filepirep') {
                $query = $conn->prepare("INSERT INTO pireps (type, flightnum, departure, arrival, flighttime, pilotid, date, aircraftid, multi, elevate_points) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
                if (!isset($_POST["multicode"]) || $_POST["multicode"] == '') {
                    $ftime = ($_POST["hrs"] * 3600) + ($_POST["mins"] * 60);
                    $multi = 'None';
                } else {
                    $multiq2 = $conn->prepare("SELECT * FROM multipliers WHERE code=?;");
                    $multiq2->bind_param("i", $_POST["multicode"]); 
                    $multiq2->execute();
                    $codeq = $multiq2->get_result()->fetch_assoc();
                    if ($codeq === FALSE) {
                        echo '<div class="alert text-center alert-warning">Invalid Multiplier Code. Your PIREP will still be filed without the multiplier.</div>';
                        $ftime = (($_POST["hrs"] * 3600) + ($_POST["mins"] * 60));
                        $multi = 'None';
                    } else {
                        $ftime = (($_POST["hrs"] * 3600) + ($_POST["mins"] * 60)) * $codeq["multiplier"];
                        echo '<div class="alert text-center alert-success">Your Multipler has been applied</div>';
                        $multi = $codeq["name"];
                    }
                }

                $realhours = ($_POST["hrs"] * 3600) + ($_POST["mins"] * 60);
                    if ($realhours < 1 * 3600) {
                        $elepoints = 5;
                    } elseif ($realhours >= 1 * 3600 && $realhours < 4 * 3600) {
                        $elepoints = 10;
                    } elseif ($realhours >= 4 * 3600 && $realhours < 6 * 3600) {
                        $elepoints = 20;
                    } elseif ($realhours >= 6 * 3600 && $realhours < 10 * 3600) {
                        $elepoints = 30;
                    } elseif ($realhours >= 10 * 3600) {
                        $elepoints = 40;
                    } else {
                        $elepoints = 0;
                    }
                $query->bind_param("sissiisisi", $_POST["ftype"], $_POST["fnum"], $_POST["dep"], $_POST["arr"], $ftime, $_SESSION["pilotinfo"]["id"], $_POST["flightdate"], $_POST["aircraft"], $multi, $elepoints);
                $ret = $query->execute();
                if ($ret === FALSE) {
                    echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                } else {
                    echo '<div class="alert text-center alert-success">Your PIREP has been submitted and is now awaiting approval<br /><a href="pilotpanel.php?page=filepirep">File Another</a></div>';
                    $fthrmin = secsToHrMin($ftime);
                    $msg = urlencode("*New PIREP by {$_SESSION['pilotinfo']['name']}*\r\nRoute: {$_POST['dep']}-{$_POST['arr']}\r\nFlight Time:{$fthrmin}\r\nFlight Number: {$_POST['ftype']}{$_POST['fnum']}\r\nHope you had a nice flight!");
                    file_get_contents("https://VGVA-Webhook-Server--webpage.repl.co?token=f280edb5-f685-4810-bb9f-19661f1389be&channel=feed&message=$msg");
                    reloadPilotInfo();
                }
            } else { 
            if (isset($_GET["routeid"])) {
                $y = $conn->prepare("SELECT * FROM routes WHERE id=?");
                $y->bind_param("i", $_GET["routeid"]);
                $y->execute();
                $flightofile = $y->get_result()->fetch_assoc();
            } ?>
            <form action="?page=filepirep" method="post">
                <input hidden name="action" value="filepirep">
                <div class="form-group">
                    <label for="flightdate">Date of Flight</label>
                    <input required type="date" value="<?php echo Date("Y-m-d") ?>" class="form-control" name="flightdate">
                </div>
                <div class="form-group">
                    <label for="ftype">Flight Type</label>
                    <select class="form-control" name="ftype" required>
                        <option value>Select</option>
                        <?php
                            $airlines = selectMultiple("SELECT code, name FROM flighttypes;");
                            while ($item = $airlines->fetch_assoc()) {
                                if (isset($flightofile) && $flightofile["typecode"] == $item["code"]) {
                                    echo "<option value=\"{$item['code']}\" selected>{$item['name']}</option>";
                                } else { 
                                    echo "<option value=\"{$item['code']}\">{$item['name']}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fnum">Flight Number</label>
                    <input type="number" min="1" class="form-control" name="fnum" <?php if (isset($flightofile)) {echo 'value="'.$flightofile["fltnum"].'"';}?>>
                </div>
                <div class="form-group">
                    <label for="hrs">Flight Time</label>
                    <input required type="number" class="form-control mb-1" min="0" placeholder="Hours" max="24" name="hrs">
                    <input required type="number" class="form-control" min="0" placeholder="Minutes" max="59" name="mins">
                </div>
                <div class="form-group">
                    <label for="dep">Departure</label>
                    <input required type="text" class="form-control" maxlength="4" placeholder="ICAO" minlength="4" name="dep" <?php if (isset($flightofile)) {echo 'value="'.$flightofile["dep"].'"';}?>>
                </div>
                <div class="form-group">
                    <label for="arr">Arrival</label>
                    <input required type="text" class="form-control" maxlength="4" placeholder="ICAO" minlength="4" name="arr" <?php if (isset($flightofile)) {echo 'value="'.$flightofile["arr"].'"';}?>>
                </div>
                <div class="form-group">
                    <label for="aircraft">Aircraft</label>
                    <select class="form-control" name="aircraft" required>
                        <option value>Select</option>
                        <?php
                            $myrankis = getFullRank($_SESSION['pilotinfo']['id'])["id"];
                            $aircraft = selectMultiple("SELECT * FROM aircraft WHERE rankreq <= {$myrankis};");
                            while ($plane = $aircraft->fetch_assoc()) {
                                if (isset($flightofile) && $flightofile["aircraftid"] == $plane["id"]) {
                                    echo "<option value=\"{$plane['id']}\" selected>{$plane['name']}</option>";
                                } else { 
                                    echo "<option value=\"{$plane['id']}\">{$plane['name']}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="multicode">Multiplier Code (if appliable)</label>
                    <input type="number" class="form-control" maxlength="6" minlength="6" id="multicode" name="multicode">
                </div>
                <input type="submit" class="btn text-light" style="background-color: #E4181E;" value="Submit">
            </form>
            <?php } ?>
        </div>
        <div class="tab-pane container" id="mypireps">
            <h4>My PIREPs</h4>
            Showing your 30 most recent PIREPs<br /><br />

            <table class="table table-striped">
                <thead class="bg-virgin"><tr><th class="mobile-hidden">Flight Number</th><th>Route</th><th class="mobile-hidden">Date</th><th class="mobile-hidden">Aircraft</th><th>Status</th></tr></thead>
                <tbody>
                    <?php
                        $recentflights = selectMultiple("SELECT pireps.type, pireps.flightnum, pireps.departure, pireps.arrival, pireps.date, pireps.date, pireps.status, aircraft.code AS aircraft FROM pireps INNER JOIN aircraft ON pireps.aircraftid = aircraft.id WHERE pilotid={$_SESSION['pilotinfo']['id']} ORDER BY pireps.id DESC LIMIT 30;");
                        if ($recentflights === FALSE) {
                            echo '<tr><td colspan="5" class="text-center">No PIREPs<br /><a href="#filepirep" data-toggle="tab" onclick="clearActive()" class="panel-link"><u>Log One Here</u></a></td></tr>';
                        } else {
                            while ($flight = $recentflights->fetch_assoc()) {
                                echo '<tr><td class="mobile-hidden align-middle">';
                                echo $flight["type"].$flight["flightnum"];
                                echo '</td><td class="align-middle">';
                                echo $flight["departure"].'-'.$flight["arrival"];
                                echo '</td><td class="mobile-hidden align-middle">';
                                echo $flight["date"];
                                echo '</td><td class="mobile-hidden align-middle">';
                                echo $flight["aircraft"];
                                echo '</td><td class="align-middle">';
                                echo $statuses[$flight["status"]];
                                echo '</td></tr>';
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="tab-pane container" id="routedb">
            <h4>Route Database</h4>
            <div id="routeMessage"></div>
            <?php if (isset($_POST["action"]) && $_POST["action"] == 'searchroutes') {
                $searchwhere = array();
                if (isset($_POST["dep"]) && $_POST["dep"] != '') {
                    array_push($searchwhere, 'dep = "'.mysqli_real_escape_string($conn, $_POST["dep"]).'"');
                }
                if (isset($_POST["arr"]) && $_POST["arr"] != '') {
                    array_push($searchwhere, 'arr = "'.mysqli_real_escape_string($conn, $_POST["arr"]).'"');
                }
                if (isset($_POST["num"]) && $_POST["num"] != '') {
                    array_push($searchwhere, 'fltnum = "'.mysqli_real_escape_string($conn, $_POST["num"]).'"');
                }
                if (isset($_POST["aircraft"]) && $_POST["aircraft"] != '') {
                    array_push($searchwhere, 'aircraftid = "'.mysqli_real_escape_string($conn, $_POST["aircraft"]).'"');
                }
                if (isset($_POST["type"]) && $_POST["type"] != '') {
                    array_push($searchwhere, 'typecode = "'.mysqli_real_escape_string($conn, $_POST["type"]).'"');
                }
                if (isset($_POST["ftime"]) && $_POST["ftime"] != '') {
                    if ($_POST["ftime"] == 0) {
                        array_push($searchwhere, 'duration <= 3600');
                    } elseif ($_POST["ftime"] == 10) {
                        array_push($searchwhere, 'duration >= 36000');
                    } elseif (is_numeric($_POST["ftime"])) {
                        array_push($searchwhere, 'duration >= '.$_POST["ftime"] * 3600);
                        array_push($searchwhere, 'duration < '.($_POST["ftime"] + 1) * 3600);
                    }
                }
                $query = 'SELECT routes.typecode, routes.fltnum, routes.dep, routes.arr, routes.duration, routes.id, aircraft.code AS aircraft, aircraft.rankreq AS rankreq FROM routes INNER JOIN aircraft ON aircraft.id = routes.aircraftid';
                $i = 0;
                foreach ($searchwhere as $cond) {
                    if ($i == 0) {
                        $query = $query . ' WHERE ' . $cond;
                    } elseif ($i == count($searchwhere) - 1) {
                        $query = $query . ' AND ' . $cond;
                    }
                    $i = $i + 1;
                }
                $routes = selectMultiple($query); ?>
                <a href="?page=routes" class="btn text-light" style="background-color: #E4181E;">New Search</a><br /><br />
                <table class="table table-striped"><thead class="bg-virgin"><tr><th class="mobile-hidden">Flight #</th><th>Route</th><th>Aircraft</th><th class="mobile-hidden">Flight Time</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php
                        if ($routes === FALSE) {
                            echo '<tr><td colspan="5" class="text-center">No Routes With Those Search Parameters and within your rank</td></tr>';
                        } else {
                            $aircraft = selectMultiple("SELECT * FROM aircraft ORDER BY id ASC;");
                            $allaircraft = array();
                            $daroutes = array();
                            $i = 0;
                            while ($ac = $aircraft->fetch_assoc()) {
                                array_push($allaircraft, $ac);
                            }
                            $myrank = getFullRank($_SESSION["pilotinfo"]["id"])["id"];
                            while ($row = $routes->fetch_assoc()) {
                                $ofrank = false;
                                if ($row["rankreq"] <= $myrank) {
                                    $ofrank = true;
                                }
                                if ($ofrank == true) {
                                    array_push($daroutes, $row);
                                    echo '<tr><td class="mobile-hidden align-middle">';
                                    echo $row["typecode"].$row["fltnum"];
                                    echo '</td><td class="align-middle">';
                                    echo $row["dep"].'-'.$row["arr"];
                                    echo '</td><td class="align-middle">';
                                    echo $row["aircraft"];
                                    echo '</td><td class="mobile-hidden align-middle">';
                                    echo secsToHrMin($row["duration"]);
                                    echo '</td><td class="align-middle">';
                                    echo '<a class="btn text-light" style="background-color: #E4181E;" href="?page=filepirep&routeid='.$row["id"].'"><i class="fa fa-plane"></i></a>';
                                    if ($_SESSION["pilotinfo"]["recruitstage"] > 3) {
                                        echo '&nbsp;<button class="btn text-light" style="background-color: #E4181E;" data-toggle="modal" data-target="#editroute'.$row["id"].'"><i class="fa fa-edit"></i></button>';
                                        echo '&nbsp;<button class="btn text-light delRoute" style="background-color: #E4181E;" data="'.$row["id"].'"><i class="fa fa-trash"></i></button>';
                                    }
                                    echo '</td></tr>';
                                }
                            }
                        }
                    ?>
                </tbody></table>
                <?php
                    if ($_SESSION["pilotinfo"]["recruitstage"] > 3 && isset($daroutes)) {
                        foreach ($daroutes as $rItem) { ?>
                            <div class="modal" id="editroute<?php echo $rItem['id']?>">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">

                                <div class="modal-header">
                                    <h4 class="modal-title">Edit Route: <?php echo $row["typecode"].$row["fltnum"] ?></h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>

                                <div class="modal-body">
                                    <form action="?page=routes" method="post">
                                        <input hidden name="action" value="editroute">
                                        <input hidden name="routeid" value="<?php echo $rItem['id']?>">
                                        <div class="form-group">
                                            <label for="fltnum">Flight Number</label>
                                            <input required type="number" min="0" class="form-control" name="fltnum" id="fltnum" value="<?php echo $rItem['fltnum']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="dep">Departure</label>
                                            <input required type="text" minlength="4" maxlength="4" class="form-control" name="dep" id="dep" placeholder="ICAO" value="<?php echo $rItem['dep']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="arr">Arrival</label>
                                            <input required type="text" minlength="4" maxlength="4" class="form-control" name="arr" id="arr" placeholder="ICAO" value="<?php echo $rItem['arr']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="durhrs">Flight Time</label>
                                            <?php
                                                $ftime = explode(':', secsToHrMin($rItem["duration"]));
                                            ?>
                                            <input required type="number" min="0" class="form-control" name="durhrs" id="durhrs" value="<?php echo $ftime[0]; ?>">
                                            <small class="form-text text-muted">Hours<br /><br /></small>
                                            <input required type="number" min="0" class="form-control" name="durmins" id="durmins" value="<?php echo $ftime[1]; ?>">
                                            <small class="form-text text-muted">Minutes</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="aircraft">Aircraft</label>
                                            <select id="aircraft" name="aircraft" class="form-control" required>
                                                <option value>Select</option>
                                                <?php
                                                    foreach ($allaircraft as $ac1) {
                                                        if ($rItem["aircraftid"] == $ac1["id"]) {
                                                            echo '<option selected value="'.$ac1["id"].'">'.$ac1["name"].'</option>';
                                                        } else { 
                                                            echo '<option value="'.$ac1["id"].'">'.$ac1["name"].'</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <input type="submit" class="btn text-light" style="background-color: #E4181E;" value="Update Route">
                                    </form>
                                </div>

                                </div>
                            </div>
                            </div>
                        <?php }
                    }
                ?>
            <?php } else {
                if ($_SESSION["pilotinfo"]["recruitstage"] > 3 && isset($_POST["action"]) && $_POST["action"] == 'editroute') {
                    $req = $conn->prepare("UPDATE routes SET fltnum=?, dep=?, arr=?, duration=?, aircraftid=? WHERE id=?");
                    $dur = ($_POST["durhrs"] * 3600) + ($_POST["durmins"] * 60);
                    $req->bind_param("sssiii", $_POST["fltnum"], $_POST["dep"], $_POST["arr"], $dur, $_POST["aircraft"], $_POST["routeid"]);
                    $returnn = $req->execute();
                    if ($returnn === FALSE) {
                        echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                    } else {
                        echo '<div class="alert text-center alert-success">Route Updated Successfully</div>';
                    }
                } elseif ($_SESSION["pilotinfo"]["recruitstage"] > 3 && isset($_POST["action"]) && $_POST["action"] == 'addroute') {
                    $req = $conn->prepare("INSERT INTO routes (typecode, fltnum, dep, arr, duration, aircraftid) VALUES (?, ?, ?, ?, ?, ?);");
                    $dur = ($_POST["durhrs"] * 3600) + ($_POST["durmins"] * 60);
                    $req->bind_param("ssssii", $_POST["ftype"], $_POST["fltnum"], $_POST["dep"], $_POST["arr"], $dur, $_POST["aircraft"]);
                    $returnn = $req->execute();
                    if ($returnn === FALSE) {
                        echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                    } else {
                        echo '<div class="alert text-center alert-success">Route Added Successfully</div>';
                        $fthrmin = secsToHrMin($dur);
                        $newac = getAC($_POST["aircraft"])["name"];
                        $msg = urlencode("*New Flight Available*\r\nRoute: {$_POST['dep']}-{$_POST['arr']}\r\nFlight Time: {$fthrmin}\r\nFlight Number: {$_POST['ftype']}{$_POST['fltnum']}\r\nAircraft: {$newac}");
                        file_get_contents("https://VGVA-Webhook-Server--webpage.repl.co?token=f280edb5-f685-4810-bb9f-19661f1389be&channel=feed&message=$msg");
                    }
                } elseif ($_SESSION["pilotinfo"]["recruitstage"] > 3) { ?>
                    <button type="button" class="btn text-light mb-2" style="background-color: #E4181E;" data-toggle="modal" data-target="#addroute">Add Route</button>

                    <div class="modal" id="addroute">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                        <div class="modal-header">
                            <h4 class="modal-title">Add Route</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">
                        <form action="?page=routes" method="post">
                            <input hidden name="action" value="addroute">
                            <div class="form-group">
                                <label for="ftype">Flight Type</label>
                                <select id="ftype" name="ftype" class="form-control" required>
                                    <option value>Select</option>
                                    <?php
                                        $allaircraft = selectMultiple("SELECT * FROM flighttypes;");
                                        foreach ($allaircraft as $ac1) {
                                            echo '<option value="'.$ac1["code"].'">'.$ac1["name"].'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="fltnum">Flight Number</label>
                                <input required type="number" min="0" class="form-control" name="fltnum" id="fltnum">
                            </div>
                            <div class="form-group">
                                <label for="dep">Departure</label>
                                <input required type="text" minlength="4" maxlength="4" class="form-control" name="dep" id="dep" placeholder="ICAO">
                            </div>
                            <div class="form-group">
                                <label for="arr">Arrival</label>
                                <input required type="text" minlength="4" maxlength="4" class="form-control" name="arr" id="arr" placeholder="ICAO">
                            </div>
                            <div class="form-group">
                                <label for="durhrs">Flight Time</label>
                                <input required type="number" min="0" class="form-control" name="durhrs" id="durhrs">
                                <small class="form-text text-muted">Hours<br /><br /></small>
                                <input required type="number" min="0" class="form-control" name="durmins" id="durmins">
                                <small class="form-text text-muted">Minutes</small>
                            </div>
                            <div class="form-group">
                                <label for="aircraft">Aircraft</label>
                                <select id="aircraft" name="aircraft" class="form-control" required>
                                    <option value>Select</option>
                                    <?php
                                        $allaircraft = selectMultiple("SELECT * FROM aircraft ORDER BY rankreq ASC;");
                                        while ($ac1 = $allaircraft->fetch_assoc()) {
                                            echo '<option value="'.$ac1["id"].'">'.$ac1["name"].'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <input type="submit" class="btn text-light" style="background-color: #E4181E;" value="Add Route">
                        </form>
                        </div>

                        </div>
                    </div>
                    </div>
                <?php }
            ?>
            <form action="?page=routes" method="post">
                <input hidden name="action" value="searchroutes">
                <input type="text" name="dep" class="form-control mb-3" placeholder="Departure ICAO (Leave Blank for Any)" minlegnth="4" maxlength="4">
                <input type="text" name="arr" class="form-control mb-3" placeholder="Arrival ICAO (Leave Blank for Any)" minlegnth="4" maxlength="4">
                <input type="number" min="1" name="num" class="form-control mb-3" placeholder="Flight Number (Leave Blank for Any)">
                <select name="aircraft" class="mb-3 form-control">
                    <option value>Any Aircraft</option>
                    <?php
                    $myrankid = getFullRank($_SESSION["pilotinfo"]["id"])["id"];
                        $aircraft = selectMultiple("SELECT * FROM aircraft WHERE rankreq <= {$myrankid};");
                        while ($plane = $aircraft->fetch_assoc()) {
                            echo "<option value=\"{$plane['id']}\">{$plane['name']}</option>";
                        }
                    ?>
                </select>
                <select name="ftime" class="mb-3 form-control">
                    <option value>Any Flight Time</option>
                    <option value="0">< 1hr</option>
                    <option value="1">1-2hrs</option>
                    <option value="2">2-3hrs</option>
                    <option value="3">3-4hrs</option>
                    <option value="4">4-5hrs</option>
                    <option value="5">5-6hrs</option>
                    <option value="6">6-7hrs</option>
                    <option value="7">7-8hrs</option>
                    <option value="9">8-9hrs</option>
                    <option value="9">9-10hrs</option>
                    <option value="10">10hrs+</option>
                </select>
                <select name="type" class="mb-3 form-control">
                    <option value>Any Flight Type</option>
                    <?php
                        $airlines = selectMultiple("SELECT code, name FROM flighttypes;");
                        while ($item = $airlines->fetch_assoc()) {
                            echo "<option value=\"{$item['code']}\">{$item['name']}</option>";
                        }
                    ?>
                </select>
                <input type="submit" class="btn text-light" style="background-color: #E4181E;" value="Search">
            </form>
            <?php } ?>
        </div>
        <div class="tab-pane container" id="featured">
            <h4 class="mb-0">Weekly Featured Routes</h4>
            <p class="mt-0"><small>WFRs can be flown by all pilots but must be logged using the links below. You are eligible for variable multipliers depending on your Elevate Tier. Red Member can claim
            1.5x, Silver 2x, Gold 2.5x and Platinum 3x. Each route may only be flown between 0600Z and 0600Z (24hrs) on the day it is featured.</small></p>
            <?php
                $todaydate = date("Y-m-d");
                $featuredroutes = selectMultiple("SELECT wfrdays.remarks, wfrdays.date, wfrdays.title AS title, routes.typecode, routes.fltnum, routes.dep, routes.arr, routes.duration, aircraft.name AS aircraft, aircraft.id AS aircraftid FROM (((wfrdays INNER JOIN wfrroutes ON wfrdays.id = wfrroutes.dayid) INNER JOIN routes ON wfrroutes.routeid = routes.id) INNER JOIN aircraft ON aircraft.id=routes.aircraftid) WHERE DATEDIFF('{$todaydate}', wfrdays.date) <= 7 OR wfrdays.date > NOW() ORDER BY wfrdays.date ASC");
                $days = array();
                if ($featuredroutes === FALSE) {
                    echo 'No Featured Routes';
                } else { ?>

                    <script>
                        $(document).ready(function() {
                            $(".featuredPirepLink").click(function() {
                                let date = $(this).attr("data-date");
                                let type = $(this).attr("data-type");
                                let num = $(this).attr("data-num");
                                let dep = $(this).attr("data-dep");
                                let arr = $(this).attr("data-arr");
                                let fAc = $(this).attr("data-ac");
                                let multi = $(this).attr("data-multi");
                                $("#fpDate").val(date);
                                $("#fpFType").val(type);
                                $("#fpFNum").val(num);
                                $("#fpDep").val(dep);
                                $("#fpArr").val(arr);
                                $("#fpAC").val(fAc);
                                $("#fpMulti").attr("value", multi);
                                console.log($("#fpMulti").val());
                                $("#featuredPirep").modal("toggle");
                            });
                        });
                    </script>

                <?php while ($fr = $featuredroutes->fetch_assoc()) {
                    if (!isset($days[$fr["date"]])) {
                        $days[$fr["date"]] = array();
                    }
                    array_push($days[$fr["date"]], $fr);
                }
                $multirank = getFullTier($_SESSION["pilotinfo"]["id"])["id"];
                foreach ($days as $dItem) { ?>
                    <div class="card mb-1 text-center">
                        <div class="card-header"><h5><?php echo date_format(date_create($dItem[0]["date"]), "l F jS") ?> - <?php echo $dItem[0]["title"] ?></h5></div>
                        <div class="card-body">
                            <?php 
                                if ($dItem[0]["remarks"] != '' && isset($dItem[0]["remarks"])) {
                                    echo "<b>{$dItem[0]['remarks']}</b>";
                                    echo '<button class="btn btn-light mb-2" style="width: 100%;" data-toggle="collapse" data-target="#routes'.date_format(date_create($dItem[0]["date"]), "U").'">View Routes</button><div id="routes'.date_format(date_create($dItem[0]["date"]), "U").'" class="collapse">';
                                }
                                
                                echo '<p><hr />';
                                foreach ($dItem as $ddItem) {
                                    $duration = secsToHrMin($ddItem["duration"]);
                                    echo "{$ddItem['typecode']}{$ddItem['fltnum']} - {$ddItem['dep']} to {$ddItem['arr']} - {$ddItem['aircraft']} ({$duration})<br />";
                                    if ($multirank == 1) {
                                        $multicode = 340713;
                                    } elseif ($multirank == 2) {
                                        $multicode = 990685;
                                    } elseif ($multirank == 3) {
                                        $multicode = 903960;
                                    } elseif ($multirank == 4) {
                                        $multicode = 885447;
                                    } else {
                                        $multicode = '';
                                    }
                                    if (intval(date_format(date_create(), "U")) > intval(date_format(date_create($dItem[0]["date"]), "U"))) {
                                        echo '<a class="featuredPirepLink text-primary" style="cursor: pointer;" data-date="'.date_format(date_create($dItem[0]["date"]), "Y-m-d").'" data-type="'.$ddItem["typecode"].'" data-num="'.$ddItem["fltnum"].'" data-dep="'.$ddItem["dep"].'" data-arr="'.$ddItem["arr"].'" data-ac="'.$ddItem["aircraftid"].'" data-multi="'.$multicode.'">File PIREP</a>';
                                    }
                                    echo '<hr />';
                                }
                                echo '</p>';

                                if ($dItem[0]["remarks"] != '' && isset($dItem[0]["remarks"])) {
                                    echo '</div>';
                                }
                            ?>
                        </div>
                    </div>
                    <div id="featuredPirep" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">File Featured Route PIREP</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form action="?page=filepirep" method="post">
                            <input hidden name="action" value="filepirep">
                            <div class="form-group">
                                <label for="flightdate">Date of Flight</label>
                                <input required type="date" readonly id="fpDate" class="form-control" name="flightdate">
                            </div>
                            <select class="form-control" name="ftype" required hidden>
                                <option id="fpFType">Select</option>
                            </select>
                            <div class="form-group">
                                <label for="fnum">Flight Number</label>
                                <input type="number" readonly class="form-control" name="fnum" id="fpFNum">
                            </div>
                            <div class="form-group">
                                <label for="hrs">Flight Time</label>
                                <input required type="number" class="form-control mb-1" min="0" placeholder="Hours" name="hrs">
                                <input required type="number" class="form-control" min="0" placeholder="Minutes" max="59" name="mins">
                            </div>
                            <div class="form-group">
                                <label for="dep">Departure</label>
                                <input required type="text" class="form-control" maxlength="4" placeholder="ICAO" minlength="4" name="dep" id="fpDep">
                            </div>
                            <div class="form-group">
                                <label for="arr">Arrival</label>
                                <input required type="text" class="form-control" maxlength="4" placeholder="ICAO" minlength="4" name="arr" id="fpArr">
                            </div>
                            <select class="form-control" name="aircraft" required hidden>
                                <option id="fpAC">Select</option>
                            </select>
                            <input hidden class="form-control" id="fpMulti" name="multicode">
                            <input type="submit" class="btn text-light" style="background-color: #E4181E;" value="Submit">
                            </form>
                        </div>
                        </div>

                    </div>
                    </div>
                <?php }} ?>
        </div>
        <div class="tab-pane container" id="events">
            <h4>Upcoming Events</h4>
            <?php
                if (isset($_POST["action"]) && $_POST["action"] == 'remESignup') {
                    $gates = selectMultiple("SELECT * FROM eventslots WHERE eventid=".mysqli_real_escape_string($conn, $_POST["eid"]));
                    $foundGate = false;
                    while ($bill = $gates->fetch_assoc()) {
                        if ($bill["pilotentity"] == $_SESSION["pilotinfo"]["id"] && $foundGate == false) {
                            $foundGate = $bill;
                        }
                    }
                    if ($foundGate == false) {
                        echo '<div class="alert alert-warning text-center">Could Not Find Your Gate</div>';
                    } else {
                        $seRet = runQ("UPDATE eventslots SET pilotentity='' WHERE id={$foundGate['id']};");
                        if ($seRet !== TRUE) {
                            echo '<div class="alert alert-danger text-center">MySQL Error: '.$seRet.'</div>';
                        } else {
                            echo '<div class="alert alert-success text-center">Your signup has been sucesfully removed.</div>';
                        }
                    }
                } elseif (isset($_POST["action"]) && $_POST["action"] == 'addESignup') {
                    $gates = selectMultiple("SELECT * FROM eventslots WHERE eventid=".mysqli_real_escape_string($conn, $_POST["eid"]));
                    $foundGate = false;
                    while ($bill = $gates->fetch_assoc()) {
                        if ($bill["pilotentity"] == '' && $foundGate == false) {
                            $foundGate = $bill;
                        }
                    }
                    if ($foundGate == false) {
                        echo '<div class="alert alert-warning text-center">No Available Gates</div>';
                    } else {
                        $seRet = runQ("UPDATE eventslots SET pilotentity='{$_SESSION['pilotinfo']['id']}' WHERE id={$foundGate['id']};");
                        if ($seRet !== TRUE) {
                            echo '<div class="alert alert-danger text-center">MySQL Error: '.$seRet.'</div>';
                        } else {
                            echo '<div class="alert alert-success text-center">You have been signed up and have the following gate; '.$foundGate["gate"].'. Thank you for coming!</div>';
                        }
                    }
                }
            ?>
            <script>
                $(document).ready(function() {
                    $(".viewEvent").click(function() {
                        var eid = $(this).attr("data-eid");
                        $.post("api.php", {
                            "command":"getEvent",
                            "text":parseInt(eid),
                            "token":false
                        }, function(data, status) {
                            if (status != 'success') {
                                $("#eventsMessage").html('<div class="alert alert-danger text-center">API Request Failed</div>');
                            } else {
                                let response = data;
                                $("#eventInformation").html(response);
                            }
                            $("#eventInformation").modal('show');
                        });
                    });
                });
            </script>
            <div id="eventsMessage"></div>
            <table class="table"><thead class="bg-virgin"><tr><th>Name</th><th>Airport</th><th>View</th></tr></thead><tbody>
                <?php
                    $now = date_format(date_create(), "Y-m-d");
                    $upcoming = selectMultiple("SELECT * FROM events WHERE date >= '{$now}' AND visibility > 0 ORDER BY date ASC;");
                    if ($upcoming === FALSE) {
                        echo '<tr><td colspan="3" class="text-center bg-light">No Upcoming Events</td></tr>';
                    } else {
                        while ($uItem = $upcoming->fetch_assoc()) {
                            if (($uItem["visibility"] == 1 && $_SESSION["pilotinfo"]["recruitstage"] > 3) || $uItem["visibility"] > 1) {
                                echo '<tr><td>';
                                echo $uItem["name"];
                                echo '</td><td>';
                                if ($uItem["type"] == 1 || $uItem["type"] == 2) {
                                    $apt = $uItem["depart"];
                                } elseif ($uItem["type"] == 3) {
                                    $apt = $uItem["arrive"];
                                } else {
                                    $apt = 'ERR_500';
                                }
                                echo $apt;
                                echo '</td><td>';
                                echo '<button class="btn bg-virgin viewEvent" data-eid="'.$uItem["id"].'">View</button>';
                                echo '</td></tr>';
                            }
                        }
                    }
                ?>
            </tbody></table>
            <div id="eventInformation" class="modal fade" role="dialog"></div>
        </div>
        <div class="tab-pane container" id="acars">
            <h4>ACARS</h4>
            <?php if (!isset($_SESSION["pilotinfo"]["ifuserid"]) || $_SESSION["pilotinfo"]["ifuserid"] == '') { ?>
                <?php
                    if (isset($_POST["action"]) && $_POST["action"] == 'setuserid') {
                        $esFlights = json_decode(file_get_contents("https://atcegserverside4--webpage.repl.co/flights"), true);
                        $gotid = false;
                        foreach ($esFlights as $aFlight) {
                            if ($aFlight["CallSign"] == $_SESSION["pilotinfo"]["callsign"]) {
                                $aRet = runQ("UPDATE pilots SET ifuserid=\"{$aFlight['UserID']}\" WHERE id={$_SESSION['pilotinfo']['id']}");
                                if ($aRet === FALSE) {
                                    echo '<div class="alert alert-danger text-center">MySQL Error</div>';
                                } else {
                                    echo '<div class="alert alert-success text-center">Success!<br /><a href="?page=acars">Proceed to ACARS Panel</a></div>';
                                    $gotid = true;
                                    reloadPilotInfo();
                                }
                            }
                        }
                        if ($gotid == false) {
                            echo '<div class="alert alert-warning text-center">Couldn\'t find you on the Expert Server. Reload this page to try again.</div>';
                        }
                    }
                ?>
                <p>Looks like you haven't set up ACARS yet. To do so, you'll need to spawn in on the Expert Server so we can grab the unique User ID assigned to you by Infinite Flight. Please have your callsign
                set to your VGVA Callsign - <?php echo $_SESSION["pilotinfo"]["callsign"]; ?>.</p>
                <form action="?page=acars" method="post">
                    <input hidden name="action" value="setuserid">
                    <input type="submit" class="btn bg-virgin btn-lg" value="I'm Spawned In, Get my ID">
                </form>
            <?php } else { ?>
                <?php
                    if (isset($_POST["action"]) && $_POST["action"] == 'acarspirep') {
                        $acRank = select("SELECT rankreq FROM aircraft WHERE ifliveryid=\"".mysqli_real_escape_string($conn, $_POST["aircraft"])."\";")["rankreq"];
                        if ($rank < $acRank) {
                            echo '<div class="alert alert-danger text-center">Your rank is not sufficent to file a PIREP with that aircraft.</div>';
                        } else {
                            $query = $conn->prepare("INSERT INTO pireps (type, flightnum, departure, arrival, flighttime, pilotid, date, aircraftid, multi, elevate_points) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
                            if (!isset($_POST["multicode"]) || $_POST["multicode"] == '') {
                                $ftime = $_POST["ftime"];
                                $multi = 'None';
                            } else {
                                $multiq2 = $conn->prepare("SELECT * FROM multipliers WHERE code=?;");
                                $multiq2->bind_param("i", $_POST["multicode"]); 
                                $multiq2->execute();
                                $codeq = $multiq2->get_result();
                                if ($codeq === FALSE) {
                                    echo '<div class="alert text-center alert-warning">Invalid Multiplier Code. Your PIREP will still be filed without the multiplier.</div>';
                                    $ftime = $_POST["ftime"];
                                    $multi = 'None';
                                } else {
                                    $codeq = $codeq->fetch_assoc();
                                    $ftime = $_POST["ftime"] * $codeq["multiplier"];
                                    echo '<div class="alert text-center alert-success">Your Multipler has been applied</div>';
                                    $multi = $codeq["name"];
                                }
                            }

                            $realhours = $_POST["ftime"];
                                if ($realhours < 1 * 3600) {
                                    $elepoints = 5;
                                } elseif ($realhours >= 1 * 3600 && $realhours < 4 * 3600) {
                                    $elepoints = 10;
                                } elseif ($realhours >= 4 * 3600 && $realhours < 6 * 3600) {
                                    $elepoints = 20;
                                } elseif ($realhours >= 6 * 3600 && $realhours < 10 * 3600) {
                                    $elepoints = 30;
                                } elseif ($realhours >= 10 * 3600) {
                                    $elepoints = 40;
                                } else {
                                    $elepoints = 0;
                                }
                            $nowdate = date("Y-m-d");
                            $filedAicraft = getACByLiv($_POST["aircraft"])["id"]; 
                            $query->bind_param("sissiisisi", $_POST["ftype"], $_POST["fnum"], $_POST["depicao"], $_POST["arricao"], $ftime, $_SESSION["pilotinfo"]["id"], $nowdate, $filedAicraft, $multi, $elepoints);
                            $ret = $query->execute();
                            if ($ret === FALSE) {
                                echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                            } else {
                                echo '<div class="alert text-center alert-success">Your PIREP has been submitted and is now awaiting approval</div>';
                                reloadPilotInfo();
                            }
                        }
                    } else {
                ?>
                <script>
                    $(document).ready(function() {
                        $("#runacars").click(function() {
                            $("#acarsinfo").html("Running ACARS...");
                            $.post("api.php", {
                                "command":"runacars",
                                "text":"x",
                                "token":"x"
                            }, function(data, status) {
                                $("#acarsinfo").html(data);
                            });
                        });
                    });
                </script>
                <button data-toggle="collapse" data-target="#howtouseacars" class="btn btn-light w-100 mb-2">How to Use ACARS&nbsp;&nbsp;<i class="fa fa-caret-down"></i></button>
                <p id="howtouseacars" class="collapse text-left">
                1. First, fly your flight under your VGVA Callsign - <?php echo $_SESSION["pilotinfo"]["callsign"]; ?>.<br />
                2. Then, once you're at the gate but not despawned, come here and click the button below.<br />
                3. The System will automatically grab your flight details, validate them, and File the PIREP.
                </p>
                <div id="acarsinfo">
                <button class="btn bg-virgin btn-lg" id="runacars">Run ACARS</button>
                <div class="alert alert-warning text-center mt-2">Before you click this button, please confirm you have 
                finished your flight and are now sitting at the gate. Activating ACARS at the wrong time will give you less 
                flight time and Elevate Points than what you've earnt. ACARS Supports the Expert Server only.</div>
                </div>
            <?php }} ?>
        </div>
        <?php if ($_SESSION["pilotinfo"]["recruitstage"] > 3) { ?>
            <script>
                $(document).ready(function() {
                    $(".approvePirep").click(function() {
                        var pirepId = $(this).attr("data");
                        $.post("api.php", {
                            "token":"95d296df-44ea1nb5e-d5f2a59",
                            "command":"approvepirep",
                            "text":pirepId
                        }, function(data, status) {
                            if (data == '1') {
                                $("#pirepMessage").html('<div class="alert text-center alert-success">PIREP #' + pirepId + ' Approved Successfully</div>');
                                window.location.href = '?page=pirepmod';
                            } else {
                                $("#pirepMessage").html('<div class="alert text-center alert-danger">An Error was Encountered while Approving the PIREP</div>');
                            }
                        });
                    });
                    $(".denyPirep").click(function() {
                        var pirepId = $(this).attr("data");
                        var conf = confirm("Are you sure?");
                        if (conf == true) {
                            $.post("api.php", {
                                "token":"95d296df-44ea1nb5e-d5f2a59",
                                "command":"denypirep",
                                "text":pirepId
                            }, function(data, status) {
                                if (data == '1') {
                                    $("#pirepMessage").html('<div class="alert text-center alert-success">PIREP #' + pirepId + ' Denied Successfully</div>');
                                    window.location.href = '?page=pirepmod';
                                } else {
                                    $("#pirepMessage").html('<div class="alert text-center alert-danger">An Error was Encountered while Denying the PIREP</div>');
                                }
                            });
                        }
                    });
                    $(".holdPirep").click(function() {
                        var pirepId = $(this).attr("data");
                        $.post("api.php", {
                            "token":"95d296df-44ea1nb5e-d5f2a59",
                            "command":"holdpirep",
                            "text":pirepId
                        }, function(data, status) {
                            if (data == '1') {
                                $("#pirepMessage").html('<div class="alert text-center alert-success">PIREP #' + pirepId + ' Held Succesfully</div>');
                                window.location.href = '?page=pirepmod';
                            } else {
                                $("#pirepMessage").html('<div class="alert text-center alert-danger">An Error was Encountered while Holding the PIREP</div>');
                            }
                        });
                    });
                    $(".delNews").click(function() {
                        var newsId = $(this).attr("data");
                        var conf = confirm("Are you sure?");
                        if (conf == true) {
                            $.post("api.php", {
                                "token":"2b116ed0-86ca417d8-57f41ad",
                                "command":"delnews",
                                "text":newsId
                            }, function(data, status) {
                                if (data == '1') {
                                    $("#newsMessage").html('<div class="alert text-center alert-success">News Deleted Succesfully</div>');
                                    window.location.href = '?page=news';
                                } else {
                                    $("#newsMessage").html('<div class="alert text-center alert-danger">An Error was Encountered while Deleting the News</div>');
                                }
                            });
                        }
                    });
                    $(".delRoute").click(function() {
                        var routeId = $(this).attr("data");
                        var conf = confirm("Are you sure?");
                        if (conf == true) {
                            $.post("api.php", {
                                "token":"b8bd10dc-d3f1a5fb9-05e0b91",
                                "command":"delroute",
                                "text":routeId
                            }, function(data, status) {
                                if (data == '1') {
                                    $("#routeMessage").html('<div class="alert text-center alert-success">Route Deleted Succesfully</div>');
                                    window.location.href = '?page=routes';
                                } else {
                                    $("#routeMessage").html('<div class="alert text-center alert-danger">An Error was Encountered while Deleting the Route</div>');
                                }
                            });
                        }
                    });
                    $(".delRank").click(function() {
                        var rankId = $(this).attr("data");
                        var conf = confirm("Are you sure?");
                        if (conf == true) {
                            $.post("api.php", {
                                "token":"b8bd10dc-d3f1a5fb9-05e0b91",
                                "command":"delrank",
                                "text":rankId
                            }, function(data, status) {
                                if (data == '1') {
                                    $("#opsMessage").html('<div class="alert text-center alert-success">Rank Deleted Succesfully</div>');
                                    window.location.href = '?page=ops';
                                } else {
                                    $("#opsMessage").html('<div class="alert text-center alert-danger">An Error was Encountered while Deleting the Rank</div>');
                                }
                            });
                        }
                    });
                    $(".delFType").click(function() {
                        var typeId = $(this).attr("data");
                        var conf = confirm("Are you sure?");
                        if (conf == true) {
                            $.post("api.php", {
                                "token":"b8bd10dc-d3f1a5fb9-05e0b91",
                                "command":"delftype",
                                "text":typeId
                            }, function(data, status) {
                                if (data == '1') {
                                    $("#opsMessage").html('<div class="alert text-center alert-success">Flight Type Deleted Succesfully</div>');
                                    window.location.href = '?page=ops';
                                } else {
                                    $("#opsMessage").html('<div class="alert text-center alert-danger">An Error was Encountered while Deleting the Flight Type</div>');
                                }
                            });
                        }
                    });
                    $(".delMulti").click(function() {
                        var multiId = $(this).attr("data");
                        var conf = confirm("Are you sure?");
                        if (conf == true) {
                            $.post("api.php", {
                                "token":"b8bd10dc-d3f1a5fb9-05e0b91",
                                "command":"delmulti",
                                "text":multiId
                            }, function(data, status) {
                                if (data == '1') {
                                    $("#multiMessage").html('<div class="alert text-center alert-success">Flight Type Deleted Succesfully</div>');
                                    window.location.href = '?page=pirepmod';
                                } else {
                                    $("#multiMessage").html('<div class="alert text-center alert-danger">An Error was Encountered while Deleting the Flight Type</div>');
                                }
                            });
                        }
                    });
                    $(".eDelete").click(function() {
                        var eId = $(this).attr("data");
                        var conf = confirm("Are you sure?");
                        if (conf == true) {
                            $.post("api.php", {
                                "token":"b8bd10dc-d3f1a5fb9-05e0b91",
                                "command":"delevent",
                                "text":eId
                            }, function(data, status) {
                                if (data == '1') {
                                    $("#eventMessage").html('<div class="alert text-center alert-success">Event Deleted Succesfully</div>');
                                    window.location.href = '?page=eventmod';
                                } else {
                                    $("#eventMessage").html('<div class="alert text-center alert-danger">An Error was Encountered while Deleting the Tier</div>');
                                }
                            });
                        } 
                    });
                });
            </script>
            <div class="tab-pane container" id="usermanage">
                <h4>User Management</h4>
                <?php
                    if (isset($_POST["action"]) && $_POST["action"] == 'edituser' && isset($_POST["pilotid"])) {
                        if (isset($_POST["changepass"]) && $_POST["changepass"] != '') {
                            $qry = $conn->prepare("UPDATE pilots SET callsign=?, name=?, ifc=?, email=?, transhours=?, transflights=?, notes=?, recruitstage=?, password=? WHERE id=?;");
                            $ta = $_POST["transhrs"] * 3600;
                            $hashpass = password_hash($_POST["changepass"], PASSWORD_ARGON2ID);
                            $qry->bind_param("ssssiisisi", $_POST["callsign"], $_POST["name"], $_POST["ifc"], $_POST["email"], $ta, $_POST["transflts"], $_POST["notes"], $_POST["usertype"], $hashpass, $_POST["pilotid"]);
                        } else {
                            $qry = $conn->prepare("UPDATE pilots SET callsign=?, name=?, ifc=?, email=?, transhours=?, transflights=?, notes=?, recruitstage=? WHERE id=?;");
                            $ta = $_POST["transhrs"] * 3600;
                            $qry->bind_param("ssssiisii", $_POST["callsign"], $_POST["name"], $_POST["ifc"], $_POST["email"], $ta, $_POST["transflts"], $_POST["notes"], $_POST["usertype"], $_POST["pilotid"]);
                        }
                        $rett = $qry->execute();
                        if ($rett == FALSE) {
                            echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                        } else {
                            echo '<div class="alert text-center alert-success">User Updated Successfully</div>';
                        }
                    }
                ?>
                <table class="table table-striped">
                    <thead class="bg-virgin"><tr><th>Name</th><th class="mobile-hidden">Callsign</th><th class="mobile-hidden">Email</th><th class="mobile-hidden">Rank</th><th class="mobile-hidden">Flight Hours</th><th class="mobile-hidden">Flights</th><th>Edit</th></tr></thead>
                    <tbody>
                        <?php
                            $users = selectMultiple("SELECT * FROM pilots WHERE recruitstage > 2;");
                            $allusers = array();
                            while ($roww = $users->fetch_assoc()) {
                                array_push($allusers, $roww);
                                echo '<tr><td class="align-middle">';
                                echo $roww["name"];
                                echo '</td><td class="mobile-hidden align-middle">';
                                echo $roww["callsign"];
                                echo '</td><td class="mobile-hidden align-middle">';
                                echo $roww["email"];
                                echo '</td><td class="mobile-hidden align-middle">';
                                echo getRank($roww["id"]);
                                echo '</td><td class="mobile-hidden align-middle">';
                                echo getHours($roww["id"]);
                                echo '</td><td class="mobile-hidden align-middle">';
                                echo getFlights($roww["id"]);
                                echo '</td><td class="align-middle">';
                                echo '<button type="button" class="btn text-light" data-toggle="modal" style="background-color: #E4181E;" data-target="#editpilot'.$roww["id"].'"><i class="fa fa-edit"></i></button>';
                                echo '</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>

                <!-- Edit User Modals -->
                <?php
                    foreach ($allusers as $user) { ?>
                        <div id="editpilot<?php echo $user["id"] ?>" class="modal fade" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Pilot: <?php echo $user["callsign"].' ('.$user["name"].')'; ?></h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form action="?page=users" method="post">
                                    <input hidden name="action" value="edituser">
                                    <input hidden name="pilotid" value="<?php echo $user["id"]?>">
                                    <div class="form-group">
                                        <label for="callsign">Callsign</label>
                                        <input required type="text" id="callsign" name="callsign" class="form-control" value="<?php echo $user['callsign'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Callsign</label>
                                        <input required type="text" id="name" name="name" class="form-control" value="<?php echo $user['name'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="ifc">IFC URL</label>
                                        <input required type="url" id="ifc" name="ifc" class="form-control" value="<?php echo $user['ifc'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input required type="email" id="email" name="email" class="form-control" value="<?php echo $user['email'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="changepass">Password</label>
                                        <input type="password" id="changepass" name="changepass" class="form-control" placeholder="Leave Blank to Leave Unchanged">
                                    </div>
                                    <div class="form-group">
                                        <label for="transhrs">Transfer Hours</label>
                                        <input type="number" id="transhrs" name="transhrs" class="form-control" value="<?php echo $user['transhours'] / 3600 ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="transflts">Transfer Flights</label>
                                        <input type="number" id="transflts" name="transflts" class="form-control" value="<?php echo $user['transflights'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="notes">Notes</label>
                                        <textarea id="notes" name="notes" class="form-control"><?php echo $user['notes'] ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="usertype">User Type</label>
                                        <select id="usertype" name="usertype" class="form-control" required>
                                            <option value>Select</option>
                                            <option value="0" <?php if($user["recruitstage"] == 0) {echo 'selected';} ?>>Awaiting Processing</option>
                                            <option value="1" <?php if($user["recruitstage"] == 1) {echo 'selected';} ?>>Exam Assigned</option>
                                            <option value="2" <?php if($user["recruitstage"] == 2) {echo 'selected';} ?>>Denied</option>
                                            <option value="3" <?php if($user["recruitstage"] == 3) {echo 'selected';} ?>>Pilot</option>
                                            <?php if ($_SESSION["pilotinfo"]["recruitstage"] == 5) {   ?>  
                                            <option value="4" <?php if($user["recruitstage"] == 4) {echo 'selected';} ?>>Staff</option>
                                            <option value="5" <?php if($user["recruitstage"] == 5) {echo 'selected';} ?>>Executive</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <input type="submit" class="btn text-light" style="background-color: #E4181E;" value="Update User">
                                </form>
                            </div>
                            </div>
                        </div>
                        </div>
                <?php } ?>
            </div>
            <div class="tab-pane container" id="recruitment">
                <h4>Recruitment</h4>
                <?php
                    if (isset($_GET["action"]) && isset($_GET["pilotid"])) {
                        if ($_GET["action"] == 'giveexam') {
                            $stmt = $conn->prepare("UPDATE pilots SET recruitstage=1 WHERE id=? AND recruitstage=0;");
                            $stmt->bind_param("i", $_GET["pilotid"]);
                            $reter = $stmt->execute();
                            if ($reter === FALSE) {
                                echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                            } else {
                                $pilotinfo = getUserInfo($_GET["pilotid"]);
                                $body = "Dear {$pilotinfo['name']},<br /><br />
                                We are pleased to advise you that your application to join the Virgin Virtual Group has been accepted and you may now take our entrance exam.<br />
                                To do this, please log onto our Crew Center <a href='https://crew.ifvirginvirtual.vip'>here</a>. We look forward to seeing you fly with
                                us soon.<br /><br />Kind Regards,<br />The VGVA Pilot Experience Team";
                                $sendEmail = [$pilotinfo["email"], 'Virgin Virtual Group - Entrance Exam', $body];
                            }
                        } elseif ($_GET["action"] == 'deny') {
                            $stmt = $conn->prepare("UPDATE pilots SET recruitstage=2 WHERE id=? AND recruitstage=0;");
                            $stmt->bind_param("i", $_GET["pilotid"]);
                            $reter = $stmt->execute();
                            if ($reter === FALSE) {
                                echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                            } else {
                                $pilotinfo = getUserInfo($_GET["pilotid"]);
                                $body = "Dear {$pilotinfo['name']},<br /><br />
                                We are sorry to advise you that your application to join the Virgin Virtual Group has been denied due to the fact you do not meet our requirements.<br />
                                If you wish to dispute this, please contact <a href='https://community.infiniteflight.com/u/VirginGroupVA'>@VirginGroupVA</a> on the IFC.
                                <br /><br />Kind Regards,<br />The VGVA Pilot Experience Team";
                                $sendEmail = [$pilotinfo["email"], 'Virgin Virtual Group - Application Denied', $body];
                            }
                        }
                    }
                ?>
                <table class="table table-striped">
                <thead class="bg-virgin"><tr><th>Name</th><th class="mobile-hidden">IFC</th><th class="mobile-hidden">Email</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php
                    $pendingpilots = selectMultiple("SELECT * FROM pilots WHERE recruitstage < 2;");
                    if ($pendingpilots === FALSE) {
                        echo '<tr><td colspan="5" class="text-center">No Pending Pilots. Good Job!</td></tr>';
                    } else {
                        $pilotstatuses = array('Awaiting Processing', 'Exam Assigned');
                        while ($pilot = $pendingpilots->fetch_assoc()) {
                            echo '<tr><td class="align-middle">';
                            echo '<u class="text-primary viewPilot" style="cursor: pointer;" data-violand="'.$pilot["violand"].'" data-grade="'.$pilot["grade"].'" data-notes="'.$pilot["notes"].'" data-cs="'.$pilot["callsign"].'">'.$pilot["name"].'</u>';
                            echo '</td><td class="align-middle mobile-hidden">';
                            $ifcname = explode("/", $pilot["ifc"])[4];
                            echo "<a target=\"_blank\" href=\"{$pilot['ifc']}\">{$ifcname}</a>";
                            echo '</td><td class="align-middle mobile-hidden">';
                            echo $pilot["email"];
                            echo '</td><td class="align-middle">';
                            echo $pilotstatuses[$pilot["recruitstage"]];
                            echo '</td><td class="align-middle">';
                            $meetsreq = false;
                            if ($pilot["violand"] <= 0.5 && $pilot["grade"] > 2) {
                                $meetsreq = true;
                            }
                            if ($meetsreq == true && $pilot["recruitstage"] == 0) {
                                echo '<a class="btn btn-success" href="?page=recruit&action=giveexam&pilotid='.$pilot["id"].'">&#10004;</a>';
                                echo '<a class="btn btn-danger" href="?page=recruit&action=deny&pilotid='.$pilot["id"].'">&#10006;</a>';
                                echo '<p class="text-center mb-0"><small>Meets Requirements</small></p>';
                            } elseif ($meetsreq == false) {
                                echo '<a class="btn btn-danger" href="?page=recruit&action=deny&pilotid='.$pilot["id"].'">&#10006;</a>';
                                echo '<p class="text-center mb-0"><small>Doesn\'t Meet Requirements</small></p>';
                            } elseif ($pilot["recruitstage"] == 1) {
                                echo '<a class="btn btn-danger" href="?page=recruit&action=deny&pilotid='.$pilot["id"].'">&#10006;</a>';
                                echo '<p class="text-center mb-0"><small>Awaiting Exam Completion</small></p>';
                            }
                            echo '</td></tr>';
                        }
                    }
                ?>
                </tbody></table>
                <script>
                    $(document).ready(function() {
                        $(".viewPilot").click(function() {
                            let name = $(this).html();
                            let cs = $(this).attr("data-cs");
                            let notes = $(this).attr("data-notes");
                            let grade = $(this).attr("data-grade");
                            let violand = $(this).attr("data-violand");
                            $("#viewPilotTitle").text("View Pilot - " + name);
                            $("#viewPilotName").text(name);
                            $("#viewPilotCallsign").text(cs);
                            $("#viewPilotNotes").text(notes);
                            $("#viewPilotGrade").text(grade);
                            $("#viewPilotVioland").text(violand);
                            $("#viewPilot").modal('show');
                        });
                    });
                </script>
                <div id="viewPilot" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="viewPilotTitle"></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>
                            <b>Name: </b><span id="viewPilotName"></span><br />
                            <b>Callsign: </b><span id="viewPilotCallsign"></span><br />
                            <b>Notes: </b><span id="viewPilotNotes"></span><br />
                            <b>Grade: </b>Grade <span id="viewPilotGrade"></span><br />
                            <b>Violation/Landing Ratio: </b><span id="viewPilotVioland"></span><br />
                        </p>
                    </div>
                    </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane container" id="pirepmanage">
                <h4>PIREP Management</h4>
                <div id="pirepMessage"></div>
                <div class="mobile-hidden">
                <h5>Pending PIREPs</h5>
                <table class="table table-striped">
                    <thead class="bg-virgin"><tr><th>Pilot</th><th>Route</th><th>Flight Time</th><th>Multiplier</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php
                            $pendingpireps = selectMultiple("SELECT * FROM pireps WHERE status=0 ORDER BY id ASC;");
                            if ($pendingpireps === FALSE) {
                                echo '<tr><td colspan="5" class="text-center">No Pending PIREPs</td></tr>';
                            } else {
                                while ($pirep1 = $pendingpireps->fetch_assoc()) {
                                    echo '<tr><td class="align-middle">';
                                    echo getUserInfo($pirep1["pilotid"])["name"];
                                    echo '</td><td class="align-middle">';
                                    echo $pirep1["departure"].'-'.$pirep1["arrival"];
                                    echo '</td><td class="align-middle">';
                                    echo secsToHrMin($pirep1["flighttime"]);
                                    echo '</td><td class="align-middle">';
                                    echo $pirep1["multi"];
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn btn-success approvePirep" data="'.$pirep1["id"].'">&#10004;</button>';
                                    echo '<button class="btn btn-danger denyPirep" data="'.$pirep1["id"].'">&#10006;</button>';
                                    echo '<button class="btn btn-warning holdPirep" data="'.$pirep1["id"].'">&#9995;</button>';
                                    echo '</td></tr>';
                                }
                            }
                        ?>
                    </tbody>
                </table><br />

                <h5>Held PIREPs</h5>
                <table class="table table-striped">
                    <thead class="bg-virgin"><tr><th>Pilot</th><th>Route</th><th>Flight Time</th><th>Multiplier</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php
                            $pendingpireps = selectMultiple("SELECT * FROM pireps WHERE status=3 ORDER BY id ASC;");
                            if ($pendingpireps === FALSE) {
                                echo '<tr><td colspan="5" class="text-center">No Held PIREPs</td></tr>';
                            } else {
                                while ($pirep1 = $pendingpireps->fetch_assoc()) {
                                    echo '<tr><td class="align-middle">';
                                    echo getUserInfo($pirep1["pilotid"])["name"];
                                    echo '</td><td class="align-middle">';
                                    echo $pirep1["departure"].'-'.$pirep1["arrival"];
                                    echo '</td><td class="align-middle">';
                                    echo secsToHrMin($pirep1["flighttime"]);
                                    echo '</td><td class="align-middle">';
                                    echo $pirep1["multi"];
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn btn-success approvePirep" data="'.$pirep1["id"].'">&#10004;</button>';
                                    echo '<button class="btn btn-danger denyPirep" data="'.$pirep1["id"].'">&#10006;</button>';
                                    echo '</td></tr>';
                                }
                            }
                        ?>
                    </tbody>
                </table><br />
                </div>
                <div class="desktop-hidden text-center"><p>Please Use a Wider Screen for PIREP Moderation</p></div>

                <h5>Multipliers</h5>
                <div id="multiMessage"></div>
                <?php if (isset($_POST["action"]) && $_POST["action"] == 'addmulti') {
                    $multiq = $conn->prepare("INSERT into multipliers (code, multiplier, name) VALUES (?, ?, ?);");
                    $multiq->bind_param("ids", $_POST["code"], $_POST["multi"], $_POST["name"]);
                    $mRet = $multiq->execute();
                    if ($mRet === FALSE) {
                        echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                    } else {
                        echo '<div class="alert text-center alert-success">Multiplier Added</div>';
                    }
                } ?>
                <button type="button" class="btn text-light" style="background-color: #E4181E;" data-toggle="modal" data-target="#addMultiplier">New Multiplier</button>
                
                <table class="table table-striped">
                    <thead class="bg-virgin"><tr><th>Code</th><th>Multiplier</th><th class="mobile-hidden">Name</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php
                            $allmultipliers = selectMultiple("SELECT * FROM multipliers;");
                            if ($allmultipliers === FALSE) {
                                echo '<tr><td colspan="5" class="text-center">No Multipliers</td></tr>';
                            } else {
                                while ($mItem = $allmultipliers->fetch_assoc()) {
                                    echo '<tr><td class="align-middle">';
                                    echo $mItem["code"];
                                    echo '</td><td class="align-middle">';
                                    echo $mItem["multiplier"].'x';
                                    echo '</td><td class="mobile-hidden align-middle">';
                                    echo $mItem["name"];
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn bg-virgin delMulti" data="'.$mItem["id"].'"><i class="fa fa-trash"></i></button>';
                                    echo '</td></tr>';
                                }
                            }
                        ?>
                    </tbody>
                </table>
                <div id="addMultiplier" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add PIREP Multiplier</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form action="?page=pirepmod" method="post">
                            <input hidden name="action" value="addmulti">
                            <div class="form-group">
                                <label for="code">Multiplier Code</label>
                                <input type="text" readonly required class="form-control" id="code" name="code" value="<?php echo generateMultiCode(); ?>">
                            </div>
                            <div class="form-group">
                                <label for="multi">Multiplication</label>
                                <input type="number" required class="form-control" step="0.5" id="multi" name="multi">
                            </div>
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" required maxlength="120" class="form-control" id="name" name="name">
                            </div>
                            <input type="submit" class="btn bg-virgin" value="Add Multiplier">
                        </form>
                    </div>
                    </div>

                </div>
                </div>
            </div>
            <div class="tab-pane container" id="newsmanage">
                <h4>News Management</h4>
                <div id="newsMessage"></div>
                <?php if (isset($_POST["action"]) && $_POST["action"] == 'addnews') { 
                $qryy = $conn->prepare("INSERT INTO news (subject, content, author, type) VALUES (?, ?, ?, ?);");
                $qryy->bind_param("sssi", $_POST["subject"], $_POST["content"], $_SESSION["pilotinfo"]["name"], $_POST["type"]);
                $return = $qryy->execute();
                if ($return === FALSE) {
                    echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                } else {
                    echo '<div class="alert text-center alert-success">News Item Added</div>';
                    $msg = urlencode('*NEWS ITEM: '.$_POST["subject"]."*\r\nCheck it out: https://crew.ifvirginvirtual.vip");
                    file_get_contents("https://VGVA-Webhook-Server--webpage.repl.co?token=f280edb5-f685-4810-bb9f-19661f1389be&channel=feed&message=$msg");
                }
                } else { ?>
                    <button type="button" class="btn text-light" style="background-color: #E4181E;" data-toggle="modal" data-target="#addNews">Add News</button>
                <?php } ?>
                <table class="table">
                    <thead class="bg-virgin"><tr><th>Subject</th><th class="mobile-hidden">Author</th><th>Delete</th></tr></tr></thead>
                    <tbody>
                        <?php
                            $allnews = selectMultiple("SELECT * FROM news;");
                            if ($allnews === FALSE) {
                                echo '<tr><td colspan="3" class="text-center">No News</td></tr>';
                            } else {
                                while ($nItem = $allnews->fetch_assoc()) {
                                    echo '<tr><td class="align-middle">';
                                    echo $nItem["subject"];
                                    echo '</td><td class="mobile-hidden align-middle">';
                                    echo $nItem["author"];
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn btn-danger delNews" data="'.$nItem["id"].'">&#10006;</button>';
                                    echo '</td></tr>';
                                }
                            }
                        ?>
                    </tbody>
                </table>
                <div id="addNews" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add News</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form action="?page=news" method="post">
                            <input hidden name="action" value="addnews">
                            <div class="form-group">
                                <label for="subject">News Subject</label>
                                <input requried type="text" maxlength="120" class="form-control" name="subject" id="subject">
                            </div>
                            <div class="form-group">
                                <label for="type">News Type</label>
                                <select name="type" id="type" class="form-control" required>
                                    <option value>Select</option>
                                    <option value="1">Blog Post</option>
                                    <option value="2">Internal News</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="editor">News Content</label>
                                <textarea name="content" id="editor"></textarea>
                                <script>
                                    ClassicEditor
                                        .create( document.querySelector( '#editor' ) )
                                        .catch( error => {
                                            console.error( error );
                                        } );
                                </script>
                            </div>
                            <input type="submit" class="btn text-light"  style="background-color: #E4181E;" value="Add News">
                        </form>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            <div class="tab-pane container" id="emailpilots">
                <h4>Email Pilots</h4>
                <?php
                    if (isset($_POST["action"]) && $_POST["action"] == 'sendmail') {
                        if ($_POST["users"] == 'X') {
                            $recipients = selectMultiple("SELECT email FROM pilots WHERE recruitstage > 2;");
                        } else {
                            $recip = $conn->prepare("SELECT email FROM pilots WHERE recruitstage=?");
                            $recip->bind_param("i", $_POST["users"]);
                            $sel = $recip->execute();
                            if ($sel === FALSE) {
                                echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                            } else {
                                $recipients = $recip->get_result();
                                if ($recipients->num_rows == 0) {
                                    $recipients = FALSE;
                                }
                            }
                        }
                        if ($recipients === FALSE) {
                            echo '<div class="alert text-center alert-warning">No Users Found in Given Group</div>';
                        } else {
                            $recips = array();
                            while ($usr = $recipients->fetch_assoc()) {
                                array_push($recips, $usr["email"]);
                            }
                            $sendEmail = [$recips, $_POST["subject"], $_POST["content"]];
                            if ($emll != true) {
                                echo '<div class="alert text-center alert-danger">Error Sending Email</div>';
                            } else {
                                echo '<div class="alert text-center alert-success">Email Sent Successfully</div>';
                            }
                        }
                    }
                ?>
                <form action="?page=email" method="post">
                    <input hidden name="action" value="sendmail">
                    <div class="form-group">
                        <label for="subject">Email Subject</label>
                        <input type="text" name="subject" required id="subject" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="users">Recipient Group</label>
                        <select require class="form-control" name="users" id="users">
                            <option value>Select</option>
                            <option value="X">All Pilots</option>
                            <option value="4">All Staff</option>
                            <option value="5">Executive Team</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editor1">Email Content</label>
                        <textarea id="editor1" name="content"></textarea>
                        <script>
                            ClassicEditor
                                .create( document.querySelector( '#editor1' ) )
                                .catch( error => {
                                    console.error( error );
                                } );
                        </script>
                    </div>
                    <input type="submit" class="btn text-light"  style="background-color: #E4181E;" value="Send Email">
                </form>
            </div>
            <div class="tab-pane container" id="wfrmanage">
                <h4>Featured Routes Admin</h4>
                <p>View Featured Routes in the Featured Routes tab</p>

                <?php
                    if (isset($_POST["action"]) && $_POST["action"] == 'addwfr') {
                        $wQuery1 = $conn->prepare("INSERT INTO wfrdays (date, title, remarks) VALUES (?, ?, ?);");
                        if (isset($_POST["remarks"]) && $_POST["remarks"] != '') {
                            $rm = $_POST["remarks"];
                        } else {
                            $rm = '';
                        }
                        $wQuery1->bind_param("sss", $_POST["date"], $_POST["title"], $rm);
                        $wRet1 = $wQuery1->execute();
                        if ($wRet1 === FALSE) {
                            echo '<div class="alert alert-danger text-center">MySQL Error: '.mysqli_error($conn).'</div>';
                        } else {
                            $dayid = select("SELECT MAX(id) as ainum FROM wfrdays")["ainum"];
                            // Get the Routes so we can grab IDs
                            $rr = selectMultiple("SELECT id, fltnum, typecode FROM routes;");
                            $allroutes = array();
                            while ($r = $rr->fetch_assoc()) {
                                $allroutes[$r["typecode"].$r["fltnum"]] = $r["id"];
                            }
                            $theroutes = array();
                            // Process Routes to get the IDs
                            $fRoutes = explode(",", $_POST["routes"]);
                            foreach ($fRoutes as $f) {
                                if (strpos($f, "-") !== FALSE && strpos($f, ":") !== FALSE) {
                                    $fa = explode(":", $f);
                                    $fCode = $fa[0];
                                    $fRange = explode("-", $fa[1]);
                                    $fNumbers = range($fRange[0], $fRange[1]);
                                    foreach ($fNumbers as $n) {
                                        $d = $fCode.$n;
                                        if (isset($allroutes[$d])) {
                                            array_push($theroutes, $allroutes[$d]);
                                        } else {
                                            echo '<div class="alert alert-warning text-center">Route '.$d.' does not exist</div>';
                                        }
                                    }
                                } else {
                                    if (isset($allroutes[$f])) {
                                        array_push($theroutes, $allroutes[$f]);
                                    } else {
                                        echo '<div class="alert alert-warning text-center">Route '.$f.' does not exist</div>';
                                    }
                                }
                            }
                            $wStmt = "INSERT INTO wfrroutes (dayid, routeid) VALUES";
                            $t = 1;
                            foreach ($theroutes as $q) {
                                if ($t == count($theroutes)) {
                                    $wStmt = $wStmt . "
                                    ($dayid, '".mysqli_real_escape_string($conn, $q)."')";
                                } else {
                                    $wStmt = $wStmt . "
                                    ($dayid, '".mysqli_real_escape_string($conn, $q)."'),";
                                }
                                $t = $t + 1;
                            }
                            $wRet2 = runQ($wStmt);
                            if ($wRet2 === FALSE) {
                                echo '<div class="alert alert-danger text-center">MySQL Error: '.mysqli_error($conn).'</div>';
                            }
                            if (mysqli_errno($conn) == 0) {
                                echo '<div class="alert alert-success text-center">Featured Routes Added Successfully</div>';
                            }
                        }
                    }
                ?>

                <h5>Add Featured Routes</h5>
                <form action="?page=wfr" method="post">
                    <input hidden name="action" value="addwfr">
                    <div class="form-group">
                        <label for="date">Date Featured</label>
                        <input required type="date" class="form-control" name="date" id="date">
                    </div>
                    <div class="form-group">
                        <label for="title">Day Title</label>
                        <input required type="text" maxlength="120" class="form-control" name="title" id="title">
                    </div>
                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <input type="text" class="form-control" name="remarks" id="remarks">
                        <small class="help-block">Optional. Used to explain mass routes. If this is set, the individual routes will be hidden in a collapisble</small>
                    </div>
                    <div class="form-group">
                        <label for="routes">Routes</label>
                        <input type="text" class="form-control" name="routes" id="routes">
                        <small class="help-block">Separate Routes with commas (no spaces, ie. VGVA1,VGVA2,NSV3). Ranges are Allowed but must be formatted as CODE:1-3 where 1-3 is the range of Flight Numbers (ie. VGVA:24-46,ASVA:5-12)</small>
                    </div>
                    <input type="submit" class="btn bg-virgin" value="Add Featured Routes">
                </form>
            </div>
            <div class="tab-pane container" id="eventmanage">
                <h4>Events Admin</h4>
                <div id="eventMessage"></div>
                <?php
                    if (isset($_POST["action"]) && $_POST["action"] == 'addevent') {
                        $eStmt = $conn->prepare("INSERT INTO events (name, description, type, date, time, depart, arrive, visibility, aircraftentity, server) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
                        $eStmt->bind_param("ssissssiss", $_POST["eName"], $_POST["eDesc"], $_POST["eType"], $_POST["eDate"], $_POST["eTime"], $_POST["eDepart"], $_POST["eArrive"], $_POST["eVis"], $_POST["eAC"], $_POST["eServ"]);
                        $eRet = $eStmt->execute();
                        if ($eRet === FALSE) {
                            echo '<div class="alert alert-danger text-center">MySQL Error: '.mysqli_error($conn).'</div>';
                        } else {
                            echo '<div class="alert alert-success text-center">Event Draft Saved Successfully</div>';
                        }
                    } elseif (isset($_POST["action"]) && $_POST["action"] == 'editevent') {
                        $eStmt = $conn->prepare("UPDATE events SET name=?, description=?, depart=?, arrive=?, visibility=?, aircraftentity=? WHERE id=?;");
                        $eStmt->bind_param("ssssisi", $_POST["eeName"], $_POST["eeDesc"], $_POST["eeDepart"], $_POST["eeArrive"], $_POST["eeVis"], $_POST["eeAC"], $_POST["eeId"]);
                        $eRet = $eStmt->execute();
                        if ($eRet === FALSE) {
                            echo '<div class="alert alert-danger text-center">MySQL Error: '.mysqli_error($conn).'</div>';
                        } else {
                            echo '<div class="alert alert-success text-center">Event Updated Successfully</div>';
                        }
                    } elseif (isset($_POST["action"]) && $_POST["action"] == 'emanualgates') {
                        $eQuery = "INSERT INTO eventslots (eventid, gate) VALUES";
                        $gates = count($_POST) - 2;
                        for ($i=1;$i<=$gates;$i++) {
                            $gItem = $_POST["gate".$i];
                            if ($i == 1) {
                                $eQuery = $eQuery . "
                                (".mysqli_real_escape_string($conn, $_POST["eid"]).", '".mysqli_real_escape_string($conn, $gItem)."')";
                            } else {
                                $eQuery = $eQuery . ",
                                (".mysqli_real_escape_string($conn, $_POST["eid"]).", '".mysqli_real_escape_string($conn, $gItem)."')";
                            }
                        }
                        $eQuery = $eQuery . ';';
                        $gRet = runQ($eQuery);
                        if ($gRet === TRUE) {
                            echo '<div class="alert alert-success text-center">Gates Added Successfully. You may now set the Event Visibility to your desired audience.</div>';
                        } else {
                            echo '<div class="alert alert-danger text-center">MySQL Error</div>';
                        }
                    } elseif (isset($_POST["action"]) && $_POST["action"] == 'eautogates') {
                        $gates = json_decode(file_get_contents("https://vh-net.com/api/airportinfo/".urlencode($_POST["eAutoAirport"])."/gates"), true);
                        $sizeArray = array("A0", "B1", "C2", "D3", "E4", "F5");
                        $mingateint = substr($_POST["eAutoSize"], 1, 1);
                        $mygates = array();
                        if (!isset($_POST["eAutoContains"]) || $_POST["eAutoContains"] == '') {
                            foreach ($gates as $gateItem) {
                                if (count($mygates) >= $_POST["eAutoGates"]) {
                                    break;
                                }
                                $gateSizeInt = 0;
                                foreach ($sizeArray as $sizeItem) {
                                    if (substr($sizeItem, 0, 1) == $gateItem["size"]) {
                                        $gateSizeInt = substr($sizeItem, 1, 1);
                                        break;
                                    }
                                }
                                if ($gateSizeInt >= $mingateint) {
                                    array_push($mygates, $gateItem["name"]);
                                }
                            }
                            echo '<div class="alert alert-info text-center">Found the following Gates:<br />';
                            foreach ($mygates as $gg) {
                                echo $gg.'<br />';
                            }
                            echo '<form action="?page=eventmod" method="post"><input hidden name="eid" value="'.$_POST["eid"].'"><input hidden name="autoGatesJson" value=\''.json_encode($mygates).'\'><input hidden name="action" value="autoGatesQuery"><input type="submit" class="btn btn-success" value="Accept & Add"><a href="pilotpanel.php?page=eventmod" class="btn btn-danger">Erase & Start Over</a></form>';
                            echo '</div>';
                        } else {
                            foreach ($gates as $gateItem) {
                                if (count($mygates) >= $_POST["eAutoGates"]) {
                                    break;
                                }
                                $gateSizeInt = 0;
                                foreach ($sizeArray as $sizeItem) {
                                    if (substr($sizeItem, 0, 1) == $gateItem["size"]) {
                                        $gateSizeInt = substr($sizeItem, 1, 1);
                                        break;
                                    }
                                }
                                if ($gateSizeInt >= $mingateint && strpos($gateItem["name"], $_POST["eAutoContains"]) !== FALSE) {
                                    array_push($mygates, $gateItem["name"]);
                                }
                            }
                            echo '<div class="alert alert-info text-center">Found the following Gates:<br />';
                            foreach ($mygates as $gg) {
                                echo $gg.'<br />';
                            }
                            echo '<form action="?page=eventmod" method="post"><input hidden name="eid" value="'.$_POST["eid"].'"><input hidden name="autoGatesJson" value=\''.json_encode($mygates).'\'><input hidden name="action" value="autoGatesQuery"><input type="submit" class="btn btn-success" value="Accept & Add"><a href="pilotpanel.php?page=eventmod" class="btn btn-danger">Erase & Start Over</a></form>';
                            echo '</div>';
                        }
                    } elseif (isset($_POST["action"]) && $_POST["action"] == 'autoGatesQuery') {
                        $mygates = json_decode($_POST["autoGatesJson"]);
                        $gQuery = "INSERT INTO eventslots (eventid, gate) VALUES";
                        for ($i=0;$i<count($mygates);$i++) {
                            $myGateItem = $mygates[$i];
                            if ($i == 0) {
                                $gQuery = $gQuery . "
                                (".mysqli_real_escape_string($conn, $_POST["eid"]).",'".mysqli_real_escape_string($conn, $myGateItem)."')";
                            } else {
                                $gQuery = $gQuery . ",
                                (".mysqli_real_escape_string($conn, $_POST["eid"]).",'".mysqli_real_escape_string($conn, $myGateItem)."')";
                            }
                        }
                        $gQuery = $gQuery.";";
                        $gRet = runQ($gQuery);
                        if ($gRet === TRUE) {
                            echo '<div class="alert alert-success text-center">Gates Added Successfully</div>';
                        } else {
                            echo '<div class="alert alert-danger text-center">MySQL Error</div>';
                        }
                    }
                ?>
                <button class="btn bg-virgin mb-2" data-toggle="modal" data-target="#eventChooseType">New Event Draft</button>
                    <div id="eventChooseType" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Choose Event Type</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <script>
                                $(document).ready(function() {
                                    $("#eventTypeSubmit").click(function() {
                                        $("#eventChooseType").modal("hide");
                                        $("#eType").val($("#eventTypeSelect").val());
                                        if ($("#eventTypeSelect").val() > 1) {
                                            $("#eVisDraft").attr("selected", true);
                                            $("#eVisGroup").hide();
                                        }
                                        $("#eventCreate").modal("show");
                                    });
                                });
                            </script>
                            <select id="eventTypeSelect" class="form-control">
                                <option value="1">Standard</option>
                                <option value="2">Flyout</option>
                                <option value="3">Flyin</option>
                            </select>
                            <button id="eventTypeSubmit" class="btn bg-virgin mt-2">Let's Go</button>
                        </div>
                        </div>

                    </div>
                    </div>
                <div id="eventCreate" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Create Event - Event Details</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-3">
                        <form action="?page=eventmod" method="post" class="m-3">
                            <input hidden name="action" value="addevent">
                            <input hidden name="eType" id="eType">
                            <input hidden name="eVis" value="0">
                            <div class="form-group">
                                <label for="eName">Event Name</label>
                                <input required type="text" class="form-control" required name="eName" id="eName" maxlength="120">
                            </div>
                            <div class="form-group">
                                <label for="eventEditor">Event Description</label>
                                <textarea id="eventEditor" name="eDesc"></textarea>
                            </div>
                            <script>
                                ClassicEditor
                                    .create( document.querySelector( '#eventEditor' ) )
                                    .catch( error => {
                                        console.error( error );
                                    } );
                            </script>
                            <div class="form-group">
                                <label for="eDate">Date</label>
                                <input required type="date" class="form-control" name="eDate" id="eDate">
                                <small class="help-block">UTC Timezone</small>
                            </div>
                            <div class="form-group">
                                <label for="eTime">Time</label>
                                <select required class="form-control" name="eTime" id="eTime">
                                    <option value>Select</option>
                                    <option value="0000">0000Z</option>
                                    <option value="0030">0030Z</option>
                                    <option value="0100">0100Z</option>
                                    <option value="0130">0130Z</option>
                                    <option value="0200">0200Z</option>
                                    <option value="0230">0230Z</option>
                                    <option value="0300">0300Z</option>
                                    <option value="0330">0330Z</option>
                                    <option value="0400">0400Z</option>
                                    <option value="0430">0430Z</option>
                                    <option value="0500">0500Z</option>
                                    <option value="0530">0530Z</option>
                                    <option value="0600">0600Z</option>
                                    <option value="0630">0630Z</option>
                                    <option value="0700">0700Z</option>
                                    <option value="0730">0730Z</option>
                                    <option value="0800">0800Z</option>
                                    <option value="0830">0830Z</option>
                                    <option value="0900">0900Z</option>
                                    <option value="0930">0930Z</option>
                                    <option value="1000">1000Z</option>
                                    <option value="1030">1030Z</option>
                                    <option value="1100">1100Z</option>
                                    <option value="1130">1130Z</option>
                                    <option value="1200">1200Z</option>
                                    <option value="1230">1230Z</option>
                                    <option value="1300">1300Z</option>
                                    <option value="1330">1330Z</option>
                                    <option value="1400">1400Z</option>
                                    <option value="1430">1430Z</option>
                                    <option value="1500">1500Z</option>
                                    <option value="1530">1530Z</option>
                                    <option value="1600">1600Z</option>
                                    <option value="1630">1630Z</option>
                                    <option value="1700">1700Z</option>
                                    <option value="1730">1730Z</option>
                                    <option value="1800">1800Z</option>
                                    <option value="1830">1830Z</option>
                                    <option value="1900">1900Z</option>
                                    <option value="1930">1930Z</option>
                                    <option value="2000">2000Z</option>
                                    <option value="2030">2030Z</option>
                                    <option value="2100">2100Z</option>
                                    <option value="2130">2130Z</option>
                                    <option value="2200">2200Z</option>
                                    <option value="2230">2230Z</option>
                                    <option value="2300">2300Z</option>
                                    <option value="2330">2330Z</option>
                                </select>
                                <small class="help-block">UTC Timezone</small>
                            </div>
                            <div class="form-group">
                                <label for="eDepart">Departure(s)</label>
                                <input required type="text" class="form-control" name="eDepart" id="eDepart" minlength="4" placeholder="ICAO">
                            </div>
                            <div class="form-group">
                                <label for="eArrive">Arrival(s)</label>
                                <input required type="text" class="form-control" name="eArrive" id="eArrive" minlength="4" placeholder="ICAO">
                            </div>
                            <div class="form-group">
                                <label for="eAC">Aircraft</label>
                                <select class="form-control" required name="eAC" id="eACSelect">
                                    <option value>Select</option>
                                    <?php
                                        foreach ($allaircraft as $u) {
                                            echo "<option value='{$u['id']}'>{$u['name']}</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="eServ">Server</label>
                                <select class="form-control" required name="eServ" id="eServ">
                                    <option value>Select</option>
                                    <option>Casual Server</option>
                                    <option>Training Server</option>
                                    <option>Expert Server</option>
                                </select>
                            </div>
                            <input type="submit" class="btn bg-virgin" value="Add Event" id="eSubmit">
                        </form>
                    </div>
                    </div>
                </div>
                </div>
                
                <script>
                    $(document).ready(function() {
                        var eee;
                        $(".eEdit").click(function() {
                            let id = $(this).attr("data-id");
                            let name = $(this).attr("data-name");
                            let desc = $(this).attr("data-desc");
                            let type = $(this).attr("data-type");
                            let dep = $(this).attr("data-dep");
                            let arr = $(this).attr("data-arr");
                            let vis = $(this).attr("data-vis");
                            let ac = $(this).attr("data-ac");
                            $("#eeId").attr("value", id);
                            $("#eeName").attr("value", name);
                            $("#editeventEditor").html(desc);
                            ClassicEditor
                                .create( document.querySelector( '#editeventEditor' ) )
                                .catch( error => {
                                    console.error( error );
                                } );
                                hgsd = editor;
                            $("#eeDepart").attr("value", dep);
                            $("#eeArrive").attr("value", arr);
                            if (isNaN(ac) == true) {
                                $("#eeACOther").attr("hidden", false);
                                $("#eeACOther").attr("disabled", false);
                                $("#eeACSelect").attr("name", "eeACSelect");
                                $("#eeACOther").attr("name", "eeAC");
                                $("$eeACOther").attr("value", ac);
                            } else {
                                $("#eeac" + ac).attr("selected", true);
                            }
                            $("#eVis" + vis).attr("selected", true);
                            $("#editEvent").modal("show");
                        });

                        $("#editEvent").on('hide.bs.modal', function() {
                            window.location.href = 'pilotpanel.php?page=eventmod';
                        });
                    });
                </script>

                <table class="table table-striped"><thead class="bg-virgin"><tr><th>Name</th><th class="mobile-hidden">Date</th><th>Visibility</th><th>Actions</th></tr></thead><tbody>
                    <?php
                        $allevents = selectMultiple("SELECT * FROM events WHERE date >= NOW() ORDER BY date ASC;");
                        $theevents = array();
                        $vis = array("Draft", "Staff Only", "Pilots Only", "Public");
                        if ($allevents === FALSE) {
                            echo '<tr><td colspan="4" class="text-center mobile-hidden">No Upcoming Events</td><td colspan="3" class="text-center desktop-hidden">No Upcoming Events</td></tr>';
                            echo mysqli_error($conn);
                        } else {
                            while ($e = $allevents->fetch_assoc()) {
                                array_push($theevents, $e);
                                echo '<tr><td class="align-middle">';
                                echo $e["name"];
                                echo '</td><td class="mobile-hidden align-middle">';
                                echo $e["date"];
                                echo '</td><td class="align-middle">';
                                echo $vis[$e["visibility"]];
                                echo '</td><td class="align-middle">';
                                echo '<button class="btn bg-virgin eGates" data="'.$e["id"].'"><i class="fa fa-luggage-cart"></i></button>';
                                echo "<button class=\"btn bg-virgin eEdit\" data-id=\"{$e["id"]}\" data-name=\"{$e["name"]}\" data-desc=\"{$e["description"]}\" data-type=\"{$e["type"]}\" data-dep=\"{$e["depart"]}\" data-arr=\"{$e["arrive"]}\" data-vis=\"{$e["visibility"]}\" data-ac=\"{$e["aircraftentity"]}\"><i class=\"fa fa-edit\"></i></button>";
                                echo '<button class="btn bg-virgin eDelete" data="'.$e["id"].'"><i class="fa fa-trash"></i></button>';
                                echo '</td></tr>';
                            }
                        }
                    ?>
                </tbody></table>
                <div id="eChoseGateMethod" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Event Gates</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-3">
                        <script>
                            $(document).ready(function() {
                                $(".eGates").click(function() {
                                    let k = $(this).attr("data");
                                    $("#tempEventId").html(k);
                                    $("#eChoseGateMethod").modal('show');
                                });
                                $("#gateMethodSubmit").click(function() {
                                    var res = $("#eGateMethod").val();
                                    if (res == '2') {
                                        $("#eChoseGateMethod").modal('hide');
                                        $("#eAutoGate").modal('show');
                                        $("#eidAuto").val($("#tempEventId").html());
                                    } else if (res == '1') {
                                        $("#eChoseGateMethod").modal('hide');
                                        $("#eManualGate").modal('show');
                                        $("#eid").val($("#tempEventId").html());
                                    }
                                });
                            });
                        </script>
                        <select class="form-control" id="eGateMethod">
                            <option value>Select</option>
                            <option value="1">Manual Gate Entry</option>
                            <option value="2">Auto-Find Gates</option>
                        </select>
                        <span hidden id="tempEventId"></span>
                        <button class="btn bg-virgin mt-2" id="gateMethodSubmit">Let's Go</button>
                    </div>
                    </div>
                </div>
                </div>
                <div id="eAutoGate" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Event Gates - Automatic</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-3">
                        <form action="?page=eventmod" method="post">
                            <input hidden name="action" value="eautogates">
                            <input hidden name="eid" id="eidAuto">
                            <div class="form-group">
                                <label for="eAutoAirport">Airport</label>
                                <input type="text" name="eAutoAirport" id="eAutoAirport" required class="form-control" placeholder="ICAO">
                            </div>
                            <div class="form-group">
                                <label for="eAutoGates">Number of Gates</label>
                                <input type="number" id="eAutoGates" name="eAutoGates" required class="form-control" min="0">
                            </div>
                            <div class="form-group">
                                <label for="eAutoContains">Gate Name Contains</label>
                                <input type="text" name="eAutoContains" id="eAutoContains" class="form-control">
                                <small class="help-text">Only use gates with this in their name. Useful for using realistic terminals. Leave blank to use any gate that meets the size minimum.</small>
                            </div>
                            <div class="form-group">
                                <label for="eAutoSize">Minimum Gate Size</label>
                                <select required class="form-control" name="eAutoSize" id="eAutoSize">
                                    <option value>Select</option>
                                    <option value="A0">Alpha</option>
                                    <option value="B1">Bravo</option>
                                    <option value="C2">Charlie</option>
                                    <option value="D3">Delta</option>
                                    <option value="E4">Echo</option>
                                    <option value="F5">Foxtrot</option>
                                </select>
                            </div>
                            <input type="submit" class="btn bg-virgin" value="Fetch Gates">
                        </form>
                    </div>
                    </div>
                </div>
                </div>
                <div id="eManualGate" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Event Gates - Manual</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-3">
                        <script>
                            $(document).ready(function() {
                                var rows = 5;
                                $("#addManualEventGate").click(function() {
                                    rows = rows + 1;
                                    $("#moregates").html($("#moregates").html() + '<input type="text" name="gate' + rows + '" class="form-control gateEntry">');
                                    $(".gateEntry").keyup(function() {
                                        let val = $(this).val();
                                        $(this).attr("value", val);
                                    });
                                });
                                $(".gateEntry").keyup(function() {
                                    let val = $(this).val();
                                    $(this).attr("value", val);
                                });
                            });
                        </script>
                        <button class="btn bg-virgin" id="addManualEventGate">Add Another Gate Box</button>
                        <form action="?page=eventmod" method="post">
                            <input hidden name="action" value="emanualgates">
                            <input hidden name="eid" id="eid">
                            <p>Enter one Gate Name per text box, add as many or as few as you want, just at least one. Once gates are added, they cannot be removed or edited.</p>
                            <input type="text" name="gate1" class="form-control gateEntry" required>
                            <input type="text" name="gate2" class="form-control gateEntry">
                            <input type="text" name="gate3" class="form-control gateEntry">
                            <input type="text" name="gate4" class="form-control gateEntry">
                            <input type="text" name="gate5" class="form-control gateEntry">
                            <div id="moregates"></div>
                            <input type="submit" class="btn bg-virgin mt-2" value="Add Gates">
                        </form>
                    </div>
                    </div>
                </div>
                </div>
                <div id="editEvent" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Event</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-3">
                        <form action="?page=eventmod" method="post" class="m-3">
                            <input hidden name="action" value="editevent">
                            <input hidden name="eeId" id="eeId">
                            <div class="form-group">
                                <label for="eeName">Event Name</label>
                                <input required type="text" class="form-control" required name="eeName" id="eeName" maxlength="120">
                            </div>
                            <div class="form-group">
                                <label for="editeventEditor">Event Description</label>
                                <textarea id="editeventEditor" name="eeDesc"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="eeDepart">Departure(s)</label>
                                <input required type="text" class="form-control" name="eeDepart" id="eeDepart" minlength="4" placeholder="ICAO">
                            </div>
                            <div class="form-group">
                                <label for="eeArrive">Arrival(s)</label>
                                <input required type="text" class="form-control" name="eeArrive" id="eeArrive" minlength="4" placeholder="ICAO">
                            </div>
                            <div class="form-group">
                                <label for="eeAC">Aircraft</label>
                                <select class="form-control" required name="eeAC" id="eeACSelect">
                                    <option value>Select</option>
                                    <?php
                                        foreach ($allaircraft as $u) {
                                            echo "<option value='{$u['id']}' id='eeac{$u['id']}'>{$u['name']}</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="eeVis">Visibility</label>
                                <select class="form-control" required name="eeVis" id="eeVis">
                                    <option value>Select</option>
                                    <option value="0" id="eVis0">Draft</option>
                                    <option value="1" id="eVis1">Staff Event</option>
                                    <option value="2" id="eVis2">Internal Event</option>
                                    <option value="3" id="eVis3">Public Event</option>
                                </select>
                            </div>
                            <input type="submit" class="btn bg-virgin" value="Edit Event" id="eeSubmit">
                        </form>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            <div class="tab-pane container" id="opsmanage">
                <h4>Operations Management</h4>
                <div id="opsMessage"></div>
                <div class="row mx-0 mb-3 p-0">
                    <div class="col-lg-6 border p-2">
                        <h5>Ranks</h5>
                        <?php
                            if (isset($_POST["action"]) && $_POST["action"] == 'addrank') {
                                $rStmt = $conn->prepare("INSERT INTO ranks (name, hoursreq) VALUES (?, ?);");
                                $requ = $_POST["minhrs"] * 3600;
                                $rStmt->bind_param("si", $_POST["name"], $requ);
                                $rRet = $rStmt->execute();
                                if ($rRet === FALSE) {
                                    echo '<div class="alert alert-danger text-center">MySQL Error</div>';
                                } else {
                                    echo '<div class="alert alert-success text-center">Rank Added Successfully</div>';
                                }
                            }
                        ?>
                        <button type="button" class="btn bg-virgin" data-toggle="modal" data-target="#newRank">New Rank</button>
                        <div class="modal fade" id="newRank" role="dialog">
                            <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                <h4 class="modal-title">New Rank</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <form action="?page=ops" method="post">
                                        <input hidden name="action" value="addrank">
                                        <div class="form-group">
                                            <label for="name">Rank Name</label>
                                            <input required type="text" maxlength="120" class="form-control" name="name" id="name">
                                        </div>
                                        <div class="form-group">
                                            <label for="minhrs">Minimum Hours</label>
                                            <input required type="number" min="0" class="form-control" name="minhrs" id="minhrs">
                                        </div>
                                        <input type="submit" class="btn bg-virgin" value="Add Rank">
                                    </form>
                                </div>
                            </div>
                            </div>
                        </div>
                        <table class="table"><thead class="bg-virgin"><tr><th>Name</th><th>Hours Required</th><th>Actions</th></tr></thead><tbody>
                        <?php
                            $allranks = selectMultiple("SELECT * FROM ranks;");
                            $theranks = array();
                            if ($allranks === FALSE) {
                                echo '<tr><td colspan="3" class="text-center">No Ranks</td></tr>';
                            } else {
                                while ($rankItem = $allranks->fetch_assoc()) {
                                    array_push($theranks, $rankItem);
                                    echo '<tr><td class="align-middle">';
                                    echo $rankItem["name"];
                                    echo '</td><td class="align-middle">';
                                    echo secsToHrMin($rankItem["hoursreq"]);
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn text-light delRank" style="background-color: #E4181E;" data="'.$rankItem["id"].'"><i class="fa fa-trash"></i></button>';
                                    echo '</td></tr>';
                                }
                            }
                        ?>
                        </tbody></table>
                    </div>
                    <div class="col-lg-6 border p-2">
                        <h5>Flight Types</h5>
                        <?php
                            if (isset($_POST["action"]) && $_POST["action"] == 'addft') {
                                $rStmt = $conn->prepare("INSERT INTO flighttypes (name, code) VALUES (?, ?);");
                                $rStmt->bind_param("ss", $_POST["name"], $_POST["code"]);
                                $rRet = $rStmt->execute();
                                if ($rRet === FALSE) {
                                    echo '<div class="alert alert-danger text-center">MySQL Error</div>';
                                } else {
                                    echo '<div class="alert alert-success text-center">Flight Type Added Successfully</div>';
                                }
                            }
                        ?>
                        <button type="button" class="btn bg-virgin" data-toggle="modal" data-target="#newFT">New Type</button>
                        <div class="modal fade" id="newFT" role="dialog">
                            <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                <h4 class="modal-title">New Rank</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <form action="?page=ops" method="post">
                                        <input hidden name="action" value="addft">
                                        <div class="form-group">
                                            <label for="name">Type Name</label>
                                            <input required type="text" maxlength="120" class="form-control" name="name" id="name">
                                        </div>
                                        <div class="form-group">
                                            <label for="code">Type Code</label>
                                            <input required type="text" maxlength="4" class="form-control" name="code" id="code">
                                        </div>
                                        <input type="submit" class="btn bg-virgin" value="Add Flight Type">
                                    </form>
                                </div>
                            </div>
                            </div>
                        </div>
                        <table class="table"><thead class="bg-virgin"><tr><th>Name</th><th>Code</th><th>Actions</th></tr></thead><tbody>
                        <?php
                            $allranks = selectMultiple("SELECT * FROM flighttypes;");
                            while ($rankItem = $allranks->fetch_assoc()) {
                                echo '<tr><td class="align-middle">';
                                echo $rankItem["name"];
                                echo '</td><td class="align-middle">';
                                echo $rankItem["code"];
                                echo '</td><td class="align-middle">';
                                echo '<button class="btn text-light delFType" style="background-color: #E4181E;" data="'.$rankItem["id"].'"><i class="fa fa-trash"></i></button>';
                                echo '</td></tr>';
                            }
                        ?>
                        </tbody></table>
                    </div>
                </div>
                <div class="row mx-0 mb-3 p-0">
                    <div class="col-lg-12 border p-2">
                        <h5>Fleet Management</h5>
                        <?php
                            if (isset($_POST["action"]) && $_POST["action"] == 'addfleet') {
                                $rStmt = $conn->prepare("INSERT INTO aircraft (name, code, rankreq, size, ifliveryid) VALUES (?, ?, ?, ?, ?);");
                                $rStmt->bind_param("ssiss", $_POST["name"], $_POST["code"], $_POST["rankreq"], $_POST["size"], $_POST["livid"]);
                                $rRet = $rStmt->execute();
                                if ($rRet === FALSE) {
                                    echo '<div class="alert alert-danger text-center">MySQL Error</div>';
                                } else {
                                    echo '<div class="alert alert-success text-center">Aircraft Added Successfully</div>';
                                }
                            } elseif (isset($_POST["action"]) && $_POST["action"] == 'editac') {
                                $rStmt = $conn->prepare("UPDATE aircraft SET name=?, code=?, rankreq=?, size=? WHERE id=?");
                                $rStmt->bind_param("ssisi", $_POST["name"], $_POST["code"], $_POST["rankreq"], $_POST["size"], $_POST["fleetid"]);
                                $rRet = $rStmt->execute();
                                if ($rRet === FALSE) {
                                    echo '<div class="alert alert-danger text-center">MySQL Error</div>';
                                } else {
                                    echo '<div class="alert alert-success text-center">Aircraft Updated Successfully</div>';
                                }
                            }
                        ?>
                        <button type="button" class="btn bg-virgin" data-toggle="modal" data-target="#newAC">New Aircraft</button>
                        <div class="modal fade" id="newAC" role="dialog">
                            <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                <h4 class="modal-title">New Aircraft</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <form action="?page=ops" method="post">
                                        <input hidden name="action" value="addfleet">
                                        <div class="form-group">
                                            <label for="name">Aircraft Name</label>
                                            <input required type="text" maxlength="120" class="form-control" name="name" id="name">
                                        </div>
                                        <div class="form-group">
                                            <label for="code">Aircraft Code</label>
                                            <input required type="text" maxlength="5" class="form-control" name="code" id="code">
                                        </div>
                                        <div class="form-group">
                                            <label for="livid">Infinite Flight Livery ID</label>
                                            <input required type="text" maxlength="36" minlength="36" class="form-control" name="livid" id="livid">
                                        </div>
                                        <div class="form-group">
                                            <label for="rankreq">Minimum Rank</label>
                                            <select required class="form-control" name="rankreq" id="rankreq">
                                                <option value>Select</option>
                                                <?php
                                                    foreach ($theranks as $itemm) {
                                                        echo "<option value=\"{$itemm['id']}\">{$itemm['name']}</option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="size">Minimum Gate Size</label>
                                            <select required class="form-control" name="size" id="size">
                                                <option value>Select</option>
                                                <option value="A">Alpha</option>
                                                <option value="B">Bravo</option>
                                                <option value="C">Charlie</option>
                                                <option value="D">Delta</option>
                                                <option value="E">Echo</option>
                                                <option value="F">Foxtrot</option>
                                            </select>
                                        </div>
                                        <input type="submit" class="btn bg-virgin" value="Add Aircraft">
                                    </form>
                                </div>
                            </div>
                            </div>
                        </div>
                        <table class="table"><thead class="bg-virgin"><tr><th>Name</th><th class="mobile-hidden">Rank Required</th><th>Actions</th></tr></thead><tbody>
                            <?php
                                $theaircraft = selectMultiple("SELECT aircraft.*, ranks.name AS rankname FROM aircraft INNER JOIN ranks ON ranks.id=aircraft.rankreq ORDER BY aircraft.rankreq ASC");
                                $editac = array();
                                while ($acitem = $theaircraft->fetch_assoc()) {
                                    array_push($editac, $acitem);
                                    echo '<tr><td class="align-middle">';
                                    echo $acitem["name"];
                                    echo '</td><td class="mobile-hidden align-middle">';
                                    echo $acitem["rankname"];
                                    echo '</td><td class="align-middle">';
                                    echo '<button type="button" class="btn bg-virgin" data-toggle="modal" data-target="#editAC'.$acitem["id"].'"><i class="fa fa-edit"></i></button>';
                                    echo '</td></tr>';
                                }
                            ?>
                        </tbody></table>
                        <?php
                            foreach ($editac as $acItem) { ?>
                                <div id="editAC<?php echo $acItem["id"]; ?>" class="modal fade" role="dialog">
                                <div class="modal-lg modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Edit Aircraft: <?php echo $acItem["name"]; ?></h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="?page=ops" method="post">
                                                <input hidden name="action" value="editac">
                                                <input hidden name="fleetid" value="<?php echo $acItem["id"] ?>">
                                                <div class="form-group">
                                                    <label for="name">Aircraft Name</label>
                                                    <input type="text" class="form-control" required name="name" id="name" value="<?php echo $acItem["name"] ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="code">Aircraft Code</label>
                                                    <input type="text" maxlength="5" class="form-control" required name="code" id="code" value="<?php echo  $acItem["code"] ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="rankreq">Minimum Rank</label>
                                                    <select name="rankreq" id="rankreq" class="form-control" required>
                                                        <?php
                                                            foreach ($theranks as $rankItem1) {
                                                                if ($acItem["rankreq"] == $rankItem1["id"]) {
                                                                    echo "<option selected value=\"{$rankItem1['id']}\">{$rankItem1['name']}</option>";
                                                                } else {
                                                                    echo "<option value=\"{$rankItem1['id']}\">{$rankItem1['name']}</option>";
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="size">Minimum Gate Size</label>
                                                    <select name="size" id="size" class="form-control" required>
                                                    <option value="A" <?php if ($acItem["size"] == 'A') {echo 'selected';} ?>>Alpha</option>
                                                    <option value="B" <?php if ($acItem["size"] == 'B') {echo 'selected';} ?>>Bravo</option>
                                                    <option value="C" <?php if ($acItem["size"] == 'C') {echo 'selected';} ?>>Charlie</option>
                                                    <option value="D" <?php if ($acItem["size"] == 'D') {echo 'selected';} ?>>Delta</option>
                                                    <option value="E" <?php if ($acItem["size"] == 'E') {echo 'selected';} ?>>Echo</option>
                                                    <option value="F" <?php if ($acItem["size"] == 'F') {echo 'selected';} ?>>Foxtrot</option>
                                                    </select>
                                                </div>
                                                <input type="submit" class="btn bg-virgin" value="Edit Aircraft">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            <?php } ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane container" id="stats">
                <h4>VA Statistics</h4>
                    <?php
                        $transhours = select("SELECT SUM(transhours) AS trans FROM pilots;")["trans"];
                        $filedhours = select("SELECT SUM(flighttime) AS filed FROM pireps WHERE status=1;")["filed"];
                        $filedflights = select("SELECT COUNT(pireps.id) AS pireps FROM pireps WHERE pireps.status=1;")["pireps"];
                        $transflights = select("SELECT SUM(pilots.transflights) AS trans FROM pilots;");
                        // Improve above

                        $totalpilots = select("SELECT COUNT(*) AS total FROM pilots WHERE recruitstage > 2;")["total"];
                        $totalroutes = select("SELECT COUNT(*) AS total FROM routes;")["total"];
                    ?>
                    <b>Total Hours: </b><?php echo secsToHrMin($transhours + $filedhours); ?><br />
                    <b>Total Flights: </b><?php echo $filedflights + $filedflights; ?><br />
                    <b>Total Pilots: </b><?php echo $totalpilots; ?><br />
                    <b>Total Routes: </b><?php echo $totalroutes; ?><br />
            </div>
        <?php } ?>
    </div>
    </div>
</div>
</div>

<?php
    if (isset($_GET["page"])) {
        echo '<script>$(document).ready(function(){document.getElementById("'.$_GET["page"].'link").click();});</script>';
    }

    if (isset($sendEmail)) {
        sendEmail($sendEmail[0], $sendEmail[1], $sendEmail[2]);
    }
?>

<script>
$(document).ready(function() {
    $("#loader").css("display", "none");
    $("#tc").css("display", "block");
});
</script>

<?php require 'footer.php'; ?>