<?php
Page::setTitle('Edit User - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script id="user-data" type="application/json">
    <?=
    Json::encode([
        'allPermissions' => Permissions::getAll(),
        'userPermissions' => Page::$pageData->permissions,
        'pireps' => Page::$pageData->pireps,
        'allAwards' => Page::$pageData->all_awards,
        'userAwards' => Page::$pageData->user_awards,
    ])
    ?>
</script>
<script>
    var pageData = JSON.parse(document.getElementById('user-data').innerHTML);
    var allEntries = pageData.pireps;
    var recentFilter = (x) => new Date().getTime() - new Date(x.date).getTime() < 14 * 24 * 60 * 60;
</script>

<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Edit User</h1>
    <div class="xl:grid grid-cols-2 space-y-4 xl:space-y-0 gap-4">
        <div class="p-3 dark:bg-white/10 bg-gray-100 rounded">
            <h2 class="text-2xl font-bold mb-2">User Information</h2>
            <form method="post" id="edit-user" class="space-y-2">
                <input type="hidden" name="id" value="<?= Page::$pageData->edit_user->id ?>" />
                <div class="space-y-1">
                    <label for="callsign">Callsign</label>
                    <input id="callsign" name="callsign" type="text" required class="form-control" value="<?= escape(Page::$pageData->edit_user->callsign) ?>" />
                </div>
                <div class="space-y-1">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" required class="form-control" value="<?= escape(Page::$pageData->edit_user->name) ?>" />
                </div>
                <div class="space-y-1">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" required class="form-control" value="<?= escape(Page::$pageData->edit_user->email) ?>" />
                </div>
                <div class="space-y-1">
                    <label for="ifc">IFC Profile URL</label>
                    <input id="ifc" name="ifc" type="url" required class="form-control" value="<?= escape(Page::$pageData->edit_user->ifc) ?>" />
                </div>
                <?php $transhours = explode(':', Time::secsToString(Page::$pageData->edit_user->transhours)); ?>
                <div class="space-y-1" x-data="{ minutes: '<?= $transhours[1] ?>', hours: '<?= $transhours[0] ?>', }">
                    <label for="transhours">Transfer Flight Time</label>
                    <div class="gap-3 space-y-1 md:flex md:space-y-0">
                        <input id="transhours-hrs" type="number" required class="flex-1 form-control" :value="hours" placeholder="Hours" @input="(e) => { hours = e.target.value; }" />
                        <input id="transhours-mins" type="number" required class="flex-1 form-control" :value="minutes" placeholder="Minutes" @input="(e) => { minutes = e.target.value; }" />
                    </div>
                    <input type="hidden" name="transhours" id="transhours" :value="`${hours}:${minutes}`" value="" required />
                </div>
                <div class="space-y-1">
                    <label for="transflights">Transfer Flight Count</label>
                    <input id="transflights" name="transflights" type="number" required class="form-control" value="<?= Page::$pageData->edit_user->transflights ?>" />
                </div>
                <div class="space-y-1">
                    <label for="joined">Date Joined</label>
                    <input id="joined" name="joined" type="date" disabled class="form-control" value="<?= (new DateTime(Page::$pageData->edit_user->joined))->format('Y-m-d') ?>" />
                </div>
                <?php $statuses = ["Pending", "Active", "Inactive", "Declined"]; ?>
                <div class="space-y-1">
                    <label for="status">Status</label>
                    <select required id="status" name="status" class="form-control" x-init="$el.value = '<?= $statuses[Page::$pageData->edit_user->status] ?>'">
                        <option>Pending</option>
                        <option>Active</option>
                        <option>Inactive</option>
                        <option>Declined</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="admin">Staff Status</label>
                    <select required id="admin" name="admin" class="form-control" x-init="$el.value = '<?= Page::$pageData->user->hasPermission('admin', Page::$pageData->edit_user->id) ? 1 : 0 ?>'">
                        <option value="0">Pilot</option>
                        <option value="1">Staff Member</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control"><?= escape(Page::$pageData->edit_user->notes) ?></textarea>
                </div>
            </form>
            <button type="submit" form="edit-user" class="button-primary mt-3">
                Save
            </button>
        </div>
        <div class="p-3 dark:bg-white/10 bg-gray-100 rounded" x-data="{ table: { current: [], orderBy: (x) => x.date, orderByName: 'Date', order: 'asc', search: '', filters: [recentFilter] }, refresh() { return updateDataTable(allEntries, this.table) }, }">
            <h2 class="text-2xl font-bold mb-2">User PIREPs</h2>
            <div class="flex gap-2 items-center mb-2">
                <input type="text" :value="table.search" class="form-control flex-1" placeholder="Search" @input="table.search = $event.target.value; refresh();" />
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
                            <th class="cursor-pointer" @click="dataTableOrder((x) => x.flightnum, $el.textContent, table)">Flight Number</th>
                            <th class="cursor-pointer" @click="dataTableOrder((x) => x.flighttime, $el.textContent, table)">Flight Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="pirep in table.current" :key="pirep.id">
                            <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = `/pireps/${pirep.id}`">
                                <td class="hidden md:table-cell" x-text="new Date(pirep.date).toLocaleDateString()"></td>
                                <td x-text="pirep.flightnum"></td>
                                <td x-text="pirep.flighttime.formatFlightTime()"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <p class="md:inline-block text-center md:text-left font-semibold md:hover:underline underline md:no-underline cursor-pointer" x-text="table.filters.length > 0 ? 'Show all PIREPs' : 'Show recent PIREPs'" @click="table.filters = table.filters.length > 0 ? [] : [recentFilter]; refresh();"></p>
        </div>
        <div class="p-3 dark:bg-white/10 bg-gray-100 rounded" x-data="{ userAwards: pageData.userAwards, allAwards: pageData.allAwards }">
            <h2 class="text-2xl font-bold mb-2">User Awards</h2>
            <ul class="space-y-1 mb-4">
                <template x-for="award in userAwards" :key="award.id">
                    <li class="flex items-center group">
                        <img :src="award.imageurl" class="w-8 h-8 mr-2" />
                        <span x-text="award.name" class="flex-1"></span>
                        <span class="invisible group-hover:visible flex-none text-gray-400 cursor-pointer" title="Remove Award" @click="await fetch(`/api.php/users/<?= urlencode(Page::$pageData->edit_user->id) ?>/awards/${encodeURIComponent(award.id)}`, { method: 'DELETE' }); userAwards = userAwards.filter(a => a.id != award.id);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                </template>
            </ul>
            <select class="form-control" @change.prevent="await fetch(`/api.php/profile/awards/${encodeURIComponent($el.value)}`, { method: 'POST' }); userAwards.push(allAwards.find(a => a.id == $event.target.value)); $el.value = '';">
                <option value="">Give Award</option>
                <template x-for="award in allAwards.filter(a => !userAwards.some(u => u.id == a.id))" :key="award.id">
                    <option :value="award.id" x-text="award.name"></option>
                </template>
            </select>
        </div>
        <div class="p-3 dark:bg-white/10 bg-gray-100 rounded" x-data="{ userPermissions: pageData.userPermissions, allPermissions: pageData.allPermissions }">
            <h2 class="text-2xl font-bold mb-2">User Permissions</h2>
            <ul class="space-y-1 mb-4">
                <template x-for="p in userPermissions.filter(p => p != 'admin')" :key="p">
                    <li class="flex items-center group">
                        <span x-text="allPermissions[p]" class="flex-1"></span>
                        <?php if (Page::$pageData->user->hasPermission('staffmanage')) : ?>
                            <span class="invisible group-hover:visible flex-none text-gray-400 cursor-pointer" title="Remove Permission" @click="await fetch(`/api.php/users/<?= urlencode(Page::$pageData->edit_user->id) ?>/permissions/${encodeURIComponent(p)}`, { method: 'DELETE' }); userPermissions = userPermissions.filter(u => u != p);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        <?php endif; ?>
                    </li>
                </template>
            </ul>
            <?php if (Page::$pageData->user->hasPermission('staffmanage')) : ?>
                <select class="form-control" @change.prevent="await fetch(`/api.php/users/<?= urlencode(Page::$pageData->edit_user->id) ?>/permissions/${encodeURIComponent($event.target.value)}`, { method: 'POST' }); userPermissions.push($event.target.value); $el.value = '';">
                    <option value="">Give Permission</option>
                    <template x-for="[id, name] in Object.entries(allPermissions).filter(([id]) => !userPermissions.some(u => u == id))" :key="id">
                        <option :value="id" x-text="name"></option>
                    </template>
                </select>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>