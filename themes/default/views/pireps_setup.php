<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('PIREPs Setup - ' . Page::$pageData->va_name);
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
                        <h3>Setup PIREPs</h3>
                        <p>
                            Before you can start filing PIREPs, we need to grab a bit of data from Infinite Flight.
                            As you're flying anonymously or haven't linked your IFC Account to your IF Account,
                            we couldn't grab this information in the background.
                        </p>
                        <p>
                            Please spawn in on the <b><?= ucfirst(Page::$pageData->server); ?> Server</b>, and ensure that you set your
                            callsign to your assigned one (<b><?= Page::$pageData->user->data()->callsign ?></b>, if you've forgotten!).
                            Then, click the button below.
                        </p>
                        <form method="post" action="/pireps/setup">
                            <input hidden name="action" value="setuppireps" />
                            <input hidden name="callsign" value="<?= Page::$pageData->user->data()->callsign ?>" />
                            <input type="submit" class="btn text-light bg-custom" value="Find Me" />
                        </form>
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