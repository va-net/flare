<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>
<a class="navbar-brand" href="#"><?= Config::get('va/name') ?></a>

<!-- navbar toggler -->
<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
    <span class="navbar-toggler-icon"></span>
</button>

<!-- navbar links -->
<div class="collapse navbar-collapse" id="collapsibleNavbar">
    <ul class="navbar-nav">
        <?php if (!$user->isLoggedIn()): ?>
        <li class="nav-item">
            <a class="nav-link" href="apply.php">Apply</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="index.php">Log In</a>
        </li>
        <?php else: ?>
        <li class="nav-item mobile-hidden">
            <a class="nav-link" href="home.php"><i class="fa fa-user"></i>&nbsp;Pilot Panel</a>
        </li>
        <li class="nav-item mobile-hidden">
            <a class="nav-link" href="logout.php"><i class="fa fa-sign-out-alt"></i>&nbsp;Log Out</a>
        </li>
        <li class="nav-item desktop-hidden">
            <a href="home.php" id="homelink" class="panel-link"><i class="fa fa-home"></i>&nbsp;Pilot Home</a>
        </li>
        <li class="nav-item desktop-hidden">
            <a href="pireps.php?page=new" id="filepireplink" class="panel-link"><i class="fa fa-plane"></i>&nbsp;File PIREP</a>
        </li>
        <li class="nav-item desktop-hidden">
            <a href="pireps.php?page=recents" id="mypirepslink" class="panel-link"><i class="fa fa-folder"></i>&nbsp;My PIREPs</a>
        </li>
        <li class="nav-item desktop-hidden">
            <a href="routes.php" id="routeslink" class="panel-link"><i class="fa fa-database"></i>&nbsp;Route Database</a>
        </li>
        <li class="nav-item desktop-hidden">
            <a href="map.php" id="maplink" class="panel-link"><i class="fa fa-map"></i>&nbsp;Live Map</a>
        </li>
        <?php 
        if (VANet::isGold()) {
            echo '<li class="nav-item desktop-hidden">
                <a href="acars.php" id="acarslink" class="panel-link"><i class="fa fa-sync"></i>&nbsp;ACARS</a>
            </li>';
        }
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
                    } else {
                        $miscpages[$permission] = $data;
                    }
                }
            }

            echo '<li class="nav-item desktop-hidden">';
            if ($userpages != []) {
                echo '<a href="#" data-toggle="collapse" data-target="#usrCollapse" class="panel-link"><i class="fa fa-caret-down"></i>&nbsp;User Management</a>';
                echo '<div id="usrCollapse" class="collapse usrCollapse">';
                foreach ($userpages as $slug => $info) {
                    echo '<a href="admin.php?page='.$slug.'" class="panel-link">
                    <i class="fa '.$info["icon"].'"></i>&nbsp;'.$info["name"].'
                    </a>';
                }
                echo '</div>';
            }
            if ($opspages != []) {
                echo '<a href="#" data-toggle="collapse" data-target="#opsCollapse" class="panel-link"><i class="fa fa-caret-down"></i>&nbsp;Operations Management</a>';
                echo '<div id="opsCollapse" class="collapse opsCollapse">';
                foreach ($opspages as $slug => $info) {
                    echo '<a href="admin.php?page=opsmanage&section='.$slug.'" class="panel-link">
                    <i class="fa '.$info["icon"].'"></i>&nbsp;'.$info["name"].'
                    </a>';
                }
                echo '</div>';
            }
            if ($miscpages != []) {
                foreach ($miscpages as $slug => $info) {
                    echo '<a href="admin.php?page='.$slug.'" id="userslink" class="panel-link"><i class="fa '.$info['icon'].'"></i>&nbsp;'.$info['name'].'</a>';
                }
            }
            echo '</li>';
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
        <?php endif; ?>
    </ul>
</div>