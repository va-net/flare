<?php
Page::setTitle('Pending PIREPs - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script type="application/json" id="allEntries">
    <?= Json::encode(Page::$pageData->pending) ?>
</script>
<script>
    var allEntries = JSON.parse(document.getElementById('allEntries').innerHTML);

    const defaultColumns = ['callsign', 'pilotname', 'fnum', 'route', 'flighttime', 'multiplier'];
    var columns = localStorage.getItem('table__pending-pireps') ? JSON.parse(localStorage.getItem('table__pending-pireps')) : defaultColumns;
</script>
<div id="content" class="text-black dark:text-white" x-data="{ table: { current: [], orderBy: (x) => x.date, orderByName: 'Date', order: 'desc', search: '', filters: [] }, refresh() { return updateDataTable(allEntries, this.table) }, }">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Pending PIREPs
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
        <div class="table-wrapper">
            <table class="table" x-init="refresh()">
                <thead>
                    <tr>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.date, $el.textContent, table)" x-show="columns.includes('date')">Date</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.pilotname, $el.textContent, table)" x-show="columns.includes('pilotname')">Pilot</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.pilotcallsign, $el.textContent, table)" x-show="columns.includes('callsign')">Callsign</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.flightnum, $el.textContent, table)" x-show="columns.includes('fnum')">Flight Number</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.departure, $el.textContent, table)" x-show="columns.includes('departure')">Departure</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.arrival, $el.textContent, table)" x-show="columns.includes('arrival')">Arrival</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => `${x.departure}-${x.arrival}`, $el.textContent, table)" x-show="columns.includes('route')">Route</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.aircraftname, $el.textContent, table)" x-show="columns.includes('aircraft')">Aircraft</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.flighttime, $el.textContent, table)" x-show="columns.includes('flighttime')">Flight Time</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.multi, $el.textContent, table)" x-show="columns.includes('multiplier')">Multiplier</th>
                        <th class="hidden md:table-cell" x-show="columns.includes('status')">Status</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <form method="post" x-ref="acceptpirep">
                        <input type="hidden" name="action" value="acceptpirep" />
                        <input type="hidden" name="accept" x-ref="acceptpirep-id" value="" />
                    </form>
                    <form method="post" x-ref="declinepirep">
                        <input type="hidden" name="action" value="declinepirep" />
                        <input type="hidden" name="decline" x-ref="declinepirep-id" value="" />
                    </form>
                    <template x-for="pirep in table.current" :key="pirep.id">
                        <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = `/pireps/${pirep.id}`">
                            <td class="hidden md:table-cell" x-text="new Date(pirep.date).toLocaleDateString()" x-show="columns.includes('date')"></td>
                            <td class="hidden md:table-cell" x-text="pirep.pilotname" x-show="columns.includes('pilotname')"></td>
                            <td class="hidden md:table-cell" x-text="pirep.pilotcallsign" x-show="columns.includes('callsign')"></td>
                            <td class="hidden md:table-cell" x-text="pirep.flightnum" x-show="columns.includes('fnum')"></td>
                            <td class="hidden md:table-cell" x-text="pirep.departure" x-show="columns.includes('departure')"></td>
                            <td class="hidden md:table-cell" x-text="pirep.arrival" x-show="columns.includes('arrival')"></td>
                            <td x-text="`${pirep.departure} - ${pirep.arrival}`" x-show="columns.includes('route')"></td>
                            <td x-text="pirep.aircraftname" x-show="columns.includes('aircraft')"></td>
                            <td class="hidden md:table-cell" x-text="pirep.flighttime.formatFlightTime()" x-show="columns.includes('flighttime')"></td>
                            <td class="hidden md:table-cell" x-text="pirep.multi" x-show="columns.includes('multiplier')"></td>
                            <td class="hidden md:table-cell" x-show="columns.includes('status')">
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
                            <td class="flex justify-end items-center gap-2">
                                <button @click.stop="$refs['acceptpirep-id'].value = pirep.id; $refs.acceptpirep.submit();" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-green-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <button @click.stop="$refs['declinepirep-id'].value = pirep.id; $refs.declinepirep.submit();" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <p class="text-right text-sm text-black/50 dark:text-white/50 mt-1">
            <a href="/profile" class="cursor-pointer hover:underline">
                Customize Columns
            </a>
        </p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>