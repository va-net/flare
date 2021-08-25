<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Site Admin - ' . Config::get('va/name'));
Page::excludeAsset('datatables');
Page::excludeAsset('chartjs');

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('site') || !$user->hasPermission('admin')) {
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
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <?php
                        $tab = "settings";
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
                        if (file_exists(__DIR__ . '/../install/install.php') && !file_exists(__DIR__ . '/../.development')) {
                            echo '<div class="alert alert-danger text-center">The Install Folder still Exists! Please delete it immediately, it poses a severe security risk.</div>';
                        }

                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                        }

                        $ver = Updater::getVersion();
                        $update = Updater::checkUpdate(Config::get('CHECK_PRERELEASE') == 1);
                        ?>
                        <h3>Flare Settings</h3>
                        <p>Here you may configure Flare to be your own.</p>
                        <ul class="nav nav-tabs nav-dark justify-content-center">
                            <li class="nav-item">
                                <a class="nav-link" id="settingslink" data-toggle="tab" href="#settings">VA Settings</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="designlink" data-toggle="tab" href="#design">Site Design</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="interactionlink" data-toggle="tab" href="#interaction">Connectivity</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="maintenancelink" data-toggle="tab" href="#maint">Maintenance</a>
                            </li>
                            <li class="nav-item">
                                <?php $updateAlert = $update ? ' <span class="text-danger"><i class="fa fa-exclamation-circle"></i></span>' : ''; ?>
                                <a class="nav-link" id="updateslink" data-toggle="tab" href="#updates">Updates<?= $updateAlert ?></a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div id="settings" class="tab-pane container-fluid p-3 fade">
                                <h4>VA Settings</h4>
                                <form action="/update.php" method="post">
                                    <input hidden name="action" value="vasettingsupdate">
                                    <div class="form-group">
                                        <label for="">VA Full Name</label>
                                        <input required type="text" class="form-control" name="vaname" value="<?= Config::get('va/name') ?>" />
                                    </div>
                                    <div class="form-group">
                                        <label for="">VA Logo URL</label>
                                        <input type="url" class="form-control" name="valogo" value="<?= Config::get('VA_LOGO_URL') ?>" />
                                        <small class="text-muted">This must be the URL ending in png, jpg, etc. If set, this will override your VA name in the navbar.</small>
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
                                        <label for="">Automatically Assign Callsigns</label>
                                        <select requried class="form-control" name="autocallsign" id="autocallsign">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                        <script>
                                            $("#autocallsign").val('<?= Config::get("AUTO_CALLSIGNS") ?>');
                                        </script>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Check for Beta Updates?</label>
                                        <select requried class="form-control" name="checkpre" id="check-prerelease" <?= $ver["prerelease"] ? 'disabled' : '' ?>>
                                            <option value="0">No (Recommended for Production Sites)</option>
                                            <option value="1">Yes</option>
                                        </select>
                                        <?php
                                        if ($ver["prerelease"]) {
                                            echo '<small class="text-muted">You cannot leave the beta while on a beta release as it may cause future conflicts.</small>';
                                        }
                                        ?>
                                        <script>
                                            $("#check-prerelease").val('<?= Config::get("CHECK_PRERELEASE") ?>');
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
                            <div id="design" class="tab-pane container-fluid p-3 fade">
                                <h4>Site Design</h4>
                                <form action="/update.php" method="post">
                                    <input hidden name="action" value="setdesign">
                                    <div class="form-group">
                                        <label for="">Main Colour (hex)</label>
                                        <input required type="text" class="form-control" name="hexcol" value="<?= Config::get('site/colour_main_hex') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Text Colour (hex)</label>
                                        <input required type="text" class="form-control" name="textcol" value="<?= Config::get('TEXT_COLOUR') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Custom CSS&nbsp;&nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" title="CSS is a Website Styling Language"></i></label>
                                        <textarea style="font-family: 'Courier New', Courier, monospace;" class="form-control" name="customcss" rows="10"><?= Config::getCss() ?></textarea>
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Save">
                                </form>
                            </div>
                            <div id="interaction" class="tab-pane container-fluid p-3 fade">
                                <h4>Connectivity Settings</h4>
                                <form action="/update.php" method="post">
                                    <input hidden name="action" value="interactionupdate">
                                    <div class="form-group">
                                        <label for="">VANet API Key</label>
                                        <input required type="text" class="form-control" name="vanetkey" value="<?= Config::get('vanet/api_key') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Send Analytics to Developers</label>
                                        <select required class="form-control" name="analytics" id="analyticsdrop">
                                            <option value="1">Yes (Recommended)</option>
                                            <option value="0">No</option>
                                        </select>
                                        <script>
                                            $("#analyticsdrop").val('<?= empty(Config::get('INSTANCE_ID')) ? 0 : 1 ?>');
                                        </script>
                                        <small class="text-muted">If enabled, reports of errors will be sent to the developers of Flare to help with debugging.</small>
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Save" />
                                </form>
                            </div>
                            <div id="maint" class="tab-pane container-fluid p-3 fade">
                                <h4>Site Maintenance</h4>
                                <div class="row">
                                    <div class="col-lg">
                                        <form action="/update.php" method="post">
                                            <input hidden name="action" value="clearlogs" />
                                            <input hidden name="period" value="*" />
                                            <input type="submit" class="btn bg-custom" value="Clear All Logs" />
                                        </form>
                                    </div>
                                    <div class="col-lg">
                                        <form action="/update.php" method="post">
                                            <input hidden name="action" value="clearlogs" />
                                            <input hidden name="period" value="30" />
                                            <input type="submit" class="btn bg-custom" value="Clear Old Logs" />
                                        </form>
                                    </div>
                                    <div class="col-lg">
                                        <form action="/update.php" method="post">
                                            <input hidden name="action" value="clearcache" />
                                            <input type="submit" class="btn bg-custom" value="Clear Cache" />
                                        </form>
                                    </div>
                                    <div class="col-lg">
                                        <button class="btn bg-custom" id="repair-btn">Repair Site</button>
                                    </div>
                                    <script>
                                        $("#repair-btn").click(function() {
                                            $.get('/admin/repair.php', function() {
                                                alert('Repair attempted successfully');
                                            });
                                        });
                                    </script>
                                </div>
                            </div>
                            <div id="updates" class="tab-pane container-fluid p-3 fade">
                                <h4>Flare Updates</h4>
                                <p>
                                    <b>You are on Flare <?php echo $ver["tag"]; ?></b>
                                    <br />
                                    <?php
                                    if (!$update) {
                                        echo "Flare is Up-to-Date!";
                                    } else {
                                        echo "<span id=\"updateAvail\">An update to Flare " . $update["tag"] . " is available<br /></span>";
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
                                color: #000 !important;
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