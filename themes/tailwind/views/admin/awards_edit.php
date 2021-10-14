<?php
Page::setTitle('Edit Award - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script id="recipients" type="application/json">
    <?= Json::encode(array_map(function ($x) {
        unset($x->password);
        return $x;
    }, Page::$pageData->recipients)) ?>
</script>
<script id="users" type="application/json">
    <?= Json::encode(array_map(function ($x) {
        unset($x->password);
        return $x;
    }, Page::$pageData->users)) ?>
</script>
<script>
    var recips = JSON.parse(document.getElementById('recipients').innerHTML);
    var users = JSON.parse(document.getElementById('users').innerHTML);
</script>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-3 text-3xl font-bold">Edit Award</h1>
    <div class="lg:flex space-y-4 lg:space-y-0 gap-4">
        <div class="flex-1 space-y-2 p-3 dark:bg-white/10 bg-gray-100 rounded">
            <h2 class="text-2xl font-bold">Award Information</h2>
            <form method="post" class="space-y-2" id="edit-award">
                <input type="hidden" name="id" value="<?= Page::$pageData->award->id ?>" />
                <div class="space-y-1">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" required class="form-control" value="<?= Page::$pageData->award->name ?>" />
                </div>
                <div class="space-y-1">
                    <label for="description">Description</label>
                    <input id="description" name="description" type="text" class="form-control" value="<?= Page::$pageData->award->description ?>" />
                </div>
                <div class="space-y-1">
                    <label for="image">Image URL</label>
                    <input id="image" name="image" type="url" required class="form-control" value="<?= Page::$pageData->award->imageurl ?>" />
                </div>
            </form>
            <button type="submit" form="edit-award" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                Save
            </button>
        </div>
        <div class="flex-1 space-y-2 p-3 dark:bg-white/10 bg-gray-100 rounded" x-data="{ recipients: recips }" x-init="recipients = JSON.parse(document.getElementById('recipients').innerHTML)">
            <h2 class="text-2xl font-bold mb-2">Award Recipients</h2>
            <ul class="mb-3 space-y-0.5">
                <template x-for="u in recipients" :key="u.id">
                    <li class="flex items-center group">
                        <span class="flex-1" x-text="u.name"></span>
                        <span class="invisible group-hover:visible flex-none text-gray-400 cursor-pointer" title="Remove Award" @click="fetch(`/api.php/users/${u.id}/awards/<?= Page::$pageData->award->id ?>`, { method: 'DELETE' }); recipients = recipients.filter(r => r.id != u.id);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                </template>
            </ul>
            <select class="form-control" @change.prevent="if ($event.target.value) { fetch(`/api.php/users/${$event.target.value}/awards/<?= Page::$pageData->award->id ?>`, { method: 'POST' }); recipients.push(users.find(u => u.id == $event.target.value)); $el.value = ''; }">
                <option value>Give Award</option>
                <template x-for="u in users.filter(u => !recipients.some(r => r.id == u.id))" :key="u.id">
                    <option :value="u.id" x-text="u.name"></option>
                </template>
            </select>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>