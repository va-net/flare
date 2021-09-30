<?php
Page::setTitle('Manage Users - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script type="application/json" id="allEntries">
    <?= Json::encode(array_map(function ($x) {
        unset($x['password']);
        return $x;
    }, Page::$pageData->users)) ?>
</script>
<script>
    var allEntries = JSON.parse(document.getElementById('allEntries').innerHTML);
    var activeFilter = (x) => x.status == 'Active';
</script>
<div id="content" class="text-black dark:text-white" x-data="{ table: { current: [], orderBy: (x) => x.name, orderByName: 'Name', order: 'asc', search: '', filters: [activeFilter] }, refresh() { return updateDataTable(allEntries, this.table) }, }">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Manage Users
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
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.callsign, $el.textContent, table)">Callsign</th>
                        <th class="cursor-pointer" @click="dataTableOrder((x) => x.name, $el.textContent, table)">Name</th>
                        <th class="hidden md:table-cell cursor-pointer" @click="dataTableOrder((x) => x.email, $el.textContent, table)">Email</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <form method="post" x-ref="deluser">
                        <input type="hidden" name="action" value="deluser" />
                        <input type="hidden" name="permanent" value="1" />
                        <input type="hidden" name="id" x-ref="deluser-id" value="" />
                    </form>
                    <template x-for="user in table.current" :key="user.id">
                        <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = `/admin/users/${user.id}`">
                            <td class="hidden md:table-cell" x-text="user.callsign"></td>
                            <td x-text="user.name"></td>
                            <td class="hidden md:table-cell" x-text="user.email"></td>
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
        <p class="md:inline-block text-center md:text-left font-semibold md:hover:underline underline md:no-underline cursor-pointer" x-text="table.filters.length > 0 ? 'Show inactive accounts' : 'Hide inactive accounts'" @click="table.filters = table.filters.length > 0 ? [] : [activeFilter]; refresh();"></p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>