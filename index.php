<?php

require_once './core/init.php';

include './includes/header.php';

if (Input::exists()) {
    if (Token::check(Input::get('token'))) {
        $validate = new Validate();
        $validation = $validate->check($_POST, array(
            'email' => array(
                'required' => true
            ),
            'password' => array(
                'required' => true
            )
        ));

        if ($validation->passed()) {
            $user = new User();
            $remember = (Input::get('remember') === 'on') ? true : false;

            $login = $user->login(Input::get('email'), Input::get('password'), $remember);

            if ($login && $user->data()->recruitstage > 0) {
                Redirect::to('home.php');
            } elseif ($user->data()->recruitstage == 0) {
                Session::flash('error', 'Hold up! You need to wait until your application is approved before you can login!');
            } else {
                Session::flash('login', 'Login failed.');
            }
        } else {
            foreach ($validation->errors() as $error) {
                echo $error, '<br>';
            }
        }
    }
}

?>

<h1 class="text-center pb-0 mb-0">Virgin Virtual Group</h1>
<h3 class="text-center py-0 my-0">Pilot Login<br /><br /></h3>

<div class="container w-50 justify-content-center">
    <?php

    if (Session::exists('error')) {
        echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
    }
    if (Session::exists('success')) {
        echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
    }

    ?>
    <form method="post">
        <input hidden name="action" value="authenticate">
        <div class="form-group text-center">
            <label for="email">Email Address</label>
            <input class="form-control" type="email" id="email" name="email">
        </div>

        <div class="form-group text-center">
            <label for="pass">Password</label>
            <input class="form-control" type="password" id="pass" name="password">
        </div>
        <input class="form-control" type="hidden" name="token" value="<?= Token::generate(); ?>">
        <div class="row">
            <div class="col text-center">
                <input type="submit" style="background-color: #E4181E; color: white;" class="btn ml-auto mr-auto display-block" value="Log In">
            </div>
        </div>
    </form>
</div>

<?php require './includes/footer.php'; ?>