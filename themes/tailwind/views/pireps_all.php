<?php
Page::setTitle('My PIREPs - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<script type="application/json" id="allEntries">
    <?= Json::encode(Page::$pageData->pireps) ?>
</script>
<script>
    var allEntries = JSON.parse(document.getElementById('allEntries').innerHTML);
</script>
<div id="content" class="m-5 text-black dark:text-white" x-data="{ table: { current: [], orderBy: (x) => x.date, orderByName: 'Date', order: 'desc', search: '', filters: [] }, refresh() { return updateDataTable(allEntries, this.table) }, }">
    <h1 class="mb-3 text-3xl font-bold">My PIREPs</h1>
    <div class="max-w-full">
        <div class="flex gap-2 items-center mb-2">
            <input type="text" :value="table.search" class="form-control flex-1" placeholder="Search" @input="table.search = $event.target.value; updateDataTable(allEntries, table);" />
            <div class="text-sm">
                <p x-text="`Ordering by ${table.orderByName}`"></p>
                <p x-text="`${table.current.length} result${table.current.length == 1 ? '' : 's'}`"></p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="table" x-init="refresh()">
                <thead>
                    <tr>
                        <th class="hidden lg:table-cell cursor-pointer" @click="dataTableOrder((x) => x.date, $el.textContent, table)">Date</th>
                        <th class="hidden lg:table-cell cursor-pointer" @click="dataTableOrder((x) => x.fnum, $el.textContent, table)">Flight #</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => `${x.departure}-${x.arrival}`, $el.textContent, table)">Route</th>
                        <th class="hidden lg:table-cell cursor-pointer" @click="dataTableOrder((x) => x.aircraft, $el.textContent, table)">Aircraft</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.status, $el.textContent, table)">Status</th>
                        <th><span class="sr-only">Edit</span></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="pirep in table.current">
                        <tr>
                            <td class="hidden lg:table-cell" x-text="new Date(pirep.date).toLocaleDateString()"></td>
                            <td class="hidden lg:table-cell" x-text="pirep.fnum"></td>
                            <td x-text="`${pirep.departure}-${pirep.arrival}`"></td>
                            <td class="hidden lg:table-cell" x-text="pirep.aircraft"></td>
                            <td>
                                <span class="px-2 text-xs font-semibold leading-5 rounded-full bg-yellow-200 text-yellow-800" x-show="pirep.status == 'Pending'">
                                    Pending
                                </span>
                                <span class="px-2 text-xs font-semibold leading-5 rounded-full bg-green-100 text-green-800 dark:bg-green-300 dark:text-green-900" x-show="pirep.status == 'Approved'">
                                    Approved
                                </span>
                                <span class="px-2 text-xs font-semibold leading-5 rounded-full bg-red-200 text-red-900" x-show="pirep.status == 'Denied'">
                                    Denied
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-right whitespace-nowrap">
                                <a :href="`/pireps/${pirep.id}`" class="inline-flex p-2 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>