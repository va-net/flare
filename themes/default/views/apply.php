<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

use RegRev\RegRev;

Page::setTitle('Apply - ' . Page::$pageData->va_name);

$trimmedPattern = preg_replace("/\/[a-z]*$/", '', preg_replace("/^\//", '', Page::$pageData->callsign_format));
$filledCallsign = '';
$callsigns = Callsign::all();
if (empty(Input::get('callsign')) && $trimmedPattern != '.*') {
    if (count($callsigns) < 1) {
        $filledCallsign = RegRev::generate($trimmedPattern);
    } else {
        $filledCallsign = $callsigns[0];
        $i = 0;
        while (in_array($filledCallsign, $callsigns) && $i < 50) {
            $filledCallsign = RegRev::generate($trimmedPattern);
            $i++;
        }
        if (in_array($filledCallsign, $callsigns)) {
            $filledCallsign = '';
        }
    }
} else {
    $filledCallsign = Input::get('callsign');
}
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
            <h1 class="text-center pb-0 mb-0"><?= escape(Page::$pageData->va_name) ?></h1>
            <h3 class="text-center py-0 my-0">Application Form<br><br></h3>
            <?php
            if (Session::exists('error')) {
                echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
            }
            if (Session::exists('success')) {
                echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
            }
            ?>
            <div class="container-fluid justify-content-center">
                <form method="post">
                    <input type="hidden" name="token" value="<?= Token::generate() ?>">
                    <div class="form-group text-center">
                        <label for="name">Name</label>
                        <input required class="form-control publicform" type="text" id="name" name="name" value="<?= escape(Input::get('name')) ?>">
                    </div>

                    <div class="form-group text-center">
                        <label for="ifc">Infinite Flight Community Profile URL</label>
                        <input required class="form-control publicform" type="url" id="ifc" name="ifc" value="<?= escape(Input::get('ifc')) ?>">
                        <small class="form-text text-muted">All pilots are required to have an active Infinite Flight Community Account</small>
                    </div>

                    <div class="form-group text-center">
                        <label for="email">Email Address</label>
                        <input required class="form-control publicform" type="email" id="email" name="email" value="<?= escape(Input::get('email')) ?>">
                    </div>

                    <div class="form-group text-center">
                        <label for="callsign">Callsign</label>
                        <input required class="form-control publicform" type="text" id="callsign" name="callsign" value="<?= $filledCallsign ?>" <?= Config::get('AUTO_CALLSIGNS') == 1 && $filledCallsign != '' ? 'readonly' : '' ?>>
                    </div>

                    <div class="form-group text-center">
                        <label for="violand">Violations to Landings Ratio</label>
                        <input required class="form-control publicform" step="0.01" type="number" id="violand" name="violand" value="<?= escape(Input::get('violand')) ?>">
                        <small class="form-text text-muted">Decimal format, eg 0.35</small>
                    </div>

                    <div class="form-group text-center">
                        <label for="violand">Infinite Flight Grade</label>
                        <select required class="form-control publicform" name="grade">
                            <option value>Select</option>
                            <?php foreach (range(1, 5) as $i) { ?>
                                <option value="<?= $i ?>" <?= (Input::get('grade') == $i) ? 'selected' : '' ?>>Grade <?= $i ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group text-center">
                        <label for="comments">Other Comments</label>
                        <textarea class="form-control publicform" id="comments" name="comments"><?= escape(Input::get('comments')) ?></textarea>
                    </div>

                    <div class="form-group text-center">
                        <label for="pass">Password</label>
                        <input required class="form-control publicform" type="password" minlength="8" id="pass" name="password">
                        <small class="form-text text-muted">Must be at least 8 characters long</small>
                    </div>

                    <div class="form-group text-center">
                        <label for="confpass">Password Again</label>
                        <input required class="form-control publicform" type="password" id="confpass" name="password-repeat">
                    </div>

                    <div class="row">
                        <div class="col text-center">
                            <input type="submit" class="btn ml-auto mr-auto display-block bg-custom" value="Apply">
                        </div>
                    </div>
                </form>
            </div>
            <footer class="container-fluid text-center">
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>

</html>