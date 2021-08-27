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
    echo 'Not Found';
    die();
});
Router::methodNotAllowed(function () {
    http_response_code(405);
    echo 'Method Not Allowed';
    die();
});

// Pilot Pages
Router::add('(/login)?', [Dependencies::get(AuthController::class), 'get']);
Router::add('(/login)?', [Dependencies::get(AuthController::class), 'post'], 'post');
Router::add('/apply', [Dependencies::get(AuthController::class), 'apply_get']);
Router::add('/apply', [Dependencies::get(AuthController::class), 'apply_post'], 'post');
Router::add('/logout', [Dependencies::get(AuthController::class), 'logout']);
Router::add('/home', [Dependencies::get(HomeController::class), 'get']);
Router::add('/home', [Dependencies::get(HomeController::class), 'post'], 'post');
Router::add('/pireps', [Dependencies::get(PirepsController::class), 'get_all']);
Router::add('/pireps', [Dependencies::get(PirepsController::class), 'post_all'], 'post');
Router::add('/pireps/new', [Dependencies::get(PirepsController::class), 'get_new']);
Router::add('/pireps/new', [Dependencies::get(PirepsController::class), 'post_new'], 'post');
Router::add('/pireps/setup', [Dependencies::get(PirepsController::class), 'get_setup']);
Router::add('/pireps/setup', [Dependencies::get(PirepsController::class), 'post_setup'], 'post');
Router::add('/pireps/acars', [Dependencies::get(PirepsController::class), 'acars']);
Router::add('/pireps/acars', [Dependencies::get(PirepsController::class), 'acars_post'], 'post');
Router::add('/routes', [Dependencies::get(RoutesController::class), 'get']);
Router::add('/routes/search', [Dependencies::get(RoutesController::class), 'search']);
Router::add('/routes/([0-9]+)', [Dependencies::get(RoutesController::class), 'view']);
Router::add('/map', [Dependencies::get(MapController::class), 'get']);
Router::add('/events', [Dependencies::get(EventsController::class), 'get']);
Router::add('/events/([0-9a-zA-z]{8}-[0-9a-zA-z]{4}-[0-9a-zA-z]{4}-[0-9a-zA-z]{4}-[0-9a-zA-z]{12})', [Dependencies::get(EventsController::class), 'view']);

// Admin Pages
Router::add('/admin(/home|/stats)?', [Dependencies::get(AdminController::class), 'dashboard']);
Router::add('/admin/stats', [Dependencies::get(AdminController::class), 'stats']);
Router::add('/admin/news', [Dependencies::get(NewsController::class), 'get']);
Router::add('/admin/news', [Dependencies::get(NewsController::class), 'post'], 'post');
Router::add('/admin/settings', [Dependencies::get(AdminController::class), 'settings']);
Router::add('/admin/settings', [Dependencies::get(AdminController::class), 'settings_post'], 'post');
Router::add('/admin/operations/ranks', [Dependencies::get(OperationsController::class), 'ranks_get']);
Router::add('/admin/operations/ranks', [Dependencies::get(OperationsController::class), 'ranks_post'], 'post');
Router::add('/admin/operations/fleet', [Dependencies::get(OperationsController::class), 'fleet_get']);
Router::add('/admin/operations/fleet', [Dependencies::get(OperationsController::class), 'fleet_post'], 'post');
Router::add('/admin/operations/routes', [Dependencies::get(OperationsController::class), 'routes_get']);
Router::add('/admin/operations/routes', [Dependencies::get(OperationsController::class), 'routes_post'], 'post');
Router::add('/admin/operations/routes/import', [Dependencies::get(OperationsController::class), 'import_get']);
Router::add('/admin/operations/routes/import', [Dependencies::get(OperationsController::class), 'import_post'], 'post');
Router::add('/admin/operations/codeshares', [Dependencies::get(CodesharesController::class), 'get']);
Router::add('/admin/operations/codeshares', [Dependencies::get(CodesharesController::class), 'post'], 'post');
Router::add('/admin/operations/events', [Dependencies::get(AdminEventsController::class), 'get']);
Router::add('/admin/operations/events', [Dependencies::get(AdminEventsController::class), 'post'], 'post');
Router::add('/admin/users', [Dependencies::get(UsersController::class), 'get']);
Router::add('/admin/users', [Dependencies::get(UsersController::class), 'post'], 'post');
Router::add('/admin/users/pending', [Dependencies::get(UsersController::class), 'get_pending']);
Router::add('/admin/users/pending', [Dependencies::get(UsersController::class), 'post_pending'], 'post');
Router::add('/admin/users/staff', [Dependencies::get(UsersController::class), 'get_staff']);
Router::add('/admin/users/staff', [Dependencies::get(UsersController::class), 'post_staff'], 'post');
Router::add('/admin/users/awards', [Dependencies::get(UsersController::class), 'get_awards']);
Router::add('/admin/users/awards', [Dependencies::get(UsersController::class), 'post_awards'], 'post');
Router::add('/admin/users/lookup/(.+)', [Dependencies::get(UsersController::class), 'lookup']);
Router::add('/admin/pireps', [Dependencies::get(AdminPirepsController::class), 'get']);
Router::add('/admin/pireps', [Dependencies::get(AdminPirepsController::class), 'post'], 'post');
Router::add('/admin/pireps/multipliers', [Dependencies::get(AdminPirepsController::class), 'get_multis']);
Router::add('/admin/pireps/multipliers', [Dependencies::get(AdminPirepsController::class), 'post_multis'], 'post');
Router::add('/admin/plugins', [Dependencies::get(PluginsController::class), 'get']);
Router::add('/admin/plugins', [Dependencies::get(PluginsController::class), 'post'], 'post');

$initfile = __DIR__ . "/themes/{$theme}/_init.php";
if (file_exists($initfile)) {
    include $initfile;
}

Router::run();
