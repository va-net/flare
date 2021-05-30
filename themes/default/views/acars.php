<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('ACARS - ' . Page::$pageData->va_name);
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
            <div class="row m-0 p-0">
                <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <h3>ACARS</h3>
                        <?php if (Page::$pageData->is_gold) : ?>
                            <button data-toggle="collapse" data-target="#howtouseacars" class="btn btn-light w-100 mb-2 collapsed" aria-expanded="false">How to Use ACARS&nbsp;&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></button>
                            <p id="howtouseacars" class="text-left collapse">
                                1. First, fly your flight under your <?= Page::$pageData->va_name ?> Callsign - <?= Page::$pageData->user->data()->callsign ?>.<br />
                                2. Then, once you're at the gate but not despawned, come here and click the button below.<br />
                                3. The System will automatically grab your flight details, validate them, and File the PIREP.
                            </p>
                            <form method="post">
                                <?php
                                if (Page::$pageData->server == 0 || Page::$pageData->server == 'casual') {
                                    echo '<div class="form-group">';
                                    echo '<label for="acars-server">Select Server</label>';
                                    echo '<select class="form-control" id="acars-server" name="server">';
                                    echo '<option value="training">Training Server</option>';
                                    echo '<option value="expert">Expert Server</option>';
                                    echo '</select>';
                                    echo '</div>';
                                } else {
                                    echo '<input hidden name="server" value="' . Page::$pageData->server . '" />';
                                }
                                ?>
                            </form>

                            <button id="acarsBtn" class="btn btn-lg bg-custom">Run ACARS</button>
                        <?php else : ?>
                            <p>In order to run ACARS, <?= Page::$pageData->va_name ?> needs to sign up to VANet Gold. You can take a look at it <a href="https://vanet.app/airline/upgrade" target="_blank">here</a>!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>

</html>