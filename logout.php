<?php

require_once 'core/init.php';

$user = new User();
$user->logout();

Session::flash('login', 'You have logged out successfully!');
Redirect::to('index.php');