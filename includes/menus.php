<?php

$GLOBALS['admin-menu']["Operations Management"] = array(
    "Manage Ranks" => [
        "link" => "/admin/operations.php?section=ranks",
        "icon" => "fa-medal",
        "permission" => "opsmanage",
        "needsGold" => false
    ],
    "Manage Fleet" => [
        "link" => "/admin/operations.php?section=fleet",
        "icon" => "fa-plane",
        "permission" => "opsmanage",
        "needsGold" => false
    ],
    "Manage Routes" => [
        "link" => "/admin/operations.php?section=routes",
        "icon" => "fa-plane-departure",
        "permission" => "opsmanage",
        "needsGold" => false
    ],
    "Manage Codeshares" => [
        "link" => "/admin/codeshares.php",
        "icon" => "fa-handshake",
        "permission" => "opsmanage",
        "needsGold" => false
    ],
    "Manage Events" => [
        "link" => "/admin/events.php",
        "icon" => "fa-plane-arrival",
        "permission" => "opsmanage",
        "needsGold" => true
    ]
);

$GLOBALS['admin-menu']["User Management"] = array(
    "Manage Users" => [
        "link" => "/admin/users.php",
        "icon" => "fa-user",
        "permission" => "usermanage",
        "needsGold" => false
    ],
    "Manage Staff" => [
        "link" => "/admin/staff.php",
        "icon" => "fa-user-shield",
        "permission" => "staffmanage",
        "needsGold" => false
    ],
    "Recruitment" => [
        "link" => "/admin/recruitment.php",
        "icon" => "fa-id-card",
        "permission" => "recruitment",
        "needsGold" => false
    ]
);

$GLOBALS['admin-menu']["PIREP Management"] = array(
    "Pending PIREPs" => [
        "link" => "/admin/pireps.php",
        "icon" => "fa-folder-open",
        "permission" => "pirepmanage",
        "needsGold" => false
    ],
    "Manage Multipliers" => [
        "link" => "/admin/multipliers.php",
        "icon" => "fa-calculator",
        "permission" => "pirepmanage",
        "needsGold" => false
    ],
    "VA Statistics" => [
        "link" => "/admin/stats.php",
        "icon" => "fa-chart-pie",
        "permission" => "statsviewing",
        "needsGold" => false
    ]
);

$GLOBALS['admin-menu']["Site Management"] = array(
    "Site Settings" => [
        "link" => "/admin/site.php",
        "icon" => "fa-cog",
        "permission" => "opsmanage",
        "needsGold" => false
    ],
    "Manage News" => [
        "link" => "/admin/news.php",
        "icon" => "fa-newspaper",
        "permission" => "newsmanage",
        "needsGold" => false
    ]
);

$GLOBALS['admin-menu']["Plugins"] = array(
    "Manage Plugins" => [
        "link" => "/admin/plugins.php",
        "icon" => "fa-plug",
        "permission" => "admin",
        "needsGold" => false
    ],
);

$GLOBALS['pilot-menu'] = array(
    "Pilot Home" => [
        "link" => "/home.php",
        "icon" => "fa-home",
        "needsGold" => false
    ],
    "File PIREP" => [
        "link" => "/pireps.php?page=new",
        "icon" => "fa-plane",
        "needsGold" => false
    ],
    "My PIREPs" => [
        "link" => "/pireps.php?page=recents",
        "icon" => "fa-folder",
        "needsGold" => false
    ],
    "Route Database" => [
        "link" => "/routes.php",
        "icon" => "fa-database",
        "needsGold" => false
    ],
    "Live Map" => [
        "link" => "/map.php",
        "icon" => "fa-map",
        "needsGold" => false
    ],
    "Events" => [
        "link" => "/events.php",
        "icon" => "fa-calendar",
        "needsGold" => true
    ],
    "ACARS" => [
        "link" => "/acars.php",
        "icon" => "fa-sync",
        "needsGold" => true
    ],
);