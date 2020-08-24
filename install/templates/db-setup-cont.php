<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
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

    <nav class="navbar navbar-expand-lg navbar-dark bg-custom">
        <?php include './templates/navbar.php'; ?>
    </nav>

    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="main-content">
                <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    <div class="tab-content" id="tc">
                        <div class="tab-pane container active" id="home" style="display: none;">
                            <h3>Database Setup</h3>
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
                                <p>Time to install the database! Please press continue to, well, continue.</p>
                                <form action="" method="post">
                                    <input type="submit" name="submit" class="btn bg-custom" value="Continue with installation">
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