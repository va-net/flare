<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

/*
This is the main Flare configuration file. Please do not change anything in this unless you know what you are
doing! If updating, please backup this file prior to doing so.
*/

session_start();

if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} elseif (file_exists(__DIR__ . '/config.new.php')) {
    require_once __DIR__ . '/config.new.php';
}

$classdirs = ['.', 'app', 'data', 'plugins', 'util', 'controllers', 'controllers/admin'];
spl_autoload_register(function ($class) {
    global $classdirs;
    foreach ($classdirs as $d) {
        $file = __DIR__ . '/../classes/' . $d . '/' . $class . '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
    }
});

if (Config::isReady() && strlen(Config::get('INSTANCE_ID')) < 1 && !file_exists(__DIR__ . '/../.development')) {
    Analytics::register();
} elseif (!Config::isReady()) {
    Redirect::to('/install/install.php');
}

$ACTIVE_THEME = Config::get('ACTIVE_THEME');
array_unshift($classdirs, "../themes/{$ACTIVE_THEME}/controllers");

require_once __DIR__ . '/../vendor/autoload.php';

// Listen to required events and import data files
Events::listen('*', 'Logger::logEvent');
Events::listen('*', 'Notifications::handleEvent');
require_once __DIR__ . '/listeners.php';
require_once __DIR__ . '/menus.php';
require_once __DIR__ . '/functions.php';

$slash = "/";
if (strpos(strtolower(php_uname('s')), "window") !== FALSE) {
    $slash = "\\";
}
$INSTALLED_PLUGINS = Json::decode(file_get_contents(__DIR__ . $slash . '..' . $slash . 'plugins.json'));
foreach ($INSTALLED_PLUGINS as $p) {
    $classname = $p["class"];
    $classname::init();
}

Events::listen('site/updated', 'Analytics::reportUpdate');
