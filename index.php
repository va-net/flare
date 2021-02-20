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

// Pilot Pages
Router::add('(/login)?', [new AuthController, 'get']);
Router::add('(/login)?', [new AuthController, 'post'], 'post');
Router::add('/apply', [new AuthController, 'apply_get']);
Router::add('/apply', [new AuthController, 'apply_post'], 'post');
Router::add('/logout', [new AuthController, 'logout']);
Router::add('/home', [new HomeController, 'get']);
Router::add('/home', [new HomeController, 'post'], 'post');
Router::add('/pireps', [new PirepsController, 'get_all']);
Router::add('/pireps/new', [new PirepsController, 'get_new']);
Router::add('/pireps/new', [new PirepsController, 'post_new'], 'post');
Router::add('/pireps/setup', [new PirepsController, 'get_setup']);
Router::add('/pireps/setup', [new PirepsController, 'post_setup'], 'post');
Router::add('/pireps/acars', [new PirepsController, 'acars']);
Router::add('/routes', [new RoutesController, 'get']);
Router::add('/routes/search', [new RoutesController, 'search']);
Router::add('/routes/([0-9]+)', [new RoutesController, 'view']);
Router::add('/map', [new MapController, 'get']);
Router::add('/events', [new EventsController, 'get']);
Router::add('/events/([0-9a-zA-z]{8}-[0-9a-zA-z]{4}-[0-9a-zA-z]{4}-[0-9a-zA-z]{4}-[0-9a-zA-z]{12})', [new EventsController, 'view']);

// Admin Pages
Router::add('/admin', [new AdminController, 'dashboard']);
Router::add('/admin/stats', [new AdminController, 'stats']);
Router::add('/admin/news', [new NewsController, 'get']);
Router::add('/admin/news', [new NewsController, 'post'], 'post');
Router::add('/admin/settings', [new AdminController, 'settings']);
Router::add('/admin/settings', [new AdminController, 'settings_post'], 'post');
Router::add('/admin/operations/ranks', [new OperationsController, 'ranks_get']);
Router::add('/admin/operations/ranks', [new OperationsController, 'ranks_post'], 'post');
Router::add('/admin/operations/fleet', [new OperationsController, 'fleet_get']);
Router::add('/admin/operations/fleet', [new OperationsController, 'fleet_post'], 'post');
Router::add('/admin/operations/routes', [new OperationsController, 'routes_get']);
Router::add('/admin/operations/routes', [new OperationsController, 'routes_post'], 'post');
Router::add('/admin/operations/routes/import', [new OperationsController, 'import_get']);
Router::add('/admin/operations/routes/import', [new OperationsController, 'import_post'], 'post');
Router::add('/admin/operations/codeshares', [new CodesharesController, 'get']);
Router::add('/admin/operations/codeshares', [new CodesharesController, 'post'], 'post');
Router::add('/admin/operations/events', [new AdminEventsController, 'get']);
Router::add('/admin/operations/events', [new AdminEventsController, 'post'], 'post');
Router::add('/admin/users', [new UsersController, 'get']);
Router::add('/admin/users', [new UsersController, 'post'], 'post');
Router::add('/admin/users/pending', [new UsersController, 'get_pending']);
Router::add('/admin/users/pending', [new UsersController, 'post_pending'], 'post');
Router::add('/admin/users/staff', [new UsersController, 'get_staff']);
Router::add('/admin/users/staff', [new UsersController, 'post_staff'], 'post');
Router::add('/admin/pireps', [new AdminPirepsController, 'get']);
Router::add('/admin/pireps', [new AdminPirepsController, 'post'], 'post');
Router::add('/admin/pireps/multipliers', [new AdminPirepsController, 'get_multis']);
Router::add('/admin/pireps/multipliers', [new AdminPirepsController, 'post_multis'], 'post');
Router::add('/admin/plugins', [new stdClass, 'get']);
Router::add('/admin/plugins/installed', [new stdClass, 'get']);

$initfile = __DIR__ . "/themes/{$theme}/_init.php";
if (file_exists($initfile)) {
    include $initfile;
}

Router::run();
