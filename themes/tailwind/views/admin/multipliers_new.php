<?php
Page::setTitle('New Multiplier - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Create Multiplier</h1>
    <form method="post" class="space-y-2" id="create-multi">
        <div class="space-y-1">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" required class="form-control" />
        </div>
        <div class="space-y-1">
            <label for="multi">Multiplication</label>
            <input id="multi" name="multi" type="number" required class="form-control" step="0.25" />
        </div>
        <div class="space-y-1">
            <label for="minrank">Minimum Rank</label>
            <select id="minrank" name="minrank" required class="form-control">
                <option value="">None</option>
                <?php foreach (Page::$pageData->ranks as $rank) : ?>
                    <option value="<?= $rank->id ?>"><?= $rank->name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    <button type="submit" form="create-multi" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
        Save
    </button>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>