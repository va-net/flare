<?php
Page::setTitle('New Rank - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Create Rank</h1>
    <form method="post" class="space-y-2" id="create-rank">
        <div class="space-y-1">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" required class="form-control" />
        </div>
        <div class="space-y-1">
            <label for="time">Minimum Hours</label>
            <input id="time" name="time" type="number" required class="form-control" />
        </div>
    </form>
    <button type="submit" form="create-rank" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
        Save
    </button>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>