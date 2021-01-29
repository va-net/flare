<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Event - ' . Page::$pageData->va_name);
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
                        <?php if (Page::$pageData->event === FALSE) : ?>
                            <h3>Event Not Found</h3>
                        <?php else : ?>
                            <h3><?= Page::$pageData->event["name"]; ?></h3>
                            <p class="text-center">
                                <?= Page::$pageData->event["description"]; ?>
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

                            <div class="row">
                                <div class="col-lg-6 px-5">
                                    <h4 class="text-left">Event Details</h4>
                                    <p class="text-left">
                                        <b>Date & Time:</b> <?= Page::$pageData->event["dateTime"] . 'Z'; ?><br />
                                        <b>Departure:</b> <?= Page::$pageData->event["departureAirport"]; ?><br />
                                        <b>Arrival:</b> <?= Page::$pageData->event["arrivalAirport"]; ?><br />
                                        <b>Aircraft:</b> <?= Page::$pageData->event["aircraft"]["aircraftName"] . ' (' . Page::$pageData->event["aircraft"]["liveryName"] . ')'; ?><br />
                                        <b>Server:</b> <?= ucfirst(Page::$pageData->event["server"]); ?><br />
                                    </p>
                                    <h4 class="text-left">Aircraft Specifications</h4>
                                    <p class="text-left">
                                        <b>Max. Takeoff Weight:</b> <?= Page::$pageData->event["aircraft"]["maxTakeoffWeight"] . "kg"; ?><br />
                                        <b>Max. Landing Weight:</b> <?= Page::$pageData->event["aircraft"]["maxLandingWeight"] . "kg"; ?><br />
                                        <b>Never Exceed Speed:</b> <?= Page::$pageData->event["aircraft"]["neverExceed"]; ?><br />
                                        <b>Service Ceiling:</b> <?= Page::$pageData->event["aircraft"]["serviceCeiling"] . "ft"; ?><br />
                                        <b>Range:</b> <?= Page::$pageData->event["aircraft"]["range"] . " NM"; ?><br />
                                        <b>Approach Speed:</b> <?= Page::$pageData->event["aircraft"]["apprSpeedRef"] . "kts"; ?><br />
                                        <b>Max. Passengers:</b> <?= Page::$pageData->event["aircraft"]["maxPassengers"]; ?><br />
                                    </p>
                                </div>
                                <div class="col-lg-6 px-5">
                                    <h4 class="text-center">Gates</h4>
                                    <?php
                                    $mygate = array_values(array_filter(Page::$pageData->event['signups'], function ($s) {
                                        return $s['pilotId'] == Page::$pageData->user->data()->ifuserid;
                                    }));
                                    if (count($mygate) < 1) {
                                        $mygate = null;
                                    } else {
                                        $mygate = $mygate[0];
                                    }
                                    $freegates = array_filter(Page::$pageData->event['signups'], function ($s) {
                                        return $s['pilotId'] == null;
                                    });
                                    if ($mygate == null && count($freegates) > 0) {
                                        echo '<button class="btn bg-custom text-light changeStatus">Sign Up</button>';
                                    }
                                    ?>
                                    <table class="table table-striped text-center datatable-nosearch">
                                        <thead class="bg-custom">
                                            <tr>
                                                <th>Gate</th>
                                                <th>Pilot</th>
                                                <!-- <th>Actions</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $pilotdata = array_filter(Page::$pageData->user->getAllUsers(), function ($u) {
                                                return $u['ifuserid'] != null;
                                            });
                                            $names = array_map(function ($p) {
                                                return $p['name'];
                                            }, $pilotdata);
                                            $ids = array_map(function ($p) {
                                                return $p['ifuserid'];
                                            }, $pilotdata);
                                            $pilots = array_combine($ids, $names);


                                            foreach (Page::$pageData->event["signups"] as $gate) {
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
                                                // echo '</td><td class="align-middle">';
                                                // if ($mygate != null && $mygate['id'] == $gate['id']) {
                                                //     echo '<button data-gid="' . $gate['id'] . '"  class="btn bg-custom text-light changeStatus">Pull Out</button>';
                                                // }
                                                echo '</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <script>
                                $(".changeStatus").click(function() {
                                    $(this).hide();
                                    $.ajax('/api.php/events/<?= urlencode(Page::$pageData->event['id']) ?>', {
                                        method: 'PUT',
                                        success: function() {
                                            location.reload();
                                        }
                                    });
                                });
                            </script>
                        <?php endif; ?>
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