<?php
Page::setTitle('Events - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="m-5 text-black dark:text-white" x-data="{ data: { events: null } }" x-init="initevents(data)">
    <h1 class="text-3xl font-bold">Upcoming Events</h1>
    <p class="mb-3">Click on any event to view more information and sign up</p>
    <div class="inline-block w-full align-middle">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">
                            Name
                        </th>
                        <th scope="col" class="hidden lg:table-cell">
                            Date
                        </th>
                        <th scope="col" class="hidden lg:table-cell">
                            Airport
                        </th>
                    </tr>
                </thead>
                <tbody x-html="eventstable(data.events)"></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>