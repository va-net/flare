<?php
Page::setTitle('Import Routes - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
$uniqueaircraft = array_unique(array_column(Page::$pageData->routes, 'aircraftid'));
$aircraftoptions = [];
foreach (Page::$pageData->aircraft as $id => $name) {
    $aircraftoptions[] = '<option value="' . $id . '">' . $name . '</option>';
}
$aircraftoptions = implode("\n", $aircraftoptions);
?>
<script type="application/json" id="routes">
    <?= Json::encode(Page::$pageData->routes) ?>
</script>
<script>
    var routes = JSON.parse(document.getElementById('routes').innerHTML);
</script>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-1 text-3xl font-bold">Import Routes</h1>
    <p class="mb-4">
        So we can import everything correctly, please select the aircraft type and livery for each registration.
        These aircraft will be added with the lowest rank if they do not already exist in your VA's database.
    </p>
    <form method="post">
        <input type="hidden" name="action" value="import" />
        <input type="hidden" name="rJson" :value="JSON.stringify(routes)" value="" />
        <div class="grid grid-cols-3 gap-1 md:gap-3 mb-3">
            <?php $i = 0; ?>
            <?php foreach ($uniqueaircraft as $aircraftid) : ?>
                <div class="flex items-center">
                    <p class="font-bold text-lg"><?= $aircraftid ?></p>
                </div>
                <div class="col-span-2" x-data="{ liveries: [] }">
                    <select required class="form-control mb-2" name="aircraft<?= $i ?>" @change="fetch(`/api.php/liveries?aircraftid=${encodeURIComponent($event.target.value)}`).then(r => r.json()).then((r) => (liveries = r.result))">
                        <option value="">Aircraft Type</option>
                        <?= $aircraftoptions ?>
                    </select>
                    <select required class="form-control mb-2" name="livery<?= $i ?>" :disabled="Object.entries(liveries).length < 1">
                        <option value="">Aircraft Livery</option>
                        <template x-for="[name, id] in Object.entries(liveries)" :key="id">
                            <option :value="id" x-text="name"></option>
                        </template>
                    </select>
                </div>
                <?php $i++; ?>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="button-primary text-lg font-semibold">Import Routes</button>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>