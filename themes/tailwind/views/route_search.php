<?php
Page::setTitle('Route Search - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="grid-cols-3 gap-5 m-5 space-y-5 text-black md:space-y-0 md:grid dark:text-white">
    <div>
        <div class="sticky top-0 pt-2">
            <h2 class="text-2xl font-bold">Route Search</h2>
            <p class="mb-3">Leave fields blank to allow any value</p>
            <form method="get" action="/routes/search" class="space-y-2" id="route-search">
                <div class="space-y-1">
                    <label for="dep">Departure ICAO</label>
                    <input id="dep" name="dep" type="text" value="<?= Input::get('dep') ?>" maxlength="4" class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="arr">Arrival ICAO</label>
                    <input id="arr" name="arr" type="text" value="<?= Input::get('arr') ?>" maxlength="4" class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="fltnum">Flight Number</label>
                    <input id="fltnum" name="fltnum" type="text" value="<?= Input::get('fltnum') ?>" class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="aircraft">Aircraft</label>
                    <select id="aircraft" name="aircraft" class="form-control">
                        <option value>Any Aircraft</option>
                        <?php
                        foreach (Page::$pageData->aircraft as $aircraft) {
                            $aircraft = (array)$aircraft;
                            $notes = $aircraft['notes'] == null ? '' : ' - ' . $aircraft['notes'];
                            if ($aircraft['id'] == Input::get('aircraft')) {
                                echo '<option value="' . $aircraft['id'] . '" selected>' . $aircraft['name'] . ' (' . $aircraft['liveryname'] . ')' . $notes . '</option>';
                            } else {
                                echo '<option value="' . $aircraft['id'] . '">' . $aircraft['name'] . ' (' . $aircraft['liveryname'] . ')' . $notes . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="duration">Flight Time</label>
                    <select id="duration" name="duration" class="form-control">
                        <option value>Any Flight Time</option>
                        <option value="0" <?= Input::get('duration') == '0' ? ' selected' : '' ?>>&lt; 1hr</option>
                        <?php foreach (range(1, 9) as $dur) : ?>
                            <option value="<?= $dur ?>" <?= Input::get('duration') == $dur ? ' selected' : '' ?>><?= $dur . '-' . ($dur + 1) ?> hours</option>
                        <?php endforeach; ?>
                        <option value="10" <?= Input::get('duration') == '10' ? ' selected' : '' ?>>10hrs+</option>
                    </select>
                </div>
            </form>
            <button type="submit" form="route-search" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                Search
            </button>
        </div>
    </div>
    <div class="min-h-full col-span-2 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
        <?php if (!isset(Page::$pageData->routes) || count(Page::$pageData->routes) < 1) : ?>
            <div class="flex min-w-full min-h-full">
                <span class="block m-auto text-4xl font-bold">No Routes Found</span>
            </div>
        <?php else : ?>
            <div class="divide-y divide-gray-200 dark:divide-gray-400">
                <?php foreach (Page::$pageData->routes as $route) : ?>
                    <div class="flex px-4 py-3">
                        <div class="flex-1">
                            <h4 class="text-xl font-bold">Flight <?= $route->fltnum ?></h4>
                            <ul class="ml-3">
                                <li>
                                    <b>Route:</b>
                                    <a href="/airport/<?= urlencode($route->dep) ?>" class="hover:underline">
                                        <?= $route->dep ?>
                                    </a>
                                    -
                                    <a href="/airport/<?= urlencode($route->arr) ?>" class="hover:underline">
                                        <?= $route->arr ?>
                                    </a>
                                </li>
                                <li><b>Flight Time:</b> <?= Time::secsToString($route->duration) ?></li>
                                <?= $route->notes == null ? '' : '<li><b>Notes:</b> ' . $route->notes . '</li>'; ?>
                            </ul>
                        </div>
                        <div class="flex items-center flex-none">
                            <a href="/routes/<?= $route->id ?>" class="inline-flex p-2 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>