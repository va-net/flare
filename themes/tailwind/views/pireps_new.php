<?php
Page::setTitle('File PIREP - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="m-5">
    <h1 class="text-3xl font-bold text-black dark:text-white">File PIREP</h1>
    <p class="mb-3 text-black dark:text-white">
        Another successful flight? Great! Let's hear all about it.
        <?= Page::$pageData->user->hasPermission('admin') && !Page::$pageData->is_gold ? '<br /><small>Psst... site admins - did you know that with <a href="https://vanet.app/account/airline/billing" class="underline">VANet Gold</a> you can get ACARS to fill out most of these details for you?</small>' : '' ?>
    </p>
    <form method="post" class="space-y-2 text-black dark:text-white" id="file-pirep">
        <div class="space-y-1">
            <label for="date">Flight Date</label>
            <input id="date" name="date" type="date" value="<?= empty(Input::get('date')) ? date('Y-m-d') : Input::get('date') ?>" required class="form-control" />
        </div>
        <div class="space-y-1">
            <label for="fnum">Flight Number</label>
            <input id="fnum" name="fnum" type="text" value="<?= Input::get('fnum') ?>" required class="form-control" placeholder="123AB" />
        </div>
        <div class="space-y-1" x-data="{ minutes: '', hours: '', }">
            <label for="ftime">Flight Time</label>
            <div class="gap-3 space-y-1 md:flex md:space-y-0">
                <input id="ftime-hrs" type="number" required class="flex-1 form-control" placeholder="Hours" @change="(e) => { hours = e.target.value; }" />
                <input id="ftime-mins" type="number" required class="flex-1 form-control" placeholder="Minutes" @change="(e) => { minutes = e.target.value; }" />
            </div>
            <input type="hidden" class="hidden" name="ftime" id="ftime" :value="`${hours}:${minutes}`" value="" required />
        </div>
        <div class="space-y-1">
            <label for="fuel">Fuel Used (kg)</label>
            <input id="fuel" name="fuel" type="number" value="<?= Input::get('fuel') ?>" required class="form-control" placeholder="1234" />
        </div>
        <div class="space-y-1">
            <label for="dep">Departure ICAO</label>
            <input id="dep" name="dep" type="text" value="<?= Input::get('dep') ?>" maxlength="4" required class="form-control" placeholder="ICAO" />
        </div>
        <div class="space-y-1">
            <label for="arr">Arrival ICAO</label>
            <input id="arr" name="arr" type="text" value="<?= Input::get('arr') ?>" maxlength="4" required class="form-control" placeholder="ICAO" />
        </div>
        <div class="space-y-1">
            <label for="aircraft">Aircraft</label>
            <select id="aircraft" name="aircraft" required class="form-control">
                <option value>Select</option>
                <?php
                foreach (Page::$pageData->aircraft as $aircraft) {
                    $notes = $aircraft['notes'] == null ? '' : ' - ' . $aircraft['notes'];
                    if ($aircraft['id'] == Input::get('aircraft')) {
                        echo '<option value="' . $aircraft['id'] . '" selected>' . $aircraft['name'] . ' (' . $aircraft['liveryname'] . ')' . $notes . '</option>';
                    } else {
                        echo '<option value="' . $aircraft['id'] . '">' . $aircraft['name'] . ' (' . $aircraft['liveryname'] . ')' . $notes . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="mb-1 space-y-1">
            <label for="mutli">Multiplier Code (if applicable)</label>
            <input id="mutli" name="multi" type="text" value="<?= Input::get('multi') ?>" class="form-control" placeholder="123456" />
        </div>
    </form>
    <button type="submit" form="file-pirep" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
        File PIREP
    </button>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>