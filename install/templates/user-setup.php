<?php
require_once '../core/init.php';
?>

<!DOCTYPE html>
<html>
<head>
    <?php include './templates/header.php'; ?>
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

    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #E4181E;">
        <?php include './templates/navbar.php'; ?>
    </nav>

    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="main-content">
                <div id="loader" class="spinner-border spinner-border-sm text-danger"></div>
                    <div class="tab-content" id="tc">
                        <div class="tab-pane container active" id="home" style="display: none;">
                            <h3>Admin user setup</h3>
                            <p>This user will be the first administrator account on the crew center.</p>
                            <?php
                            if (Session::exists('error')) {
                                echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                            }
                            if (Session::exists('success')) {
                                echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                            }
                            ?>
                            <section>
                                <form action="?page=user-setup-complete" method="post">
                                    <input hidden name="action" value="filepirep">
                                    <div class="form-group">
                                        <label for="va-name">Name</label>
                                        <input type="text" min="1" class="form-control" name="name" value="<?= escape(Input::get('name')) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="callsign">Callsign</label>
                                        <input type="text" min="1" class="form-control" name="callsign" value="<?= escape(Config::get('va/identifier')) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="callsign">Link to IFC account</label>
                                        <input type="text" min="1" class="form-control" name="ifc" value="<?= escape(Input::get('ifc')) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" min="1" class="form-control" name="email" value="<?= escape(Input::get('email')) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="password-repeat">Password</label>
                                        <input type="password" min="1" class="form-control" name="password">
                                    </div>
                                    <div class="form-group">
                                        <label for="password-repeat">Password Repeat</label>
                                        <input type="password" min="1" class="form-control" name="password-repeat">
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Save">
                                </form>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php include './templates/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>
</html>