<?php
require_once './core/init.php';

$user = new User();
?>

<!DOCTYPE html>
<html>
<head>
<title>Virgin Virtual Group Crew</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/custom.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/17.0.0/classic/ckeditor.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://cdn.jsdelivr.net/gh/cferdinandi/smooth-scroll@15/dist/smooth-scroll.polyfills.min.js"></script>
<script>
  $(document).ready(function() {
    $("nav .panel-link").addClass("nav-link");
    $(".toggledark").click(function() {
      $.post("api.php", {
        "command":"toggledarkmode",
        "text":false,
        "token":false
      }, function(data, status) {
        window.location.href='home.php';
      });
    });
  });
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body style="min-height: 100vh;">
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #E4181E;">
  <a class="navbar-brand" href="/"><img src="assets/logo_white.png" style="height: 30px; width: auto;" /><span class="mobile-hidden">Virgin Virtual Group Crew</span><span class="desktop-hidden">VGVA Crew</span></a>
  
  <!-- Toggler/collapsibe Button -->
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
    <span class="navbar-toggler-icon"></span>
  </button>

  <!-- Navbar links -->
  <div class="collapse navbar-collapse" id="collapsibleNavbar">
    <ul class="navbar-nav">
      <?php if (!$user->isLoggedIn()) { ?>
      <li class="nav-item">
        <a class="nav-link" href="https://ifvirginvirtual.vip">Main Site</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="apply.php">Apply</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/">Log In</a>
      </li>
      <?php } else {?>
      <li class="nav-item mobile-hidden">
        <a class="nav-link" href="pilotpanel.php"><i class="fa fa-user"></i>&nbsp;Pilot Panel</a>
      </li>
      <?php if (!Session::get('darkmode')) { ?>
      <li class="nav-item mobile-hidden">
        <a class="nav-link toggledark"><i class="fa fa-cloud-moon"></i>&nbsp;Dark Mode</a>
      </li>
      <?php } else { ?>
      <li class="nav-item mobile-hidden">
        <a class="nav-link toggledark"><i class="fa fa-cloud-sun"></i>&nbsp;Light Mode</a>
      </li>
      <?php } ?>
      <li class="nav-item mobile-hidden">
        <a class="nav-link" href="logout.php"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
      </li>
        <li class="nav-item desktop-hidden">
        <a href="#home" id="homelink" data-toggle="tab" class="panel-link"><i class="fa fa-home"></i>&nbsp;Pilot Home</a>
        </li>
        <li class="nav-item desktop-hidden">
        <a href="#filepirep" id="filepireplink" data-toggle="tab" class="panel-link"><i class="fa fa-plane"></i>&nbsp;File PIREP</a>
        </li>
        <li class="nav-item desktop-hidden">
        <a href="#mypireps" id="mypirepslink" data-toggle="tab" class="panel-link"><i class="fa fa-folder"></i>&nbsp;My PIREPs</a>
        </li>
        <li class="nav-item desktop-hidden">
        <a href="#routedb" id="routeslink" data-toggle="tab" class="panel-link"><i class="fa fa-database"></i>&nbsp;Route Database</a>
        </li>
        <li class="nav-item desktop-hidden">
        <a href="#featured" id="featuredlink" data-toggle="tab" class="panel-link"><i class="fa fa-map-marked-alt"></i>&nbsp;Featured Routes</a>
        </li>
        <li class="nav-item desktop-hidden">
        <a href="#events" id="eventslink" data-toggle="tab" class="panel-link"><i class="fa fa-calendar"></i>&nbsp;Events</a>
        </li>
        <li class="nav-item desktop-hidden">
        <a href="#acars" id="acarslink" data-toggle="tab" class="panel-link"><i class="fa fa-sync"></i>&nbsp;ACARS</a>
        </li>
        <?php 
        $permissions = Permission::getAll();
        if ($user->hasPermission('admin')) {
          foreach ($permissions as $permission => $data) {
            if ($user->hasPermission($permission)) {
                echo '<li class="nav-item desktop-hidden">';
                echo '<a href="admin.php#'.$permission.'" id="'.$permission.'link" data-toggle="tab" class="panel-link"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$data['name'].'</a>';
                echo '</li>';
            }
          }
        }
        ?>
        <li class="nav-item desktop-hidden">
          <a class="nav-link toggledark"><i class="fa fa-cloud-moon"></i>&nbsp;Dark Mode</a>
        </li>
        <li class="nav-item desktop-hidden">
          <a class="nav-link toggledark"><i class="fa fa-cloud-sun"></i>&nbsp;Light Mode</a>
        </li>
        <li class="nav-item desktop-hidden">
        <a href="logout.php" class="panel-link"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
        </li>
        <?php } ?>
    </ul>
  </div>
</nav> 
<div class="container-fluid">