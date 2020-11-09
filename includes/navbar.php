<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>

<a class="navbar-brand" href="/">
    <?php if (empty(Config::get("VA_LOGO_URL"))): ?>
        <?= Config::get('va/name') ?>
    <?php else: ?>
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
            if (($data['loginOnly'] && $user->isLoggedIn()) || (!$data['loginOnly'] && !$user->isLoggedIn())) {
                $mobileHidden = $data['mobileHidden'] ? ' mobile-hidden' : '';
                echo '<li class="nav-item'.$mobileHidden.'">';
                echo '<a class="nav-link" href="'.$data['link'].'" style="color: '.$textcol.'!important;"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$name.'</a>';
                echo '</li>';
            }
        }

        if ($user->isLoggedIn()):
            $IS_GOLD = VANet::isGold();
            foreach ($GLOBALS['pilot-menu'] as $name => $data) {
                if ($IS_GOLD || $data["needsGold"] == false) {
                    echo '<li class="nav-item desktop-hidden">';
                    echo '<a href="'.$data['link'].'" class="panel-link" style="color: '.$textcol.' !important;"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$name.'</a>';
                    echo '</li>';
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
                $i = 0;
                foreach ($localmenu as $category => $items) {
                    
                    echo '<li class="nav-item desktop-hidden">';
                    echo '<a href="#" data-toggle="collapse" data-target="#collapse'.$i.'" class="panel-link" style="color: '.$textcol.'!important;"><i class="fa fa-caret-down"></i>&nbsp;'.$category.'</a>';
                    echo '<div id="collapse'.$i.'" class="collapse '.strtolower(str_replace(" ", "-", $category)).'">';
                    
                    foreach ($items as $label => $data) {
                        if ($user->hasPermission($data["permission"])) {
                            if (($IS_GOLD && $data["needsGold"]) || !$data["needsGold"]) {
                                echo '<a href="'.$data["link"].'" class="panel-link" style="color: '.$textcol.'!important;"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$label.'</a>';
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
    <ul class="navbar-nav ml-auto">
        <li class="nav-item mobile-hidden">
            <a class="nav-link" href="#" id="myNotifications-btn" data-toggle="dropdown" data-target="myNotifications" aria-haspopup="true" aria-expanded="false" style="color: <?= $textcol ?>!important;"><i class="fa fa-bell"></i></a>
            <div class="dropdown-menu dropdown-menu-right m-2" id="myNotifications">
                <span class="dropdown-item">
                    <i class="fa fa-check"></i>&nbsp;&nbsp;<b>PIREP Accepted</b><br />
                    <small><small>Your PIREP from KLAX to KSFO was Accepted by Example</small></small>
                </span>
                <div class="dropdown-divider"></div>
                <span class="dropdown-item">
                    <i class="fa fa-newspaper"></i>&nbsp;&nbsp;<b>News Added</b><br />
                    <small><small>A News Item titled Hello World was added by Example</small></small>
                </span>
                <div class="dropdown-divider"></div>
                <span class="dropdown-item">
                    <i class="fa fa-thumbs-up"></i>&nbsp;&nbsp;<b>Promotion</b><br />
                    <small><small>You have been promoted to Captain. Congratulations!</small></small>
                </span>
            </div>
        </li>
    </ul>
</div>