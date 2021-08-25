<?php
Page::setTitle('ACARS - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="m-5 text-black dark:text-white">
    <h1 class="text-3xl font-bold">ACARS</h1>
    <p class="mb-2">
        Nice! We've found you. If you've finished your flight
        and at the gate, go ahead and confirm the details below.
        If not, reload the page once you're done.
    </p>
    <form action="/pireps/new" method="post" class="space-y-2" id="acars-confirm">
        <div class="space-y-1">
            <label for="date">Flight Date</label>
            <input id="date" name="date" type="date" value="<?= date('Y-m-d') ?>" readonly class="form-control" />
        </div>
        <div class="space-y-1">
            <label for="fnum">Flight Number</label>
            <input id="fnum" name="fnum" type="text" required class="form-control" placeholder="123AB" />
        </div>
        <?php $ftime = explode(':', Time::secsToString(Page::$pageData->acars['result']['flightTime'])); ?>
        <div class="space-y-1" x-data="{ minutes: '<?= $ftime[1] ?>', hours: '<?= $ftime[0] ?>', }">
            <label for="ftime">Flight Time</label>
            <div class="gap-3 space-y-1 md:flex md:space-y-0">
                <input id="ftime-hrs" type="number" readonly class="flex-1 form-control" :value="hours" value="" placeholder="Hours" @change="(e) => { hours = e.target.value; }" />
                <input id="ftime-mins" type="number" readonly class="flex-1 form-control" :value="minutes" value="" placeholder="Minutes" @change="(e) => { minutes = e.target.value; }" />
            </div>
            <input type="hidden" class="hidden" name="ftime" id="ftime" :value="`${hours}:${minutes}`" value="" required />
        </div>
        <div class="space-y-1">
            <label for="fuel">Fuel Used (kg)</label>
            <input id="fuel" name="fuel" type="number" value="<?= Input::get('fuel') ?>" required class="form-control" placeholder="1234" />
        </div>
        <?php $dep = Page::$pageData->acars['result']['departure'] == null ? 'required' : 'readonly'; ?>
        <div class="space-y-1">
            <label for="dep">Departure ICAO</label>
            <input id="dep" name="dep" type="text" value="<?= Page::$pageData->acars['result']['departure'] ?>" maxlength="4" <?= $dep ?> class="form-control" placeholder="Please specify" />
        </div>
        <?php $arr = Page::$pageData->acars['result']['arrival'] == null ? 'required' : 'readonly'; ?>
        <div class="space-y-1">
            <label for="arr">Arrival ICAO</label>
            <input id="arr" name="arr" type="text" value="<?= Page::$pageData->acars['result']['arrival'] ?>" maxlength="4" <?= $arr ?> class="form-control" placeholder="Please specify" />
        </div>
        <div class="space-y-1">
            <label for="aircraft">Aircraft</label>
            <input name="aircraft" type="hidden" value="<?= Page::$pageData->aircraft->id ?>" class="hidden" />
            <input id="aircraft" type="text" value="<?= Page::$pageData->aircraft->name ?> - <?= Page::$pageData->aircraft->liveryname ?>" readonly class="form-control" />
        </div>
        <div class="mb-1 space-y-1">
            <label for="mutli">Multiplier Code (if applicable)</label>
            <input id="mutli" name="multi" type="text" value="<?= Input::get('multi') ?>" class="form-control" placeholder="123456" />
        </div>
    </form>
    <button type="submit" form="acars-confirm" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
        File PIREP
    </button>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>