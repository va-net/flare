<div class="col-lg-3 p-3 bg-light text-left mobile-hidden" id="desktopMenu" style="height: 100%;">
    <h3>Pilot Panel - <?= escape($user->data()->callsign) ?></h3>
    <hr class="mt-0 divider" />
    <a href="home.php" id="homelink" class="panel-link"><i class="fa fa-home"></i>&nbsp;Pilot Home</a><br>
    <a href="pireps.php?page=new" id="filepireplink" class="panel-link"><i class="fa fa-plane"></i>&nbsp;File PIREP</a><br>
    <a href="pireps.php?page=recents" id="mypirepslink" class="panel-link"><i class="fa fa-folder"></i>&nbsp;My PIREPs</a><br>
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
                if ($permission == 'opsmanage') {
                    echo '
                    <a href="#" data-toggle="collapse" data-target="#demo" class="panel-link"><i class="fa fa-caret-down"></i>&nbsp;Operations Management</a><br>
                    <div id="demo" class="collapse">
                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-medal"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=ranks" class="panel-link">Manage Ranks</a><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plane"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=fleet" class="panel-link">Manage Fleet</a><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plane-departure"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=routes" class="panel-link">Manage Routes</a><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-globe"></i>&nbsp;<a href="./admin.php?page=site" class="panel-link">Manage Site</a><br>
                    </div>
                    ';
                } else {
                    echo '<a href="admin.php?page='.$permission.'" id="userslink" class="panel-link"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$data['name'].'</a><br>';
                }
            }
        }
    }
    ?>
    <br>
    <a href="logout.php" class="panel-link"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
</div>