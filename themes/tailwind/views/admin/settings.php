<?php
Page::setTitle('Site Settings - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script>
    var alert = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>';
</script>
<div id="content" class="text-black dark:text-white m-6" x-data="{ activeTab: '<?= empty(Input::get('tab')) ? 'settings' : Input::get('tab') ?>', alerts: [<?= !empty(Page::$pageData->update) ? "'updates'" : '' ?>] }">
    <h1 class="mb-5 text-4xl font-bold block">Site Settings</h1>
    <div class="flex gap-3 border-b border-gray-200 dark:border-gray-500 mb-3 overflow-x-auto overflow-y-hidden">
        <template x-for="tab in ['Settings', 'Design', 'Interaction', 'Maintenance', 'Updates']" :key="tab">
            <button :class="`${activeTab == tab.toLowerCase() ? 'border-b-2 border-primary' : 'border-b-2 border-transparent hover:border-black dark:hover:border-white'} pb-1 px-2 mb-[-0.0625rem] cursor-pointer flex items-center font-semibold text-lg`" @click="activeTab = tab.toLowerCase()">
                <span x-text="tab" class="flex-1"></span>
                <span x-show="alerts.includes(tab.toLowerCase())" x-html="alert" class="text-red-500 flex items-center ml-1"></span>
            </button>
        </template>
    </div>
    <div id="tabs-content" class="mx-2">
        <div id="settings" x-show="activeTab == $el.id">
            <form method="post" class="space-y-2" id="settings-form">
                <input type="hidden" name="action" value="vasettingsupdate" />
                <div class="space-y-1">
                    <label for="vaname">Full VA Name</label>
                    <input id="vaname" name="vaname" type="text" value="<?= Page::$pageData->va_name ?>" required class="form-control" />
                </div>
                <div class="space-y-1 hidden">
                    <label for="valogo">VA Logo URL</label>
                    <input id="valogo" name="valogo" type="url" value="<?= Page::$pageData->logo_url ?>" class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="vaident">VA Callsign RegEx</label>
                    <input id="vaident" name="vaident" type="text" value="<?= Page::$pageData->callsign_format ?>" required class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="vaabbrv">VA Abbreviation</label>
                    <input id="vaabbrv" name="vaabbrv" type="text" value="<?= Page::$pageData->va_ident ?>" required class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="forceserv">Force Live Server</label>
                    <select id="forceserv" name="forceserv" required class="form-control" x-data="{ server: '<?= Page::$pageData->force_server ?>' }" @change="server = $event.target.value">
                        <option value="0" :selected="server == 0">Select</option>
                        <template x-for="s in ['Casual', 'Training', 'Expert']" :key="s">
                            <option :value="s.toLowerCase()" :selected="s == server" x-text="`Force ${s} Server`"></option>
                        </template>
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="checkpre">Check for Beta Updates</label>
                    <select id="checkpre" name="checkpre" required class="form-control" x-data="{ val: '<?= Page::$pageData->check_pre ?>' }" @change="val = $event.target.value" :disabled="<?= Page::$pageData->version["prerelease"] ? 'true' : 'false' ?>">
                        <option value="0" :selected="val == 0">No (Recommended for Production Sites)</option>
                        <option value="1" :selected="val == 1">Yes</option>
                    </select>
                </div>
            </form>
            <button type="submit" form="settings-form" class="button-primary">
                Save
            </button>
        </div>
        <div id="design" x-show="activeTab == $el.id">
            <form method="post" class="space-y-2" id="design-form">
                <input type="hidden" name="action" value="setdesign" />
                <div class="space-y-1">
                    <label for="theme">Site Theme</label>
                    <select id="theme" name="theme" required class="form-control" x-data="{ val: '<?= Page::$pageData->active_theme ?>' }" @change="val = $event.target.value">
                        <?php foreach (Page::$pageData->themes as $t) : ?>
                            <option value="<?= $t ?>" :selected="val == '<?= $t ?>'"><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="hexcol">Main Colour (hex)</label>
                    <input id="hexcol" name="hexcol" type="text" value="<?= Page::$pageData->color_main ?>" required class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="textcol">VA Logo URL</label>
                    <input id="textcol" name="textcol" type="text" value="<?= Page::$pageData->text_color ?>" required class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="customcss">Custom CSS</label>
                    <textarea rows="3" id="customcss" name="customcss" class="form-control font-mono" @keydown="resizeTextarea($el)" x-init="resizeTextarea($el)"><?= escape(Page::$pageData->custom_css) ?></textarea>
                </div>
            </form>
            <button type="submit" form="design-form" class="button-primary">
                Save
            </button>
        </div>
        <div id="interaction" x-show="activeTab == $el.id">
            <form method="post" id="setupapp">
                <input hidden name="action" value="setupapp" />
            </form>
            <form method="post" class="space-y-2" id="interaction-form">
                <input type="hidden" name="action" value="interactionupdate" />
                <div class="space-y-1">
                    <label for="analytics">Send Analytics to Developers</label>
                    <select id="analytics" name="analytics" required class="form-control" x-data="{ val: '<?= Page::$pageData->analytics_enabled ? 1 : 0 ?>' }" @change="val = $event.target.value" :disabled="<?= Page::$pageData->version["prerelease"] ? 'true' : 'false' ?>">
                        <option value="1" :selected="val == 1">Yes (Recommended)</option>
                        <option value="0" :selected="val == 0">No</option>
                    </select>
                </div>
            </form>
            <div>
                <button type="submit" form="interaction-form" class="button-primary">
                    Save
                </button>
                <?php if (Page::$pageData->setup_app) : ?>
                    <button type="submit" form="setupapp" class="button-primary ml-2">
                        Setup App
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div id="maintenance" x-show="activeTab == $el.id">
            <div class="md:flex space-y-2 lg:space-y-0">
                <div class="flex-1 text-center">
                    <form method="post">
                        <input hidden name="action" value="clearlogs" />
                        <input hidden name="period" value="*" />
                        <input type="submit" class="button-primary" value="Clear All Logs" />
                    </form>
                </div>
                <div class="flex-1 text-center">
                    <form method="post">
                        <input hidden name="action" value="clearlogs" />
                        <input hidden name="period" value="30" />
                        <input type="submit" class="button-primary" value="Clear Old Logs" />
                    </form>
                </div>
                <div class="flex-1 text-center">
                    <form method="post">
                        <input hidden name="action" value="clearcache" />
                        <input type="submit" class="button-primary" value="Clear Cache" />
                    </form>
                </div>
                <div class="flex-1 text-center">
                    <button type="button" @click="await repairSite(); flashSuccess = 'Repair Attempted Successfully';" class="button-primary">
                        Repair Site
                    </button>
                </div>
                <?php if (Page::$pageData->migrate_config) : ?>
                    <div class="flex-1 text-center">
                        <button type="button" @click="await migrateConfig(); flashSuccess = 'Configuration Migrated Successfully';" class="button-primary">
                            Migrate Configuration
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div id="updates" x-show="activeTab == $el.id">
            <?php if (Page::$pageData->update) : ?>
                <div class="text-center" x-ref="update-content">
                    <p class="text-lg font-bold mb-2">
                        An update to <?= Page::$pageData->update["tag_name"] ?> is available
                    </p>
                    <button @click="await updateSite($refs['update-content'])" type="button" class="px-3 py-2 text-xl font-semibold rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                        Update Now
                    </button>
                </div>
            <?php else : ?>
                <div class="text-center">
                    <p class="text-lg font-bold">
                        Flare is up-to-date
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>