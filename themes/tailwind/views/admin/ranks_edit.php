<?php
Page::setTitle('Edit Rank - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Edit Rank</h1>
    <form method="post" class="space-y-2" id="edit-rank">
        <input type="hidden" name="id" value="<?= Page::$pageData->rank->id ?>" />
        <div class="space-y-1">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="<?= escape(Page::$pageData->rank->name) ?>" required class="form-control" />
        </div>
        <div class="space-y-1">
            <label for="time">Minimum Hours</label>
            <input id="time" name="time" type="number" value="<?= escape(Page::$pageData->rank->timereq / 3600) ?>" required class="form-control" />
        </div>
    </form>
    <button type="submit" form="edit-rank" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
        Save
    </button>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>