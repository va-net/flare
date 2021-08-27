<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

$user = new User();

Page::setTitle('User Lookup - ' . Config::get('va/name'));

$ACTIVE_CATEGORY = 'user-management';
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
                        ?>
                        <div class="container">
                            <h1>User Lookup</h1>
                            <div class="row h-100">
                                <div class="mb-4 col-md h-100">
                                    <div class="h-full p-3 text-left border rounded shadow">
                                        <h2>Basic Details</h2>
                                        <ul>
                                            <li>
                                                <b>IFC:</b>
                                                <?php if (empty(Page::$pageData->lookup['ifcUsername'])) : ?>
                                                    N/A
                                                <?php else : ?>
                                                    <a target="_blank" href="https://community.infiniteflight.com/u/<?= urlencode(Page::$pageData->lookup['ifcUsername']) ?>">
                                                        <?= escape(Page::$pageData->lookup['ifcUsername']) ?>
                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <b>User ID:</b> <?= Page::$pageData->lookup['userId'] ?>
                                            </li>
                                            <li>
                                                <b>Total XP:</b> <?= Page::$pageData->lookup['totalXp'] ?>
                                            </li>
                                            <li>
                                                <b>VO Affiliation:</b> <?= escape(Page::$pageData->lookup['virtualOrganization']) ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="mb-4 col-md h-100">
                                    <div class="p-3 text-left border rounded shadow h-100">
                                        <h2>Groups</h2>
                                        <ul>
                                            <li>
                                                <b>IFATC:</b> <?= Page::$pageData->lookup['isIfatc'] ? 'Yes' : 'No' ?>
                                            </li>
                                            <li>
                                                <b>Moderator:</b> <?= Page::$pageData->lookup['isModerator'] ? 'Yes' : 'No' ?>
                                            </li>
                                            <li>
                                                <b>Staff:</b> <?= Page::$pageData->lookup['isStaff'] ? 'Yes' : 'No' ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md">
                                    <div class="p-3 border rounded shadow">
                                        <h2>Violations</h2>
                                        <div class="text-left row">
                                            <div class="col-md">
                                                <ul>
                                                    <li>
                                                        <b>Level 1 Count:</b> <?= Page::$pageData->lookup['violationsByLevel']['level1'] ?>
                                                    </li>
                                                    <li>
                                                        <b>Level 2 Count:</b> <?= Page::$pageData->lookup['violationsByLevel']['level2'] ?>
                                                    </li>
                                                    <li>
                                                        <b>Level 3 Count:</b> <?= Page::$pageData->lookup['violationsByLevel']['level3'] ?>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md">
                                                <ul>
                                                    <li>
                                                        <b>Last Level 1 Date:</b> <?= empty(Page::$pageData->lookup['lastLevel1ViolationDate']) ? 'N/A' : date_format(date_create(Page::$pageData->lookup['lastLevel1ViolationDate']), 'Y-m-d') ?>
                                                    </li>
                                                    <li>
                                                        <b>Last Report Date:</b> <?= empty(Page::$pageData->lookup['lastReportDate']) ? 'N/A' : date_format(date_create(Page::$pageData->lookup['lastReportDate']), 'Y-m-d') ?>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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