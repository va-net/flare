<?php

/*
This is the main configuration file. Please do not change anything in this unless you know what you are
doing! If updating, please backup this file prior to doing so.
*/

session_start();

$GLOBALS['config'] = array(
    'mysql' => array(
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'db' => 'flare'
        ),
    'remember' => array(
        'cookie_name' => 'remember',
        'cookie_expiry' => 604800
    ),
    'session' => array(
        'session_name' => 'user',
        'token_name' => 'token'
    ),
    'va' => array(
        'name' => 'Virgin Virtual Group',
        'identifier' => 'VGVA'
    )
);

spl_autoload_register(function($class) {

    require_once 'classes/'.$class.'.php';

});

require_once 'functions/escape.php';

if (Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))) {
    $hash = Cookie::get(Config::get('remember/cookie_name'));
    $hashCheck = DB::getInstance()->get('sessions', array('hash', '=', $hash));

    if ($hashCheck->count()) {
        $user = new User($hashCheck->first()->user_id);
        $user->login();
    }
}