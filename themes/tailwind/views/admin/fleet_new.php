<?php
Page::setTitle('Add Aircraft - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Add Aircraft</h1>
    <div class="flex gap-3 mb-3 items-center p-2 rounded bg-blue-500 dark:bg-blue-600 border-primary text-primary-text text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="flex-1">
            Aircraft can now be assigned a minimum rank and/or an award that is required for a pilot to be able to fly it. If both options are selected, the pilot can have the award OR the minimum rank to fly the aircraft. If no options are selected, the aircraft is not available to pilots.
        </p>
    </div>
    <form method="post" class="space-y-2" id="add-aircraft" x-data="{ liveries: {} }">
        <div class="space-y-1">
            <label for="type">Aircraft Type</label>
            <select required name="type" id="type" class="form-control" @change="liveries = await fetchLiveries($event.target.value)">
                <option value>Select</option>
                <?php foreach (Page::$pageData->types as $id => $name) : ?>
                    <option value="<?= $id ?>"><?= $name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="space-y-1">
            <label for="livery">Aircraft Livery</label>
            <select required name="livery" id="livery" class="form-control">
                <template x-for="[name, id] in Object.entries(liveries)">
                    <option :value="id" x-text="name"></option>
                </template>
            </select>
        </div>
        <div class="space-y-1">
            <label for="rank">Minimum Rank</label>
            <select name="rank" id="rank" class="form-control">
                <option value>None</option>
                <?php foreach (Page::$pageData->ranks as $rank) : ?>
                    <option value="<?= $rank->id ?>"><?= $rank->name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="space-y-1">
            <label for="award">Award</label>
            <select name="award" id="award" class="form-control">
                <option value>None</option>
                <?php foreach (Page::$pageData->awards as $award) : ?>
                    <option value="<?= $award->id ?>"><?= $award->name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="space-y-1">
            <label for="notes">Notes</label>
            <input name="notes" id="notes" class="form-control" type="text" />
        </div>
    </form>
    <button type="submit" form="add-aircraft" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
        Save
    </button>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>