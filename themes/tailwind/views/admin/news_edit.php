<?php
Page::setTitle('Edit News - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Edit News</h1>
    <form method="post" class="space-y-2" id="edit-news">
        <input type="hidden" class="hidden" name="id" value="<?= Page::$pageData->article->id ?>" />
        <div class="space-y-1">
            <label for="dateposted">Date Posted</label>
            <input id="dateposted" name="dateposted" type="date" disabled value="<?= (new DateTime(Page::$pageData->article->dateposted))->format('Y-m-d') ?>" required class="form-control" />
        </div>
        <div class="space-y-1">
            <label for="author">Author</label>
            <input id="author" name="author" type="text" value="<?= escape(Page::$pageData->article->author) ?>" disabled class="form-control" />
        </div>
        <div class="space-y-1">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" value="<?= escape(Page::$pageData->article->subject) ?>" required class="form-control" />
        </div>
        <div class="space-y-1">
            <label for="news-content">Content</label>
            <textarea rows="2" id="news-content" name="content" required class="form-control" @keydown="resizeTextarea($el)" x-init="resizeTextarea($el)"><?= escape(Page::$pageData->article->content) ?></textarea>
        </div>
    </form>
    <button type="submit" form="edit-news" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
        Save
    </button>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>