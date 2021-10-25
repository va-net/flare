<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('VA Stats - ' . Page::$pageData->va_name);
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
                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <h3>VA Statistics</h3>
                        <table class="table">
                            <tr>
                                <td><b>Total Hours</b></td>
                                <td><?php echo Page::$pageData->hours; ?></td>
                            </tr>
                            <tr>
                                <td><b>Total Flights</b></td>
                                <td><?php echo Page::$pageData->flights; ?></td>
                            </tr>
                            <tr>
                                <td><b>Total Pilots</b></td>
                                <td><?php echo Page::$pageData->pilots; ?></td>
                            </tr>
                            <tr>
                                <td><b>Total Routes</b></td>
                                <td><?php echo Page::$pageData->routes; ?></td>
                            </tr>
                        </table>
                        <?php if (!Page::$pageData->is_gold) : ?>
                            <p>
                                View vFinance Stats on <a href="https://vanet.app/airline/finance/">VANet</a>.
                                Sign Up to VANet Gold in order to get access to VANet Stats right here.
                            </p>
                        <?php else : ?>
                            <h4>VANet Statistics</h4>
                            <table class="table">
                                <tr>
                                    <td><b>Event Count</b></td>
                                    <td><?php echo Page::$pageData->stats["eventCount"]; ?></td>
                                </tr>
                                <tr>
                                    <td><b>Total Revenue</b></td>
                                    <td>$<?php echo Page::$pageData->stats["totalRevenue"]; ?></td>
                                </tr>
                            </table>
                        <?php endif; ?>
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
            $(".<?= Page::$pageData->active_dropdown ?>").collapse('show');
        });
    </script>
</body>

</html>