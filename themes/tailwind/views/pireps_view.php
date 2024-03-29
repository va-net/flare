<?php
Page::setTitle(Page::$pageData->pirep->departure . ' to ' . Page::$pageData->pirep->arrival . ' - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
$disabled = Page::$pageData->user->hasPermission('pirepmanage') ? 'required' : 'disabled';
?>
<script>
    function getRawFlightTime() {
        return document.getElementById('ftime-raw').value;
    }

    function setRawFlightTime(value) {
        const el = document.getElementById('ftime-raw');
        el.value = value;
        el.dispatchEvent(new Event('change'));
    }
</script>
<div id="content" class="m-5 text-black dark:text-white">
    <h1 class="mb-3 text-3xl font-bold">Edit PIREP #<?= Page::$pageData->pirep->id ?></h1>

    <div class="grid-cols-2 gap-3 space-y-3 lg:grid lg:space-y-0">
        <div class="p-3 rounded bg-black/5 dark:bg-white/20">
            <form method="post" class="space-y-2" id="edit-pirep">
                <h2 class="text-2xl font-bold">PIREP Details</h2>
                <input type="hidden" class="hidden" name="action" value="editpirep" />
                <input type="hidden" class="hidden" name="id" value="<?= Page::$pageData->pirep->id ?>" />
                <div class="space-y-1">
                    <label for="date">Flight Date</label>
                    <input id="date" name="date" type="date" value="<?= date_format(date_create(Page::$pageData->pirep->date), 'Y-m-d') ?>" required class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="fnum">Flight Number</label>
                    <input id="fnum" name="fnum" type="text" value="<?= escape(Page::$pageData->pirep->flightnum) ?>" required class="form-control" placeholder="123AB" />
                </div>
                <?php $ftime = explode(':', Time::secsToString(Page::$pageData->pirep->flighttime)); ?>
                <div class="space-y-1" x-data="{ minutes: '<?= $ftime[1] ?>', hours: '<?= $ftime[0] ?>', }">
                    <label for="ftime">Flight Time</label>
                    <div class="gap-3 space-y-1 md:flex md:space-y-0">
                        <input id="ftime-hrs" type="number" :value="hours" <?= $disabled ?> class="flex-1 form-control" placeholder="Hours" @input="(e) => { hours = e.target.value; }" />
                        <input id="ftime-mins" type="number" :value="minutes" <?= $disabled ?> class="flex-1 form-control" placeholder="Minutes" @input="(e) => { minutes = e.target.value; }" />
                    </div>
                    <input type="hidden" class="hidden" <?= $disabled ?> id="ftime" name="ftime" :value="`${hours}:${minutes}`" value="" />
                    <input type="hidden" class="hidden" <?= $disabled ?> id="ftime-raw" name="ftime-raw" :value="hours * 3600 + minutes * 60" value="" @change="let ftime = $event.target.value; hours = Math.floor(ftime / 3600); minutes = Math.floor((ftime % 3600) / 60);" />
                </div>
                <div class="space-y-1">
                    <label for="dep">Departure ICAO</label>
                    <input id="dep" name="dep" type="text" value="<?= Page::$pageData->pirep->departure ?>" maxlength="4" required class="form-control" placeholder="ICAO" />
                </div>
                <div class="space-y-1">
                    <label for="arr">Arrival ICAO</label>
                    <input id="arr" name="arr" type="text" value="<?= Page::$pageData->pirep->arrival ?>" maxlength="4" required class="form-control" placeholder="ICAO" />
                </div>
                <div class="space-y-1">
                    <label for="fuel">Fuel Used (kg)</label>
                    <input id="fuel" name="fuel" type="number" value="<?= Page::$pageData->pirep->fuelused ?>" min="1" required class="form-control" placeholder="1234" />
                </div>
                <div class="space-y-1">
                    <label for="aircraft">Aircraft</label>
                    <select id="aircraft" name="aircraft" <?= $disabled ?> class="form-control">
                        <?php
                        foreach (Page::$pageData->aircraft as $aircraft) {
                            $notes = $aircraft['notes'] == null ? '' : ' - ' . $aircraft['notes'];
                            if ($aircraft['id'] == Page::$pageData->pirep->aircraftid) {
                                echo '<option value="' . $aircraft['id'] . '" selected>' . $aircraft['name'] . ' (' . $aircraft['liveryname'] . ')' . $notes . '</option>';
                            } else {
                                echo '<option value="' . $aircraft['id'] . '">' . $aircraft['name'] . ' (' . $aircraft['liveryname'] . ')' . $notes . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-1 space-y-1">
                    <label for="mutli">Multiplier</label>
                    <div class="flex gap-2" x-data="{ showMenu: false, multi: '<?= Page::$pageData->pirep->multi ?>' }">
                        <input id="multi" name="multi" x-ref="multi" type="text" :value="multi" @change="multi = $event.target.value" readonly class="form-control flex-1" placeholder="123456" />
                        <?php if (Page::$pageData->user->hasPermission('pirepmanage')) : ?>
                            <div class="relative inline-block text-left" @click.outside="showMenu = false">
                                <button type="button" class="button-primary !mt-0 h-full" id="menu-button" aria-expanded="true" aria-haspopup="true" @click="showMenu = !showMenu">
                                    Edit
                                </button>
                                <div x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" x-show="showMenu" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-600 dark:text-white" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                                    <div class="py-1" role="none">
                                        <span x-show="multi !== 'None'" @click="(async () => { setRawFlightTime(await removeMultiplier(multi, getRawFlightTime())); multi = 'None'; showMenu = false; })()" class="block px-4 py-2 text-sm cursor-pointer hover:bg-white/20" role="menuitem" tabindex="-1" id="menu-item-0">Remove Multiplier</span>
                                        <span x-show="multi === 'None'" @click="(async () => { const obj = await addMultiplier(prompt('Enter Multiplier Code'), getRawFlightTime()); multi = obj.name; setRawFlightTime(obj.flightTime); showMenu = false; })()" class="block px-4 py-2 text-sm cursor-pointer hover:bg-white/20" role="menuitem" tabindex="-1" id="menu-item-0">Add Multiplier</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="space-y-1">
                    <label for="status">Status</label>
                    <select id="status" name="status" <?= $disabled ?> class="form-control">
                        <?php
                        $statuses = ['Pending', 'Approved', 'Denied'];
                        foreach ($statuses as $id => $label) {
                            if ($id == Page::$pageData->pirep->status) {
                                echo '<option value="' . $id . '" selected>' . $label . '</option>';
                            } else {
                                echo '<option value="' . $id . '">' . $label . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </form>
            <button type="submit" form="edit-pirep" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                Save
            </button>
        </div>
        <div class="h-full p-3 rounded bg-black/5 dark:bg-white/20" x-data='<?= Json::encode(['comments' => Page::$pageData->comments]) ?>'>
            <h2 class="text-2xl font-bold">PIREP Comments</h2>
            <div class="mb-3 space-y-2 lg:space-y-1">
                <template x-for="c in comments" :key="c.id">
                    <div class="flex px-1 group hover:bg-black/20">
                        <p class="flex-1">
                            <b x-text="`${c.userName}: `"></b>
                            <span x-text="c.content"></span>
                        </p>
                        <p class="flex flex-none">
                            <small x-text="new Date(c.dateposted + 'Z').toLocaleString()" class="m-auto"></small>
                        </p>
                    </div>
                </template>
            </div>
            <form class="flex gap-1" @submit="handleComment($event, message, <?= Page::$pageData->pirep->id ?>, (v) => { comments = v; }, (v) => { message = v; })" x-data="{ message: '' }">
                <input class="flex-1 form-control" type="text" :value="message" value="" @change="message = $event.target.value" placeholder="Add Comment..." />
                <button class="px-2 py-1 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>