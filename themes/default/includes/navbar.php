<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>

<a class="navbar-brand" href="/">
    <?php if (empty(Config::get("VA_LOGO_URL"))) : ?>
        <?= Page::$pageData->va_name ?>
    <?php else : ?>
        <img src="<?= Config::get("VA_LOGO_URL") ?>" height="35" />
    <?php endif; ?>
</a>

<!-- navbar toggler -->
<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
    <span class="navbar-toggler-icon"></span>
</button>
<?php $textcol = Config::get('TEXT_COLOUR'); ?>
<!-- navbar links -->
<div class="collapse navbar-collapse" id="collapsibleNavbar">
    <ul class="navbar-nav">
        <?php
        foreach ($GLOBALS['top-menu'] as $name => $data) {
            if (isset($data["vanetFeature"]) && $PROFILE['activeFeatures'][$data['vanetFeature']] === FALSE) {
                continue;
            }

            if (($data['loginOnly'] && Page::$pageData->user->isLoggedIn()) || (!$data['loginOnly'] && !Page::$pageData->user->isLoggedIn())) {
                $mobileHidden = $data['mobileHidden'] ? ' mobile-hidden' : '';
                echo '<li class="nav-item' . $mobileHidden . '">';
                echo '<a class="nav-link" href="' . $data['link'] . '" style="color: ' . $textcol . '!important;"><i class="fa ' . $data['icon'] . '"></i>&nbsp;' . $name . '</a>';
                echo '</li>';
            }
        }

        if (Page::$pageData->user->isLoggedIn()) :
            $PROFILE = VANet::myInfo();
            $IS_GOLD = $PROFILE['isGoldPlan'];
            foreach ($GLOBALS['pilot-menu'] as $name => $data) {
                if (isset($data["vanetFeature"]) && $PROFILE['activeFeatures'][$data['vanetFeature']] === FALSE) {
                    continue;
                }

                if ($IS_GOLD || $data["needsGold"] == false) {
                    echo '<li class="nav-item desktop-hidden">';
                    echo '<a href="' . $data['link'] . '" class="panel-link" style="color: ' . $textcol . ' !important;"><i class="fa ' . $data['icon'] . '"></i>&nbsp;' . $name . '</a>';
                    echo '</li>';
                }
            }
            if (Page::$pageData->user->hasPermission('admin')) {
                $localmenu = array();
                foreach ($GLOBALS['admin-menu'] as $name => $data) {
                    $hasAnyPerms = false;
                    foreach ($data as $key => $item) {
                        if (Page::$pageData->user->hasPermission($item['permission'])) {
                            $hasAnyPerms = true;
                        }
                    }

                    if ($hasAnyPerms) {
                        $localmenu[$name] = $data;
                    }
                }
                $i = 0;
                foreach ($localmenu as $category => $items) {

                    echo '<li class="nav-item desktop-hidden">';
                    echo '<a href="#" data-toggle="collapse" data-target="#collapse' . $i . '" class="panel-link" style="color: ' . $textcol . '!important;"><i class="fa fa-caret-down"></i>&nbsp;' . $category . '</a>';
                    echo '<div id="collapse' . $i . '" class="collapse ' . strtolower(str_replace(" ", "-", $category)) . '">';

                    foreach ($items as $label => $data) {
                        if (isset($data["vanetFeature"]) && $PROFILE['activeFeatures'][$data['vanetFeature']] === FALSE) {
                            continue;
                        }

                        if (Page::$pageData->user->hasPermission($data["permission"])) {
                            if (($IS_GOLD && $data["needsGold"]) || !$data["needsGold"]) {
                                echo '<a href="' . $data["link"] . '" class="panel-link" style="color: ' . $textcol . '!important;"><i class="fa ' . $data['icon'] . '"></i>&nbsp;' . $label . '</a>';
                            }
                        }
                    }
                    echo '</div>';
                    echo '</li>';
                    $i++;
                }
            }
        ?>
            <li class="nav-item desktop-hidden">
                <a href="/logout.php" class="panel-link" style="color: <?= $textcol ?>!important;"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
            </li>
        <?php endif; ?>
    </ul>
    <?php if (Page::$pageData->user->isLoggedIn()) : ?>
        <ul class="ml-auto navbar-nav">
            <li class="nav-item mobile-hidden">
                <a class="nav-link" href="#" id="myNotifications-btn" data-toggle="dropdown" data-target="myNotifications" aria-haspopup="true" aria-expanded="false" style="color: <?= $textcol ?>!important;"><i class="fa fa-bell"></i></a>
                <div class="m-2 shadow-lg dropdown-menu dropdown-menu-right" id="myNotifications">
                    <?php
                    $notifications = array_map(function ($n) {
                        return '<div class="dropdown-item">
                            <small class="p-0 m-0 d-block"><small class="moment">' . $n->formattedDate . '</small></small>
                            <span class="p-0 m-0 d-block"><i class="fa ' . $n->icon . '"></i>&nbsp;&nbsp;<b>' . escape($n->subject) . '</b></span>
                            <small class="p-0 m-0 d-block">' . escape($n->content) . '</small>
                        </div>';
                    }, Notifications::mine(Page::$pageData->user->data()->id));
                    if (count($notifications) != 0) {
                        echo implode('<div class="dropdown-divider"></div>', $notifications);
                    } else {
                        echo '<div class="dropdown-item">No Notifications Yet!</div>';
                    }
                    ?>
                </div>
            </li>
        </ul>
    <?php endif; ?>
</div>