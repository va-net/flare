<div class="col-lg-3 p-3 bg-light text-left mobile-hidden border rounded shadow" id="desktopMenu" style="height: 100%;">
    <h3>Pilot Panel - <?= escape($user->data()->callsign) ?></h3>
    <hr class="mt-0 divider" />
    <?php
    foreach ($GLOBALS['pilot-menu'] as $name => $data) {
        if ($IS_GOLD || $data["needsGold"] == false) {
            echo '<a href="' . $data['link'] . '" class="panel-link"><i class="fa ' . $data['icon'] . '"></i>&nbsp;' . $name . '</a><br />';
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

            echo '<a href="#" data-toggle="collapse" data-target="#collapse' . $i . '" class="panel-link"><i class="fa fa-caret-down"></i>&nbsp;' . $category . '</a><br />';
            echo '<div id="collapse' . $i . '" class="collapse ' . strtolower(str_replace(" ", "-", $category)) . '">';

            $j = 0;
            foreach ($items as $label => $data) {
                if ($user->hasPermission($data["permission"])) {
                    if ($IS_GOLD || !$data["needsGold"]) {
                        $badge = !isset($data["badgeid"]) || $data["badgeid"] == null ? '' : $data["badgeid"];
                        if ($j == 0) {
                            echo '&nbsp;&nbsp;<a href="' . $data["link"] . '" class="panel-link" data-badge="' . $badge . '"><i class="fa ' . $data['icon'] . '"></i>&nbsp;' . $label . '</a>';
                        } else {
                            echo '<br />&nbsp;&nbsp;<a href="' . $data["link"] . '" class="panel-link" data-badge="' . $badge . '"><i class="fa ' . $data['icon'] . '"></i>&nbsp;' . $label . '</a>';
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
<script>
    $.get('/api.php/menu/badges', function(data) {
        var result = Object.entries(data.result);
        for (var i = 0; i < result.length; i++) {
            var id = result[i][0];
            var badge = result[i][1];
            if (typeof badge == 'boolean') {
                if (badge) {
                    badge = '<i class="fa fa-exclamation-circle"></i>';
                } else {
                    continue;
                }
            }
            if (badge == 0) continue;
            var e = $("[data-badge='" + id + "']");
            e.html(e.html() + '&nbsp;<span class="badge badge-danger"><span class="align-middle">' + badge + '</span></span>');
        }
    });
</script>