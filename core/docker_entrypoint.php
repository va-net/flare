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

$db = DB::getInstance();
$tables = array_map(function ($x) {
    return ((array)$x)['Tables_in_' . Config::get('mysql/db')];
}, $db->query('SHOW TABLES')->results());
if (!in_array('pilots', $tables)) {
    $branch = Updater::getVersion()['prerelease'] ? Updater::githubPrereleaseBranch() : Updater::githubDefaultBranch();
    $dl = Updater::downloadUrl();
    $auth = Updater::authentication();

    $opts = array(
        'http' => array(
            'method' => "GET",
            'header' => "User-Agent: va-net\r\n"
        )
    );
    if (!empty($auth)) {
        $opts['http']['header'] .= "Authorization: Basic " . base64_encode($auth) . "\r\n";
    }
    $context = stream_context_create($opts);

    $res = Json::decode(file_get_contents("{$DL_URL}/install/db.sql?ref=" . urlencode($branch), false, $context));
    $sql = base64_decode($res["content"]);
    $db->query($sql);
}

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
