<?php
Page::setTitle('Manage Users - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';

$ifvarbList = [];
foreach (Page::$pageData->watchlist as $ifc => $reason) {
    $ifvarbList[$ifc] = [
        'type' => 'watchlist',
        'reason' => $reason,
    ];
}
foreach (Page::$pageData->blacklist as $ifc => $reason) {
    $ifvarbList[$ifc] = [
        'type' => 'blacklist',
        'reason' => $reason,
    ];
}
foreach (Page::$pageData->previousWatchlist as $ifc => $reason) {
    $ifvarbList[$ifc] = [
        'type' => 'previousWatchlist',
        'reason' => $reason,
    ];
}
foreach (Page::$pageData->previousBlacklist as $ifc => $reason) {
    $ifvarbList[$ifc] = [
        'type' => 'previousBlacklist',
        'reason' => $reason,
    ];
}
?>
<script type="application/json" id="allEntries">
    <?= Json::encode(array_map(function ($x) {
        unset($x['password']);
        return $x;
    }, Page::$pageData->users)) ?>
</script>
<script type="application/json" id="allRanks">
    <?= Json::encode(array_reverse(Page::$pageData->ranks)) ?>
</script>
<script type="application/json" id="ifvarbList">
    <?= Json::encode($ifvarbList); ?>
</script>
<script>
    var allEntries = JSON.parse(document.getElementById('allEntries').innerHTML);
    var allRanks = JSON.parse(document.getElementById('allRanks').innerHTML);
    var ifvarbList = JSON.parse(document.getElementById('ifvarbList').innerHTML);
    var activeFilter = (x) => x.status == 'Active';
    var getRank = (user) => allRanks.find(x => x.timereq <= parseFloat(user.flighttime) + parseFloat(user.transhours));

    const defaultColumns = ['callsign', 'name', 'email', 'flighttime', 'rank'];
    var columns = localStorage.getItem('table__users-admin') ? JSON.parse(localStorage.getItem('table__users-admin')) : defaultColumns;
</script>
<div id="content" class="text-black dark:text-white" x-data="{ table: { current: [], orderBy: (x) => x.name, orderByName: 'Name', order: 'asc', search: '', limit: 25, filters: [activeFilter] }, refresh() { return updateDataTable(allEntries, this.table) }, }">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Manage Users
        </h2>
    </div>

    <div class="md:px-5 px-2 max-w-full">
        <div class="flex gap-2 items-center mb-2">
            <input type="text" :value="table.search" class="form-control flex-1" placeholder="Search" @input="table.search = $event.target.value; table.limit = 25; refresh();" />
            <div class="text-sm">
                <p x-text="`Ordering by ${table.orderByName}`"></p>
                <p x-text="`${table.current.actualLength} result${table.current.actualLength == 1 ? '' : 's'}`"></p>
            </div>
        </div>
        <div class="table-wrapper mb-1">
            <table class="table" x-init="refresh()">
                <thead>
                    <tr>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.callsign, $el.textContent, table)" x-show="columns.includes('callsign')">Callsign</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.name, $el.textContent, table)" x-show="columns.includes('name')">Name</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.ifc, $el.textContent, table)" x-show="columns.includes('ifc')">IFC</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.email, $el.textContent, table)" x-show="columns.includes('email')">Email</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => new Date(x.joined), $el.textContent, table)" x-show="columns.includes('joined')">Joined</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => parseFloat(x.flighttime) + parseFloat(x.transhours), $el.textContent, table)" x-show="columns.includes('flighttime')">Flight Time</th>
                        <th class="hidden lg:table-cell cursor-pointer" @click="dataTableOrder((x) => getRank(x).timereq, $el.textContent, table)" x-show="columns.includes('rank')">Rank</th>
                        <th class="hidden lg:table-cell cursor-pointer" @click="dataTableOrder((x) => x.violand, $el.textContent, table)" x-show="columns.includes('violand')">Vio:Landing</th>
                        <th class="hidden lg:table-cell cursor-pointer" @click="dataTableOrder((x) => x.grade, $el.textContent, table)" x-show="columns.includes('grade')">Grade</th>
                        <th class="hidden lg:table-cell" x-show="columns.includes('flags')">Flags</th>
                        <th class="hidden lg:table-cell cursor-pointer" @click="dataTableOrder((x) => x.notes, $el.textContent, table)" x-show="columns.includes('notes')">Notes</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <form method="post" x-ref="deluser">
                        <input type="hidden" name="action" value="deluser" />
                        <input type="hidden" name="permanent" value="1" />
                        <input type="hidden" name="id" x-ref="deluser-id" value="" />
                    </form>
                    <template x-for="(user, index) in table.current" :key="user.id">
                        <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = `/admin/users/${user.id}`" x-intersect="if ((index + 1) % 25 === 0 && table.limit === index + 1) { table.limit = index + 26; refresh(); }">
                            <td class="hidden md:table-cell" x-text="user.callsign" x-show="columns.includes('callsign')"></td>
                            <td x-text="user.name" x-show="columns.includes('name')"></td>
                            <td x-data="{ ifc: user.ifc.split('/')[4] }" x-show="columns.includes('ifc')">
                                <a :href="`https://community.infiniteflight.com/u/${ifc}`" target="_blank" class="font-semibold flex items-center" @click.stop>
                                    <span x-text="ifc ?? 'Not Provided'" class="mr-1"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor" x-show="!!ifc">
                                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                                    </svg>
                                </a>
                            </td>
                            <td class="hidden md:table-cell" x-text="user.email" x-show="columns.includes('email')"></td>
                            <td class="hidden md:table-cell" x-text="new Date(user.joined).toLocaleDateString()" x-show="columns.includes('joined')"></td>
                            <td class="hidden md:table-cell" x-text="(parseFloat(user.flighttime) + parseFloat(user.transhours)).formatFlightTime()" x-show="columns.includes('flighttime')"></td>
                            <td class="hidden lg:table-cell" x-text="getRank(user).name" x-show="columns.includes('rank')"></td>
                            <td class="hidden md:table-cell" x-text="user.violand" x-show="columns.includes('violand')"></td>
                            <td class="hidden md:table-cell" x-text="user.grade" x-show="columns.includes('grade')"></td>
                            <td class="hidden md:table-cell" x-data="{ listEntry: ifvarbList[user.ifc.split('/')[4]?.toLowerCase()] }" x-show="columns.includes('flags')">
                                <span class="px-2 font-semibold leading-5 rounded-full bg-green-100 text-green-800 dark:bg-green-300 dark:text-green-900" x-show="!listEntry">
                                    None
                                </span>
                                <span class="px-2 font-semibold leading-5 rounded-full bg-yellow-200 text-yellow-800" x-show="listEntry?.type === 'watchlist'">
                                    Watchlisted
                                </span>
                                <span class="px-2 font-semibold leading-5 rounded-full bg-red-200 text-red-900" x-show="listEntry?.type === 'blacklist'">
                                    Blacklisted
                                </span>
                                <span class="px-2 font-semibold leading-5 rounded-full bg-indigo-300 text-indigo-900" x-show="listEntry?.type === 'previousWatchlist'">
                                    Expired Watchlist
                                </span>
                                <span class="px-2 font-semibold leading-5 rounded-full bg-yellow-200 text-yellow-800" x-show="listEntry?.type === 'previousBlacklist'">
                                    Prev. Blacklist
                                </span>
                            </td>
                            <td class="hidden md:table-cell" x-text="user.notes" x-show="columns.includes('notes')"></td>
                            <td class="flex justify-end items-center gap-2">
                                <?php if (Page::$pageData->is_gold && VANet::featureEnabled('airline-userlookup')) : ?>
                                    <a :href="`/admin/users/lookup/${user.ifuserid == null ? encodeURIComponent(user.ifc.split('/')[4]) + '?ifc=true' : encodeURIComponent(user.ifuserid)}`" class="inline-block px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z" />
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                <?php if (Page::$pageData->user->hasPermission('staffmanage')) : ?>
                                    <button @click.stop="confirm('Are you ABSOLUTELY SURE you want to delete this user? This can\'t be undone!') && (() => { $refs['deluser-id'].value = user.id; $refs.deluser.submit(); })()" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="sm:flex mt-1 items-center">
            <p class="md:inline-block text-center md:text-left font-semibold md:hover:underline underline md:no-underline cursor-pointer" x-text="table.filters.length > 0 ? 'Show inactive accounts' : 'Hide inactive accounts'" @click="table.filters = table.filters.length > 0 ? [] : [activeFilter]; refresh();"></p>
            <p class="text-right text-sm text-black/50 dark:text-white/50 flex-1">
                <a href="/profile" class="cursor-pointer hover:underline">
                    Customize Columns
                </a>
            </p>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>