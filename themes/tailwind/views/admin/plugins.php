<?php
Page::setTitle('Manage Plugins - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script id="allPlugins" type="application/json">
    <?= Json::encode(Page::$pageData->all['data']) ?>
</script>
<script>
    var allPlugins = JSON.parse(document.getElementById('allPlugins').innerHTML);
</script>
<div id="content" class="text-black dark:text-white" x-data="{ activeTab: '<?= Input::get('tab') == 'installed' ? 'installed-plugins' : 'plugin-store' ?>' }">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center gap-2">
        <h1 class="flex-1 text-2xl font-bold lg:text-4xl">
            Manage Plugins
        </h1>
    </div>
    <div class="md:px-5 px-2 max-w-full">
        <div class="flex gap-3 border-b border-gray-200 dark:border-gray-500 mb-3 overflow-x-auto overflow-y-hidden">
            <template x-for="tab in ['Plugin Store', 'Installed Plugins']" :key="tab">
                <button :class="`${activeTab == tab.generateId() ? 'border-b-2 border-primary' : 'border-b-2 border-transparent hover:border-black dark:hover:border-white'} pb-1 px-2 mb-[-0.0625rem] cursor-pointer flex items-center font-semibold text-lg`" @click="activeTab = tab.generateId()" x-text="tab"></button>
            </template>
        </div>
        <div x-show="activeTab == 'plugin-store'" x-data="{ prerelease: false, search: '', isLoading: false, async refresh() { this.isLoading = true; prerelease = (await (await fetch(`/api.php/plugins?search=${encodeURIComponent(this.search)}&prerelease=${this.prerelease ? 'true' : 'false'}`)).json()).result.data; this.isLoading = false; } }">
            <form x-ref="installplugin" method="post">
                <input type="hidden" name="action" value="installplugin" />
                <input type="hidden" name="plugin" x-ref="installplugin-id" />
                <input type="hidden" name="prerelease" :value="prerelease ? 1 : 0" />
            </form>
            <div class="md:flex mb-4 gap-3 md:space-y-0 space-y-3">
                <div class="flex flex-1 flex-grow-[3]">
                    <div class="dark:bg-black/50 bg-gray-300 rounded-l flex-none flex p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" class="form-control flex-1 !rounded-l-none" placeholder="Search plugins" @keyup.enter="await refresh()" @input="search = $event.target.value;" />
                </div>
                <select class="form-control flex-1" @change="prerelease = $event.target.value == 1 ? true : false; await refresh();">
                    <option :selected=" !prerelease" value="0">Stable Only</option>
                    <option :selected="prerelease" value="1">Include Pre-releases</option>
                </select>
            </div>
            <div class="md:grid grid-cols-3 lg:grid-cols-4 gap-4 md:space-y-0 space-y-4 mb-4" x-show="!isLoading">
                <template x-for="plugin in allPlugins" :key="plugin.id">
                    <div class="bg-black/10 dark:bg-black/40 rounded p-3 flex flex-col">
                        <h2 class="text-xl font-bold mb-1" x-text="plugin.name"></h2>
                        <p class="text-sm" x-text="plugin.description"></p>
                        <p class="text-xs mb-2 flex-1">
                            <template x-for="tag in plugin.tags" :key="tag">
                                <span class="px-2 font-semibold inline-block leading-5 rounded-full bg-white/20 mr-2 mt-2" x-text="tag"></span>
                            </template>
                        </p>
                        <div><button class="button-primary" :disabled="plugin.installed" @click="$refs['installplugin-id'].value = plugin.id; $refs.installplugin.submit();">Install</button></div>
                    </div>
                </template>
            </div>
            <p class="text-lg font-semibold text-center" x-show="isLoading">Loading...</p>
        </div>
        <div x-show="activeTab == 'installed-plugins'" x-data="{ updates: [], prerelease: false, async refresh() { this.updates = (await (await fetch(`/api.php/plugins/updates?prerelease=${this.prerelease ? 'true' : 'false'}`)).json()).result; } }" x-init="await refresh();">
            <form x-ref="removeplugin" method="post">
                <input type="hidden" name="action" value="removeplugin" />
                <input type="hidden" name="plugin" x-ref="removeplugin-id" />
            </form>
            <form x-ref="updateplugin" method="post">
                <input type="hidden" name="action" value="updateplugin" />
                <input type="hidden" name="prerelease" :value="prerelease ? 1 : 0" />
                <input type="hidden" name="plugin" x-ref="updateplugin-id" />
            </form>
            <div class="flex items-center gap-1 mb-3">
                <input type="checkbox" id="prerelease" @change="prerelease = $event.target.checked; await refresh();" />
                <label for="prerelease">Include Pre-release</label>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Version</th>
                            <th><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (Page::$pageData->installed as $plugin) : ?>
                            <tr>
                                <td><?= $plugin['pluginInfo']['name'] ?></td>
                                <td><?= $plugin['versionTag'] ?></td>
                                <td>
                                    <button @click="$refs['updateplugin-id'].value = '<?= $plugin['pluginInfo']['id'] ?>'; $refs.updateplugin.submit();" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white" x-show="updates.some(u => u.pluginId == '<?= $plugin['pluginInfo']['id'] ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                    <button @click="confirm('Are you sure you want to uninstall this plugin?') && (() => {$refs['removeplugin-id'].value = '<?= $plugin['pluginInfo']['id'] ?>'; $refs.removeplugin.submit();})();" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>