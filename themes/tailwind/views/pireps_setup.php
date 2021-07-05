<?php
Page::setTitle('Setup PIREPs - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="m-5 text-black dark:text-white">
    <h1 class="mb-1 text-3xl font-bold">Setup PIREPs</h1>
    <p>
        Before you can start filing PIREPs, we need to grab a bit of data from Infinite Flight.
        Please change your settings in IF so that "Show Username In-Flight" is <b>ON</b>, then start a
        quick flight so that everything syncs. Then come back here and refresh the page.
    </p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>