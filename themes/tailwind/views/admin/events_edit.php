<?php
Page::setTitle('Edit Event - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
$date = explode('T', Page::$pageData->event['date'])[0];
$time = str_replace(':', '', substr(explode('T', Page::$pageData->event['date'])[1], 0, 5));
?>
<script type="application/json" id="gates">
    <?= Json::encode(Page::$pageData->event['slots']) ?>
</script>

<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Edit Event</h1>
    <div x-data="{ gates: JSON.parse(document.getElementById('gates').innerHTML) }">
        <div class="lg:flex space-y-4 lg:space-y-0 gap-4">
            <form method="post" id="edit-event" class="flex-1 space-y-2 p-3 dark:bg-white/10 bg-gray-100 rounded">
                <h2 class="text-2xl font-bold">Event Information</h2>
                <input type="hidden" name="id" value="<?= Page::$pageData->event['id'] ?>" />
                <div class="space-y-1">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" required class="form-control" value="<?= Page::$pageData->event['name'] ?>" />
                </div>
                <div class="space-y-1">
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" required class="form-control" value="<?= $date ?>" />
                </div>
                <div class="space-y-1">
                    <label for="time">Time (UTC)</label>
                    <select id="time" name="time" required class="form-control" x-init="$el.value = '<?= $time ?>'">
                        <option value="">Select</option>
                        <?php
                        $times = [
                            "00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17",
                            "18", "19", "20", "21", "22", "23"
                        ];
                        foreach ($times as $t) {
                            echo '<option value="' . $t . '00' . '">' . $t . '00Z</option>';
                            echo '<option value="' . $t . '30' . '">' . $t . '30Z</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required class="form-control" rows="3" @input="resizeTextarea($el)"><?= escape(Page::$pageData->event['description']) ?></textarea>
                </div>
                <div class="space-y-1">
                    <label for="dep">Departure ICAO</label>
                    <input id="dep" name="dep" type="text" required class="form-control" value="<?= Page::$pageData->event['departureIcao'] ?>" />
                </div>
                <div class="space-y-1">
                    <label for="arr">Arrival ICAO</label>
                    <input id="arr" name="arr" type="text" required class="form-control" value="<?= Page::$pageData->event['arrivalIcao'] ?>" />
                </div>
                <div class="space-y-1">
                    <label for="aircraft">Aircraft</label>
                    <select id="aircraft" name="aircraft" required class="form-control" x-init="$el.value = '<?= Page::$pageData->event['aircraft']['liveryID'] ?>'">
                        <option value>Select</option>
                        <?php
                        foreach (Page::$pageData->fleet as $aircraft) {
                            echo '<option value="' . $aircraft->ifliveryid . '">' . $aircraft->name . ' (' . $aircraft->liveryname . ')</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="server">Server</label>
                    <select id="server" name="server" required class="form-control" x-init="$el.value = '<?= Page::$pageData->event['server'] ?>'">
                        <option value>Select</option>
                        <option value="casual">Casual Server</option>
                        <option value="training">Training Server</option>
                        <option value="expert">Expert Server</option>
                    </select>
                </div>
            </form>
            <div class="flex-1 p-3 dark:bg-white/10 bg-gray-100 rounded">
                <h2 class="text-2xl font-bold mb-2">Event Gates</h2>
                <ul class="list-disc mb-3 ml-5">
                    <template x-for="g in gates" :key="g.id">
                        <li x-text="g.gate"></li>
                    </template>
                </ul>
            </div>
        </div>
        <button type="submit" :disabled="aircraft.length < 1" class="button-primary text-lg font-semibold" form="edit-event">
            Save
        </button>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>