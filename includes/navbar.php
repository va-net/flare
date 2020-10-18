<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>
<a class="navbar-brand" href="index.php"><?= Config::get('va/name') ?></a>

<!-- navbar toggler -->
<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
    <span class="navbar-toggler-icon"></span>
</button>
<?php $textcol = Config::get('TEXT_COLOUR'); ?>
<!-- navbar links -->
<div class="collapse navbar-collapse" id="collapsibleNavbar">
    <ul class="navbar-nav">
        <?php if (!$user->isLoggedIn()): ?>
        <li class="nav-item">
            <a class="nav-link" href="/apply.php" style="color: <?= $textcol ?>!important;">Apply</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/index.php" style="color: <?= $textcol ?>!important;">Log In</a>
        </li>
        <?php else: ?>
        <li class="nav-item mobile-hidden">
            <a class="nav-link" href="/home.php" style="color: <?= $textcol ?>!important;"><i class="fa fa-user"></i>&nbsp;Pilot Panel</a>
        </li>
        <li class="nav-item mobile-hidden">
            <a class="nav-link" href="/logout.php" style="color: <?= $textcol ?>!important;"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
        </li>
        <?php
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
</div>