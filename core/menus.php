<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
$GLOBALS['admin-menu']["Operations Management"] = array(
    "Manage Ranks" => [
        "link" => "/admin/operations/ranks",
        "icon" => "fa-medal",
        "permission" => "opsmanage",
        "needsGold" => false,
        "vanetFeature" => null,
        "badgeid" => null,
    ],
    "Manage Fleet" => [
        "link" => "/admin/operations/fleet",
        "icon" => "fa-plane",
        "permission" => "opsmanage",
        "needsGold" => false,
        "vanetFeature" => null,
        "badgeid" => null,
    ],
    "Manage Routes" => [
        "link" => "/admin/operations/routes",
        "icon" => "fa-plane-departure",
        "permission" => "opsmanage",
        "needsGold" => false,
        "vanetFeature" => null,
        "badgeid" => null,
    ],
    "Manage Codeshares" => [
        "link" => "/admin/operations/codeshares",
        "icon" => "fa-handshake",
        "permission" => "opsmanage",
        "needsGold" => false,
        "vanetFeature" => null,
        "badgeid" => "codeshares",
    ],
    "Manage Events" => [
        "link" => "/admin/operations/events",
        "icon" => "fa-plane-arrival",
        "permission" => "opsmanage",
        "needsGold" => true,
        "vanetFeature" => "events",
        "badgeid" => null,
    ]
);

$GLOBALS['admin-menu']["User Management"] = array(
    "Manage Users" => [
        "link" => "/admin/users",
        "icon" => "fa-user",
        "permission" => "usermanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Manage Staff" => [
        "link" => "/admin/users/staff",
        "icon" => "fa-user-shield",
        "permission" => "staffmanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Recruitment" => [
        "link" => "/admin/users/pending",
        "icon" => "fa-id-card",
        "permission" => "recruitment",
        "needsGold" => false,
        "badgeid" => "recruitment",
    ]
);

$GLOBALS['admin-menu']["PIREP Management"] = array(
    "Pending PIREPs" => [
        "link" => "/admin/pireps",
        "icon" => "fa-folder-open",
        "permission" => "pirepmanage",
        "needsGold" => false,
        "badgeid" => "pireps",
    ],
    "Manage Multipliers" => [
        "link" => "/admin/pireps/multipliers",
        "icon" => "fa-calculator",
        "permission" => "pirepmanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "VA Statistics" => [
        "link" => "/admin/stats",
        "icon" => "fa-chart-pie",
        "permission" => "statsviewing",
        "needsGold" => false,
        "badgeid" => null,
    ]
);

$GLOBALS['admin-menu']["Site Management"] = array(
    "Site Dashboard" => [
        "link" => "/admin",
        "icon" => "fa-tachometer-alt",
        "permission" => "admin",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Manage News" => [
        "link" => "/admin/news",
        "icon" => "fa-newspaper",
        "permission" => "newsmanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Site Settings" => [
        "link" => "/admin/settings",
        "icon" => "fa-cog",
        "permission" => "site",
        "needsGold" => false,
        "badgeid" => "settings",
    ],
);

// $GLOBALS['admin-menu']["Plugins"] = array(
//     "Manage Plugins" => [
//         "link" => "/admin/plugins",
//         "icon" => "fa-plug",
//         "permission" => "site",
//         "needsGold" => false,
//         "badgeid" => null,
//     ],
// );

$GLOBALS['pilot-menu'] = array(
    "Pilot Home" => [
        "link" => "/home",
        "icon" => "fa-home",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "File PIREP" => [
        "link" => "/pireps/new",
        "icon" => "fa-plane",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "My PIREPs" => [
        "link" => "/pireps",
        "icon" => "fa-folder",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Route Database" => [
        "link" => "/routes",
        "icon" => "fa-database",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Live Map" => [
        "link" => "/map",
        "icon" => "fa-map",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Events" => [
        "link" => "/events",
        "icon" => "fa-calendar",
        "needsGold" => true,
        "vanetFeature" => "events",
        "badgeid" => null,
    ],
    "ACARS" => [
        "link" => "/pireps/acars",
        "icon" => "fa-sync",
        "needsGold" => true,
        "vanetFeature" => "acars",
        "badgeid" => null,
    ],
);

$GLOBALS['top-menu'] = array(
    "Apply" => [
        "link" => "/apply",
        "icon" => "fa-id-card",
        "loginOnly" => false,
        "mobileHidden" => false,
    ],
    "Log In" => [
        "link" => "/",
        "icon" => "fa-sign-in-alt",
        "loginOnly" => false,
        "mobileHidden" => false,
    ],
    "Pilot Panel" => [
        "link" => "/home",
        "icon" => "fa-user",
        "loginOnly" => true,
        "mobileHidden" => true,
    ],
    "Log Out" => [
        "link" => "/logout",
        "icon" => "fa-sign-out-alt",
        "loginOnly" => true,
        "mobileHidden" => true,
    ]
);
