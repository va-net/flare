<?php

spl_autoload_register(function($class) {

    require_once '../classes/'.$class.'.php';

});

require_once 'Installer.php';

Page::setTitle('Install Flare');

switch (Input::get('page')) {

    case 'va-details':
    case '':
        Installer::showTemplate('va-details');
        break;
    case 'va-details-complete':
        if (!Installer::createConfig(array(
            'VA_NAME' => Input::get('va-name'),
            'VA_IDENTIFIER' => Input::get('va-ident'),
            'VANET_API_KEY' => Input::get('vanet-api')
        ))) {
            Session::flash('error', 'Whoops! Something went wrong. Please try again.');
            Redirect::to('?page=va-details');
        }

        Redirect::to('?page=db-setup');
        break;
    case 'db-setup':
        Installer::showTemplate('db-setup');
        break;
    case 'db-install':
        if (!Installer::appendConfig(array(
            'DB_HOST' => Input::get('db-host'),
            'DB_USER' => Input::get('db-user'),
            'DB_PASS' => Input::get('db-pass'),
            'DB_NAME' => Input::get('db-name')
        ))) {
            Session::flash('error', 'Whoops! Flare couldn\'t connect to the database. Please try again.');
            Redirect::to('?page=db-setup');
        }
        require_once '../core/init.php';
        $db = DB::newInstance();
        $sql = file_get_contents('./db.sql');
        if ($db->query($sql)) {
            Redirect::to('?page=user-setup');
        }
        break;
    case 'user-setup':
        Installer::showTemplate('user-setup');
        break;
    case 'user-setup-complete':
        require_once '../core/init.php';
        $validate = new Validate();
        $validation = $validate->check($_POST, array(
            'password' => array(
                'matches' => 'password-repeat'
            )
        ));
        if ($validation->passed()) {
            $user = new User();
            try {
                $user->create(array(
                    'name' => Input::get('name'),
                    'email' => Input::get('email'),
                    'ifc' => Input::get('ifc'),
                    'password' => Hash::make(Input::get('password')),
                    'callsign' => Input::get('callsign'),
                    'permissions' => Config::get('defaults/fulladminpermissions'),
                    'status' => 1
                ));
            } catch(Exception $e) {
                Session::flash('error', 'Something went wrong when trying to register the user. Please try again.');
                Redirect::to('?page=user-setup');
            }
            Redirect::to('?page=success');
        }
        break;
    case 'success':
        Installer::showTemplate('success');

}


