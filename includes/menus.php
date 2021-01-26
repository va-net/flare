<?php

$GLOBALS['admin-menu']["Operations Management"] = array(
    "Manage Ranks" => [
        "link" => "/admin/operations.php?section=ranks",
        "icon" => "fa-medal",
        "permission" => "opsmanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Manage Fleet" => [
        "link" => "/admin/operations.php?section=fleet",
        "icon" => "fa-plane",
        "permission" => "opsmanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Manage Routes" => [
        "link" => "/admin/operations.php?section=routes",
        "icon" => "fa-plane-departure",
        "permission" => "opsmanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Manage Codeshares" => [
        "link" => "/admin/codeshares.php",
        "icon" => "fa-handshake",
        "permission" => "opsmanage",
        "needsGold" => false,
        "badgeid" => "codeshares",
    ],
    "Manage Events" => [
        "link" => "/admin/events.php",
        "icon" => "fa-plane-arrival",
        "permission" => "opsmanage",
        "needsGold" => true,
        "badgeid" => null,
    ]
);

$GLOBALS['admin-menu']["User Management"] = array(
    "Manage Users" => [
        "link" => "/admin/users.php",
        "icon" => "fa-user",
        "permission" => "usermanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Manage Staff" => [
        "link" => "/admin/staff.php",
        "icon" => "fa-user-shield",
        "permission" => "staffmanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Recruitment" => [
        "link" => "/admin/recruitment.php",
        "icon" => "fa-id-card",
        "permission" => "recruitment",
        "needsGold" => false,
        "badgeid" => "recruitment",
    ]
);

$GLOBALS['admin-menu']["PIREP Management"] = array(
    "Manage PIREPs" => [
        "link" => "/admin/pireps.php",
        "icon" => "fa-folder-open",
        "permission" => "pirepmanage",
        "needsGold" => false,
        "badgeid" => "pireps",
    ],
    "Manage Multipliers" => [
        "link" => "/admin/multipliers.php",
        "icon" => "fa-calculator",
        "permission" => "pirepmanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "VA Statistics" => [
        "link" => "/admin/stats.php",
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
        "link" => "/admin/news.php",
        "icon" => "fa-newspaper",
        "permission" => "newsmanage",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Site Settings" => [
        "link" => "/admin/site.php",
        "icon" => "fa-cog",
        "permission" => "site",
        "needsGold" => false,
        "badgeid" => "settings",
    ],
);

$GLOBALS['admin-menu']["Plugins"] = array(
    "Manage Plugins" => [
        "link" => "/admin/plugins.php",
        "icon" => "fa-plug",
        "permission" => "site",
        "needsGold" => false,
        "badgeid" => null,
    ],
);

$GLOBALS['pilot-menu'] = array(
    "Pilot Home" => [
        "link" => "/home.php",
        "icon" => "fa-home",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "File PIREP" => [
        "link" => "/pireps.php?page=new",
        "icon" => "fa-plane",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "My PIREPs" => [
        "link" => "/pireps.php?page=recents",
        "icon" => "fa-folder",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Route Database" => [
        "link" => "/routes.php",
        "icon" => "fa-database",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Live Map" => [
        "link" => "/map.php",
        "icon" => "fa-map",
        "needsGold" => false,
        "badgeid" => null,
    ],
    "Events" => [
        "link" => "/events.php",
        "icon" => "fa-calendar",
        "needsGold" => true,
        "badgeid" => null,
    ],
    "ACARS" => [
        "link" => "/acars.php",
        "icon" => "fa-sync",
        "needsGold" => true,
        "badgeid" => null,
    ],
);

$GLOBALS['top-menu'] = array(
    "Apply" => [
        "link" => "/apply.php",
        "icon" => "fa-id-card",
        "loginOnly" => false,
        "mobileHidden" => false,
    ],
    "Log In" => [
        "link" => "/index.php",
        "icon" => "fa-sign-in-alt",
        "loginOnly" => false,
        "mobileHidden" => false,
    ],
    "Pilot Panel" => [
        "link" => "/home.php",
        "icon" => "fa-user",
        "loginOnly" => true,
        "mobileHidden" => true,
    ],
    "Log Out" => [
        "link" => "/logout.php",
        "icon" => "fa-sign-out-alt",
        "loginOnly" => true,
        "mobileHidden" => true,
    ]
);
