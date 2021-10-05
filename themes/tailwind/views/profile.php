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
                <input id="email" name="email" type="email" value="<?= escape($user->email) ?>" class="form-control" />
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
        <?php if (!empty(Page::$pageData->user->data()->password)) : ?>
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
        <?php else : ?>
            <p class="font-semibold text-red-500">You are using VANet to log in. A password is not required, nor is one stored.</p>
        <?php endif; ?>
    </div>
    <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
        <h2 class="mb-2 text-2xl font-bold">Your Awards</h2>
        <?php if (count(Page::$pageData->awards) < 1) : ?>
            <p>None Yet!</p>
        <?php else : ?>
            <ul>
                <?php foreach (Page::$pageData->awards as $a) : ?>
                    <li>
                        <img src="<?= $a->imageurl ?>" style="height: 25px; width: auto; display: inline;" />
                        <?= $a->name ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
        <h2 class="mb-1 text-2xl font-bold">VANet Account</h2>
        <p class="mb-3">
            <?= Page::$pageData->va_name ?> partners with
            <a href="https://vanet.app" target="_blank" class="font-semibold hover:underline">VANet</a>
            for various services. You can now link your VANet account to Flare in order to log in simply
            and more securely.
        </p>
        <?php if (Page::$pageData->user->data()->vanet_id && !empty(Page::$pageData->user->data()->email)) : ?>
            <p>
                <span class="font-bold">You VANet account is linked.</span>
                You can now choose to delete all sensitive data from this Crew Center.
                This will mean that you must use VANet to log in instead of your email
                and password.
            </p>
            <form method="post" action="/home">
                <input type="hidden" class="hidden" name="action" value="delsensitive" />
                <button type="submit" class="px-3 py-2 mt-3 rounded-md shadow-md bg-red-500 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                    Delete sensitive data
                </button>
            </form>
        <?php elseif (Page::$pageData->user->data()->vanet_id && empty(Page::$pageData->user->data()->email)) : ?>
            <p>
                <span class="font-bold">You VANet account is linked and no sensitive data is stored in our database.</span>
                You must use VANet to log in instead of your email and password.
            </p>
        <?php else : ?>
            <p>
                <span class="font-bold">You VANet account is not linked.</span>
                You can now choose to link your VANet account to Flare in order to log in simply
                and more securely.
            </p>
            <a href="/oauth/login" class="button-primary inline-block">Link VANet</a>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>