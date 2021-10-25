<?php
Page::setTitle('Manage Events - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script>
    var allEntries = [];
</script>
<div id="content" class="text-black dark:text-white" x-data="{ table: { current: [], orderBy: (x) => x.date, orderByName: 'Name', order: 'asc', search: '', filters: [] }, refresh() { return updateDataTable(allEntries, this.table) }, }">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Manage Events
        </h2>
        <a href="/admin/events/new" class="button-primary text-lg font-semibold">New Event</a>
    </div>

    <div class="md:px-5 px-2 max-w-full">
        <div class="flex gap-2 items-center mb-2">
            <input type="text" :value="table.search" class="form-control flex-1" placeholder="Search" @input="table.search = $event.target.value; updateDataTable(allEntries, table);" />
            <div class="text-sm">
                <p x-text="`Ordering by ${table.orderByName}`"></p>
                <p x-text="`${table.current.length} result${table.current.length == 1 ? '' : 's'}`"></p>
            </div>
        </div>
        <div class="table-wrapper mb-1">
            <table class="table" x-init="allEntries = (await (await fetch('/api.php/events')).json()).result || []; refresh();">
                <thead>
                    <tr>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.date, $el.textContent, table)">Date</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.name, $el.textContent, table)">Name</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.departureIcao, $el.textContent, table)">Airport</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <form method="post" x-ref="deleteevent">
                        <input type="hidden" name="action" value="deleteevent" />
                        <input type="hidden" name="delete" x-ref="deleteevent-id" value="" />
                    </form>
                    <template x-for="event in table.current" :key="event.id">
                        <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = `/admin/events/${encodeURIComponent(event.id)}`">
                            <td x-text="new Date(event.date + 'Z').toLocaleDateString()"></td>
                            <td x-text="event.name"></td>
                            <td class="hidden md:table-cell" x-text="event.departureIcao"></td>
                            <td class="flex justify-end items-center gap-2">
                                <button @click.stop="confirm('Are you ABSOLUTELY SURE you want to delete this event? This can\'t be undone!') && (() => { $refs['deleteevent-id'].value = event.id; $refs.deleteevent.submit(); })()" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>