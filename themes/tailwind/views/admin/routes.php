<?php
Page::setTitle('Manage Routes - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script type="application/json" id="allEntries">
    <?= Json::encode(Page::$pageData->routes) ?>
</script>
<script>
    var allEntries = JSON.parse(document.getElementById('allEntries').innerHTML);
</script>
<div id="content" class="text-black dark:text-white" x-data="{ table: { current: [], orderBy: (x) => x.fltnum, orderByName: 'Flight Number', order: 'asc', search: '' }, refresh() { return updateDataTable(allEntries, this.table) }, }">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Manage Routes
        </h2>
        <a href="/admin/routes/new" class="inline-block px-3 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
            Create Route
        </a>
        <a href="/admin/routes/import" class="inline-block px-3 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
            Import Routes
        </a>
    </div>

    <div class="md:px-5 px-2 max-w-full">
        <div class="flex gap-2 items-center mb-2">
            <input type="text" :value="table.search" class="form-control flex-1" placeholder="Search" @input="table.search = $event.target.value; updateDataTable(allEntries, table);" />
            <div class="text-sm">
                <p x-text="`Ordering by ${table.orderByName}`"></p>
                <p x-text="`${table.current.length} result${table.current.length == 1 ? '' : 's'}`"></p>
            </div>
        </div>
        <div class="table-wrapper mb-2">
            <table class="table" x-init="refresh()">
                <thead>
                    <tr>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.fltnum, $el.textContent, table)">Flight Number</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => `${x.dep}-${x.arr}`, $el.textContent, table)">Route</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.duration, $el.textContent, table)">Flight Time</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <form method="post" x-ref="deleteroute">
                        <input type="hidden" name="action" value="deleteroute" />
                        <input type="hidden" name="delete" value="" x-ref="deleteroute-id" />
                    </form>
                    <template x-for="route in table.current" :key="route.id">
                        <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = `/admin/routes/${route.id}`">
                            <td class="hidden md:table-cell" x-text="route.fltnum"></td>
                            <td x-text="`${route.dep}-${route.arr}`"></td>
                            <td class="hidden md:table-cell" x-text="route.duration.formatFlightTime()"></td>
                            <td class="text-right">
                                <button @click.stop="confirm('Are you sure you want to delete this route?') && (() => { $refs['deleteroute-id'].value = route.id; $refs.deleteroute.submit(); })" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
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