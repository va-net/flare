<?php
Page::setTitle('Manage Awards - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<div id="content" class="text-black dark:text-white">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Manage Awards
        </h2>
        <a href="/admin/awards/new" class="inline-block px-3 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
            Create Award
        </a>
    </div>
    <form method="post" x-ref="delaward">
        <input type="hidden" name="action" value="delaward" />
        <input type="hidden" name="id" value="" x-ref="delaward-id" id="delaward-id" />
    </form>

    <div class="md:px-5 px-2 max-w-full">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th class="hidden md:table-cell">Image</th>
                        <th>Name</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Page::$pageData->awards as $a) : ?>
                        <tr class="hover:bg-black/20 cursor-pointer" @click="window.location.href = '/admin/awards/<?= $a->id ?>'">
                            <td class="hidden md:table-cell">
                                <img src="<?= $a->imageurl ?>" style="height: 25px; width: auto;" />
                            </td>
                            <td>
                                <?= $a->name ?>
                            </td>
                            <td class="text-right">
                                <button @click.stop="confirm('Are you sure you want to delete this award?') && (() => { $refs['delaward-id'].value = '<?= $a->id ?>'; $refs.delaward.submit(); })" class="px-2 py-1 text-lg font-semibold rounded-md shadow-md hover:shadow-lg bg-red-600 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>