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
    <div class="flex justify-center min-h-screen px-4 py-12 bg-gray-100 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="mb-8">
                <h3 class="text-xl font-bold text-center text-gray-500"><?= Page::$pageData->va_name ?></h3>
                <h2 class="text-4xl font-bold text-center text-gray-900">
                    Sign in to your account
                </h2>
                <p class="mt-2 text-sm text-center text-gray-600">
                    Or
                    <a href="/apply" class="font-semibold text-primary hover:underline">
                        apply now
                    </a>
                </p>
            </div>
            <form class="block p-6 space-y-6 bg-white border border-gray-200 rounded-lg shadow-lg" action="#" method="POST">
                <?php if (Session::exists('success')) : ?>
                    <p class="text-sm font-bold text-center text-green-600"><?= Session::flash('success') ?></p>
                <?php elseif (Session::exists('error')) : ?>
                    <p class="text-sm font-bold text-center text-red-500"><?= Session::flash('error') ?></p>
                <?php endif; ?>
                <input hidden type="hidden" name="action" value="authenticate" />
                <input hidden type="hidden" name="token" value="<?= Token::generate() ?>" />
                <div class="mb-3">
                    <label for="email-address" class="block mb-1 font-semibold text-gray-700">Email address</label>
                    <input id="email-address" name="email" type="email" autocomplete="email" required class="relative block w-full px-3 py-2 text-gray-900 border-gray-400 rounded appearance-none focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
                </div>
                <div>
                    <label for="password" class="block mb-1 font-semibold text-gray-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="relative block w-full px-3 py-2 text-gray-900 border-gray-400 rounded appearance-none focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm">
                </div>

                <div>
                    <button type="submit" class="relative flex justify-center w-full px-4 py-2 text-sm font-medium border border-transparent rounded-md text-primary-text bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 hover:shadow-lg">
                        Sign in
                    </button>
                </div>

                <?php if (isset(Page::$pageData->vanet_signin) && Page::$pageData->vanet_signin) : ?>
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 border-b border-gray-200"></div>
                            <p class="flex-none text-sm text-gray-700">Or continue with</p>
                            <div class="flex-1 border-b border-gray-200"></div>
                        </div>
                        <div class="flex gap-3">
                            <a href="/oauth/login" class="flex-1 px-4 flex justify-center py-2 text-sm font-medium border border-gray-300 rounded-md text-black focus:outline-none focus:ring-2 focus:ring-offset-2 hover:shadow">
                                <img src="https://vanet.app/logo.png" class="h-8 w-auto" />
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <script src="https://unpkg.com/alpinejs@3.0.1/dist/cdn.min.js" defer></script>
</body>

</html>