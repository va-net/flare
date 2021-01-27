<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('ACARS - '.Config::get('va/name'));
Page::excludeAsset('datatables');
Page::excludeAsset('chartjs');

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './includes/header.php'; ?>
</head>
<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php include './includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php include './includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper"><div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div></div>
                    <div class="loaded">
                        <h3>ACARS</h3>
                        <?php if ($IS_GOLD): ?>
                            <?php if ($user->data()->ifuserid != null): ?>
                                <div id="preRun">
                                <button data-toggle="collapse" data-target="#howtouseacars" class="btn btn-light w-100 mb-2 collapsed" aria-expanded="false">How to Use ACARS&nbsp;&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></button>
                                <p id="howtouseacars" class="text-left collapse">
                                    1. First, fly your flight under your <?= Config::get('va/name') ?> Callsign - <?= $user->data()->callsign ?>.<br />
                                    2. Then, once you're at the gate but not despawned, come here and click the button below.<br />
                                    3. The System will automatically grab your flight details, validate them, and File the PIREP.
                                </p>
                                    <?php
                                        $server = Config::get('FORCE_SERVER');

                                        if ($server == 0 || $server == 'casual') {
                                            echo '<div class="form-group">';
                                            echo '<label for="acars-server">Select Server</label>';
                                            echo '<select class="form-control" id="acars-server">';
                                            echo '<option value="training">Training Server</option>';
                                            echo '<option value="expert">Expert Server</option>';
                                            echo '</select>';
                                            echo '</div>';
                                        } else {
                                            echo '<input hidden id="acars-server" value="'.$server.'" />';
                                        }
                                    ?>

                                    <button id="acarsBtn" class="btn btn-lg bg-custom">Run ACARS</button>
                                </div>

                                <div id="postRun"></div>

                                <script>
                                    $(document).ready(function() {
                                        $("#acarsBtn").click(function() {
                                            $(".loaded").hide();
                                            $("#loader-wrapper").show();
                                            $.post("vanet.php", {
                                                "server": $("#acars-server").val(),
                                                "method": "acars"
                                            }, function (data, status) {
                                                $("#preRun").hide();
                                                $("#loader-wrapper").hide();
                                                $(".loaded").show();
                                                $("#postRun").html(data);
                                            });
                                        });
                                    });
                                </script>
                            <?php else: ?>
                                <p>Looks like you haven't yet setup PIREPs! Go to <a href="./pireps.php">My PIREPs</a>, set them up, then come back here and try again.</p>
                            <?php endif ?> 
                        <?php else: ?>
                            <p>In order to run ACARS, <?= Config::get('va/name') ?> needs to sign up to VANet Gold. You can take a look at it <a href="https://vanet.app/airline/upgrade" target="_blank">here</a>!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php include './includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>
</html>