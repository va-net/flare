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
Router::add('/pireps/([0-9]+)', [new stdClass, 'get']); // [ ]
Router::add('/pireps/new', [new PirepsController, 'get_new']);
Router::add('/pireps/new', [new PirepsController, 'post_new'], 'post');
Router::add('/pireps/setup', [new PirepsController, 'get_setup']);
Router::add('/pireps/setup', [new PirepsController, 'post_setup'], 'post');
Router::add('/routes', [new stdClass, 'get']); // [ ]
Router::add('/routes/([0-9]+)', [new stdClass, 'get']); // [ ]
Router::add('/map', [new stdClass, 'get']); // [ ]
Router::add('/events', [new stdClass, 'get']); // [ ] // [ ]
Router::add('/events/([0-9a-zA-z]{8}-[0-9a-zA-z]{4}-[0-9a-zA-z]{4}-[0-9a-zA-z]{4}-[0-9a-zA-z]{12})', [new stdClass, 'get']); // [ ]
Router::add('/pireps/acars', [new stdClass, 'get']); // [ ]

// Admin Pages
Router::add('/admin/operations/ranks', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/fleet', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/routes', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/codeshares', [new stdClass, 'get']); // [ ]
Router::add('/admin/operations/events', [new stdClass, 'get']); // [ ]
Router::add('/admin/users', [new stdClass, 'get']); // [ ]
Router::add('/admin/users/pending', [new stdClass, 'get']); // [ ]
Router::add('/admin/users/staff', [new stdClass, 'get']); // [ ]
Router::add('/admin/pireps', [new stdClass, 'get']); // [ ]
Router::add('/admin/pireps/pending', [new stdClass, 'get']); // [ ]
Router::add('/admin/pireps/multipliers', [new stdClass, 'get']); // [ ]
Router::add('/admin/stats', [new stdClass, 'get']); // [ ]
Router::add('/admin', [new stdClass, 'get']); // [ ]
Router::add('/admin/news', [new stdClass, 'get']); // [ ]
Router::add('/admin/settings', [new stdClass, 'get']); // [ ]
Router::add('/admin/plugins', [new stdClass, 'get']); // [ ]
Router::add('/admin/plugins/installed', [new stdClass, 'get']); // [ ]

Router::run();
