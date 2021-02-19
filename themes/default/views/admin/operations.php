<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Operations Admin - ' . Config::get('va/name'));

if (!$user->isLoggedIn()) {
    Redirect::to('/index.php');
} elseif (!$user->hasPermission('opsmanage') || !$user->hasPermission('admin')) {
    Redirect::to('/home.php');
}

$ACTIVE_CATEGORY = 'operations-management';
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
                        <?php if (Input::get('section') === 'fleet') : ?>
                            <h3>Fleet</h3>
                            <button type="button" class="btn bg-custom mb-2" data-toggle="modal" data-target="#addAircraft">Add Aircraft</button>
                            <div id="addAircraft" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Add Aircraft</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/update.php" method="post">
                                                <input hidden name="action" value="addaircraft">
                                                <div class="form-group">
                                                    <label for="aircraft">Type</label>
                                                    <select required class="form-control" name="aircraftselect" id="aircraftselect" required>
                                                        <option value>Select</option>
                                                        <?php
                                                        $allac = Aircraft::fetchAllAircraftFromVANet();
                                                        foreach ($allac as $id => $name) {
                                                            echo '<option value="' . $id . '">' . $name . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="rank">Livery</label>
                                                    <select required class="form-control" name="livery" id="liveriesselect" required>
                                                        <option disabled>Loading...</option>
                                                    </select>
                                                </div>
                                                <script>
                                                    $("#aircraftselect").change(function() {
                                                        $.ajax({
                                                            url: "/vanet.php",
                                                            type: "POST",
                                                            data: {
                                                                method: "liveriesforaircraft",
                                                                data: $(this).val()
                                                            },
                                                            success: function(html) {
                                                                $("#liveriesselect").empty();
                                                                $("#liveriesselect").append("<option>Select</option>");
                                                                $("#liveriesselect").append(html);
                                                            }
                                                        });
                                                    });
                                                </script>
                                                <div class="form-group">
                                                    <label for="rank">Minimum Rank</label>
                                                    <select required class="form-control" name="rank" required>
                                                        <option value>Select</option>
                                                        <?php
                                                        $ranks = Rank::fetchAllNames()->results();

                                                        foreach ($ranks as $rank) {
                                                            echo '<option value="' . $rank->id . '">' . $rank->name . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="notes">Notes</label>
                                                    <input type="text" class="form-control" maxlength="12" name="notes" id="notes" />
                                                </div>
                                                <input type="submit" class="btn bg-custom" value="Add Aircraft">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="confirmFleetDelete">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h4 class="modal-title">Are You Sure?</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>

                                        <div class="modal-body">
                                            Are you sure you want to delete this Aircraft?
                                            <form id="deleteaircraft" action="/update.php" method="post">
                                                <input hidden name="action" value="deleteaircraft" />
                                                <input hidden name="delete" id="confirmFleetDelete-id" />
                                                <input type="submit" class="btn btn-danger" value="Delete" />
                                            </form>
                                        </div>

                                        <div class="modal-footer text-center justify-content-center">
                                            <button type="button" class="btn bg-custom" data-dismiss="modal">Cancel</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <form id="deleteaircraft" method="post" action="/update.php">
                                <input hidden value="deleteaircraft" name="action">
                            </form>
                            <table class="table table-striped datatable">
                                <thead class="bg-custom">
                                    <tr>
                                        <th>Name</th>
                                        <th class="mobile-hidden">Livery</th>
                                        <th class="mobile-hidden">Min. Rank</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $all = Aircraft::fetchActiveAircraft()->results();
                                    foreach ($all as $aircraft) {
                                        echo '<tr><td class="align-middle">';
                                        echo $aircraft->name;
                                        echo '</td><td class="align-middle mobile-hidden">';
                                        echo $aircraft->liveryname;
                                        echo '</td><td class="align-middle mobile-hidden">';
                                        echo $aircraft->rank;
                                        echo '</td><td class="align-middle">';
                                        echo '&nbsp;<button data-id="' . $aircraft->id . '" class="btn btn-danger text-light deleteFleet"><i class="fa fa-trash"></i></button>';
                                        echo '&nbsp;<button class="btn btn-primary editFleet" data-acName="' . $aircraft->name . ' (' . $aircraft->liveryname . ')' . '" 
                                        data-rankReq="' . $aircraft->rankreq . '" data-id="' . $aircraft->id . '" data-notes="' . $aircraft->notes . '">
                                        <i class="fa fa-edit"></i></button>';
                                        echo '</td>';
                                    }
                                    ?>
                                </tbody>
                            </table>

                            <script>
                                $(".deleteFleet").click(function() {
                                    var id = $(this).data('id');
                                    $("#confirmFleetDelete-id").val(id);
                                    $("#confirmFleetDelete").modal('show');
                                });
                            </script>

                            <div class="modal fade" id="fleetedit">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="fleetedit-title"></h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/update.php" method="post">
                                                <input hidden name="action" value="editfleet" />
                                                <input hidden name="id" id="fleetedit-id" />
                                                <div class="form-group">
                                                    <label for="fleetedit-rank">Minimum Rank</label>
                                                    <select required class="form-control" name="rank" id="fleetedit-rank">
                                                        <?php
                                                        $ranks = Rank::fetchAllNames()->results();
                                                        foreach ($ranks as $r) {
                                                            echo '<option id="fleetedot-rank-' . $r->id . '" value="' . $r->id . '">' . $r->name . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="fleetedit-notes">Notes</label>
                                                    <input type="text" class="form-control" maxlength="12" name="notes" id="fleetedit-notes" />
                                                </div>
                                                <input type="submit" class="btn bg-custom" value="Save" />
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                                $(".editFleet").click(function() {
                                    var acName = $(this).data('acname');
                                    var acRank = $(this).data('rankreq');
                                    var acId = $(this).data('id');
                                    var acNotes = $(this).data('notes');

                                    $("#fleetedit-title").text("Edit Aircraft: " + acName);
                                    $("#fleetedit-id").val(acId);
                                    $("#fleetedit-rank").val(acRank);
                                    $("#fleetedit-notes").val(acNotes);

                                    $("#fleetedit").modal('show');
                                });
                            </script>
                        <?php elseif (Input::get('section') === 'routes') : ?>
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
                                            <form action="/update.php" method="post" id="addroute-form">
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
                                                        $all = Aircraft::fetchActiveAircraft()->results();

                                                        foreach ($all as $aircraft) {
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
                                    $all = Route::fetchAll();

                                    foreach ($all as $id => $route) {
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
                                            <form action="/update.php" method="post" id="routeedit-form">
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
                                                        $aircraft = Aircraft::fetchAllAircraft()->results();

                                                        foreach ($aircraft as $a) {
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
                            <a href="?page=opsmanage&section=phpvms">Import Routes</a>
                            <form id="deleteroute" method="post" action="/update.php">
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
                        <?php elseif (Input::get('section') === 'ranks') : ?>
                            <!--  -->
                        <?php elseif (Input::get('section') === 'phpvms') : ?>
                            <h3>phpVMS Importer</h3>
                            <p>
                                Here, you can import your routes from phpVMS.
                            </p>
                            <?php if (empty(Input::get('action'))) : ?>
                                <form method="post" enctype="multipart/form-data">
                                    <input hidden name="action" value="phpvms" />
                                    <div class="custom-file mb-2">
                                        <input required type="file" class="custom-file-input" name="routes-upload" accept=".csv" id="routes-upload">
                                        <label class="custom-file-label" id="routes-upload-label" for="routes-upload">Routes File</label>
                                    </div>
                                    <input type="submit" class="btn bg-custom" value="Process" />
                                </form>
                                <script>
                                    $("#routes-upload").on("change", function() {
                                        var fileName = $(this).val().split("\\").pop();
                                        $(this).siblings("#routes-upload-label").addClass("selected").html(fileName);
                                    });

                                    $("#aircraft-upload").on("change", function() {
                                        var fileName = $(this).val().split("\\").pop();
                                        $(this).siblings("#aircraft-upload-label").addClass("selected").html(fileName);
                                    });
                                </script>
                            <?php else : ?>
                                <p>
                                    So we can import everything correctly, please select the aircraft type and livery for each registration.
                                    These aircraft will be added with the lowest rank if they do not already exist in your VA's database.
                                </p>
                                <?php
                                $file = Input::getFile('routes-upload');
                                if ($file["error"] == 1 || $file["error"] === TRUE) {
                                    Session::flash('error', 'Upload failed. Maybe your file is too big?');
                                    echo '<script>window.location.href= "admin.php?page=opsmanage&section=phpvms";</script>';
                                    die();
                                }
                                $routes = file_get_contents($file["tmp_name"]);
                                preg_match_all('/\r?\n.*/m', $routes, $routelines);
                                $routesArray = array_map(function ($l) {
                                    $l = trim($l);
                                    $segments = str_getcsv($l);
                                    if (strpos($segments[10], ".") === FALSE && strpos($segments[10], ":") === FALSE) {
                                        $segments[10] .= ".00";
                                    }

                                    return array(
                                        "fltnum" => $segments[1],
                                        "dep" => $segments[2],
                                        "arr" => $segments[3],
                                        "duration" => Time::strToSecs(str_replace('.', ':', $segments[10])),
                                        "aircraftid" => $segments[5]
                                    );
                                }, $routelines[0]);

                                $routesJson = Json::encode($routesArray);

                                $allAircraft = Aircraft::fetchAllAircraftFromVANet();
                                $aircraftOptions = "";
                                foreach ($allAircraft as $id => $name) {
                                    $aircraftOptions .= '<option value="' . $id . '">' . $name . '</option>';
                                }

                                echo '<form action="/update.php" method="post">';
                                echo '<input hidden name="action" value="phpvms" />';
                                echo "<input hidden name='rJson' value='$routesJson' />";
                                $j = 0;
                                $doneAircraft = [];
                                echo '<table class="w-100 mb-2">';
                                $i = 0;
                                foreach ($routesArray as $r) {
                                    if (!in_array($r['aircraftid'], $doneAircraft)) {
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
                                }
                                echo '</table>';
                                echo '<input type="submit" class="btn bg-custom" value="Import Now" />';
                                echo '</form>';

                                echo '<script>
                                        $(document).ready(function() {
                                            $(".aircraftSelect").change(function() {
                                                var id = $(this).attr("id");
                                                $("#livery" + id).html("<option value>Loading...</option>");
                                                $.ajax({
                                                    url: "/vanet.php",
                                                    type: "POST",
                                                    data: { method: "liveriesforaircraft", data: $(this).val() },
                                                    success: function(html){
                                                        $("#livery" + id).empty();
                                                        $("#livery" + id).append("<option>Select</option>");
                                                        $("#livery" + id).append(html);
                                                    }
                                                });
                                            });
                                        });
                                    </script>';
                                ?>
                            <?php endif; ?>
                        <?php endif; ?>
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