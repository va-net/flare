<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Operations Admin - '.Config::get('va/name'));

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
                    <div id="loader-wrapper"><div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div></div>
                    <div class="loaded">
                        <?php
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: '.Session::flash('error').'</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">'.Session::flash('success').'</div>';
                        }
                        ?>
                        <?php if (Input::get('section') === 'fleet'): ?>
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
                                                            echo '<option value="'.$id.'">'.$name.'</option>';
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
                                                            data: { method: "liveriesforaircraft", data: $(this).val() },
                                                            success: function(html){
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
                                                            echo '<option value="'.$rank->id.'">'.$rank->name.'</option>';
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
                                        echo '&nbsp;<button data-id="'.$aircraft->id.'" class="btn btn-danger text-light deleteFleet"><i class="fa fa-trash"></i></button>';
                                        echo '&nbsp;<button class="btn btn-primary editFleet" data-acName="'.$aircraft->name.' ('.$aircraft->liveryname.')'.'" 
                                        data-rankReq="'.$aircraft->rankreq.'" data-id="'.$aircraft->id.'" data-notes="'.$aircraft->notes.'">
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

                            <a href="?page=opsmanage&section=export">Export Aircraft</a> | <a href="?page=opsmanage&section=import">Import Aircraft</a>

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
                                                            echo '<option id="fleetedot-rank-'.$r->id.'" value="'.$r->id.'">'.$r->name.'</option>';
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
                        <?php elseif (Input::get('section') === 'routes'): ?>
                            <h3>Route Management</h3>
                            <p>Here you can Manage your VA's Routes.</p>
                            <button type="button" class="btn bg-custom mb-2" data-toggle="modal" data-target="#addRoute">Add Route</button>
                            <div id="addRoute" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Add Route</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/update.php" method="post">
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
                                                            $("#flightTimeHrs").keyup(function() {
                                                                formatFlightTime();
                                                            });
                                                            $("#flightTimeMins").keyup(function() {
                                                                formatFlightTime();
                                                            });
                                                            reverseFormatFlightTime();
                                                        });
                                                    </script>
                                                </div>
                                                <div class="form-group">
                                                    <label for="">Aircraft</label>
                                                    <select class="form-control" name="aircraft" required>
                                                        <option value>Select</option>
                                                        <?php
                                                        $all = Aircraft::fetchActiveAircraft()->results();

                                                        foreach ($all as $aircraft) {
                                                            $notes = $aircraft->notes == null ? '' : ' - '.$aircraft->notes;
                                                            echo '<option value="'.$aircraft->id.'">'.$aircraft->name.' ('.$aircraft->liveryname.')'.$notes.'</option>';
                                                        }
                                                        ?>
                                                    </select>
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
                                        <th class="mobile-hidden">Aircraft</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $all = Route::fetchAll()->results();

                                    foreach ($all as $route) {
                                        echo '<tr><td class="align-middle mobile-hidden">';
                                        echo $route->fltnum;
                                        echo '</td><td class="align-middle">';
                                        echo $route->dep;
                                        echo '</td><td class="align-middle">';
                                        echo $route->arr;
                                        echo '</td><td class="align-middle mobile-hidden">';
                                        echo $route->aircraft.'<span class="mobile-hidden"> ('.$route->livery.')</span>';
                                        echo '</td><td class="align-middle">';
                                        echo '<button class="btn bg-custom editRoute" 
                                        data-id="'.$route->id.'" data-fltnum="'.$route->fltnum.'" 
                                        data-dep="'.$route->dep.'" data-arr="'.$route->arr.'" 
                                        data-duration="'.Time::secsToString($route->duration).'" data-aircraft="'.$route->aircraftid.'" 
                                        ><i class="fa fa-edit"></i></button>';
                                        echo '&nbsp;<button value="'.$route->id.'" form="deleteroute" type="submit" class="btn btn-danger text-light" name="delete"><i class="fa fa-trash"></i></button>';
                                        echo '</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <div id="routeedit" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Edit Route</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/update.php" method="post">
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
                                                            $("#routeedit-hrs").keyup(function() {
                                                                formatEditFlightTime();
                                                            });
                                                            $("#routeedit-mins").keyup(function() {
                                                                formatEditFlightTime();
                                                            });
                                                            reverseFormatEditFlightTime();
                                                        });
                                                    </script>
                                                </div>
                                                <div class="form-group">
                                                    <label for="aircraft">Aircraft</label>
                                                    <select class="form-control" id="routeedit-aircraft" name="aircraft" required>
                                                        <option value>Select</option>
                                                        <?php
                                                        $aircraft = Aircraft::fetchAllAircraft()->results();

                                                        foreach ($aircraft as $a) {
                                                            $notes = $a->notes == null ? '' : ' - '.$a->notes;
                                                            echo '<option value="'.$a->id.'">'.$a->name.' ('.$a->liveryname.')'.$notes.'</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <input type="submit" class="btn bg-custom" value="Save" />
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="?page=opsmanage&section=export">Export Routes</a> | <a href="?page=opsmanage&section=import">Import Routes</a>
                            <form id="deleteroute" method="post" action="/update.php">
                                <input hidden value="deleteroute" name="action">
                            </form>
                            <script>
                                $(".editRoute").click(function() {
                                    var id = $(this).data('id');
                                    var fltnum = $(this).data('fltnum');
                                    var dep = $(this).data('dep');
                                    var arr = $(this).data('arr');
                                    var duration = $(this).data('duration');
                                    var aircraft = $(this).data('aircraft');

                                    $("#routeedit-id").val(id);
                                    $("#routeedit-fltnum").val(fltnum);
                                    $("#routeedit-dep").val(dep);
                                    $("#routeedit-arr").val(arr);
                                    $("#routeedit-duration").val(duration);
                                    reverseFormatEditFlightTime();
                                    $("#routeedit-aircraft").val(aircraft);

                                    $("#routeedit").modal('show');
                                });
                            </script>
                        <?php elseif (Input::get('section') === 'ranks'): ?>
                            <h3>Manage Ranks</h3>
                            <p>Here you can Manage the Ranks that your pilots can be Awarded.</p>
                            <button type="button" class="btn bg-custom mb-2" data-toggle="modal" data-target="#addRank">Add Rank</button>
                            <div id="addRank" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Add Rank</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/update.php" method="post">
                                                <input hidden name="action" value="addrank">
                                                <div class="form-group">
                                                    <label for="name">Name</label>
                                                    <input type="text" name="name" class="form-control" placeholder="Second Officer" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="time">Flight time required (in hours)</label>
                                                    <input type="number" name="time" class="form-control" placeholder="50" required>
                                                </div>
                                                <input type="submit" class="btn bg-custom" value="Add Rank">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form id="delrank" action="/update.php" method="post">
                                <input hidden name="action" value="delrank" />
                            </form>

                            <table class="table table-striped datatable">
                                <thead class="bg-custom">
                                    <tr>
                                        <th>Name</th>
                                        <th>Min. Hours</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $all = Rank::fetchAllNames()->results();
                                    foreach ($all as $rank) {
                                        echo '<tr><td class="align-middle">';
                                        echo $rank->name;
                                        echo '</td><td class="align-middle">';
                                        echo Time::secsToString($rank->timereq);
                                        echo '</td><td class="align-middle">';
                                        echo '<button class="btn btn-primary text-light editRank" 
                                        data-id="'.$rank->id.'" data-name="'.$rank->name.'" 
                                        data-minhrs="'.($rank->timereq / 3600).'">
                                        <i class="fa fa-edit"></i></button>';
                                        echo '&nbsp;<button class="btn btn-danger text-light" 
                                        value="'.$rank->id.'" form="delrank" name="delete">
                                        <i class="fa fa-trash"></i></button>';
                                        echo '</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <div id="rankmodal" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="rankmodal-title"></h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/update.php" method="post">
                                                <input hidden name="action" value="editrank">
                                                <input hidden name="id" id="rankmodal-id">
                                                <div class="form-group">
                                                    <label for="name">Name</label>
                                                    <input type="text" name="name" class="form-control" id="rankmodal-name" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="time">Flight Time Required (in hours)</label>
                                                    <input type="number" min="0" name="time" class="form-control" id="rankmodal-hours" required>
                                                </div>
                                                <input type="submit" class="btn bg-custom" value="Save">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <script>
                                $('.editRank').click(function(e) {
                                    var rankId = $(this).data("id");
                                    var rankName = $(this).data("name");
                                    var rankHrs = $(this).data("minhrs");

                                    $("#rankmodal-id").val(rankId);
                                    $("#rankmodal-name").val(rankName);
                                    $("#rankmodal-hours").val(rankHrs);
                                    $("#rankmodal-title").text("Edit Rank - " + rankName);

                                    $("#rankmodal").modal("show");
                                });
                            </script>
                        <?php elseif (Input::get('section') === 'import'): ?>
                            <h3>Import Operations Files</h3>
                            <p>
                                Here, you can import Flare JSON Files containg routes and aircraft into your database.
                                Please note when you are importing aircraft, they will all be set to the default rank.<br /><br />

                                Alternatively, you can import your routes from the phpVMS 
                                format <a href="admin.php?page=opsmanage&section=phpvms">here</a>.
                            </p>

                            <ul class="nav nav-tabs nav-dark justify-content-center">
                                <li class="nav-item">
                                    <a class="nav-link active" id="importrouteslink" data-toggle="tab" href="#routes">Import Routes</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="importaircraftlink" data-toggle="tab" href="#aircraft">Import Aircraft</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div id="routes" class="tab-pane container-fluid p-3 active">
                                    <h4>Import Routes</h4>
                                    <form action="/update.php" method="post" enctype="multipart/form-data">
                                        <input hidden name="action" value="importroutes" />
                                        <div class="custom-file mb-2">
                                            <input required type="file" class="custom-file-input" name="upload" accept=".json" id="importroutes-upload">
                                            <label class="custom-file-label" id="importroutes-upload-label" for="importroutes-upload">Choose File</label>
                                        </div>
                                        <input type="submit" class="btn bg-custom" value="Import" />
                                    </form>
                                </div>
                                <div id="aircraft" class="tab-pane container-fluid p-3 fade">
                                    <h4>Import Aircraft</h4>
                                    <form action="/update.php" method="post" enctype="multipart/form-data">
                                        <input hidden name="action" value="importaircraft" />
                                        <div class="custom-file mb-2">
                                            <input required type="file" class="custom-file-input" name="upload" accept=".json" id="importaircraft-upload">
                                            <label class="custom-file-label" id="importaircraft-upload-label" for="importaircraft-upload">Choose File</label>
                                        </div>
                                        <input type="submit" class="btn bg-custom" value="Import" />
                                    </form>
                                </div>
                            </div>

                            <style>
                                .nav-tabs .nav-link {
                                    color: #000!important;
                                }
                            </style>
                            <script>
                                $("#importroutes-upload").on("change", function() {
                                    var fileName = $(this).val().split("\\").pop();
                                    $(this).siblings("#importroutes-upload-label").addClass("selected").html(fileName);
                                });

                                $("#importaircraft-upload").on("change", function() {
                                    var fileName = $(this).val().split("\\").pop();
                                    $(this).siblings("#importaircraft-upload-label").addClass("selected").html(fileName);
                                });
                            </script>
                        <?php elseif (Input::get('section') === 'export'): ?>
                            <h3>Export Operations Files</h3>
                            <p>
                                Here, you can export your aircraft and routes to Flare JSON files.
                                These are useful for backups.
                            </p>
                            <div class="row">
                                <div class="col-lg-6">
                                    <a href="/update.php?action=exportroutes" download="routes.json" class="btn bg-custom">Export Routes</a>
                                </div>
                                <div class="col-lg-6">
                                    <a href="/update.php?action=exportaircraft" download="aircraft.json" class="btn bg-custom">Export Aircraft</a>
                                </div>
                            </div>
                        <?php elseif (Input::get('section') === 'phpvms'): ?>
                            <h3>phpVMS Importer</h3>
                            <p>
                                Here, you can import your routes from phpVMS. 
                            </p>
                            <?php if (empty(Input::get('action'))): ?>
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
                            <?php else: ?>
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
                                    preg_match_all('/\n.*/m', $routes, $routelines);

                                    $routesArray = [];
                                    foreach ($routelines[0] as $l) {
                                        $segments = preg_split('/, ?/', $l);
                                        array_push($routesArray, array(
                                            "fltnum" => $segments[1],
                                            "dep" => $segments[2],
                                            "arr" => $segments[3],
                                            "duration" => Time::strToSecs(str_replace('.', ':', $segments[10])),
                                            "aircraftid" => $segments[5]
                                        ));
                                    }

                                    $routesJson = Json::encode($routesArray);

                                    $allAircraft = Aircraft::fetchAllAircraftFromVANet();
                                    $aircraftOptions = "";
                                    foreach ($allAircraft as $id => $name) {
                                        $aircraftOptions .= '<option value="'.$id.'">'.$name.'</option>';
                                    }

                                    echo '<form action="/update.php" method="post">';
                                    echo '<input hidden name="action" value="phpvms" />';
                                    echo "<input hidden name='rJson' value='$routesJson' />";
                                    $j = 0;
                                    $doneAircraft = [];
                                    echo '<table class="w-100 mb-2">';
                                    for ($j=0; $j<$i-1; $j++) {
                                        $r = $routesArray[$j];
                                        if (!in_array($r['aircraftid'], $doneAircraft)) {
                                            echo '<tr class="border-bottom border-top"><td class="align-middle p-2"><b>';
                                            echo $r['aircraftid'];
                                            echo '</b></td><td class="align-middle py-2">';
                                            echo '<input hidden name="rego'.$j.'" value="'.$r["aircraftid"].'" />';
                                            echo '<select required class="form-control mb-2 aircraftSelect" name="aircraft'.$j.'" id="'.$j.'">';
                                            echo '<option value>Aircraft Type</option>';
                                            echo $aircraftOptions;
                                            echo '</select>';
                                            echo '<select required class="form-control" name="livery'.$j.'" id="livery'.$j.'">';
                                            echo '<option value>Select an Aircraft to Get Liveries</option>';
                                            echo '</select>';
                                            echo '</td></tr>';
                                            array_push($doneAircraft, $r['aircraftid']);
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
                                                    url: "vanet.php",
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