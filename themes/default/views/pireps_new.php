<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('File PIREP - ' . Page::$pageData->va_name);
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
                        <h3>File PIREP</h3>
                        <br />
                        <?php
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <form action="/pireps/new" method="post">
                            <input hidden name="action" value="filepirep">
                            <div class="form-group">
                                <label for="flightdate">Date of Flight</label>
                                <input required type="date" value="<?php echo date("Y-m-d") ?>" class="form-control" name="date">
                            </div>
                            <div class="form-group">
                                <label for="fnum">Flight Number</label>
                                <input requried type="text" class="form-control" name="fnum" value="<?= escape(Input::get('fnum')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="hrs">Flight Time</label>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <input required type="number" min="0" id="flightTimeHrs" class="form-control" placeholder="Hours" />
                                    </div>
                                    <div class="col-sm-6">
                                        <input required type="number" min="0" id="flightTimeMins" class="form-control" placeholder="Minutes" />
                                    </div>
                                </div>
                                <input hidden name="ftime" id="flightTimeFormatted" class="form-control" value="<?= escape(Input::get('ftime')) ?>" required />
                                <script>
                                    function formatFlightTime() {
                                        var hrs = $("#flightTimeHrs").val();
                                        var mins = $("#flightTimeMins").val();
                                        $("#flightTimeFormatted").val(hrs + ":" + mins);
                                    }

                                    function reverseFormatFlightTime() {
                                        var formatted = $("#flightTimeFormatted").val();
                                        if (formatted != '') {
                                            var split = formatted.split(":");
                                            var hrs = split[0];
                                            var mins = split[1];
                                            $("#flightTimeHrs").val(hrs);
                                            $("#flightTimeMins").val(mins);
                                        }
                                    }

                                    $(document).ready(function() {
                                        $("#flightTimeHrs").on('change', function() {
                                            formatFlightTime();
                                        });
                                        $("#flightTimeMins").on('change', function() {
                                            formatFlightTime();
                                        });
                                        reverseFormatFlightTime();
                                    });
                                </script>
                            </div>
                            <div class="form-group">
                                <label for="fuel">Fuel Used (kg)</label>
                                <input required type="number" class="form-control" name="fuel" value="<?= escape(Input::get('fuel')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="dep">Departure</label>
                                <input required type="text" class="form-control" maxlength="4" placeholder="ICAO" maxlength="4" minlength="4" name="dep" value="<?= escape(Input::get('dep')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="arr">Arrival</label>
                                <input required type="text" class="form-control" maxlength="4" placeholder="ICAO" maxlength="4" minlength="4" name="arr" value="<?= escape(Input::get('arr')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="aircraft">Aircraft</label>
                                <select class="form-control" name="aircraft" required>
                                    <option value>Select</option>
                                    <?php
                                    foreach (Page::$pageData->aircraft as $aircraft) {
                                        $notes = $aircraft['notes'] == null ? '' : ' - ' . $aircraft['notes'];
                                        if ($aircraft["name"] == Input::get("aircraft")) {
                                            echo '<option value="' . $aircraft['id'] . '" selected>' . $aircraft['name'] . ' (' . $aircraft['liveryname'] . ')' . $notes . '</option>';
                                        } else {
                                            echo '<option value="' . $aircraft['id'] . '">' . $aircraft['name'] . ' (' . $aircraft['liveryname'] . ')' . $notes . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="multi">Multiplier Code (if applicable)</label>
                                <input type="text" class="form-control" maxlength="6" minlength="6" id="multi" name="multi" />
                            </div>
                            <input type="submit" class="btn text-light bg-custom" value="Submit">
                        </form>
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