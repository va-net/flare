<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once '../core/init.php';

$user = new User();

Page::setTitle('Events Admin - '.Config::get('va/name'));

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
                        <h3>Manage Events</h3>
                        <button type="button" class="btn bg-custom mb-2" data-toggle="modal" data-target="#newevent">New Event</button>

                        <div class="modal fade" id="confirmEventDelete">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">

                            <div class="modal-header">
                                <h4 class="modal-title">Are You Sure?</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>

                            <div class="modal-body">
                                Are you sure you want to delete this Event?
                                <form id="deleteevent" action="/update.php" method="post">
                                    <input hidden name="action" value="deleteevent" />
                                    <input hidden name="delete" id="confirmEventDelete-id" />
                                    <input type="submit" class="btn btn-danger" value="Delete" />
                                </form>
                            </div>

                            <div class="modal-footer text-center justify-content-center">
                                <button type="button" class="btn bg-custom" data-dismiss="modal">Cancel</button>
                            </div>

                            </div>
                        </div>
                        </div>

                        <!-- Add Event Modal -->
                        <div class="modal fade" id="newevent">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Add Event</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>

                                <div class="modal-body">
                                    <form action="/update.php" method="post">
                                        <input hidden name="action" value="addevent" />
                                        <div class="form-group">
                                            <label for="event-name">Event Name</label>
                                            <input required type="text" class="form-control" name="name" id="event-name" />
                                        </div>
                                        <div class="form-group">
                                            <label for="event-description">Event Description</label>
                                            <textarea required class="form-control" name="description" id="event-description"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="event-date">Event Date</label>
                                            <input required type="date" class="form-control" name="date" id="event-date" min="<?= date("Y-m-d"); ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label for="event-time">Event Time</label>
                                            <select required class="form-control" name="time" id="event-time">
                                                <option value>Select</option>
                                                <?php
                                                    $times = ["00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", 
                                                    "18", "19", "20", "21", "22", "23"];
                                                    foreach ($times as $t) {
                                                        echo '<option value="'.$t.'00'.'">'.$t.'00Z</option>';
                                                        echo '<option value="'.$t.'30'.'">'.$t.'30Z</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="event-dep">Departure ICAO</label>
                                            <input required type="text" class="form-control" name="dep" id="event-dep" />
                                        </div>
                                        <div class="form-group">
                                            <label for="event-arr">Arrival ICAO</label>
                                            <input required type="text" class="form-control" name="arr" id="event-arr" />
                                        </div>
                                        <div class="form-group">
                                            <label for="event-aircraft">Aircraft</label>
                                            <select required class="form-control" name="aircraft" id="event-aircraft">
                                                <option value>Select</option>
                                                <?php
                                                    $activeAircraft = Aircraft::fetchActiveAircraft()->results();
                                                    foreach ($activeAircraft as $aircraft) {
                                                        echo '<option value="'.$aircraft->ifliveryid.'">'.$aircraft->name.' ('.$aircraft->liveryname.')</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="event-vis">Visible to Pilots?</label>
                                            <select required class="form-control" name="visible" id="event-vis">
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="event-serv">Event Server</label>
                                            <select required class="form-control" name="server" id="event-serv">
                                                <option value>Select</option>
                                                <option value="casual">Casual Server</option>
                                                <option value="training">Training Server</option>
                                                <option value="expert">Expert Server</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="event-gates">Gate Names</label>
                                            <input required type="text" class="form-control" name="gates" id="event-gates" />
                                            <small class="text-muted">Comma-Separated List of Gate Names</small>
                                        </div>
                                        
                                        <input type="submit" class="btn bg-custom" value="Add Event" />
                                    </form>
                                </div>
                            </div>
                        </div>
                        </div>

                        <table class="table table-striped">
                            <thead class="bg-custom text-center">
                                <tr>
                                    <th>Name</th>
                                    <th>Airport</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="events-table">
                                <tr><td colspan="3">Loading...</td></tr>
                            </tbody>
                        </table>
                        <script>
                            $.post("/vanet.php", {
                                "method": "events-admin"
                            }, function (data, status) {
                                $("#events-table").html(data);
                                $(".deleteEvent").click(function() {
                                    var id = $(this).data('id');
                                    $("#confirmEventDelete-id").val(id);
                                    $("#confirmEventDelete").modal('show');
                                });
                                $(".editEvent").click(function() {
                                    var eventName = $(this).data('name');
                                    var eventDesc = $(this).data('desc');
                                    var eventDep = $(this).data('dep');
                                    var eventArr = $(this).data('arr');
                                    var eventAircraft = $(this).data('aircraft');
                                    var eventVis = $(this).data('vis');
                                    var eventServer = $(this).data('server');
                                    var eventId = $(this).data('id');

                                    $("#editevent-name").val(eventName);
                                    $("#editevent-description").val(eventDesc);
                                    $("#editevent-dep").val(eventDep);
                                    $("#editevent-arr").val(eventArr);
                                    $("#editevent-aircraft").val(eventAircraft);
                                    $("#editevent-vis").val(eventVis);
                                    $("#editevent-serv").val(eventServer);
                                    $("#editevent-id").val(eventId);

                                    $("#editevent").modal('show');
                                });
                            });
                        </script>

                        <!-- Edit Event Modal -->
                        <div class="modal fade" id="editevent">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="editevent-title">Edit Event</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>

                                <div class="modal-body">
                                    <form action="/update.php" method="post">
                                        <input hidden name="action" value="editevent" />
                                        <input hidden name="id" id="editevent-id" />
                                        <div class="form-group">
                                            <label for="editevent-name">Event Name</label>
                                            <input required type="text" class="form-control" name="name" id="editevent-name" />
                                        </div>
                                        <div class="form-group">
                                            <label for="editevent-description">Event Description</label>
                                            <textarea required class="form-control" name="description" id="editevent-description"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="editevent-dep">Departure ICAO</label>
                                            <input required type="text" class="form-control" name="dep" id="editevent-dep" />
                                        </div>
                                        <div class="form-group">
                                            <label for="editevent-arr">Arrival ICAO</label>
                                            <input required type="text" class="form-control" name="arr" id="editevent-arr" />
                                        </div>
                                        <div class="form-group">
                                            <label for="editevent-aircraft">Aircraft</label>
                                            <select required class="form-control" name="aircraft" id="editevent-aircraft">
                                                <option value>Select</option>
                                                <?php
                                                    $activeAircraft = Aircraft::fetchActiveAircraft()->results();
                                                    foreach ($activeAircraft as $aircraft) {
                                                        echo '<option value="'.$aircraft->ifliveryid.'">'.$aircraft->name.' ('.$aircraft->liveryname.')</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="editevent-vis">Visible to Pilots?</label>
                                            <select required class="form-control" name="visible" id="editevent-vis">
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="editevent-serv">Event Server</label>
                                            <select required class="form-control" name="server" id="editevent-serv">
                                                <option value>Select</option>
                                                <option value="casual">Casual Server</option>
                                                <option value="training">Training Server</option>
                                                <option value="expert">Expert Server</option>
                                            </select>
                                        </div>
                                        
                                        <input type="submit" class="btn bg-custom" value="Save" />
                                    </form>
                                </div>
                            </div>
                        </div>
                        </div>
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