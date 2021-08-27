<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Routes Admin - ' . Page::$pageData->va_name);
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
                        <h3>Route Management</h3>
                        <p>Here you can Manage your VA's Routes.</p>
                        <button type="button" class="btn bg-custom mb-2" data-toggle="modal" data-target="#addRoute">Add Route</button>
                        <div id="addRoute" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Add Route</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="/admin/operations/routes" method="post" id="addroute-form">
                                            <input hidden name="action" value="addroute">
                                            <div class="form-group">
                                                <label for="">Departure Airport</label>
                                                <input type="text" name="dep" class="form-control" placeholder="ICAO" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="">Arrival Airport</label>
                                                <input type="text" name="arr" class="form-control" placeholder="ICAO" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="">Flight Number</label>
                                                <input maxlength="10" type="text" name="fltnum" class="form-control" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="">Flight Duration</label>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <input required type="number" min="0" id="flightTimeHrs" class="form-control" placeholder="Hours" />
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <input required type="number" min="0" id="flightTimeMins" class="form-control" placeholder="Minutes" />
                                                    </div>
                                                </div>
                                                <input hidden name="duration" id="flightTimeFormatted" class="form-control" required />
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
                                                <label for="">Aircraft</label>
                                                <select multiple class="form-control selectpicker" id="addroute-aircraft" data-live-search="true" required>
                                                    <option value>Select</option>
                                                    <?php
                                                    foreach (Page::$pageData->fleet as $aircraft) {
                                                        $notes = $aircraft->notes == null ? '' : ' - ' . $aircraft->notes;
                                                        echo '<option value="' . $aircraft->id . '">' . $aircraft->name . ' (' . $aircraft->liveryname . ')' . $notes . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                                <input requried hidden name="aircraft" id="addroute-aircraft-actual" />
                                            </div>
                                            <div class="form-group">
                                                <label for="">Notes</label>
                                                <input type="text" name="notes" class="form-control" />
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Add Route" />
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table class="table table-striped datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th class="mobile-hidden">Flight Number</th>
                                    <th>Departure</th>
                                    <th>Arrival</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (Page::$pageData->routes as $id => $route) {
                                    $route['id'] = $id;
                                    echo '<tr><td class="align-middle mobile-hidden">';
                                    echo $route['fltnum'];
                                    echo '</td><td class="align-middle">';
                                    echo $route['dep'];
                                    echo '</td><td class="align-middle">';
                                    echo $route['arr'];
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn bg-custom editRoute" data-route=\'' . Json::encode($route) . '\'><i class="fa fa-edit"></i></button>';
                                    echo '&nbsp;<button data-rid="' . $id . '" class="btn btn-danger deleteRoute"><i class="fa fa-trash"></i></button>';
                                    echo '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                        <div id="routeedit" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Edit Route</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="/admin/operations/routes" method="post" id="routeedit-form">
                                            <input hidden name="action" value="editroute">
                                            <input hidden name="id" id="routeedit-id" />
                                            <div class="form-group">
                                                <label for="routeedit-dep">Departure Airport</label>
                                                <input type="text" name="dep" id="routeedit-dep" class="form-control" placeholder="ICAO" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="aircraft">Arrival Airport</label>
                                                <input type="text" name="arr" id="routeedit-arr" class="form-control" placeholder="ICAO" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="aircraft">Flight Number</label>
                                                <input maxlength="10" type="text" name="fltnum" id="routeedit-fltnum" class="form-control" required />
                                            </div>
                                            <div class="form-group">
                                                <label for="aircraft">Flight Duration</label>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <input required type="number" min="0" id="routeedit-hrs" class="form-control" placeholder="Hours" />
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <input required type="number" min="0" id="routeedit-mins" class="form-control" placeholder="Minutes" />
                                                    </div>
                                                </div>
                                                <input hidden name="duration" id="routeedit-duration" class="form-control" required />
                                                <script>
                                                    function formatEditFlightTime() {
                                                        var hrs = $("#routeedit-hrs").val();
                                                        var mins = $("#routeedit-mins").val();
                                                        $("#routeedit-duration").val(hrs + ":" + mins);
                                                    }

                                                    function reverseFormatEditFlightTime() {
                                                        var formatted = $("#routeedit-duration").val();
                                                        if (formatted != '') {
                                                            var split = formatted.split(":");
                                                            var hrs = split[0];
                                                            var mins = split[1];
                                                            $("#routeedit-hrs").val(hrs);
                                                            $("#routeedit-mins").val(mins);
                                                        }
                                                    }

                                                    $(document).ready(function() {
                                                        $("#routeedit-hrs").on('change', function() {
                                                            formatEditFlightTime();
                                                        });
                                                        $("#routeedit-mins").on('change', function() {
                                                            formatEditFlightTime();
                                                        });
                                                        reverseFormatEditFlightTime();
                                                    });
                                                </script>
                                            </div>
                                            <div class="form-group">
                                                <label for="aircraft">Aircraft</label>
                                                <select multiple class="form-control selectpicker" data-live-search="true" id="routeedit-aircraft" required>
                                                    <option value>Select</option>
                                                    <?php
                                                    foreach (Page::$pageData->fleet as $a) {
                                                        $notes = $a->notes == null ? '' : ' - ' . $a->notes;
                                                        echo '<option value="' . $a->id . '">' . $a->name . ' (' . $a->liveryname . ')' . $notes . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                                <input requried hidden name="aircraft" id="routeedit-aircraft-actual" />
                                            </div>
                                            <div class="form-group">
                                                <label for="notes">Notes</label>
                                                <input type="text" name="notes" id="routeedit-notes" class="form-control" />
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Save" />
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="/admin/operations/routes/import">Import Routes</a>
                        <form id="deleteroute" method="post" action="/admin/operations/routes">
                            <input hidden value="deleteroute" name="action" />
                            <input hidden name="delete" id="deleteroute-id" />
                        </form>
                        <script>
                            $(".editRoute").click(function() {
                                var route = $(this).data('route');
                                var id = route.id;
                                var fltnum = route.fltnum;
                                var dep = route.dep;
                                var arr = route.arr;
                                var duration = Math.floor(route.duration / 3600) + ':' + Math.floor(route.duration % 3600 / 60);
                                var aircraft = route.aircraft.map(function(a) {
                                    return a.id;
                                });
                                var notes = route.notes;

                                $("#routeedit-id").val(id);
                                $("#routeedit-fltnum").val(fltnum);
                                $("#routeedit-dep").val(dep);
                                $("#routeedit-arr").val(arr);
                                $("#routeedit-duration").val(duration);
                                reverseFormatEditFlightTime();
                                $("#routeedit-aircraft").selectpicker('val', aircraft);
                                $("#routeedit-notes").val(notes);

                                $("#routeedit").modal('show');
                            });
                            $("#routeedit-aircraft").on('changed.bs.select', function() {
                                var acs = $("#routeedit-aircraft").val();
                                $("#routeedit-aircraft-actual").val(acs.join(','));
                            });
                            $("#addroute-aircraft").on('changed.bs.select', function() {
                                var acs = $("#addroute-aircraft").val();
                                $("#addroute-aircraft-actual").val(acs.join(','));
                            });
                            $("#addroute-form").submit(function(e) {
                                if ($("#addroute-aircraft-actual").val().length < 1) {
                                    e.preventDefault();
                                    alert('You must select at least one aircraft!');
                                }
                            });
                            $("#routeedit-form").submit(function(e) {
                                if ($("#routeedit-aircraft-actual").val().length < 1) {
                                    e.preventDefault();
                                    alert('You must select at least one aircraft!');
                                }
                            });
                            $(".deleteRoute").click(function() {
                                var conf = confirm('Are you sure you want to delete this route?');
                                if (!conf) return;

                                var id = $(this).data('rid');
                                $("#deleteroute-id").val(id);
                                $("#deleteroute").submit();
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