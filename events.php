<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

Page::setTitle('Events - ' . Config::get('va/name'));
Page::excludeAsset('chartjs');

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}
?>
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
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <?php if (!$IS_GOLD) : ?>
                            <h3>Events</h3>
                            <p><?= Config::get('va/name'); ?> needs to Subscribe to VANet Gold to access Events.</p>
                        <?php else : ?>
                            <?php if (empty(Input::get('page'))) : ?>
                                <h3>Events</h3>
                                <?php
                                if (Session::exists('error')) {
                                    echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                                }
                                if (Session::exists('success')) {
                                    echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                                }
                                ?>
                                <p>Welcome to the <?= Config::get('va/name'); ?> Events Page. Here you can see upcoming events and sign up.</p>

                                <table class="table table-striped text-center">
                                    <thead class="bg-custom">
                                        <tr>
                                            <th>Name</th>
                                            <th>Airport</th>
                                            <th>View</th>
                                        </tr>
                                    </thead>
                                    <tbody id="events-table">
                                        <tr>
                                            <td colspan="3">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <script>
                                    $.get("/api.php/events", function(data) {
                                        $("#events-table").html(data.result.map(function(e) {
                                            return `<tr><td class="align-middle">
                                            ${e.name}
                                            </td><td class="align-middle">
                                            ${e.departureIcao}
                                            </td><td class="align-middle">
                                            <a href="/events.php?page=view&event=${e.id}" class="btn bg-custom">View</button>
                                            </td></tr>`
                                        }).join(''));
                                    });
                                </script>
                            <?php elseif (Input::get('page') === 'view' && !empty(Input::get('event'))) : ?>
                                <?php $event = VANet::findEvent(Input::get('event')); ?>

                                <?php if ($event == null) : ?>
                                    <h3>Event Not Found</h3>
                                <?php else : ?>
                                    <h3><?= $event["name"]; ?></h3>
                                    <p class="text-center">
                                        <?= $event["description"]; ?>
                                    </p>
                                    <hr />
                                    <?php
                                    if (Session::exists('error')) {
                                        echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                                    }
                                    if (Session::exists('success')) {
                                        echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                                    }
                                    ?>

                                    <?php if ($user->data()->ifuserid == null) : ?>
                                        <p class="text-center">
                                            <b>
                                                You have not set up PIREPs yet. You will need to do this before you can view or register for this event.
                                                You can set up PIREPs <a href="pireps.php?page=new">here</a>.
                                            </b>
                                        </p>
                                    <?php else : ?>
                                        <div class="row">
                                            <div class="col-lg-6 px-5">
                                                <h4 class="text-left">Event Details</h4>
                                                <p class="text-left">
                                                    <b>Date & Time:</b> <?= str_replace('T', ' ', $event["date"]) . 'Z'; ?><br />
                                                    <b>Departure:</b> <?= $event["departureIcao"]; ?><br />
                                                    <b>Arrival:</b> <?= $event["arrivalIcao"]; ?><br />
                                                    <b>Aircraft:</b> <?= $event["aircraft"]["aircraftName"] . ' (' . $event["aircraft"]["liveryName"] . ')'; ?><br />
                                                    <b>Server:</b> <?= ucfirst($event["server"]); ?><br />
                                                </p>
                                                <h4 class="text-left">Aircraft Specifications</h4>
                                                <p class="text-left">
                                                    <b>Max. Takeoff Weight:</b> <?= $event["aircraft"]["maxTakeoffWeight"] . "kg"; ?><br />
                                                    <b>Max. Landing Weight:</b> <?= $event["aircraft"]["maxLandingWeight"] . "kg"; ?><br />
                                                    <b>Never Exceed Speed:</b> <?= $event["aircraft"]["neverExceed"]; ?><br />
                                                    <b>Service Ceiling:</b> <?= $event["aircraft"]["serviceCeiling"] . "ft"; ?><br />
                                                    <b>Range:</b> <?= $event["aircraft"]["range"] . " NM"; ?><br />
                                                    <b>Approach Speed:</b> <?= $event["aircraft"]["apprSpeedRef"] . "kts"; ?><br />
                                                    <b>Max. Passengers:</b> <?= $event["aircraft"]["maxPassengers"]; ?><br />
                                                </p>
                                            </div>
                                            <div class="col-lg-6 px-5">
                                                <h4 class="text-center">Gates</h4>
                                                <form id="signUp" action="update.php" method="post">
                                                    <input hidden name="action" value="eventsignup" />
                                                    <input hidden name="event" value="<?= $event["id"] ?>" />
                                                </form>
                                                <form id="vacate" action="update.php" method="post">
                                                    <input hidden name="action" value="vacateslot" />
                                                    <input hidden name="event" value="<?= $event["id"] ?>" />
                                                </form>
                                                <table class="table table-striped text-center datatable-nosearch">
                                                    <thead class="bg-custom">
                                                        <tr>
                                                            <th>Gate</th>
                                                            <th>Pilot</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $mygate = array_values(array_filter($event['slots'], function ($s) {
                                                            global $user;
                                                            return $s['pilotId'] == $user->data()->ifuserid;
                                                        }));
                                                        if (count($mygate) < 1) {
                                                            $mygate = null;
                                                        } else {
                                                            $mygate = $mygate[0];
                                                        }

                                                        $pilotdata = array_filter($user->getAllUsers(), function ($u) {
                                                            return $u['ifuserid'] != null;
                                                        });
                                                        $names = array_map(function ($p) {
                                                            return $p['name'];
                                                        }, $pilotdata);
                                                        $ids = array_map(function ($p) {
                                                            return $p['ifuserid'];
                                                        }, $pilotdata);
                                                        $pilots = array_combine($ids, $names);


                                                        foreach ($event["slots"] as $gate) {
                                                            $pilotName = $gate['pilotName'];
                                                            if ($pilotName === '' && isset($pilots[$gate['pilotId']])) {
                                                                $pilotName = $pilots[$gate['pilotId']];
                                                            } elseif ($pilotName === '') {
                                                                $pilotName = $gate['pilotId'];
                                                            }

                                                            echo '<tr><td class="align-middle">';
                                                            echo $gate["gate"];
                                                            echo '</td><td class="align-middle">';
                                                            echo $pilotName;
                                                            echo '</td><td class="align-middle">';
                                                            if ($mygate != null && $mygate['id'] == $gate['id']) {
                                                                echo '<button value="' . $gate['id'] . '" form="vacate" type="submit" class="btn bg-custom text-light" name="gate">Pull Out</button>';
                                                            } elseif ($mygate == null && $gate['pilotId'] == null) {
                                                                echo '<button value="' . $gate['id'] . '" form="signUp" type="submit" class="btn bg-custom text-light" name="gate">Sign Up</button>';
                                                            }
                                                            echo '</td></tr>';
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="text-center">
                                    <hr />
                                    <small><a href="events.php">Back to All Events</a></small>
                                </div>
                            <?php endif; ?>
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