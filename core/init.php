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
    require_once __DIR__ . '/../classes/' . $class . '.php';
});

if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} elseif (file_exists(__DIR__ . '/config.new.php')) {
    require_once __DIR__ . '/config.new.php';
}

if (Config::isReady() && strlen(Config::get('INSTANCE_ID')) < 1) {
    Analytics::register();
}

require_once __DIR__ . '/../vendor/autoload.php';

// Listen to required events and import data files
Events::listen('*', 'Logger::logEvent');
Events::listen('*', 'Notifications::handleEvent');
require_once __DIR__ . '/listeners.php';
require_once __DIR__ . '/../functions/escape.php';
require_once __DIR__ . '/../functions/daterange.php';
require_once __DIR__ . '/../includes/menus.php';

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

Events::listen('site/updated', 'Analytics::reportUpdate');
