<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Home - ' . Page::$pageData->va_name);
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
        <div class="mt-4 text-center container-fluid" style="overflow: auto;">
            <div class="p-0 m-0 row">
                <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <h3>Pilot Home</h3>
                        <p>Welcome to the <?= escape(Page::$pageData->va_name) ?> Crew Center, <?= escape(Page::$pageData->user->data()->name) ?>!</p>
                        <!-- profile -->
                        <section id="profile" class="mb-2">
                            <h3>Your Profile</h3>
                            <?php
                            if (Session::exists('error')) {
                                echo '<div class="text-center alert alert-danger">Error: ' . Session::flash('error') . '</div>';
                            }
                            if (Session::exists('success')) {
                                echo '<div class="text-center alert alert-success">' . Session::flash('success') . '</div>';
                            }
                            ?>
                            <button type="button" class="mb-2 btn bg-custom" data-toggle="modal" data-target="#editMyProfile">Edit Profile</button>
                            <button type="button" class="mb-2 btn bg-custom" data-toggle="modal" data-target="#changePassword">Change Password</button>
                            <!-- edit profile form -->
                            <div id="editMyProfile" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Edit Profile</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/home" method="post">
                                                <input type="hidden" name="action" value="editprofile" />
                                                <div class="form-group">
                                                    <label for="name">Name</label>
                                                    <input type="text" maxlegnth="120" name="name" id="name" class="form-control" required value="<?= escape(Page::$pageData->user->data()->name) ?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label for="callsign">Callsign</label>
                                                    <input type="text" name="callsign" id="callsign" class="form-control" required value="<?= escape(Page::$pageData->user->data()->callsign) ?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" name="email" id="email" class="form-control" required value="<?= escape(Page::$pageData->user->data()->email) ?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label for="ifc">IFC URL</label>
                                                    <input type="url" name="ifc" id="ifc" class="form-control" required value="<?= escape(Page::$pageData->user->data()->ifc) ?>" />
                                                </div>
                                                <input type="submit" class="btn bg-custom" value="Edit Profile" />
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- change password form -->
                            <div id="changePassword" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Change Password</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/home" method="post">
                                                <input type="hidden" name="action" value="changepass">
                                                <div class="form-group">
                                                    <label for="oldpass">Old Password</label>
                                                    <input type="password" name="oldpass" id="oldpass" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="newpass">New Password</label>
                                                    <input type="password" minlengh="8" name="newpass" id="newpass" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="confpass">Confirm New Password</label>
                                                    <input type="password" name="confpass" id="confpass" class="form-control" required>
                                                </div>
                                                <p id="cpError" class="text-danger"></p>
                                                <input type="submit" class="btn bg-custom" id="cpSubmit" value="Change Password">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <!-- stats table -->
                        <section id="stats" class="mb-2">
                            <table class="table mb-0 border-bottom">
                                <tr>
                                    <td class="align-middle"><b>Name</b></td>
                                    <td class="align-middle"><?= escape(Page::$pageData->user->data()->name) ?></td>
                                </tr>
                                <tr>
                                    <td class="align-middle"><b>IFC Profile</b></td>
                                    <?php
                                    Page::$pageData->username = explode('/', Page::$pageData->user->data()->ifc);
                                    if (Page::$pageData->username === FALSE || count(Page::$pageData->username) < 5) {
                                        Page::$pageData->username = [null, null, null, null, ''];
                                    }
                                    ?>
                                    <td class="align-middle"><a href="<?= Page::$pageData->user->data()->ifc ?>" target="_blank"><?= escape(Page::$pageData->username[4]) ?></td>
                                </tr>
                                <tr>
                                    <td class="align-middle"><b>Callsign</b></td>
                                    <td class="align-middle"><?= escape(Page::$pageData->user->data()->callsign) ?></td>
                                </tr>
                                <tr>
                                    <td class="align-middle"><b>Flight Time</b></td>
                                    <td class="align-middle"><?= escape(Time::secsToString(Page::$pageData->user->getFlightTime())) ?></td>
                                </tr>
                                <tr>
                                    <td class="align-middle"><b>Rank</b></td>
                                    <?php
                                    $next = Page::$pageData->user->nextrank();
                                    $tip = "The Top Rank!";
                                    if ($next != null) {
                                        $hrs = $next->timereq / 3600;
                                        $tip = "Next Rank: {$next->name} ({$hrs}hrs)";
                                    }
                                    ?>
                                    <td class="align-middle" data-toggle="tooltip" title="<?= $tip ?>"><?= escape(Page::$pageData->user->rank()) ?></td>
                                </tr>
                                <tr>
                                    <td class="align-middle"><b>PIREPs</b></td>
                                    <td class="align-middle"><?= escape(Page::$pageData->user->numPirepsFiled()) ?></td>
                                </tr>
                            </table>
                        </section>
                        <!-- news -->
                        <section id="news" class="mb-3">
                            <h3>News Feed</h3>
                            <div id="news-container">
                                <p>Loading...</p>
                            </div>
                        </section>
                        <!-- pireps -->
                        <section id="pireps" class="mb-3">
                            <h3>Your Recent PIREPs</h3>
                            <table class="table table-striped">
                                <thead class="bg-custom">
                                    <tr>
                                        <th class="mobile-hidden">Flight Number</th>
                                        <th>Route</th>
                                        <th class="mobile-hidden">Date</th>
                                        <th class="mobile-hidden">Aircraft</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="pireps-body">
                                    <tr class="mobile-hidden">
                                        <td colspan="5">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>
                        <?php if (Page::$pageData->is_gold && VANet::featureEnabled('events')) : ?>
                            <!-- events -->
                            <section id="events" class="mb-3">
                                <h3>Upcoming Events</h3>
                                <table class="table text-center table-striped">
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
                            </section>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <footer class="text-center container-fluid">
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
    <script>
        <?php if (Page::$pageData->is_gold && VANet::featureEnabled('events')) : ?>
            // Load Events
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
        <?php endif; ?>

        // Load News
        $.get("/api.php/news", function(data, status) {
            if (data.status != 0) {
                $("#news-container").html('<p>Failed to load news</p>');
                return;
            }

            if (data.result.length == 0) {
                $("#news-container").html('<p class="mb-2">No news!</p>');
                return;
            }

            data.result = data.result.slice(0, 4);

            var newsHtml = data.result.map(function(n) {
                return `<div class="mb-3 card"><div class="card-body">
                        <h5 class="card-title"><u>${n.title}</u></h5>
                        <p><small><i class="fa fa-user"></i> ${n.author}&nbsp;&nbsp;&nbsp;
                        <i class="fa fa-clock"></i> ${n.dateposted}</small></p>
                        <p class="card-text">${n.content}</p>
                        </div></div>`;
            });
            $("#news-container").html(newsHtml.join(''));
        });

        // Load PIREPs
        $.get("/api.php/pireps", function(data, status) {
            if (data.status != 0) {
                $("#pireps-body").html('<tr class="mobile-hidden"><td colspan="5">Failed to load PIREPs</td></tr>');
                return;
            }
            if (data.result.length == 0) {
                $("#pireps-body").html('<tr class="mobile-hidden"><td colspan="5">No PIREPs!</td></tr>');
                return;
            }

            data.result = data.result.slice(0, 6);

            var pirepsHtml = data.result.map(function(p) {
                var pirepStatus = ['Pending', 'Approved', 'Denied'];
                return `<tr><td class="align-middle mobile-hidden">${p.flightnum}</td>
                        <td class="align-middle">${p.departure}-${p.arrival}</td>
                        <td class="align-middle mobile-hidden">${p.date}</td>
                        <td class="align-middle mobile-hidden">${p.aircraft}</td>
                        <td class="align-middle">${pirepStatus[p.status]}</td></tr>`;
            });
            $("#pireps-body").html(pirepsHtml.join(''));
        });
    </script>
</body>

</html>