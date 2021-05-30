<?php

spl_autoload_register(function ($class) {
    require_once __DIR__ . '/../classes/' . $class . '.php';
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
