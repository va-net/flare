<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/assets/tailwind.style.css.php" />
    <link rel="stylesheet" href="/assets/tailwind.index.css" />
    <link rel="stylesheet" href="/assets/custom.css" />
    <link rel="stylesheet" href="/assets/fontawesome.min.css" />
    <title>Apply - <?= Page::$pageData->va_name ?></title>
    <script src="/assets/js/tailwind.js"></script>
</head>

<body>
    <div class="flex justify-center min-h-screen px-4 py-12 bg-gray-100 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl">
            <div class="mb-8">
                <h3 class="text-xl font-bold text-center text-gray-500"><?= Page::$pageData->va_name ?></h3>
                <h2 class="text-4xl font-bold text-center text-gray-900">
                    Apply for an account
                </h2>
            </div>
            <form class="block p-6 space-y-4 bg-white border border-gray-200 rounded-lg shadow-lg" action="/apply" method="POST">
                <p class="text-center">
                    Hey <?= escape(Page::$pageData->apply_data['name']) ?>! We were able to fetch some account details from VANet.
                    Please fill out the rest of the details required for your application.
                </p>
                <?php if (Session::exists('success')) : ?>
                    <p class="text-sm font-bold text-center text-green-600"><?= Session::flash('success') ?></p>
                <?php elseif (Session::exists('error')) : ?>
                    <p class="text-sm font-bold text-center text-red-500"><?= Session::flash('error') ?></p>
                <?php endif; ?>
                <input type="hidden" name="token" value="<?= Token::generate() ?>" />
                <input type="hidden" name="name" value="<?= escape(Page::$pageData->apply_data['name']) ?>">
                <div>
                    <label for="ifc-url" class="block mb-1 font-semibold text-gray-700">IFC Profile URL</label>
                    <input id="ifc-url" name="ifc" type="url" required class="relative block w-full px-3 py-2 text-gray-900 placeholder-gray-400 border-gray-400 rounded appearance-none focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="https://community.infiniteflight.com/u/John_Doe">
                </div>
                <div>
                    <label for="req-callsign" class="block mb-1 font-semibold text-gray-700">Request Callsign</label>
                    <input id="req-callsign" name="callsign" type="text" required class="relative block w-full px-3 py-2 text-gray-900 placeholder-gray-400 border-gray-400 rounded appearance-none focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="<?= Page::$pageData->callsign ?>">
                </div>
                <div>
                    <label for="vio-land-ratio" class="block mb-1 font-semibold text-gray-700">Violations to Landings Ratio</label>
                    <input id="vio-land-ratio" name="violand" type="number" step="0.01" min="0" max="1" required class="relative block w-full px-3 py-2 text-gray-900 placeholder-gray-400 border-gray-400 rounded appearance-none focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="0.02">
                </div>
                <div>
                    <label for="if-grade" class="block mb-1 font-semibold text-gray-700">Grade</label>
                    <select id="if-grade" name="grade" class="block w-full px-3 py-2 mt-1 text-gray-900 bg-white border border-gray-400 rounded focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
                        <option value>Select</option>
                        <?php foreach (range(1, 5) as $i) : ?>
                            <option value="<?= $i ?>" <?= (Input::get('grade') == $i) ? 'selected' : '' ?>>Grade <?= $i ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="relative flex justify-center w-full px-4 py-2 text-sm font-medium border border-transparent rounded-md text-primary-text bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 hover:shadow-lg">
                        Apply
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://unpkg.com/alpinejs@3.0.1/dist/cdn.min.js" defer></script>
</body>

</html>