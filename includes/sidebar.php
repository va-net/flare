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
            echo '<a href="events.php" id="eventslink" class="panel-link"><i class="fa fa-calendar"></i>&nbsp;Events</a><br />';
            echo '<a href="acars.php" id="acarslink" class="panel-link"><i class="fa fa-sync"></i>&nbsp;ACARS</a><br />';
        }
    ?>
    <?php
    $permissions = Permissions::getAll();

    if ($user->hasPermission('admin')) {
        $localmenu = array();
        foreach ($GLOBALS['admin-menu'] as $name => $data) {
            $hasAnyPerms = false;
            foreach ($data as $key => $item) {
                if ($user->hasPermission($item['permission'])) {
                    $hasAnyPerms = true;
                }
            }

            if ($hasAnyPerms) {
                $localmenu[$name] = $data;
            }
        }

        echo '<br />';
        echo '<h3>Administration</h3>';
        echo '<hr class="mt-0 divider">';
        $i = 0;
        $gold = VANet::isGold();
        foreach ($localmenu as $category => $items) {
            
            echo '<a href="#" data-toggle="collapse" data-target="#collapse'.$i.'" class="panel-link"><i class="fa fa-caret-down"></i>&nbsp;'.$category.'</a><br />';
            echo '<div id="collapse'.$i.'" class="collapse '.strtolower(str_replace(" ", "-", $category)).'">';
            
            $j = 0;
            foreach ($items as $label => $data) {
                if ($user->hasPermission($data["permission"])) {
                    if (($gold && $data["needsGold"]) || !$data["needsGold"]) {
                        if ($j == 0) {
                            echo '&nbsp;&nbsp;<a href="'.$data["link"].'" class="panel-link"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$label.'</a>';
                        } else {
                            echo '<br />&nbsp;&nbsp;<a href="'.$data["link"].'" class="panel-link"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$label.'</a>';
                        }
                        $j++;
                    }
                }
            }
            echo '</div>';
            $i++;
        }
    }
    ?>
    <br />
    <a href="logout.php" class="panel-link"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
</div>