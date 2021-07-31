<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('PIREPs Admin - ' . Config::get('va/name'));
$ACTIVE_CATEGORY = 'pirep-management';
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
        <div class="mt-4 text-center container-fluid" style="overflow: auto;">
            <div class="p-0 m-0 row">
                <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <?php
                        if (file_exists(__DIR__ . '/../install/install.php') && !file_exists(__DIR__ . '/../.development')) {
                            echo '<div class="text-center alert alert-danger">The Install Folder still Exists! Please delete it immediately, it poses a severe security risk.</div>';
                        }

                        if (Session::exists('error')) {
                            echo '<div class="text-center alert alert-danger">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="text-center alert alert-success">' . Session::flash('success') . '</div>';
                        }

                        $tab = "pending";
                        if (!empty(Input::get('tab'))) {
                            $tab = Input::get('tab');
                        }
                        ?>
                        <script>
                            $(document).ready(function() {
                                $("#<?= $tab; ?>link").click();
                            });
                        </script>
                        <h3>Manage PIREPs</h3>
                        <ul class="nav nav-tabs nav-dark justify-content-center">
                            <li class="nav-item">
                                <a class="nav-link" id="pendinglink" data-toggle="tab" href="#pending">Pending PIREPs</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="alllink" data-toggle="tab" href="#all">All PIREPs</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div id="pending" class="p-3 tab-pane container-fluid fade">
                                <form id="acceptpirep" action="/admin/pireps" method="post">
                                    <input hidden name="action" value="acceptpirep">
                                </form>
                                <form id="declinepirep" action="/admin/pireps" method="post">
                                    <input hidden name="action" value="declinepirep">
                                </form>
                                <table class="table table-striped">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th class="mobile-hidden">Callsign</th>
                                            <th class="mobile-hidden">Flight Number</th>
                                            <th>Dep<span class="mobile-hidden">arture</span></th>
                                            <th>Arr<span class="mobile-hidden">ival</span></th>
                                            <th>Flight Time</th>
                                            <th class="mobile-hidden">Multiplier</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $x = 0;
                                        foreach (Page::$pageData->pending as $pirep) {
                                            echo '<tr><td class="align-middle mobile-hidden">';
                                            echo $pirep['pilotcallsign'];
                                            echo '</td><td class="align-middle mobile-hidden">';
                                            echo $pirep['flightnum'];
                                            echo '</td><td class="align-middle">';
                                            echo $pirep['departure'];
                                            echo '</td><td class="align-middle">';
                                            echo $pirep['arrival'];
                                            echo '</td><td class="align-middle">';
                                            echo Time::secsToString($pirep["flighttime"]);
                                            echo '</td><td class="align-middle mobile-hidden">';
                                            echo $pirep["multi"];
                                            echo '</td><td class="align-middle">';
                                            echo '<button class="btn btn-success text-light" value="' . $pirep['id'] . '" form="acceptpirep" type="submit" name="accept"><i class="fa fa-check"></i></button>';
                                            echo '&nbsp;<button value="' . $pirep['id'] . '" form="declinepirep" type="submit" class="btn btn-danger text-light" name="decline"><i class="fa fa-times"></i></button>';
                                            echo '</td>';
                                            $x++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="all" class="p-3 tab-pane container-fluid fade">
                                <form method="post" action="/admin/pireps" id="delpirep">
                                    <input hidden name="action" value="delpirep" type="hidden" />
                                    <input hidden name="id" value="" type="hidden" id="delpirep-id" />
                                </form>
                                <table class="table table-striped datatable">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th>Date</th>
                                            <th class="mobile-hidden">Pilot</th>
                                            <th>Dep<span class="mobile-hidden">arture</span></th>
                                            <th>Arr<span class="mobile-hidden">ival</span></th>
                                            <th class="mobile-hidden">Status</th>
                                            <th><span class="mobile-hidden">Actions</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $statuses = [
                                            [
                                                "badge" => "warning",
                                                "text" => "Pending",
                                            ],
                                            [
                                                "badge" => "success",
                                                "text" => "Accepted",
                                            ],
                                            [
                                                "badge" => "danger",
                                                "text" => "Denied"
                                            ],
                                        ];
                                        foreach (Page::$pageData->all as $a) {
                                            echo '<tr><td class="align-middle">';
                                            echo $a['date'];
                                            echo '</td><td class="align-middle mobile-hidden">';
                                            echo $a['pilotname'];
                                            echo '</td><td class="align-middle">';
                                            echo $a['departure'];
                                            echo '</td><td class="align-middle">';
                                            echo $a['arrival'];
                                            echo '</td><td class="align-middle mobile-hidden">';
                                            $s = $statuses[$a['status']];
                                            echo '<span class="badge badge-' . $s['badge'] . '">' . $s['text'] . '</span>';
                                            echo '</td><td class="align-middle">';
                                            echo '<button class="mr-1 btn bg-custom editpirepbtn" data-pirep=\'' . json_encode($a) . '\'><i class="fa fa-edit"></i></button>';
                                            echo '<button class="btn btn-danger delpirepbtn" data-pirepid="' . $a['id'] . '"><i class="fa fa-trash"></i></button>';
                                            echo '</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>

                                <!-- Edit PIREP Modal -->
                                <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="editpirep-modal">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit PIREP</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="/admin/pireps" method="post">
                                                    <input hidden name="action" value="editpirep">
                                                    <input hidden name="id" id="editpirep-id">
                                                    <div class="form-group">
                                                        <label for="editpirep-date">Date of Flight</label>
                                                        <input required type="date" class="form-control" name="date" id="editpirep-date">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editpirep-fnum">Flight Number</label>
                                                        <input required type="text" class="form-control" name="fnum" id="editpirep-fnum">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editpirep-dep">Departure</label>
                                                        <input required maxlength="4" minlength="4" type="text" id="editpirep-dep" class="form-control" name="dep">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editpirep-arr">Arrival</label>
                                                        <input required maxlength="4" minlength="4" type="text" id="editpirep-arr" class="form-control" name="arr">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="">Flight Time</label>
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <input required type="number" min="0" id="editpirep-ftime-hrs" class="form-control" placeholder="Hours" />
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <input required type="number" min="0" id="editpirep-ftime-mins" class="form-control" placeholder="Minutes" />
                                                            </div>
                                                        </div>
                                                        <input hidden name="ftime" id="editpirep-ftime" class="form-control" required />
                                                        <script>
                                                            function formatFlightTime() {
                                                                var hrs = $("#editpirep-ftime-hrs").val();
                                                                var mins = $("#editpirep-ftime-mins").val();
                                                                $("#editpirep-ftime").val(hrs + ":" + mins);
                                                            }

                                                            function reverseFormatFlightTime() {
                                                                var formatted = $("#editpirep-ftime").val();
                                                                if (formatted != '') {
                                                                    var split = formatted.split(":");
                                                                    var hrs = split[0];
                                                                    var mins = split[1];
                                                                    $("#editpirep-ftime-hrs").val(hrs);
                                                                    $("#editpirep-ftime-mins").val(mins);
                                                                }
                                                            }

                                                            $(document).ready(function() {
                                                                $("#editpirep-ftime-hrs").on('change', function() {
                                                                    formatFlightTime();
                                                                });
                                                                $("#editpirep-ftime-mins").on('change', function() {
                                                                    formatFlightTime();
                                                                });
                                                                reverseFormatFlightTime();
                                                            });
                                                        </script>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editpirep-ac">Aircraft</label>
                                                        <select required class="form-control" name="aircraft" id="editpirep-ac">
                                                            <?php
                                                            $allaircraft = Aircraft::fetchActiveAircraft()->results();
                                                            foreach ($allaircraft as $aircraft) {
                                                                $notes = $aircraft->notes == null ? '' : ' - ' . $aircraft->notes;
                                                                echo '<option value="' . $aircraft->id . '">' . $aircraft->name . ' (' . $aircraft->liveryname . ')' . $notes . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editpirep-status">Status</label>
                                                        <select required class="form-control" name="status" id="editpirep-status">
                                                            <option value="0">Pending</option>
                                                            <option value="1">Accepted</option>
                                                            <option value="2">Denied</option>
                                                        </select>
                                                    </div>
                                                    <input type="submit" class="btn bg-custom" value="Save">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit/Delete PIREP Scripts -->
                                <script>
                                    $(".editpirepbtn").click(function() {
                                        var pirep = $(this).data('pirep');
                                        $("#editpirep-id").val(pirep.id);
                                        $("#editpirep-date").val(pirep.date);
                                        $("#editpirep-fnum").val(pirep.flightnum);
                                        $("#editpirep-dep").val(pirep.departure);
                                        $("#editpirep-arr").val(pirep.arrival);
                                        $("#editpirep-ac").val(pirep.aircraftid);
                                        $("#editpirep-status").val(pirep.status);

                                        var hrs = Math.floor(pirep.flighttime / 3600);
                                        if (hrs < 10) {
                                            hrs = `0${hrs}`;
                                        }
                                        $("#editpirep-ftime-hrs").val(hrs);
                                        var mins = Math.floor((pirep.flighttime - hrs * 3600) / 60);
                                        if (mins < 10) {
                                            mins = `0${mins}`;
                                        }
                                        $("#editpirep-ftime-mins").val(mins);
                                        formatFlightTime();
                                        $("#editpirep-modal").modal('show');
                                    });
                                    $(".delpirepbtn").click(function() {
                                        var conf = confirm('Are you sure you want to PERMANETLY delete this PIREP?');
                                        if (!conf) return;

                                        var id = $(this).data('pirepid');
                                        $("#delpirep-id").val(id);
                                        $("#delpirep").submit();
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .nav-tabs .nav-link {
                    color: #000 !important;
                }
            </style>
            <footer class="text-center container-fluid">
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