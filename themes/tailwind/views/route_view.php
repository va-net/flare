<?php
Page::setTitle('View Route - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="m-5 text-black dark:text-white">
    <div class="flex items-center mb-3">
        <h1 class="flex-1 text-3xl font-bold">View Route</h1>
        <a href="/pireps/new?fnum=<?= urlencode(Page::$pageData->route->fltnum) ?>&dep=<?= urlencode(Page::$pageData->route->dep) ?>&arr=<?= urlencode(Page::$pageData->route->arr) ?>" class="flex-none inline-block px-3 py-2 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
            File PIREP
        </a>
    </div>
    <div class="grid-cols-2 gap-4 space-y-4 md:grid md:space-y-0">
        <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
            <h2 class="text-2xl font-bold">Basic Information</h2>
            <ul>
                <li>
                    <b>Flight Number:</b> <?= Page::$pageData->route->fltnum ?>
                </li>
                <li>
                    <b>Departure:</b> <?= Page::$pageData->route->dep ?>
                </li>
                <li>
                    <b>Arrival:</b> <?= Page::$pageData->route->arr ?>
                </li>
                <li>
                    <b>Approx. Duration:</b> <?= Time::secsToString(Page::$pageData->route->duration) ?>
                </li>
                <li>
                    <b>Notes:</b> <?= empty(Page::$pageData->route->notes) ? 'N/A' : Page::$pageData->route->notes ?>
                </li>
            </ul>
        </div>
        <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
            <h2 class="text-2xl font-bold">Aircraft</h2>
            <ul class="ml-5 list-disc">
                <?php foreach (Page::$pageData->aircraft as $a) : ?>
                    <li><?= $a->name ?> (<?= $a->liveryname ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-span-2 p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
            <h2 class="mb-2 text-2xl font-bold text-center">Previous PIREPs</h2>
            <div class="overflow-hidden border-b border-gray-200 shadow dark:border-gray-400 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-400">
                    <thead class="bg-primary text-primary-text">
                        <tr>
                            <th scope="col" class="hidden px-6 py-3 text-xs font-medium tracking-wider text-left uppercase lg:table-cell">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase">
                                Pilot
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase">
                                Aircraft
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-500">
                        <?php if (count(Page::$pageData->pireps) > 0) : ?>
                            <?php foreach (Page::$pageData->pireps as $pirep) : ?>
                                <tr>
                                    <td class="hidden px-6 py-3 whitespace-nowrap lg:table-cell">
                                        <?= date_format(date_create($pirep->date), 'Y-m-d') ?>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <?= $pirep->pilotname ?>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <?= $pirep->aircraftname ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td class="px-6 py-3 font-semibold text-center" colspan="3">No PIREPs Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>