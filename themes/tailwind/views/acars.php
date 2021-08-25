<?php
Page::setTitle('ACARS - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="m-5 text-black dark:text-white">
    <h1 class="text-3xl font-bold">ACARS</h1>
    <p class="mb-2">
        ACARS can fill out many details of your PIREP by pulling them from Infinite Flight before you despawn.
        First, fly your flight under your <?= Page::$pageData->va_name ?> callsign -
        <?= Page::$pageData->user->data()->callsign ?>. Then, once you're at the gate but not despawned, come
        back here and click the button below. The System will automatically grab your flight details, validate them,
        prompt you for the missing information, then file the PIREP.
    </p>
    <form method="post" class="space-y-3">
        <?php if (Page::$pageData->server == 0 || Page::$pageData->server == 'casual') : ?>
            <div class="space-y-1">
                <label for="server">Server</label>
                <select id="server" name="server" required class="form-control">
                    <option value>Select</option>
                    <?php foreach (['training', 'expert'] as $s) : ?>
                        <option value="<?= $s ?>"><?= ucfirst($s) ?> Server</option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php else : ?>
            <input type="hidden" class="hidden" name="server" value="<?= Page::$pageData->server ?>" />
        <?php endif; ?>
        <button type="submit" class="px-3 py-2 text-xl font-semibold rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
            Run ACARS
        </button>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>