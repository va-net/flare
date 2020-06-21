<?php 
    require 'header.php';
    if (isset($_SESSION["authed"]) && $_SESSION["authed"] == true) {
        echo '<script>window.location.href="pilotpanel.php";</script>';
        die();
    } 
?>

<h1 class="text-center pb-0 mb-0">Virgin Virtual Group</h1>
<h3 class="text-center py-0 my-0">Pilot Login<br /><br /></h3>

<div class="container w-50 justify-content-center">

<?php
    if (isset($_POST["action"]) && $_POST["action"] == 'authenticate') {
        $query = $conn->prepare("SELECT * FROM pilots WHERE email=? AND recruitstage > 0 AND recruitstage != 2;");
        $query->bind_param("s", $_POST["email"]);
        $ret = $query->execute();
        $data = $query->get_result();
        if ($data->num_rows != 0) {
            $user = $data->fetch_assoc();
        }
        if (isset($user) && $user !== FALSE && password_verify($_POST["pass"], $user["password"])) {
            $_SESSION["authed"] = true;
            $_SESSION["pilotinfo"] = $user;
            echo '<script>window.location.href="pilotpanel.php";</script>';
            die();
        } else {
            echo '<div class="alert text-center alert-danger">Invalid email or password or your application is yet to be processed.</div>';
        }
    } 
?>

<form method="post">
    <input hidden name="action" value="authenticate">
    <div class="form-group text-center">
    <label for="email">Email Address</label>
    <input class="form-control" type="email" id="email" name="email">
    </div>

    <div class="form-group text-center">
    <label for="pass">Password</label>
    <input class="form-control" type="password" id="pass" name="pass">
    </div>
    <div class="row">
    <div class="col text-center">
    <input type="submit" style="background-color: #E4181E; color: white;" class="btn ml-auto mr-auto display-block" value="Log In">
    </div>
    </div>
</form>
</div>

<?php require 'footer.php'; ?>