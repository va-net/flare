<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

if (!file_exists('./core/config.php')) {
    Redirect::to('./install/install.php');
}

Page::setTitle('Login - '.Config::get('va/name'));

$user = new User();
if ($user->isLoggedIn()) {
    Redirect::to('home.php');
}

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

            $remember = (Input::get('remember') === 'on') ? true : false;

            if ($user->login(Input::get('email'), Input::get('password'), $remember)) {
                Redirect::to('home.php');
            } else {
                $user->logout();
                Session::flash('error', 'Login Failed. Your application may still be pending or it may have been denied. Please contact us for more details if you believe this is an error.');
            } 

        } else {
            foreach ($validation->errors() as $error) {
                echo $error, '<br>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './includes/header.php'; ?>
</head>
<body>
    <style>
        #loader {
        position: absolute;
        left: 50%;
        top: 50%;
        z-index: 1;
        width: 150px;
        height: 150px;
        margin: -75px 0 0 -75px;
        width: 120px;
        height: 120px;
        }
    </style>

    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php include './includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <h1 class="text-center pb-0 mb-0"><?= escape(Config::get('va/name')) ?></h1>
            <h3 class="text-center py-0 my-0">Pilot Login<br><br></h3>
            <div class="container-fluid justify-content-center">
                <?php
                    if (Session::exists('error')) {
                        echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                    }
                    if (Session::exists('success')) {
                        echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                    }
                ?>
                <form method="post" action="">
                    <input hidden name="action" value="authenticate">
                    <div class="form-group text-center">
                        <label for="email">Email Address</label>
                        <input class="form-control publicform" type="email" id="email" name="email">
                    </div>

                    <div class="form-group text-center">
                        <label for="pass">Password</label>
                        <input class="form-control publicform" type="password" id="pass" name="password">
                    </div>
                    <input class="form-control" type="hidden" name="token" value="<?= Token::generate(); ?>">
                    <div class="row">
                        <div class="col text-center">
                            <input type="submit" class="btn ml-auto mr-auto display-block bg-custom" value="Log In">
                        </div>
                    </div>
                </form>
            </div>
            <footer class="container-fluid text-center">
                <?php include './includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>
</html>