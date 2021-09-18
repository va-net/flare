<?php
$adminmenu = [];
if (Page::$pageData->user->hasPermission('admin')) {
    foreach ($GLOBALS['admin-menu'] as $name => $data) {
        $subitems = [];
        foreach ($data as $key => $item) {
            if (
                isset(Page::$pageData->va_profile['activeFeatures'][$item['vanetFeature']])
                && Page::$pageData->va_profile['activeFeatures'][$item['vanetFeature']] === FALSE
            ) {
                continue;
            }

            if (Page::$pageData->user->hasPermission($item['permission']) && (Page::$pageData->is_gold || !$item['needsGold'])) {
                $subitems[$key] = $item;
            }
        }

        if (count($subitems) > 0) {
            $adminmenu[$name] = $subitems;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/assets/tailwind.style.css.php" />
    <link rel="stylesheet" href="/assets/tailwind.index.css" />
    <link rel="stylesheet" href="/assets/custom.css" />
    <link rel="stylesheet" href="/assets/fontawesome.min.css" />
    <title><?= Page::getTitle() ?></title>
    <script src="/assets/js/tailwind.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js"></script>
</head>

<body>
    <div class="flex flex-col w-full min-h-screen" id="root">
        <div :class="`md:flex md:flex-row min-h-full flex-grow ${isDarkMode ? 'dark' : 'light'}`" x-data="{ sidebarOpen: Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0) >= 1024, isDarkMode: false, account: false, notifications: false, }" x-cloak="md" x-init="() => { if ('darkMode' in localStorage && localStorage.getItem('darkMode') == 1) isDarkMode = true; }">
            <aside id="sidebar" class="w-screen max-w-[275px] absolute md:sticky md:top-0 h-screen md:border-r md:border-gray-200 dark:border-none bg-gray-100 text-black dark:bg-gray-800 dark:text-white md:shadow-inner shadow-xl overflow-y-auto z-20" x-show="sidebarOpen" x-transition:enter="transform transition-transform duration-500" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition-transform duration-500" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" @click.away="if (Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0) < 1024) sidebarOpen = false">
                <div id="sidebar-brand" class="flex items-center px-4 mb-3 h-14">
                    <h1 class="text-xl font-bold">
                        <?= Page::$pageData->va_name ?>
                    </h1>
                </div>
                <ul id="sidebar-nav" class="mx-3" x-data="{ openDropdown: <?= isset(Page::$pageData->active_dropdown) ? '\'' . Page::$pageData->active_dropdown . '\'' : 'null' ?>, badges: {} }" x-init="initbadges(badges)">
                    <?php foreach ($GLOBALS['pilot-menu'] as $name => $data) : ?>
                        <?php
                        if (isset($data["vanetFeature"]) && Page::$pageData->va_profile['activeFeatures'][$data['vanetFeature']] === FALSE) {
                            continue;
                        }
                        ?>
                        <?php if (Page::$pageData->is_gold || $data["needsGold"] == false) : ?>
                            <li class="p-2 mb-2 font-semibold rounded hover:bg-black hover:bg-opacity-10 dark:hover:bg-opacity-40">
                                <a class="flex items-center" href="<?= $data['link'] ?>">
                                    <?= TailwindIcons::icon($data['icon'], 'text-xl text-black dark:text-white opacity-70 h-6 w-6 mr-2') ?>
                                    <?= $name ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php foreach ($adminmenu as $name => $items) : ?>
                        <?php $catId = preg_replace("/\s/", '-', strtolower($name)); ?>
                        <script id="badges_<?= $catId ?>" type="application/json">
                            <?=
                            Json::encode(array_values(array_filter(array_map(function ($x) {
                                return $x["badgeid"];
                            }, array_values($items)), function ($x) {
                                return !empty($x);
                            })));
                            ?>
                        </script>
                        <li :class="`mb-2 p-2 rounded font-semibold flex justify-[right] cursor-pointer ${openDropdown == '<?= $catId ?>' ? 'bg-black/20 dark:bg-black' : 'hover:bg-black hover:bg-opacity-10 dark:hover:bg-opacity-40'}`" @click="openDropdown = openDropdown == '<?= $catId ?>' ? null : '<?= $catId ?>'">
                            <div class="flex items-center">
                                <span class="relative">
                                    <?= TailwindIcons::icon("admin:{$name}", 'text-xl text-black dark:text-white opacity-70 h-6 w-6 mr-2') ?>
                                    <span class="absolute top-0 left-0 flex w-2 h-2" x-show="anyCategoryBadge('<?= $catId ?>', badges)">
                                        <span class="absolute inline-flex w-full h-full bg-red-400 rounded-full opacity-75 motion-safe:animate-ping"></span>
                                        <span class="relative inline-flex w-2 h-2 bg-red-600 rounded-full"></span>
                                    </span>
                                </span>
                                <div class="flex-1">
                                    <?= $name ?>
                                </div>
                            </div>
                        </li>
                        <div x-show="openDropdown == '<?= $catId ?>'" x-cloak>
                            <?php foreach ($items as $label => $data) : ?>
                                <li class="ml-5 mb-1 py-1 px-2 rounded font-semibold flex justify-[right] cursor-pointer hover:bg-black hover:bg-opacity-10 dark:hover:bg-opacity-40">
                                    <a class="flex items-center w-full" href="<?= $data['link'] ?>">
                                        <div class="flex-1"><?= $label ?></div>
                                        <?php if ($data['badgeid'] !== null) : ?>
                                            <span class="px-2 text-xs font-semibold leading-5 text-white bg-red-500 rounded-full" x-show="typeof badges?.<?= $data['badgeid'] ?> === 'string' && badges?.<?= $data['badgeid'] ?> != 0" x-text="badges?.<?= $data['badgeid'] ?>"></span>
                                            <span class="text-red-500" x-show="typeof badges?.<?= $data['badgeid'] ?> === 'boolean' && badges?.<?= $data['badgeid'] ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </ul>
            </aside>
            <main class="z-10 flex flex-col w-full min-h-screen shadow-lg dark:bg-gray-700">
                <div class="flex items-center w-full gap-2 p-4 shadow-lg">
                    <!-- Sidebar Toggle Button -->
                    <div class="flex items-center flex-1">
                        <button class="focus:outline-none" @click="sidebarOpen = !sidebarOpen">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-black dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                    <!-- Dark Mode Toggle Button -->
                    <button class="flex-none focus:outline-none" @click="isDarkMode = !isDarkMode; localStorage.setItem('darkMode', isDarkMode ? '1' : '0');">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-black dark:hidden group-focus:opacity-100 opacity-60 " fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="hidden w-6 h-6 text-white dark:block group-focus:opacity-100 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                    <!-- Notifications Button -->
                    <button class="flex-none focus:outline-none group" id="notifications-button" @click="notifications = !notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-black dark:text-white group-focus:opacity-100 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>
                    <!-- Notifications Popup -->
                    <div x-show="notifications" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" @click.away="notifications = false" class="origin-top-right absolute right-8 top-[3.3rem] mt-2 rounded-md w-80 shadow-lg bg-white text-gray-700 dark:bg-gray-600 dark:text-white ring-1 ring-black ring-opacity-5 focus:outline-none py-1" role="menu" aria-orientation="vertical" aria-labelledby="notifications-button" tabindex="-1">
                        <?php
                        $notifications = array_map(function ($n) {
                            return '<div class="flex items-center gap-3 px-4 py-2 text-sm" tabindex="-1">
                                <i class="block text-2xl fa ' . $n->icon . '"></i>
                                <div>
                                    <h4 class="text-lg font-bold">
                                        ' . escape($n->subject) . '
                                    </h4>
                                    <p>' . escape($n->content) . '</p>
                                    <small x-text="new Date(\'' . $n->formattedDate . '\').toLocaleString()"></small>
                                </div>
                            </div>';
                        }, Notifications::mine(Page::$pageData->user->data()->id));

                        if (count($notifications) != 0) {
                            echo implode('', $notifications);
                        } else {
                            echo '<div class="px-4 py-2 text-sm text-gray-700 dark:text-white">No Notifications Yet!</div>';
                        }
                        ?>
                    </div>
                    <!-- Account Button -->
                    <button class="flex-none w-6 h-6 rounded-full focus:outline-none bg-primary text-primary-text focus:ring-2 focus:ring-offset-2 focus:ring-transparent focus:ring-offset-black dark:focus:ring-offset-white" @click="account = !account" id="user-menu-button">
                        <?= substr(Page::$pageData->user->data()->name, 0, 1) ?>
                    </button>
                    <!-- Account Popup Menu -->
                    <div x-show="account" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" @click.away="account = false" class="origin-top-right absolute right-2 top-[3.3rem] mt-2 w-48 rounded-md shadow-lg bg-white text-gray-700 dark:bg-gray-600 dark:text-white ring-1 ring-black ring-opacity-5 focus:outline-none py-1" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                        <a href="/profile" class="block px-4 py-2 text-sm" role="menuitem" tabindex="-1">Edit Profile</a>
                        <a href="/logout" class="block px-4 py-2 text-sm" role="menuitem" tabindex="-1">Sign Out</a>
                    </div>
                </div>