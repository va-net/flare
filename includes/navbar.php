<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>
<a class="navbar-brand" href="#"><span class="mobile-hidden"><?= Config::get('va/name') ?></span><span class="desktop-hidden">VGVA Crew</span></a>

<!-- navbar toggler -->
<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
    <span class="navbar-toggler-icon"></span>
</button>

<!-- navbar links -->
<div class="collapse navbar-collapse" id="collapsibleNavbar">
    <ul class="navbar-nav">
        <?php if (!$user->isLoggedIn()): ?>
        <li class="nav-item">
            <a class="nav-link" href="https://ifvirginvirtual.vip">Main Site</a>
        </li>
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
        <?php if (Session::get('darkmode') != true): ?>
        <li class="nav-item mobile-hidden">
            <a class="nav-link toggledark"><i class="fa fa-cloud-moon"></i>&nbsp;Dark Mode</a>
        </li>
        <?php else: ?>
        <li class="nav-item mobile-hidden">
            <a class="nav-link toggledark"><i class="fa fa-cloud-sun"></i>&nbsp;Light Mode</a>
        </li>
        <?php endif; ?>
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
        $permissions = Permissions::getAll();
        if ($user->hasPermission('admin')) {
            foreach ($permissions as $permission => $data) {
                if ($user->hasPermission($permission)) {
                    echo '<li class="nav-item desktop-hidden">';
                    if ($permission == 'opsmanage') {
                        echo '
                        <a href="#" data-toggle="collapse" data-target="#demo" class="panel-link"><i class="fa fa-caret-down"></i>&nbsp;Operations Management</a>
                        <div id="demo" class="collapse">
                        &nbsp;<i class="fa fa-plane"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=fleet" class="panel-link">Manage Fleet</a>
                        &nbsp;<i class="fa fa-plane-departure"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=routes" class="panel-link">Manage Routes</a>
                        &nbsp;<i class="fa fa-globe"></i>&nbsp;<a href="./admin.php?page=opsmanage&section=site" class="panel-link">Manage Site</a>
                        </div>
                        ';
                    } else {
                        echo '<a href="admin.php?page='.$permission.'" id="userslink" class="panel-link"><i class="fa '.$data['icon'].'"></i>&nbsp;'.$data['name'].'</a>';
                    }
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
        <?php endif; ?>
    </ul>
</div>