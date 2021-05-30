<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Events - ' . Config::get('va/name'));
?>
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
                        <?php if (!Page::$pageData->is_gold) : ?>
                            <h3>Events</h3>
                            <p><?= Page::$pageData->va_name ?> needs to Subscribe to VANet Gold to access Events.</p>
                        <?php else : ?>
                            <h3>Events</h3>
                            <?php
                            if (Session::exists('error')) {
                                echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                            }
                            if (Session::exists('success')) {
                                echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                            }
                            ?>
                            <p>Welcome to the <?= Page::$pageData->va_name ?> Events Page. Here you can see upcoming events and sign up.</p>

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
                                        <a href="/events/${e.id}" class="btn bg-custom">View</button>
                                        </td></tr>`
                                    }).join(''));
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