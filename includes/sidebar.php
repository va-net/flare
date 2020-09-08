<div class="col-lg-3 p-3 bg-light text-left mobile-hidden" id="desktopMenu" style="height: 100%;">
    <h3>Pilot Panel - <?= escape($user->data()->callsign) ?></h3>
    <hr class="mt-0 divider" />
    <a href="home.php" id="homelink" class="panel-link"><i class="fa fa-home"></i>&nbsp;Pilot Home</a><br />
    <a href="pireps.php?page=new" id="filepireplink" class="panel-link"><i class="fa fa-plane"></i>&nbsp;File PIREP</a><br />
    <a href="pireps.php?page=recents" id="mypirepslink" class="panel-link"><i class="fa fa-folder"></i>&nbsp;My PIREPs</a><br />
    <a href="routes.php" id="routeslink" class="panel-link"><i class="fa fa-database"></i>&nbsp;Route Database</a><br />
    <a href="map.php" id="maplink" class="panel-link"><i class="fa fa-map"></i>&nbsp;Live Map</a><br />
    <?php
        if (VANet::isGold()) {
            echo '<a href="acars.php" id="acarslink" class="panel-link"><i class="fa fa-sync"></i>&nbsp;ACARS</a><br />';
        }
    ?>
    <?php
    $permissions = Permissions::getAll();

    if ($user->hasPermission('admin')) {
        $userpages = [];
        $opspages = [];
        $miscpages = [];

        foreach ($permissions as $permission => $data) {
            if ($user->hasPermission($permission)) {
                if ($permission == "usermanage" || $permission == "staffmanage" || $permission == "recruitment") {
                    $userpages[$permission] = $data;
                } elseif ($permission == "opsmanage") {
                    $opspages["ranks"] = [
                        "icon" => "fa-medal",
                        "name" => "Manage Ranks",
                    ];
                    $opspages["fleet"] = [
                        "icon" => "fa-plane",
                        "name" => "Manage Fleet",
                    ];
                    $opspages["routes"] = [
                        "icon" => "fa-plane-departure",
                        "name" => "Manage Routes",
                    ];
                    $miscpages["site"] = [
                        "icon" => "fa-globe",
                        "name" => "Manage Site",
                    ];
                } elseif ($permission == "pirepmanage") {
                    $miscpages[$permission] = $data;
                    $miscpages["multimanage"] = [
                        "icon" => "fa-calculator",
                        "name" => "Multipliers",
                    ];
                } else {
                    $miscpages[$permission] = $data;
                }
            }
        }

        echo '<br />';
        echo '<h3>Administration</h3>';
        echo '<hr class="mt-0 divider">';
        if ($userpages != []) {
            echo '<a href="#" data-toggle="collapse" data-target="#usrCollapse" class="panel-link"><i class="fa fa-caret-down"></i>&nbsp;User Management</a><br />';
            echo '<div id="usrCollapse" class="collapse usrCollapse">';
            foreach ($userpages as $slug => $info) {
                echo '&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="admin.php?page='.$slug.'" class="panel-link">
                  <i class="fa '.$info["icon"].'"></i>&nbsp;'.$info["name"].'
                </a><br />';
            }
            echo '</div>';
        }
        if ($opspages != []) {
            echo '<a href="#" data-toggle="collapse" data-target="#opsCollapse" class="panel-link"><i class="fa fa-caret-down"></i>&nbsp;Operations Management</a><br />';
            echo '<div id="opsCollapse" class="collapse opsCollapse">';
            foreach ($opspages as $slug => $info) {
                echo '&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="admin.php?page=opsmanage&section='.$slug.'" class="panel-link">
                  <i class="fa '.$info["icon"].'"></i>&nbsp;'.$info["name"].'
                </a><br />';
            }
            echo '</div>';
        }
        if ($miscpages != []) {
            foreach ($miscpages as $slug => $info) {
                echo '<a href="admin.php?page='.$slug.'" id="userslink" class="panel-link"><i class="fa '.$info['icon'].'"></i>&nbsp;'.$info['name'].'</a><br />';
            }
        }
    }
    ?>
    <br />
    <a href="logout.php" class="panel-link"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
</div>