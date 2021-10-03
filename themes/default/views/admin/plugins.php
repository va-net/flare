<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Plugins - ' . Page::$pageData->va_name);
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
                            <div id="store" class="p-3 tab-pane container-fluid fade">
                                <h4>Plugin Store</h4>
                                <form id="installplugin" method="post" action="/admin/plugins">
                                    <input hidden name="action" value="installplugin" />
                                    <input hidden name="plugin" id="installplugin-plugin" />
                                    <input hidden name="prerelease" id="installplugin-prerelease" />
                                </form>
                                <form id="pluginsearch" class="mb-3 d-flex">
                                    <div class="input-group" style="flex: 1 1 0%;">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Search" aria-label="Search" id="pluginsearch-search">
                                    </div>
                                    <div style="flex: none;" class="pl-2 d-flex">
                                        <div class="my-auto custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="pluginsearch-prerelease" />
                                            <label class="custom-control-label" for="pluginsearch-prerelease">Include Prerelease</label>
                                        </div>
                                    </div>
                                </form>
                                <div class="card-columns" id="search-results">
                                    <?php foreach (Page::$pageData->all['data'] as $plugin) : ?>
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= $plugin['name'] ?></h5>
                                                <p class="mb-0 card-text"><?= $plugin['description'] ?></p>
                                                <p class="card-text"><small class="text-muted">Tags: <?= implode(', ', $plugin['tags']) ?></small></p>
                                                <?php if ($plugin['installed']) : ?>
                                                    <button class="btn bg-custom" disabled>Installed</button>
                                                <?php else : ?>
                                                    <button class="btn bg-custom" type="submit" form="installplugin" name="plugin" value="<?= $plugin['id'] ?>">Install</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div id="installed" class="p-3 tab-pane container-fluid fade">
                                <h4>Installed Plugins</h4>
                                <form id="removeplugin" method="post" action="/admin/plugins">
                                    <input hidden name="action" value="removeplugin" />
                                    <input hidden name="plugin" id="removeplugin-id" />
                                </form>
                                <form id="updateplugin" method="post" action="/admin/plugins">
                                    <input hidden name="action" value="updateplugin" />
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
                                            echo $p['pluginInfo']['name'];
                                            echo '</td><td class="align-middle">';
                                            echo $p['versionTag'];
                                            echo '</td><td class="align-middle" id="pluginactions-' . $p['pluginInfo']['id'] . '">';
                                            echo '<button class="btn btn-danger removeBtn" data-id="' . $p['pluginInfo']['id'] . '" data-name="' . $p['pluginInfo']['name'] . '"><i class="fa fa-trash"></i></button>';
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
                            $(".removeBtn").click(function() {
                                var id = $(this).data('id');

                                var conf = confirm('Are you sure you want to remove the plugin "' + $(this).data('name') + '"?');
                                if (conf) {
                                    $("#removeplugin-id").val(id);
                                    $("#removeplugin").submit();
                                }
                            });

                            const refreshPlugins = function() {
                                $("#search-results").text('Loading...');
                                $.get(
                                    `/api.php/plugins?search=${encodeURIComponent($("#pluginsearch-search").val())}&prerelease=${$("#pluginsearch-prerelease").is(':checked') ? 'true' : 'false'}`,
                                    function(data) {
                                        $("#search-results").html(data.result.data.map((p) => {
                                            return `<div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">${p.name}</h5>
                                                    <p class="mb-0 card-text">${p.description}</p>
                                                    <p class="card-text"><small class="text-muted">Tags: ${p.tags.join(', ')}</small></p>
                                                    ${p.installed ? '<button class="btn bg-custom" disabled>Installed</button>' : `<button class="btn bg-custom" type="submit" form="installplugin" name="plugin" value="${p.id}">Install</button>`}
                                                </div>
                                            </div>`;
                                        }).join(''));
                                    }
                                )
                            };

                            $("#pluginsearch").submit(function(e) {
                                e.preventDefault();
                                refreshPlugins();
                            });

                            $("#pluginsearch-prerelease").change(function(e) {
                                $("#installplugin-prerelease").val(e.target.checked ? '1' : '0');
                                refreshPlugins();
                            });

                            $.get('/api.php/plugins/updates', function(data) {
                                var updates = data.result;
                                if (!updates) return;

                                for (var u of updates) {
                                    $(`#pluginactions-${u.pluginId}`).prepend(
                                        `<button class="mr-2 btn bg-custom" type="submit" form="updateplugin" name="plugin" value="${u.pluginId}">
                                            <i class="fa fa-sync"></i>
                                        </button>`
                                    );
                                }
                            });
                        </script>
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
            $(".<?= Page::$pageData->active_dropdown ?>").collapse('show');
        });
    </script>
</body>

</html>