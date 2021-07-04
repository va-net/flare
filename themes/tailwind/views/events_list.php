<?php
Page::setTitle('Events - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="m-5 text-black dark:text-white" x-data="{ data: { events: null } }" x-init="initevents(data)">
    <h1 class="text-3xl font-bold">Upcoming Events</h1>
    <p class="mb-3">Click on any event to view more information and sign up</p>
    <div class="inline-block w-full align-middle">
        <div class="overflow-hidden border-b border-gray-200 shadow dark:border-gray-400 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-400">
                <thead class="bg-primary text-primary-text">
                    <tr>
                        <th scope="col" class="p-3 text-xs font-medium tracking-wider text-left uppercase">
                            Name
                        </th>
                        <th scope="col" class="hidden p-3 text-xs font-medium tracking-wider text-left uppercase lg:table-cell">
                            Date
                        </th>
                        <th scope="col" class="hidden p-3 text-xs font-medium tracking-wider text-left uppercase lg:table-cell">
                            Airport
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-600" x-html="eventstable(data.events)"></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>