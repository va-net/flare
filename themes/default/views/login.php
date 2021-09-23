<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Login - ' . Config::get('va/name'));
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <h1 class="text-center pb-0 mb-0"><?= escape(Config::get('va/name')) ?></h1>
            <h3 class="text-center py-0 mt-0">Pilot Login</h3>
            <?php if (Page::$pageData->vanet_signin) : ?>
                <div class="text-center mb-3">
                    <a href="/oauth/login" class="btn btn-secondary d-inline-flex align-items-center" style="gap: 8px;">
                        <img src="https://vanet.app/logo.png" style="height: 20px; width: auto;" /> <span>Login with VANet</span>
                    </a>
                </div>
            <?php endif; ?>
            <div class="container justify-content-center">
                <?php
                if (Session::exists('error')) {
                    echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                }
                if (Session::exists('success')) {
                    echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                }

                $form = new Form();
                $form->setAction('')
                    ->setSubmitText('Log In')
                    ->addField('text', true, false, '', 'action', 'authenticate', [], 'login_action')
                    ->addField('email', false, true, 'Email Address', 'email', '', [], 'login_email')
                    ->addField('password', false, true, 'Password', 'password', '', [], 'login_pass')
                    ->addField('hidden', true, true, '', 'token', Token::generate(), [], 'login_token')
                    ->render();
                ?>
            </div>
            <footer class="container-fluid text-center">
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>

</html>