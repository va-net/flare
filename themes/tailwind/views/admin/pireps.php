<?php
Page::setTitle('All PIREPs - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script type="application/json" id="allEntries">
    <?= Json::encode(Page::$pageData->pireps) ?>
</script>
<script>
    var allEntries = JSON.parse(document.getElementById('allEntries').innerHTML);
    var acceptedFilter = (x) => x.status == 1;
</script>
<div id="content" class="text-black dark:text-white" x-data="{ table: { current: [], orderBy: (x) => x.date, orderByName: 'Date', order: 'desc', search: '', filters: [acceptedFilter] }, refresh() { return updateDataTable(allEntries, this.table) }, }">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            All PIREPs
        </h2>
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
            <table class="table" x-init="refresh()">
                <thead>
                    <tr>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.date, $el.textContent, table)">Date</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.pilotname, $el.textContent, table)">Pilot</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => `${x.departure}-${x.arrival}`, $el.textContent, table)">Route</th>
                        <th class="hidden md:table-cell">Status</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.flighttime, $el.textContent, table)">Flight Time</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <form method="post" x-ref="delpirep">
                        <input type="hidden" name="action" value="delpirep" />
                        <input type="hidden" name="id" x-ref="delpirep-id" value="" />
                    </form>
                    <template x-for="pirep in table.current" :key="pirep.id">
                        <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = `/pireps/${pirep.id}`">
                            <td class="hidden md:table-cell" x-text="new Date(pirep.date).toLocaleDateString()"></td>
                            <td class="hidden md:table-cell" x-text="pirep.pilotname"></td>
                            <td x-text="`${pirep.departure}-${pirep.arrival}`"></td>
                            <td class="hidden md:table-cell">
                                <span class="px-2 font-semibold leading-5 rounded-full bg-green-100 text-green-800 dark:bg-green-300 dark:text-green-900 text-sm md:text-base" x-show="pirep.status == 1">
                                    Accepted
                                </span>
                                <span class="px-2 font-semibold leading-5 rounded-full bg-yellow-200 text-yellow-800 text-sm md:text-base" x-show="pirep.status == 0">
                                    Pending
                                </span>
                                <span class="px-2 font-semibold leading-5 rounded-full bg-red-200 text-red-900 text-sm md:text-base" x-show="pirep.status == 2">
                                    Denied
                                </span>
                            </td>
                            <td class="hidden md:table-cell" x-text="pirep.flighttime.formatFlightTime()"></td>
                            <td class="flex justify-end items-center gap-2">
                                <button @click.stop="confirm('Are you ABSOLUTELY SURE you want to delete this PIREP? This can\'t be undone!') && (() => { $refs['delpirep-id'].value = pirep.id; $refs.delpirep.submit(); })()" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
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
        <p class="md:inline-block text-center md:text-left font-semibold md:hover:underline underline md:no-underline cursor-pointer" x-text="table.filters.length > 0 ? 'Show all PIREPs' : 'Show accepted PIREPs only'" @click="table.filters = table.filters.length > 0 ? [] : [acceptedFilter]; refresh();"></p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>