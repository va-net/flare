<?php
$db = DB::getInstance();

$test = $db->query("SELECT `notes` FROM `routes`");
if ($test->error()) {
    $db->query("ALTER TABLE `routes` ADD `notes` TEXT NULL DEFAULT NULL");
}
