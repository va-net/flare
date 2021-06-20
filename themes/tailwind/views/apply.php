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
    <div class="min-h-screen flex justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl w-full">
            <div class="mb-8">
                <h3 class="text-xl text-center font-bold text-gray-500"><?= Page::$pageData->va_name ?></h3>
                <h2 class="text-center text-4xl font-bold text-gray-900">
                    Apply for an account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Or
                    <a href="/login" class="font-semibold text-primary hover:underline">
                        log in
                    </a>
                </p>
            </div>
            <form class="space-y-4 p-6 bg-white border-gray-200 border rounded-lg shadow-lg block" action="#" method="POST">
                <?php if (Session::exists('success')) : ?>
                    <p class="text-green-600 font-bold text-center text-sm"><?= Session::flash('success') ?></p>
                <?php elseif (Session::exists('error')) : ?>
                    <p class="text-red-500 font-bold text-center text-sm"><?= Session::flash('error') ?></p>
                <?php endif; ?>
                <input hidden type="hidden" name="token" value="<?= Token::generate() ?>" />
                <div>
                    <label for="user-name" class="block font-semibold text-gray-700 mb-1">Name</label>
                    <input id="user-name" name="name" type="text" autocomplete="given-name" required class="appearance-none rounded relative block w-full px-3 py-2 border-gray-400 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm placeholder-gray-400" placeholder="John Doe">
                </div>
                <div>
                    <label for="email-address" class="block font-semibold text-gray-700 mb-1">Email address</label>
                    <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded relative block w-full px-3 py-2 border-gray-400 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm placeholder-gray-400" placeholder="john.doe@example.com">
                </div>
                <div>
                    <label for="ifc-url" class="block font-semibold text-gray-700 mb-1">IFC Profile URL</label>
                    <input id="ifc-url" name="ifc" type="url" required class="appearance-none rounded relative block w-full px-3 py-2 border-gray-400 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm placeholder-gray-400" placeholder="https://community.infiniteflight.com/u/John_Doe">
                </div>
                <div>
                    <label for="req-callsign" class="block font-semibold text-gray-700 mb-1">Request Callsign</label>
                    <input id="req-callsign" name="callsign" type="text" required class="appearance-none rounded relative block w-full px-3 py-2 border-gray-400 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm placeholder-gray-400">
                </div>
                <div>
                    <label for="vio-land-ratio" class="block font-semibold text-gray-700 mb-1">Violations to Landings Ratio</label>
                    <input id="vio-land-ratio" name="violand" type="number" step="0.01" min="0" max="1" required class="appearance-none rounded relative block w-full px-3 py-2 border-gray-400 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm placeholder-gray-400" placeholder="0.02">
                </div>
                <div>
                    <label for="if-grade" class="block font-semibold text-gray-700 mb-1">Grade</label>
                    <select id="if-grade" name="grade" class="mt-1 block w-full py-2 px-3 border border-gray-400 bg-white text-gray-900 rounded focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
                        <option value>Select</option>
                        <?php foreach (range(1, 5) as $i) : ?>
                            <option value="<?= $i ?>" <?= (Input::get('grade') == $i) ? 'selected' : '' ?>>Grade <?= $i ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="password" class="block font-semibold text-gray-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded relative block w-full px-3 py-2 border-gray-400 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm placeholder-gray-400">
                </div>
                <div>
                    <label for="password-confirm" class="block font-semibold text-gray-700 mb-1">Confirm Password</label>
                    <input id="password-confirm" name="password-repeat" type="password" required class="appearance-none rounded relative block w-full px-3 py-2 border-gray-400 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm placeholder-gray-400">
                </div>
                <div>
                    <button type="submit" class="relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-primary-text bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 hover:shadow-lg">
                        Apply
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://unpkg.com/alpinejs@3.0.1/dist/cdn.min.js" defer></script>
</body>

</html>