<?php

$classdirs = ['.', 'app', 'data', 'plugins', 'util', 'controllers', 'controllers/admin'];
spl_autoload_register(function ($class) {
    global $classdirs;
    foreach ($classdirs as $d) {
        $file = __DIR__ . '/../classes/' . $d . '/' . $class . '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
    }
});

if (file_exists(__DIR__ . '/config.new.php')) die();

$output = "<?php\n";

$envs = array_filter(array_keys($_ENV), function ($x) {
    return strpos($x, "FLARE_") === 0;
});
foreach ($envs as $key) {
    $output .= "define('{$key}', '{$_ENV[$key]}');\n";
}

file_put_contents(__DIR__ . '/config.new.php', $output);

sleep(2);

if (count((new User)->getAllUsers()) == 0) {
    Permissions::giveAll(1);
}
