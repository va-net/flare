<?php
Page::setTitle('New Award - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Create Award</h1>
    <form method="post" class="space-y-2" id="create-award">
        <div class="space-y-1">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" required class="form-control" />
        </div>
        <div class="space-y-1">
            <label for="description">Description</label>
            <input id="description" name="description" type="text" class="form-control" />
        </div>
        <div class="space-y-1">
            <label for="image">Image URL</label>
            <input id="image" name="image" type="url" required class="form-control" />
        </div>
    </form>
    <button type="submit" form="create-award" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
        Save
    </button>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>