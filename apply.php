<?php require './includes/header.php'; ?>

<h1 class="text-center pb-0 mb-0">Virgin Virtual Group</h1>
<h3 class="text-center py-0 my-0">Pilot Application<br /><br /></h3>

<div class="container w-50 justify-content-center">

<?php
    if (isset($_POST["action"]) && $_POST["action"] == 'apply') {
        $currentpilots = selectMultiple("SELECT * FROM pilots;");
        $cstaken = false;
        if ($currentpilots !== FALSE) {
            while ($pilot = $currentpilots->fetch_assoc()) {
                if ($pilot["callsign"] == $_POST["callsign"]) {
                    $cstaken = true;
                }
            }
        }

        if ($cstaken == true) {
            echo '<div class="alert text-center alert-warning">Sorry, looks like that callsign is taken. Please choose another one</div>';
        } else {
            if ($_POST["pass"] != $_POST["confpass"]) {
                echo '<div class="alert text-center alert-warning">Passwords do not match</div>';
            } else {
                $stmt = $conn->prepare("INSERT INTO pilots (callsign, name, ifc, email, password, violand, grade, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
                $encpass = password_hash($_POST["pass"], PASSWORD_BCRYPT);
                $stmt->bind_param("sssssdis", $_POST["callsign"], $_POST["name"], $_POST["ifc"], $_POST["email"], $encpass, $_POST["violand"], $_POST["ifgrade"], $_POST["comments"]);
                $ret = $stmt->execute();
                if ($ret !== TRUE) {
                    echo '<div class="alert text-center alert-danger">MySQL Error: '.mysqli_error($conn).'</div>';
                } else {
                    echo '<div class="alert text-center alert-success">Thank you for your application. We will be in touch within 36 hours to advise you of the status of your application<br /><br /><a href="https://ifvirginvirtual.vip">Return to Main Site</a></div>';
                    require 'footer.php';
                    $ip = getIP();
                    $mes = urlencode("New Pilot Application - {$_POST['name']} from IP {$ip}");
                    $res = file_get_contents("https://VGVA-Webhook-Server--webpage.repl.co?token=f280edb5-f685-4810-bb9f-19661f1389be&channel=recruitment&message=$mes");                        
                    die();
                }
            }
        }
    } 
    if (isset($_SESSION["authed"]) && $_SESSION["authed"] == true) {
        header("Location: ".getUrl().'/pilotpanel.php');
        http_response_code(302);
        die();
    }
?>

<form method="post">
    <input hidden name="action" value="apply">
    <div class="form-group text-center">
    <label for="name">Name</label>
    <input required class="form-control" type="text" id="name" name="name" value="<?php if (isset($_POST["name"])) {echo $_POST["name"];} ?>">
    </div>

    <div class="form-group text-center">
    <label for="ifc">Infinite Flight Community Profile URL</label>
    <input required class="form-control" type="url" id="ifc" name="ifc" value="<?php if (isset($_POST["ifc"])) {echo $_POST["ifc"];} ?>">
    <small class="form-text text-muted">All pilots are required to have an active Infinite Flight Community account</small>
    </div>
    
    <div class="form-group text-center">
    <label for="email">Email Address</label>
    <input required class="form-control" type="email" id="email" name="email" value="<?php if (isset($_POST["email"])) {echo $_POST["email"];} ?>">
    </div>

    <div class="form-group text-center">
    <label for="callsign">Callsign</label>
    <input required class="form-control" type="text" value="VGVA" id="callsign" name="callsign" value="<?php if (isset($_POST["callsign"])) {echo $_POST["callsign"];} ?>">
    <small class="form-text text-muted">Must begin with VGVA then have 2-4 numbers, eg VGVA123</small>
    </div>

    <div class="form-group text-center">
    <label for="violand">Violations to Landings Ratio</label>
    <input required class="form-control" step="0.01" type="number" id="violand" name="violand" value="<?php if (isset($_POST["violand"])) {echo $_POST["violand"];} ?>">
    <small class="form-text text-muted">Decimal format, eg 0.35</small>
    </div>

    <div class="form-group text-center">
    <label for="violand">Infinite Flight Grade</label>
    <select required class="form-control" name="ifgrade">
        <option value>Select</option>
        <option disabled value="1">Grade 1</option>
        <option disabled value="2">Grade 2</option>
        <option value="3" <?php if (isset($_POST["ifgrade"]) && $_POST["ifgrade"] == '3') {echo 'selected';} ?>>Grade 3</option>
        <option value="4" <?php if (isset($_POST["ifgrade"]) && $_POST["ifgrade"] == '4') {echo 'selected';} ?>>Grade 4</option>
        <option value="5" <?php if (isset($_POST["ifgrade"]) && $_POST["ifgrade"] == '5') {echo 'selected';} ?>>Grade 5</option>
    </select>
    </div>

    <div class="form-group text-center">
    <label for="comments">Other Comments</label>
    <textarea class="form-control" id="comments" name="comments"><?php if (isset($_POST["comments"])) {echo $_POST["comments"];} ?></textarea>
    </div>

    <div class="form-group text-center">
    <label for="pass">Password</label>
    <input required class="form-control" type="password" minlength="8" id="pass" name="pass">
    <small class="form-text text-muted">Must be at least 8 characters long</small>
    </div>

    <div class="form-group text-center">
    <label for="confpass">Password Again</label>
    <input required class="form-control" type="password" id="confpass" name="confpass">
    </div>

    <div class="row">
    <div class="col text-center">
    <input type="submit" style="background-color: #E4181E; color: white;" class="btn ml-auto mr-auto display-block" value="Apply">
    </div>
    </div>
</form>
</div>

<?php require './includes/footer.php'; ?>