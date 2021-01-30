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
Router::add('/admin/news', [new stdClass, 'get']); // [ ]
Router::add('/admin/news/add', [new stdClass, 'get']); // [ ]
Router::add('/admin/news/([0-9]+)', [new stdClass, 'get']); // [ ]
Router::add('/admin/settings', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/ranks', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/ranks/add', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/ranks/([0-9]+)', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/fleet', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/fleet/add', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/fleet/([0-9]+)', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/routes', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/routes/add', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/routes/edit', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/codeshares', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/events', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/events/add', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/events/([0-9a-zA-z]{8}-[0-9a-zA-z]{4}-[0-9a-zA-z]{4}-[0-9a-zA-z]{4}-[0-9a-zA-z]{12})', [new stdClass, 'get']); // [ ]
Router::add('/admin/users', [new stdClass, 'get']); // [ ]
Router::add('/admin/([0-9]+)', [new stdClass, 'get']); // [ ]
Router::add('/admin/([0-9]+)/edit', [new stdClass, 'get']); // [ ]
Router::add('/admin/users/pending', [new stdClass, 'get']); // [ ]
Router::add('/admin/users/staff', [new stdClass, 'get']); // [ ]
Router::add('/admin/users/staff/([0-9]+)', [new stdClass, 'get']); // [ ]
Router::add('/admin/pireps', [new stdClass, 'get']); // [ ]
Router::add('/admin/pireps/([0-9]+)', [new stdClass, 'get']); // [ ]
Router::add('/admin/pireps/([0-9]+)/edit', [new stdClass, 'get']); // [ ]
Router::add('/admin/pireps/pending', [new stdClass, 'get']); // [ ]
Router::add('/admin/pireps/multipliers', [new stdClass, 'get']); // [ ]
Router::add('/admin/plugins', [new stdClass, 'get']); // [ ]
Router::add('/admin/plugins/installed', [new stdClass, 'get']); // [ ]

$initfile = __DIR__ . "/themes/{$theme}/_init.php";
if (file_exists($initfile)) {
    include $initfile;
}

Router::run();
