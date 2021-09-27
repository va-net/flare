<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Import Routes - ' . Page::$pageData->va_name);
$ACTIVE_CATEGORY = 'operations-management';
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once __DIR__ . '/../../includes/header.php'; ?>
</head>

<body>
    <nav class="navbar navbar-dark navbar-expand-lg bg-custom">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    </nav>
    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="row m-0 p-0">
                <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
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
                        ?>
                        <h3>phpVMS Importer</h3>
                        <p>
                            So we can import everything correctly, please select the aircraft type and livery for each registration.
                            These aircraft will be added with the lowest rank if they do not already exist in your VA's database.
                        </p>
                        <?php
                        $routesJson = Json::encode(Page::$pageData->routes);
                        $aircraftOptions = "";
                        foreach (Page::$pageData->aircraft as $id => $name) {
                            $aircraftOptions .= '<option value="' . $id . '">' . $name . '</option>';
                        }

                        echo '<form action="/admin/routes/import" method="post">';
                        echo '<input hidden name="action" value="import" />';
                        echo "<input hidden name='rJson' value='$routesJson' />";
                        echo '<table class="w-100 mb-2">';
                        $i = 0;
                        $doneAircraft = [];
                        foreach (Page::$pageData->routes as $r) {
                            if (in_array($r['aircraftid'], $doneAircraft)) continue;

                            echo '<tr class="border-bottom border-top"><td class="align-middle p-2"><b>';
                            echo $r['aircraftid'];
                            echo '</b></td><td class="align-middle py-2">';
                            echo '<input hidden name="rego' . $i . '" value="' . $r["aircraftid"] . '" />';
                            echo '<select required class="form-control mb-2 aircraftSelect" name="aircraft' . $i . '" id="' . $i . '">';
                            echo '<option value>Aircraft Type</option>';
                            echo $aircraftOptions;
                            echo '</select>';
                            echo '<select required class="form-control" name="livery' . $i . '" id="livery' . $i . '">';
                            echo '<option value>Select an Aircraft to Get Liveries</option>';
                            echo '</select>';
                            echo '</td></tr>';
                            array_push($doneAircraft, $r['aircraftid']);
                            $i++;
                        }
                        echo '</table>';
                        echo '<input type="submit" class="btn bg-custom" value="Import Now" />';
                        echo '</form>';
                        ?>
                        <script>
                            $(document).ready(function() {
                                $(".aircraftSelect").change(function() {
                                    var id = $(this).attr("id");
                                    $("#livery" + id).html("<option value>Loading...</option>");
                                    $.ajax({
                                        url: "/api.php/liveries?aircraftid=" + encodeURIComponent($(this).val()),
                                        type: "GET",
                                        success: function(data) {
                                            $("#livery" + id).empty();

                                            var html = Object.entries(data.result).map(([name, id]) => `<option value="${id}">${name}</option>`);
                                            $("#livery" + id).append("<option>Select</option>");
                                            $("#livery" + id).append(html);
                                        }
                                    });
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
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