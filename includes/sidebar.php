<div class="col-lg-3 p-3 bg-light text-left mobile-hidden border rounded shadow" id="desktopMenu" style="height: 100%;">
    <h3>Pilot Panel - <?= escape($user->data()->callsign) ?></h3>
    <hr class="mt-0 divider" />
    <?php
    foreach ($GLOBALS['pilot-menu'] as $name => $data) {
        if ($IS_GOLD || $data["needsGold"] == false) {
            echo '<a href="'.$data['link'].'" class="panel-link"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$name.'</a><br />';
        }
    }
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
        foreach ($localmenu as $category => $items) {
            
            echo '<a href="#" data-toggle="collapse" data-target="#collapse'.$i.'" class="panel-link"><i class="fa fa-caret-down"></i>&nbsp;'.$category.'</a><br />';
            echo '<div id="collapse'.$i.'" class="collapse '.strtolower(str_replace(" ", "-", $category)).'">';
            
            $j = 0;
            foreach ($items as $label => $data) {
                if ($user->hasPermission($data["permission"])) {
                    if (($IS_GOLD && $data["needsGold"]) || !$data["needsGold"]) {
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
    <a href="/logout.php" class="panel-link"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
</div>