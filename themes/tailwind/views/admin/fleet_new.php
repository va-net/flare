<?php
Page::setTitle('Add Aircraft - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Add Aircraft</h1>
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
            <select required name="rank" id="rank" class="form-control">
                <option value>Select</option>
                <?php foreach (Page::$pageData->ranks as $rank) : ?>
                    <option value="<?= $rank->id ?>"><?= $rank->name ?></option>
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