<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/


require_once __DIR__ . '/core/init.php';

$theme = Config::get('ACTIVE_THEME');
if (empty($theme)) $theme = 'default';

$initfile = __DIR__ . "/themes/{$theme}/_init.php";
if (file_exists($initfile)) {
    include($initfile);
}

// Skeleton Routes
Router::pathNotFound(function () {
    http_response_code(404);
    echo '<h1>Not Found</h1>';
    die();
});
Router::methodNotAllowed(function () {
    http_response_code(405);
    echo '<h1>Method Not Allowed</h1>';
    die();
});

Router::add('(/login)?', [new LoginController, 'get']);
Router::add('(/login)?', [new LoginController, 'post'], 'post');
Router::add('/home', [new HomeController, 'get']);
Router::add('/home', [new HomeController, 'post'], 'post');

Router::run();
