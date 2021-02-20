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

spl_autoload_register(function ($class) {
    $dirs = ['.', 'app', 'data', 'util', 'controllers', 'controllers/admin'];
    foreach ($dirs as $d) {
        $file = __DIR__ . '/../classes/' . $d . '/' . $class . '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
    }
});

if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

require_once __DIR__ . '/../vendor/autoload.php';

// Listen to required events and import data files
Events::listen('*', 'Logger::logEvent');
Events::listen('*', 'Notifications::handleEvent');
require_once __DIR__ . '/listeners.php';
require_once __DIR__ . '/menus.php';
require_once __DIR__ . '/functions.php';

// Index installed plugins
$slash = "/";
if (strpos(strtolower(php_uname('s')), "window") !== FALSE) {
    $slash = "\\";
}
$INSTALLED_PLUGINS = Json::decode(file_get_contents(__DIR__ . $slash . '..' . $slash . 'plugins.json'));
foreach ($INSTALLED_PLUGINS as $p) {
    $classname = $p["class"];
    $classname::init();
}

// Add Error Listeners
Events::listen('db/query-failed', 'Analytics::reportDbError');
Events::listen('site/updated', 'Analytics::reportUpdate');
set_error_handler('Analytics::reportError', E_ALL);
set_exception_handler('Analytics::reportException');
