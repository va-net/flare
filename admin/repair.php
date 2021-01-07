<?php
require_once '../core/init.php';
$db = DB::getInstance();

$test = $db->query("SELECT `notes` FROM `routes`");
if ($test->error()) {
    $db->query("ALTER TABLE `routes` ADD `notes` TEXT NULL DEFAULT NULL");
}

if (Config::get('CHECK_PRERELEASE') == 0 && Updater::getVersion()["prerelease"]) {
    Config::replace('CHECK_PRERELEASE', 1);
}

$pluginsPath = __DIR__ . "{$slash}..${slash}plugins.json";
$plugins = Json::decode(file_get_contents($pluginsPath));
$plugins = array_unique($plugins);
file_put_contents($pluginsPath, Json::encode($plugins));
