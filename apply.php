<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';
use RegRev\RegRev;

Page::setTitle('Apply - '.Config::get('va/name'));

$user = new User();
if ($user->isLoggedIn()) {
    Redirect::to('home.php');
}

$csPattern = Config::get('VA_CALLSIGN_FORMAT');
$trimmedPattern = preg_replace("/\/[a-z]*$/", '', preg_replace("/^\//", '', $csPattern));

if (Input::exists()) {
    if (Token::check(Input::get('token'))) {
        $validate = new Validate();
        $validation = $validate->check($_POST, array(
            'name' => array(
                'required' => true,
                'min' => 2,
                'max' => 50
            ),
            'ifc' => array(
                'required' => true
            ),
            'email' => array(
                'required' => true,
                'min' => 5,
                'max' => 50,
                'unique' => 'pilots'
            ), 
            'callsign' => array(
                'required' => true,
                'max' => 10,
                'unique' => 'pilots'
            ),
            'violand' => array(
                'required' => true
            ),
            'grade' => array(
                'required' => true
            ),
            'password' => array(
                'required' => true,
                'min' => 6
            ),
            'password-repeat' => array(
                'required' => true,
                'min' => 6,
                'matches' => 'password'
            )
        ));

        if ($validate->passed() && Regex::match($csPattern, Input::get('callsign'))) {
            $user = new User();
            try {
                $user->create(array(
                    'name' => Input::get('name'),
                    'email' => Input::get('email'),
                    'ifc' => Input::get('ifc'),
                    'password' => Hash::make(Input::get('password')),
                    'callsign' => Input::get('callsign'),
                    'grade' => Input::get('grade'),
                    'violand' => Input::get('violand'),
                    'notes' => Input::get('notes'),
                ));
            } catch(Exception $e) {
                die($e->getMessage());
            }
            Session::flash('success', 'Your application has been submitted! You will be contacted by a staff member in the coming weeks regarding the status of your application.');
            Redirect::to('index.php');
        } elseif (!$validate->passed()) {
            foreach ($validate->errors() as $error) {
                Session::flash('error', $error);
            }
        } else {
            Session::flash('error', 'Your Callsign is in an Invalid Format');
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
            <h3 class="text-center py-0 my-0">Application Form<br><br></h3>
            <?php
                if (Session::exists('error')) {
                    echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                }
                if (Session::exists('success')) {
                    echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
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
                    <input required class="form-control publicform" type="text" id="callsign" name="callsign" value="<?= escape(empty(Input::get('callsign')) && $trimmedPattern != ".*" ? RegRev::generate($trimmedPattern) : Input::get('callsign')) ?>">
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
                    <textarea class="form-control publicform" id="comments" name="comments"><?= escape(Input::get('violand')) ?></textarea>
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
                <?php include './includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>
</html>
