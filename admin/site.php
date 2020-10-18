<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Site Admin - '.Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('opsmanage') || !$user->hasPermission('admin')) { // TODO: Need a specific permission for this
    Redirect::to('/home.php');
}

$ACTIVE_CATEGORY = 'site-management';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include '../includes/header.php'; ?>
</head>
<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php include '../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php include '../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper"><div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div></div>
                    <div class="loaded">
                        <?php
                            $tab = "colors";
                            if (!empty(Input::get('tab'))) {
                                $tab = Input::get('tab');
                            }
                            $ACTIVE_CATEGORY = 'site-management'; 
                        ?>
                        <script>
                            $(document).ready(function() {
                                $("#<?= $tab; ?>link").click();
                            });
                        </script>
                        <?php
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                        }
                        ?>
                        <h3>Flare Settings</h3>
                        <p>Here you may configure Flare to be your own.</p>
                        <ul class="nav nav-tabs nav-dark justify-content-center">
                            <li class="nav-item">
                                <a class="nav-link" id="colorslink" data-toggle="tab" href="#colors">Color Theme</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="settingslink" data-toggle="tab" href="#settings">VA Settings</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="vanetlink" data-toggle="tab" href="#vanet">VANet Settings</a>
                            </li>
                            <?php if (Updater::getVersion()["prerelease"]) { ?>
                                <li class="nav-item">
                                    <a class="nav-link" id="debuglink" data-toggle="tab" href="#debug">Debugging Info</a>
                                </li>
                            <?php } ?>
                            <li class="nav-item">
                                <a class="nav-link" id="updateslink" data-toggle="tab" href="#updates">Updates</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div id="colors" class="tab-pane container-fluid p-3 fade">
                                <h4>Colour Theme</h4>
                                <form action="/update.php" method="post">
                                    <input hidden name="action" value="setcolour">
                                    <div class="form-group">
                                        <label for="">Main Colour (hex)</label>
                                        <input required type="text" class="form-control" name="hexcol" value="<?= Config::get('site/colour_main_hex') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Text Colour (hex)</label>
                                        <input required type="text" class="form-control" name="textcol" value="<?= Config::get('TEXT_COLOUR') ?>">
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Save">
                                </form>
                            </div>
                            <div id="settings" class="tab-pane container-fluid p-3 fade">
                                <h4>VA Settings</h4>
                                <form action="/update.php" method="post">
                                    <input hidden name="action" value="vasettingsupdate">
                                    <div class="form-group">
                                        <label for="">VA Full Name</label>
                                        <input required type="text" class="form-control" name="vaname" value="<?= Config::get('va/name') ?>" />
                                    </div>
                                    <div class="form-group">
                                        <label for="">VA Callsign RegEx&nbsp;&nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" title="RegEx is a way to match complex text formats"></i></label>
                                        <input required type="text" class="form-control" name="vaident" id="vaident" value="<?= Config::get('VA_CALLSIGN_FORMAT') ?>" />
                                        <small class="text-muted">
                                            <b>Pre-fill:</b> 
                                            <span class="text-primary cursor-pointer" id="prefill-va">Airline 123VA</span> | 
                                            <span class="text-primary cursor-pointer" id="prefill-any">Any Callsign</span>
                                        </small>
                                    </div>
                                    <div class="form-group">
                                        <label for="">VA Abbreviation</label>
                                        <input required type="text" class="form-control" name="vaabbrv" value="<?= Config::get('va/identifier') ?>" />
                                        <small class="text-muted">This is your VA Abbreviation such as BAVA, DLVA, etc</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Force Live Server</label>
                                        <select required class="form-control" name="forceserv" id="forceserv">
                                            <option value="0">Don't Force Server</option>
                                            <option value="casual">Force Casual Server</option>
                                            <option value="training">Force Training Server</option>
                                            <option value="expert">Force Expert Server</option>
                                        </select>
                                        <script>
                                            $(document).ready(function() {
                                                $("#forceserv").val("<?= Config::get('FORCE_SERVER'); ?>")
                                            });
                                        </script>
                                        <small class="text-muted">This will force all operations (PIREP lookups, ACARS, etc) to be on this server. If turned off, pilots will be able to choose.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Check for Beta Updates?</label>
                                        <select requried class="form-control" name="checkpre" id="check-prerelease">
                                            <option value="0">No (Recommended for Production Sites)</option>
                                            <option value="1">Yes</option>
                                        </select>
                                        <script>
                                            $("#check-prerelease").val('<?= Config::get("CHECK_PRERELEASE"); ?>');
                                        </script>
                                        <small class="text-muted">Beta Pushes are often unstable and may break your site.</small>
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Save">
                                </form>
                                <script>
                                    $(document).ready(function() {
                                        $("#prefill-va").click(function() {
                                            var airline = prompt('Enter Airline Callsing (Speedbird, American, etc)');
                                            var regex = '/' + airline + ' \\d{1,3}VA/i';
                                            $("#vaident").val(regex);
                                            alert('Filled!');
                                        });
                                        $("#prefill-any").click(function() {
                                            var regex = '/.*/i';
                                            $("#vaident").val(regex);
                                            alert('Filled!');
                                        });
                                    });
                                </script>
                            </div>
                            <div id="vanet" class="tab-pane container-fluid p-3 fade">
                                <h4>VANet Settings</h4>
                                <form action="/update.php" method="post">
                                    <input hidden name="action" value="vanetupdate">
                                    <div class="form-group">
                                        <label for="">VANet API Key</label>
                                        <input required type="text" class="form-control" name="vanetkey" value="<?= Config::get('vanet/api_key') ?>">
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Save">
                                </form>
                            </div>
                            <div id="debug" class="tab-pane container-fluid p-3 fade">
                                <h4>Debugging Information</h4>
                                <p>
                                    This screen is shown to VAs running a pre-release version of Flare only. It contains information to help the
                                    Flare developers reproduce any issues you may have.
                                </p>
                                <table class="table">
                                    <tr>
                                        <th>DB Port</th>
                                        <td><?= Config::get('mysql/port'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>VANet Gold</th>
                                        <td><?= VANet::isGold() ? 'Yes': 'No' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Force Server</th>
                                        <td><?= Config::get('FORCE_SERVER') == 0 ? 'None' : Config::get('FORCE_SERVER') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Version</th>
                                        <td><?= Updater::getVersion()["tag"]; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div id="updates" class="tab-pane container-fluid p-3 fade">
                                <h4>Flare Updates</h4>
                                <p>
                                    <?php $ver = Updater::getVersion(); ?>
                                    <b>You are on Flare <?php echo $ver["tag"]; ?></b>
                                    <br />
                                    <?php
                                        $update = Updater::checkUpdate(Config::get('CHECK_PRERELEASE') == 1);
                                        if (!$update) {
                                            echo "Flare is Up-to-Date!";
                                        } else {
                                            echo "<span id=\"updateAvail\">An update to Flare ".$update["tag"]." is available<br /></span>";
                                            echo '<button class="btn bg-custom" id="updateNow">Update Now</button>';
                                            echo '<p id="updateResult"></p>';
                                        }
                                    ?>
                                </p>
                                <script>
                                    $(document).ready(function() {
                                        $("#updateNow").click(function() {
                                            $(this).hide();
                                            $("#updateAvail").hide();
                                            $(".loaded").hide();
                                            $("#loader-wrapper").show();
                                            $("#updateResult").html('<div class="spinner-grow spinner-custom"></div>');
                                            $.get("/updater.php", function(data, status) {
                                                $("#updateResult").html(data);
                                                $(".loaded").show();
                                                $("#loader-wrapper").hide();
                                            });
                                        });
                                    });
                                </script>
                            </div>
                        </div>

                        <style>
                            .nav-tabs .nav-link {
                                color: #000!important;
                            }
                        </style>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php include '../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $(".<?= $ACTIVE_CATEGORY ?>").collapse('show');
        });
    </script>
</body>
</html>