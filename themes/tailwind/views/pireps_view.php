<?php
Page::setTitle(Page::$pageData->pirep->departure . ' to ' . Page::$pageData->pirep->arrival . ' - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
$disabled = Page::$pageData->user->hasPermission('pirepmanage') ? 'disabled' : 'disabled';
?>
<div id="content" class="m-5 text-black dark:text-white">
    <h1 class="text-3xl font-bold">Edit PIREP #<?= Page::$pageData->pirep->id ?></h1>

    <form action="/pireps" method="post" class="space-y-2" id="edit-pirep">
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
                <input id="ftime-hrs" type="number" :value="hours" <?= $disabled ?> class="flex-1 form-control" placeholder="Hours" @change="(e) => { hours = e.target.value; }" />
                <input id="ftime-mins" type="number" :value="minutes" <?= $disabled ?> class="flex-1 form-control" placeholder="Minutes" @change="(e) => { minutes = e.target.value; }" />
            </div>
            <input type="hidden" class="hidden" <?= $disabled ?> id="ftime" name="ftime" :value="`${hours}:${minutes}`" value="" />
        </div>
        <div class="space-y-1">
            <label for="dep">Departure ICAO</label>
            <input id="dep" name="dep" type="text" value="<?= Page::$pageData->pirep->departure ?>" maxlength="4" required class="form-control" placeholder="KLAX" />
        </div>
        <div class="space-y-1">
            <label for="arr">Arrival ICAO</label>
            <input id="arr" name="arr" type="text" value="<?= Page::$pageData->pirep->arrival ?>" maxlength="4" required class="form-control" placeholder="KJFK" />
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
            <input id="mutli" name="multi" type="text" value="<?= Page::$pageData->pirep->multi ?>" disabled class="form-control" placeholder="123456" />
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
<?php require_once __DIR__ . '/../includes/footer.php'; ?>