<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Plugins - ' . Page::$pageData->va_name);
$ACTIVE_CATEGORY = 'plugins';
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
                        if (file_exists(__DIR__ . '/../install/install.php') && !file_exists(__DIR__ . '/../.development')) {
                            echo '<div class="alert alert-danger text-center">The Install Folder still Exists! Please delete it immediately, it poses a severe security risk.</div>';
                        }

                        if (Session::exists('error')) {
                            echo '<div class="alert alert-danger text-center">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="alert alert-success text-center">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <h3>Manage Plugins</h3>
                        <?php
                        $tab = "store";
                        if (!empty(Input::get('tab'))) {
                            $tab = Input::get('tab');
                        }
                        ?>
                        <script>
                            $(document).ready(function() {
                                $("#<?= $tab; ?>link").click();
                            });
                        </script>
                        <ul class="nav nav-tabs nav-dark justify-content-center">
                            <li class="nav-item">
                                <a class="nav-link" id="storelink" data-toggle="tab" href="#store">Store</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="installedlink" data-toggle="tab" href="#installed">Installed</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div id="store" class="tab-pane container-fluid p-3 fade">
                                <h4>Plugin Store</h4>
                                <form id="installplugin" method="post" action="/admin/plugins">
                                    <input hidden name="action" value="installplugin" />
                                    <input hidden name="plugin" id="installplugin-plugin" />
                                </form>
                                <table class="table table-striped mobile-hidden" id="pluginstable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Tags</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach (Page::$pageData->all as $p) {
                                            echo '<tr><td class="align-middle" data-toggle="tooltip" title="' . escape($p["description"]) . '">';
                                            echo $p["name"];
                                            echo '</td><td class="align-middle">';
                                            $tags = implode('</span><span class="badge badge-light mx-1">', $p["tags"]);
                                            echo '<span class="badge badge-light mx-1">' . $tags . '</span>';
                                            echo '</td><td class="align-middle">';
                                            echo '<button class="btn bg-custom installBtn" data-slug="' . $p["slug"] . '" data-name="' . $p["name"] . '"><i class="fa fa-cloud-download-alt"></i></button>';
                                            echo '</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <p class="desktop-hidden">Please user a larger screen for the plugins store</p>
                            </div>
                            <div id="installed" class="tab-pane container-fluid p-3 fade">
                                <h4>Installed Plugins</h4>
                                <form id="removeplugin" method="post" action="/admin/plugins">
                                    <input hidden name="action" value="removeplugin" />
                                    <input hidden name="plugin" id="removeplugin-name" />
                                </form>
                                <table class="table datatable-nosearch">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Version</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach (Page::$pageData->installed as $p) {
                                            echo '<tr><td class="align-middle">';
                                            echo $p["name"];
                                            echo '</td><td class="align-middle">';
                                            echo $p["version"]["tag"];
                                            echo '</td><td class="align-middle">';
                                            echo '<button class="btn btn-danger removeBtn" data-name="' . $p["name"] . '"><i class="fa fa-trash"></i></button>';
                                            echo '</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <style>
                            .nav-tabs .nav-link {
                                color: #000 !important;
                            }
                        </style>
                        <script>
                            if ($("#pluginstable").css('display') != 'none') {
                                $("#pluginstable").dataTable({
                                    "paging": true,
                                    "ordering": true,
                                    "info": true,
                                    "pageLength": 10
                                });
                            }
                            $(".installBtn").click(function() {
                                var name = $(this).data('name');
                                var slug = $(this).data('slug');

                                var conf = confirm('Are you sure you want to install the plugin "' + name + '"?');
                                if (conf) {
                                    $("#installplugin-plugin").val(slug);
                                    $("#installplugin").submit();
                                }
                            });
                            $(".removeBtn").click(function() {
                                var name = $(this).data('name');

                                var conf = confirm('Are you sure you want to Remove the plugin "' + name + '"?');
                                if (conf) {
                                    $("#removeplugin-name").val(name);
                                    $("#removeplugin").submit();
                                }
                            });
                        </script>
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
            $(".<?= $ACTIVE_CATEGORY ?>").collapse('show');
        });
    </script>
</body>

</html>