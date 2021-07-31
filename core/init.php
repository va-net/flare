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

require_once __DIR__ . '/../vendor/autoload.php';

// Listen to required events and import data files
Events::listen('*', 'Logger::logEvent');
Events::listen('*', 'Notifications::handleEvent');
Events::listen('site/updated', 'Analytics::reportUpdate');
require_once __DIR__ . '/functions.php';

if (!isset($IS_API) || !$IS_API) {
    if (Config::isReady() && strlen(Config::get('INSTANCE_ID')) < 1 && !file_exists(__DIR__ . '/../.development')) {
        Analytics::register();
    } elseif (!Config::isReady() && !isset($IS_INSTALLER)) {
        Redirect::to('/install/install.php');
    }

    require_once __DIR__ . '/menus.php';

    $ACTIVE_THEME = Config::get('ACTIVE_THEME');
    array_unshift($classdirs, "../themes/{$ACTIVE_THEME}/controllers");

    $INSTALLED_PLUGINS = Json::decode(file_get_contents(__DIR__ . '/../plugins.json'));
    foreach ($INSTALLED_PLUGINS as $p) {
        $classname = $p['className'];
        $classname::init();
    }
}
