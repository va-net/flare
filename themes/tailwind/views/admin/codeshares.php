<?php
Page::setTitle('Manage Codeshares - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script id="allRoutes" type="application/json">
    <?= Json::encode(Page::$pageData->routes) ?>
</script>
<script>
    var allRoutes = JSON.parse(document.getElementById('allRoutes').innerHTML);
    var allEntries = [];
</script>
<div id="content" class="text-black dark:text-white" x-data="{ table: { current: [], orderBy: (x) => x.date, orderByName: 'Name', order: 'asc', search: '', filters: [] }, refresh() { return updateDataTable(allEntries, this.table) }, activeTab: 'codeshares' }">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Manage Codeshares
        </h2>
    </div>
    <div class="md:px-5 px-2 max-w-full">
        <div class="flex gap-3 border-b border-gray-200 dark:border-gray-500 mb-3 overflow-x-auto overflow-y-hidden">
            <template x-for="tab in ['Codeshares', 'New Codeshare']" :key="tab">
                <button :class="`${activeTab == tab.generateId() ? 'border-b-2 border-primary' : 'border-b-2 border-transparent hover:border-black dark:hover:border-white'} pb-1 px-2 mb-[-0.0625rem] cursor-pointer flex items-center font-semibold text-lg`" @click="activeTab = tab.generateId()" x-text="tab"></button>
            </template>
        </div>
        <div class="table-wrapper mb-1" x-show="activeTab == 'codeshares'">
            <table class="table" x-init="allEntries = (await (await fetch('/api.php/codeshares')).json()).result || []; refresh();">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th class="hidden md:table-cell">Message</th>
                        <th># Routes</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <form method="post" x-ref="deletecodeshare">
                        <input type="hidden" name="action" value="deletecodeshare" />
                        <input type="hidden" name="delete" x-ref="deletecodeshare-id" value="" />
                    </form>
                    <form method="post" x-ref="importcodeshare">
                        <input type="hidden" name="action" value="importcodeshare" />
                        <input type="hidden" name="id" x-ref="importcodeshare-id" value="" />
                    </form>
                    <template x-for="c in table.current" :key="c.id">
                        <tr>
                            <td x-text="c.senderName"></td>
                            <td x-text="c.message"></td>
                            <td class="hidden md:table-cell" x-text="c.routes.length"></td>
                            <td class="flex justify-end items-center gap-2">
                                <button @click.stop="confirm('Are you sure you want to accept this request and import these routes?') && (() => { $refs['importcodeshare-id'].value = c.id; $refs.importcodeshare.submit(); })()" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                </button>
                                <button @click.stop="confirm('Are you ABSOLUTELY SURE you want to delete this codeshare? This can\'t be undone!') && (() => { $refs['deletecodeshare-id'].value = c.id; $refs.deletecodeshare.submit(); })()" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
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
        <div x-show="activeTab == 'new-codeshare'">
            <form method="post" class="space-y-2" x-data="{ routes: [] }" id="new-codeshare">
                <input type="hidden" name="action" value="newcodeshare" />
                <div class="space-y-1">
                    <label for="recipient">Recipient VA Identifier</label>
                    <input id="recipient" name="recipient" type="text" required class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="message">Optional Message</label>
                    <input id="message" name="message" type="text" class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="routes">Routes</label>
                    <select id="routes" class="form-control" @change="routes.push(allRoutes.find(r => r.id == $event.target.value)); $el.value = '';">
                        <option value="">Select</option>
                        <template x-for="r in allRoutes.filter(r => !routes.some(rr => rr.id == r.id))" :key="r.id">
                            <option :value="r.id" x-text="`${r.fltnum} (${r.dep}-${r.arr})`"></option>
                        </template>
                    </select>
                    <ul>
                        <template x-for="r in routes" :key="r.id">
                            <li class="flex items-center group">
                                <span class="flex-1" x-text="`${r.fltnum} (${r.dep}-${r.arr})`"></span>
                                <span class="invisible group-hover:visible flex-none text-gray-400 cursor-pointer" title="Remove Route" @click="routes = routes.filter(rr => rr.id != r.id);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </li>
                        </template>
                    </ul>
                    <div id="routes[]">
                        <template x-for="r in allRoutes" :key="r.id">
                            <input type="hidden" name="routes[]" :value="r.id" />
                        </template>
                    </div>
                </div>
            </form>
            <button type="submit" form="new-codeshare" class="button-primary">Send</button>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>