<?php
Page::setTitle('Import Routes - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
?>
<script>
    var uploadicon = '<svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z" /></svg>';
    var checkmark = '<svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
</script>
<div id="content" class="text-black dark:text-white m-5">
    <h1 class="mb-1 text-3xl font-bold">Import Routes</h1>
    <p class="mb-4">
        This page allows you to import routes from a CSV file in the phpVMS format. A template can be found <a class="underline" href="https://drive.google.com/file/d/1EJQYOQVLymL8Jvn5y-MS-lVTb32Sn90D/view?usp=sharing" target="_blank">here</a> -
        just note that code, route, flightlevel, distance, deptime, arrtime, price, flighttype, daysofweek, enabled, and weeks1-4 are all ignored but cannot be removed.
    </p>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="choose" />
        <label for="routes-upload" class="relative cursor-pointer rounded-md" x-data="{ fileName: null }">
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                <div class="space-y-1 text-center">
                    <div x-html="uploadicon" x-ref="formicon" x-init="$watch('fileName', (v, ov) => ov === null && v !== null && spinAndChange($refs.formicon, checkmark))"></div>
                    <div class="flex text-sm">
                        <p x-text="fileName === null ? 'Click to upload' : `${fileName}`" class="flex-1 text-center">Click to upload</p>
                        <input required id="routes-upload" name="routes-upload" type="file" accept=".csv" class="sr-only" @change="fileName = $event.target.value.split('\\').pop()" />
                    </div>
                    <p class="text-xs" x-text="fileName === null ? 'CSV files only' : 'Wrong file? Click to select a different one.'">
                        CSV files only
                    </p>
                </div>
            </div>
        </label>
        <button type="submit" class="button-primary text-lg font-semibold">Import</button>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>