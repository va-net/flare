<?php
Page::setTitle('User Lookup - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>

<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">User Lookup</h1>
    <div class="xl:grid grid-cols-2 space-y-4 xl:space-y-0 gap-4">
        <div class="p-3 dark:bg-white/10 bg-gray-100 rounded">
            <h2 class="text-2xl font-bold mb-2">Basic Details</h2>
            <ul>
                <li>
                    <b>IFC:</b>
                    <?php if (empty(Page::$pageData->lookup['ifcUsername'])) : ?>
                        N/A
                    <?php else : ?>
                        <a target="_blank" href="https://community.infiniteflight.com/u/<?= urlencode(Page::$pageData->lookup['ifcUsername']) ?>" class="font-semibold hover:underline">
                            <?= escape(Page::$pageData->lookup['ifcUsername']) ?>
                        </a>
                    <?php endif; ?>
                </li>
                <li>
                    <b>User ID:</b> <?= Page::$pageData->lookup['userId'] ?>
                </li>
                <li>
                    <b>Total XP:</b> <?= Page::$pageData->lookup['totalXp'] ?>
                </li>
                <li>
                    <b>VO Affiliation:</b> <?= escape(Page::$pageData->lookup['virtualOrganization'] ?? 'N/A') ?>
                </li>
            </ul>
        </div>
        <div class="p-3 dark:bg-white/10 bg-gray-100 rounded" x-data="{ table: { current: [], orderBy: (x) => x.date, orderByName: 'Date', order: 'asc', search: '', filters: [recentFilter] }, refresh() { return updateDataTable(allEntries, this.table) }, }">
            <h2 class="text-2xl font-bold mb-2">Groups</h2>
            <ul class="list-disc list-inside">
                <li>
                    <b>IFATC:</b> <?= Page::$pageData->lookup['isIfatc'] ? 'Yes' : 'No' ?>
                </li>
                <li>
                    <b>Moderator:</b> <?= Page::$pageData->lookup['isModerator'] ? 'Yes' : 'No' ?>
                </li>
                <li>
                    <b>Staff:</b> <?= Page::$pageData->lookup['isStaff'] ? 'Yes' : 'No' ?>
                </li>
            </ul>
        </div>
        <div class="p-3 dark:bg-white/10 bg-gray-100 rounded col-span-2">
            <h2 class="text-2xl font-bold mb-2">Violations</h2>
            <div class="md:flex space-y-2">
                <div class="flex-1">
                    <ul>
                        <li>
                            <b>Level 1 Count:</b> <?= Page::$pageData->lookup['violationsByLevel']['level1'] ?>
                        </li>
                        <li>
                            <b>Level 2 Count:</b> <?= Page::$pageData->lookup['violationsByLevel']['level2'] ?>
                        </li>
                        <li>
                            <b>Level 3 Count:</b> <?= Page::$pageData->lookup['violationsByLevel']['level3'] ?>
                        </li>
                    </ul>
                </div>
                <div class="flex-1">
                    <ul>
                        <li>
                            <b>Last Level 1 Date:</b> <?= empty(Page::$pageData->lookup['lastLevel1ViolationDate']) ? 'N/A' : date_format(date_create(Page::$pageData->lookup['lastLevel1ViolationDate']), 'Y-m-d') ?>
                        </li>
                        <li>
                            <b>Last Report Date:</b> <?= empty(Page::$pageData->lookup['lastReportDate']) ? 'N/A' : date_format(date_create(Page::$pageData->lookup['lastReportDate']), 'Y-m-d') ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>