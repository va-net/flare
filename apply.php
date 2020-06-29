<?php

require_once './core/init.php';

require './includes/header.php';
?>

<h1 class="text-center pb-0 mb-0">Virgin Virtual Group</h1>
<h3 class="text-center py-0 my-0">Pilot Application<br /><br /></h3>

<div class="container w-50 justify-content-center">

<?php
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

        if ($validate->passed()) {
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
                    'rank' => Rank::getFirstRank()
                ));
            } catch(Exception $e) {
                die($e->getMessage());
            }
            Session::flash('success', 'Your application has been submitted, you can now login!');
            Redirect::to('index.php');
        } else {
            foreach ($validate->errors() as $error) {
                echo '<div class="alert alert-danger text-center">Error: '.$error.'</div>';
            }
        }
    }
}

if (Session::exists('error')) {
    echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
}
if (Session::exists('success')) {
    echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
}

?>



<form method="post">
    <input type="hidden" name="token" value="<?= Token::generate() ?>">
    <div class="form-group text-center">
    <label for="name">Name</label>
    <input required class="form-control" type="text" id="name" name="name" value="<?= Input::get('name') ?>">
    </div>

    <div class="form-group text-center">
    <label for="ifc">Infinite Flight Community Profile URL</label>
    <input required class="form-control" type="url" id="ifc" name="ifc" value="<?= Input::get('ifc') ?>">
    <small class="form-text text-muted">All pilots are required to have an active Infinite Flight Community account</small>
    </div>
    
    <div class="form-group text-center">
    <label for="email">Email Address</label>
    <input required class="form-control" type="email" id="email" name="email" value="<?= Input::get('email') ?>">
    </div>

    <div class="form-group text-center">
    <label for="callsign">Callsign</label>
    <input required class="form-control" type="text" value="VGVA" id="callsign" name="callsign" value="<?= Input::get('callsign') ?>">
    <small class="form-text text-muted">Must begin with VGVA then have 2-4 numbers, eg VGVA123</small>
    </div>

    <div class="form-group text-center">
    <label for="violand">Violations to Landings Ratio</label>
    <input required class="form-control" step="0.01" type="number" id="violand" name="violand" value="<?= Input::get('violand') ?>">
    <small class="form-text text-muted">Decimal format, eg 0.35</small>
    </div>

    <div class="form-group text-center">
    <label for="violand">Infinite Flight Grade</label>
    <select required class="form-control" name="grade">
        <option value>Select</option>
        <?php foreach (range(1, 5) as $i) { ?>
            <option value="<?= $i ?>" <?= (Input::get('grade') == $i) ? 'selected' : '' ?>>Grade <?= $i ?></option>
        <?php } ?>
    </select>
    </div>

    <div class="form-group text-center">
    <label for="comments">Other Comments</label>
    <textarea class="form-control" id="comments" name="comments"><?= Input::get('violand') ?></textarea>
    </div>

    <div class="form-group text-center">
    <label for="pass">Password</label>
    <input required class="form-control" type="password" minlength="8" id="pass" name="password">
    <small class="form-text text-muted">Must be at least 8 characters long</small>
    </div>

    <div class="form-group text-center">
    <label for="confpass">Password Again</label>
    <input required class="form-control" type="password" id="confpass" name="password-repeat">
    </div>

    <div class="row">
    <div class="col text-center">
    <input type="submit" style="background-color: #E4181E; color: white;" class="btn ml-auto mr-auto display-block" value="Apply">
    </div>
    </div>
</form>
</div>

<?php require './includes/footer.php'; ?>