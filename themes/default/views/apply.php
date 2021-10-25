<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Apply - ' . Page::$pageData->va_name);
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
        <div class="container mt-4 text-center" style="overflow: auto;">
            <h1 class="pb-0 mb-0 text-center"><?= escape(Page::$pageData->va_name) ?></h1>
            <h3 class="py-0 mt-0 text-center">Application Form</h3>
            <?php if (Page::$pageData->vanet_signin) : ?>
                <div class="text-center mb-3">
                    <a href="/oauth/login" class="btn btn-secondary d-inline-flex align-items-center" style="gap: 8px;">
                        <img src="https://vanet.app/logo.png" style="height: 20px; width: auto;" /> <span>Apply with VANet</span>
                    </a>
                </div>
            <?php endif; ?>
            <?php
            if (Session::exists('error')) {
                echo '<div class="text-center alert alert-danger">Error: ' . Session::flash('error') . '</div>';
            }
            if (Session::exists('success')) {
                echo '<div class="text-center alert alert-success">' . Session::flash('success') . '</div>';
            }
            ?>
            <div class="container-fluid justify-content-center">
                <form method="post">
                    <input type="hidden" name="token" value="<?= Token::generate() ?>">
                    <div class="text-center form-group">
                        <label for="name">Name</label>
                        <input required class="form-control publicform" type="text" id="name" name="name" value="<?= escape(Input::get('name')) ?>">
                    </div>

                    <div class="text-center form-group">
                        <label for="ifc">Infinite Flight Community Profile URL</label>
                        <input required class="form-control publicform" type="url" id="ifc" name="ifc" value="<?= escape(Input::get('ifc')) ?>">
                        <small class="form-text text-muted">All pilots are required to have an active Infinite Flight Community Account</small>
                    </div>

                    <div class="text-center form-group">
                        <label for="email">Email Address</label>
                        <input required class="form-control publicform" type="email" id="email" name="email" value="<?= escape(Input::get('email')) ?>">
                    </div>

                    <div class="text-center form-group">
                        <label for="callsign">Callsign</label>
                        <input required class="form-control publicform" type="text" id="callsign" name="callsign" placeholder="<?= escape(Page::$pageData->callsign) ?>">
                    </div>

                    <div class="text-center form-group">
                        <label for="violand">Violations to Landings Ratio</label>
                        <input required class="form-control publicform" step="0.01" type="number" id="violand" name="violand" value="<?= escape(Input::get('violand')) ?>">
                        <small class="form-text text-muted">Decimal format, eg 0.35</small>
                    </div>

                    <div class="text-center form-group">
                        <label for="grade">Infinite Flight Grade</label>
                        <select required class="form-control publicform" name="grade">
                            <option value>Select</option>
                            <?php foreach (range(1, 5) as $i) { ?>
                                <option value="<?= $i ?>" <?= (Input::get('grade') == $i) ? 'selected' : '' ?>>Grade <?= $i ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="text-center form-group">
                        <label for="comments">Other Comments</label>
                        <textarea class="form-control publicform" id="comments" name="comments"><?= escape(Input::get('comments')) ?></textarea>
                    </div>

                    <div class="text-center form-group">
                        <label for="pass">Password</label>
                        <input required class="form-control publicform" type="password" minlength="8" id="pass" name="password">
                        <small class="form-text text-muted">Must be at least 8 characters long</small>
                    </div>

                    <div class="text-center form-group">
                        <label for="confpass">Password Again</label>
                        <input required class="form-control publicform" type="password" id="confpass" name="password-repeat">
                    </div>

                    <div class="row">
                        <div class="text-center col">
                            <input type="submit" class="ml-auto mr-auto btn display-block bg-custom" value="Apply">
                        </div>
                    </div>
                </form>
            </div>
            <footer class="text-center container-fluid">
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>

</html>