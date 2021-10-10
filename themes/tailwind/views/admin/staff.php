<?php
Page::setTitle('Manage Staff - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script type="application/json" id="allEntries">
    <?= Json::encode(array_map(function ($x) {
        unset($x['password']);
        return $x;
    }, Page::$pageData->staff)) ?>
</script>
<script>
    var allEntries = JSON.parse(document.getElementById('allEntries').innerHTML);
    var activeFilter = (x) => x.status == 'Active';
</script>
<div id="content" class="text-black dark:text-white" x-data="{ table: { current: [], orderBy: (x) => x.name, orderByName: 'Name', order: 'asc', search: '', filters: [] }, refresh() { return updateDataTable(allEntries, this.table) }, }">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Manage Staff
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
                    <template x-for="user in table.current" :key="user.id">
                        <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = `/admin/users/${user.id}`">
                            <td class="hidden md:table-cell" x-text="user.callsign"></td>
                            <td x-text="user.name"></td>
                            <td class="hidden md:table-cell" x-text="user.email"></td>
                            <td class="flex justify-end items-center gap-2">
                                <button @click.stop="confirm('Are you sure you want to remove this user\'s staff access?') && fetch(`/api.php/users/${user.id}/permissions/admin`, { method: 'DELETE' }) && table.filters.push(x => x.id != user.id) && refresh();" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
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
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>