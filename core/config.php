<?php

/*
This is the main Flare configuration file. Please do not change anything in this unless you know what you are
doing! If updating, please backup this file prior to doing so.
*/

session_start();

spl_autoload_register(function($class) {

    require_once 'classes/'.$class.'.php';

});

$GLOBALS['config'] = array(
    'mysql' => array(
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
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
        'name' => 'IFHUB',
        'identifier' => 'IFHUB'
    ),
    'vanet' => array(
        'api_key' => '1bcf4484-d982-4e6c-9a33-63634d72ae28',
        'base_url' => 'https://vanet.app'
    ),
    'defaults' => array(
        'permissions' => '{"admin": 0, "usermanage": 0, "staffmanage": 0, "recruitment": 0, "pirepmanage": 0, "newsmanage":0, "emailpilots": 0, "opsmanage": 0, "statsviewing": 0}',
        'fulladminpermissions' => '{"admin": 1, "usermanage": 1, "staffmanage": 1, "recruitment": 1, "pirepmanage": 1, "newsmanage": 1, "emailpilots": 1, "opsmanage": 1, "statsviewing": 1}'
    )
);

if (Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))) {
    $hash = Cookie::get(Config::get('remember/cookie_name'));
    $hashCheck = DB::getInstance()->get('sessions', array('hash', '=', $hash));

    if ($hashCheck->count()) {
        $user = new User($hashCheck->first()->user_id);
        $user->login();
    }
}