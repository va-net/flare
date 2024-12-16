<?php
Page::setTitle('Add Route - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script id="all-aircraft" type="application/json">
    <?= Json::encode(Page::$pageData->aircraft) ?>
</script>
<script>
    var allaircraft = JSON.parse(document.getElementById('all-aircraft').innerHTML);
</script>

<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Add Route</h1>
    <form method="post" id="add-route" x-data="{ aircraft: [] }">
        <div class="lg:flex space-y-4 lg:space-y-0 gap-4">
            <div class="flex-1 space-y-2 p-3 dark:bg-white/10 bg-gray-100 rounded">
                <h2 class="text-2xl font-bold">Route Information</h2>
                <div class="space-y-1">
                    <label for="dep">Departure ICAO</label>
                    <input id="dep" name="dep" type="text" required class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="arr">Arrival ICAO</label>
                    <input id="arr" name="arr" type="text" required class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="fltnum">Flight Number</label>
                    <input id="fltnum" name="fltnum" type="text" required class="form-control" />
                </div>
                <div class="space-y-1" x-data="{ minutes: '', hours: '', }">
                    <label for="duration">Flight Time</label>
                    <div class="gap-3 space-y-1 md:flex md:space-y-0">
                        <input id="duration-hrs" type="number" required class="flex-1 form-control" placeholder="Hours" @input="(e) => { hours = e.target.value; }" />
                        <input id="duration-mins" type="number" required class="flex-1 form-control" placeholder="Minutes" @input="(e) => { minutes = e.target.value; }" />
                    </div>
                    <input type="hidden" name="duration" id="duration" :value="`${hours}:${minutes}`" value="" required />
                </div>
                <div class="space-y-1">
                    <label for="notes">Notes</label>
                    <input id="notes" name="notes" type="text" class="form-control" />
                </div>
                <input type="hidden" name="aircraft" :value="aircraft.map(a => a.id).join(',')" />
            </div>
            <div class="flex-1 p-3 dark:bg-white/10 bg-gray-100 rounded">
                <h2 class="text-2xl font-bold mb-2">Route Aircraft</h2>
                <ul class="mb-3 space-y-0.5">
                    <template x-for="a in aircraft" :key="a.id">
                        <li class="flex items-center group">
                            <span class="flex-1" x-text="`${a.name} (${a.liveryname})`"></span>
                            <span class="invisible group-hover:visible flex-none text-gray-400 cursor-pointer" title="Remove Aircraft" @click="aircraft = aircraft.filter(ac => ac.id != a.id)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </li>
                    </template>
                </ul>
                <select class="form-control" @change.prevent="if ($event.target.value) { aircraft.push(allaircraft.find(a => a.ifliveryid == $event.target.value)); $el.value = ''; }">
                    <option value>Add Aircraft</option>
                    <template x-for="a in allaircraft.filter(ac => !aircraft.some(a => a.id == ac.id))">
                        <option :value="a.ifliveryid" x-text="`${a.name} (${a.liveryname})${a.notes ? ' - ' + a.notes : ''}`"></option>
                    </template>
                </select>
            </div>
        </div>
        <button type="submit" :disabled="aircraft.length < 1" class="button-primary">
            Save
        </button>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>