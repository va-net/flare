<?php
Page::setTitle('Profile - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
$user = Page::$pageData->user->data();
?>
<div id="content" class="grid-cols-2 gap-4 m-5 space-y-4 text-black md:grid md:space-y-0 dark:text-white">
    <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
        <h2 class="text-2xl font-bold">Edit Profile</h2>
        <form method="post" class="space-y-2" id="edit-profile">
            <div class="space-y-1">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="<?= escape($user->name) ?>" required class="form-control" />
            </div>
            <div class="space-y-1">
                <label for="name">Callsign</label>
                <input id="callsign" name="callsign" type="text" value="<?= escape($user->callsign) ?>" required class="form-control" />
            </div>
            <div class="space-y-1">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?= escape($user->email) ?>" required class="form-control" />
            </div>
            <div class="space-y-1">
                <label for="ifc">IFC URL</label>
                <input id="ifc" name="ifc" type="url" value="<?= escape($user->ifc) ?>" required class="form-control" />
            </div>
        </form>
        <button type="submit" form="edit-profile" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
            Save
        </button>
    </div>
    <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
        <h2 class="text-2xl font-bold">Change Password</h2>
        <form method="post" action="/home" class="space-y-2" id="change-password">
            <input type="hidden" class="hidden" name="action" value="changepass" />
            <div class="space-y-1">
                <label for="oldpass">Old Password</label>
                <input id="oldpass" name="oldpass" type="password" required class="form-control" />
            </div>
            <div class="space-y-1">
                <label for="newpass">New Password</label>
                <input id="newpass" name="newpass" type="password" minlength="8" required class="form-control" />
            </div>
            <div class="space-y-1">
                <label for="confpass">Confirm New Password</label>
                <input id="confpass" name="confpass" type="password" minlength="8" required class="form-control" />
            </div>
        </form>
        <button type="submit" form="change-password" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
            Save
        </button>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>