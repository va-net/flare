<?php

$GLOBALS['admin-menu']["Operations Management"] = array(
    "Manage Ranks" => [
        "link" => "admin.php?page=opsmanage&section=ranks",
        "icon" => "fa-medal",
        "permission" => "opsmanage",
        "needsGold" => false
    ],
    "Manage Fleet" => [
        "link" => "admin.php?page=opsmanage&section=fleet",
        "icon" => "fa-plane",
        "permission" => "opsmanage",
        "needsGold" => false
    ],
    "Manage Routes" => [
        "link" => "admin.php?page=opsmanage&section=routes",
        "icon" => "fa-plane-departure",
        "permission" => "opsmanage",
        "needsGold" => false
    ],
    "Manage Codeshares" => [
        "link" => "admin.php?page=codeshares",
        "icon" => "fa-handshake",
        "permission" => "opsmanage",
        "needsGold" => false
    ],
    "Manage Events" => [
        "link" => "admin.php?page=events",
        "icon" => "fa-plane-arrival",
        "permission" => "opsmanage",
        "needsGold" => true
    ]
);

$GLOBALS['admin-menu']["User Management"] = array(
    "Manage Users" => [
        "link" => "admin.php?page=usermanage",
        "icon" => "fa-user",
        "permission" => "usermanage",
        "needsGold" => false
    ],
    "Manage Staff" => [
        "link" => "admin.php?page=staffmanage",
        "icon" => "fa-user-shield",
        "permission" => "staffmanage",
        "needsGold" => false
    ],
    "Recruitment" => [
        "link" => "admin.php?page=recruitment",
        "icon" => "fa-id-card",
        "permission" => "recruitment",
        "needsGold" => false
    ]
);

$GLOBALS['admin-menu']["PIREP Management"] = array(
    "Pending PIREPs" => [
        "link" => "admin.php?page=pirepmanage",
        "icon" => "fa-folder-open",
        "permission" => "pirepmanage",
        "needsGold" => false
    ],
    "Manage Multipliers" => [
        "link" => "admin.php?page=multimanage",
        "icon" => "fa-calculator",
        "permission" => "pirepmanage",
        "needsGold" => false
    ],
    "VA Statistics" => [
        "link" => "admin.php?page=statsviewing",
        "icon" => "fa-chart-pie",
        "permission" => "statsviewing",
        "needsGold" => false
    ]
);

$GLOBALS['admin-menu']["Site Management"] = array(
    "Site Settings" => [
        "link" => "admin.php?page=site",
        "icon" => "fa-cog",
        "permission" => "opsmanage",
        "needsGold" => false
    ],
    "Manage News" => [
        "link" => "admin.php?page=newsmanage",
        "icon" => "fa-newspaper",
        "permission" => "newsmanage",
        "needsGold" => false
    ]
);