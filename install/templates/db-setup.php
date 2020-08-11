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
                            <h3>Database setup</h3>
                            <br>
                            <?php
                            if (Session::exists('error')) {
                                echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                            }
                            if (Session::exists('success')) {
                                echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                            }
                            ?>
                            <section>
                                <form action="?page=db-install" method="post">
                                    <input class="form-control" type="hidden" name="token" value="<?= Token::generate(); ?>">
                                    <div class="form-group">
                                        <label for="db-host">Database Host (usually <i>localhost</i>)</label>
                                        <input type="text" min="1" class="form-control" name="db-host" value="<?= escape(Input::get('db-host')) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="db-user">Database Username</label>
                                        <input type="text" min="1" class="form-control" name="db-user" value="<?= escape(Input::get('db-user')) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="db-pass">Database Password</label>
                                        <input type="password" min="1" class="form-control" name="db-pass">
                                    </div>
                                    <div class="form-group">
                                        <label for="db-name">Database Name</label>
                                        <input type="text" min="1" class="form-control" name="db-name" value="<?= escape(Input::get('db-name')) ?>">
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