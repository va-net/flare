<?php
Page::setTitle('Edit Route - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script id="all-aircraft" type="application/json">
    <?= Json::encode(Page::$pageData->fleet) ?>
</script>
<script id="route-aircraft" type="application/json">
    <?= Json::encode(Page::$pageData->aircraft) ?>
</script>
<script>
    var allaircraft = JSON.parse(document.getElementById('all-aircraft').innerHTML);
    var routeaircraft = JSON.parse(document.getElementById('route-aircraft').innerHTML);
</script>

<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Edit Route</h1>
    <form method="post" x-data="{ aircraft: routeaircraft }">
        <div class="lg:flex space-y-4 lg:space-y-0 gap-4">
            <div class="flex-1 space-y-2 p-3 dark:bg-white/10 bg-gray-100 rounded">
                <h2 class="text-2xl font-bold">Route Information</h2>
                <input type="hidden" name="id" value="<?= Page::$pageData->route->id ?>" />
                <div class="space-y-1">
                    <label for="dep">Departure ICAO</label>
                    <input id="dep" name="dep" type="text" required class="form-control" value="<?= Page::$pageData->route->dep ?>" />
                </div>
                <div class="space-y-1">
                    <label for="arr">Arrival ICAO</label>
                    <input id="arr" name="arr" type="text" required class="form-control" value="<?= Page::$pageData->route->arr ?>" />
                </div>
                <div class="space-y-1">
                    <label for="fltnum">Flight Number</label>
                    <input id="fltnum" name="fltnum" type="text" required class="form-control" value="<?= Page::$pageData->route->fltnum ?>" />
                </div>
                <?php $duration = explode(':', Time::secsToString(Page::$pageData->route->duration)); ?>
                <div class="space-y-1" x-data="{ minutes: '<?= $duration[1] ?>', hours: '<?= $duration[0] ?>', }">
                    <label for="duration">Flight Time</label>
                    <div class="gap-3 space-y-1 md:flex md:space-y-0">
                        <input id="duration-hrs" type="number" required class="flex-1 form-control" :value="hours" placeholder="Hours" @input="(e) => { hours = e.target.value; }" />
                        <input id="duration-mins" type="number" required class="flex-1 form-control" :value="minutes" placeholder="Minutes" @input="(e) => { minutes = e.target.value; }" />
                    </div>
                    <input type="hidden" name="duration" id="duration" :value="`${hours}:${minutes}`" value="" required />
                </div>
                <input type="hidden" name="aircraft" :value="aircraft.map(a => a.id).join(',')" />
            </div>
            <div class="flex-1 p-3 dark:bg-white/10 bg-gray-100 rounded">
                <h2 class="text-2xl font-bold mb-2">Route Aircraft</h2>
                <ul class="list-disc mb-3 ml-5">
                    <template x-for="a in aircraft" :key="a.id">
                        <li x-text="`${a.name} (${a.liveryname})`"></li>
                    </template>
                </ul>
                <div class="space-y-1">
                    <label for="newaircraft">Add Aircraft</label>
                    <select id="newaircraft" x-ref="newaircraft" class="form-control" @change.prevent="if ($event.target.value) { aircraft.push(allaircraft.find(a => a.ifliveryid == $event.target.value)); $refs.newaircraft.value = ''; }">
                        <option value>Select</option>
                        <template x-for="a in allaircraft.filter(ac => !aircraft.includes(ac))">
                            <option :value="a.ifliveryid" x-text="`${a.name} (${a.liveryname})`"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" :disabled="aircraft.length < 1" class="button-primary text-lg font-semibold">
            Save
        </button>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>