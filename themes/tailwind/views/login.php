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
    <title>Log In - <?= Page::$pageData->va_name ?></title>
    <script src="/assets/js/tailwind.js"></script>
</head>

<body>
    <div class="min-h-screen flex justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="mb-8">
                <h3 class="text-xl text-center font-bold text-gray-500"><?= Page::$pageData->va_name ?></h3>
                <h2 class="text-center text-4xl font-bold text-gray-900">
                    Sign in to your account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Or
                    <a href="/apply" class="font-semibold text-primary hover:underline">
                        apply now
                    </a>
                </p>
            </div>
            <form class="space-y-6 p-6 bg-white border-gray-200 border rounded-lg shadow-lg block" action="#" method="POST">
                <?php if (Session::exists('success')) : ?>
                    <p class="text-green-600 font-bold text-center text-sm"><?= Session::flash('success') ?></p>
                <?php elseif (Session::exists('error')) : ?>
                    <p class="text-red-500 font-bold text-center text-sm"><?= Session::flash('error') ?></p>
                <?php endif; ?>
                <input hidden type="hidden" name="action" value="authenticate" />
                <input hidden type="hidden" name="token" value="<?= Token::generate() ?>" />
                <div class="mb-3">
                    <label for="email-address" class="block font-semibold text-gray-700 mb-1">Email address</label>
                    <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded relative block w-full px-3 py-2 border-gray-400 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
                </div>
                <div>
                    <label for="password" class="block font-semibold text-gray-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded relative block w-full px-3 py-2 border-gray-400 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
                </div>

                <div>
                    <button type="submit" class="relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-primary-text bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 hover:shadow-lg">
                        Sign in
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://unpkg.com/alpinejs@3.0.1/dist/cdn.min.js" defer></script>
</body>

</html>