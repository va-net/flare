<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('Recruitment Admin - ' . Page::$pageData->va_name);

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
                        <h3>Recruitment</h3>
                        <p>Here you can manage any Pending Applications</p>
                        <form id="accept" action="/admin/users/pending" method="post">
                            <input hidden name="action" value="acceptapplication" />
                        </form>
                        <table class="table table-striped datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th>Name</th>
                                    <th class="mobile-hidden">Email</th>
                                    <th class="mobile-hidden">IFC</th>
                                    <th>Flags</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $lists = Json::decode(file_get_contents("https://ifvarb.com/watchlist_api.php?apikey=a5f2963d-29b1-40e4-8867-a4fbb384002c"));
                                $watchlist = [];
                                $blacklist = [];
                                $prevwatch = [];
                                $prevblack = [];
                                foreach ($lists as $l) {
                                    if (new DateTime("now") > new DateTime($l['expire_date'])) {
                                        if ($l["type"] == "Watchlist") {
                                            $prevwatch[strtolower($l["ifc"])] = "{$l["notes"]} - Expired {$l["expire_date"]}";
                                        } else {
                                            $prevblack[strtolower($l["ifc"])] = "{$l["notes"]} - Expired {$l["expire_date"]}";
                                        }
                                        continue;
                                    }
                                    if ($l["type"] == "Watchlist") {
                                        $watchlist[strtolower($l["ifc"])] = $l["notes"];
                                    } else {
                                        $blacklist[strtolower($l["ifc"])] = $l["notes"];
                                    }
                                }

                                $x = 0;
                                foreach (Page::$pageData->users as $user) {
                                    echo '<tr><td class="align-middle">';
                                    echo $user["name"];
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo $user["email"];
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    $username = explode('/', $user['ifc']);
                                    if ($username === FALSE || count($username) < 5) {
                                        $username = '';
                                    } else {
                                        $username = $username[4];
                                    }
                                    echo '<a href="' . $user['ifc'] . '" target="_blank">' . $username . '</a>';
                                    echo '</td><td class="align-middle">';
                                    if (array_key_exists(strtolower($username), $blacklist)) {
                                        echo '<span class="badge badge-danger" data-toggle="tooltip" title="' . $blacklist[strtolower($username)] . '">Blacklisted</span>';
                                    } elseif (array_key_exists(strtolower($username), $watchlist)) {
                                        echo '<span class="badge badge-warning" data-toggle="tooltip" title="' . $watchlist[strtolower($username)] . '">Watchlisted</span>';
                                    } elseif (array_key_exists(strtolower($username), $prevblack)) {
                                        echo '<span class="badge badge-warning" data-toggle="tooltip" title="' . $prevblack[strtolower($username)] . '">Previous Blacklist</span>';
                                    } elseif (array_key_exists(strtolower($username), $prevwatch)) {
                                        echo '<span class="badge badge-info" data-toggle="tooltip" title="' . $prevwatch[strtolower($username)] . '">Previous Watchlist</span>';
                                    } else {
                                        echo '<span class="badge badge-success">None</span>';
                                    }
                                    echo '</td><td class="align-middle">&nbsp;';
                                    if (!array_key_exists(strtolower($username), $blacklist)) echo '<button class="btn btn-success text-light" value="' . $user['id'] . '" form="accept" type="submit" name="accept"><i class="fa fa-check"></i></button>&nbsp;';
                                    echo '<button value="' . $user['id'] . '" id="delconfirmbtn" data-toggle="modal" data-target="#user' . $x . 'declinemodal" class="btn btn-danger text-light" name="decline"><i class="fa fa-times"></i></button>&nbsp;';
                                    if (Page::$pageData->is_gold && VANet::featureEnabled('airline-userlookup')) {
                                        echo '<a href="/admin/users/lookup/' . (empty($user['ifuserid']) ? $username . '?ifc=true' : $user['ifuserid']) . '" class="btn bg-custom">
                                        <i class="fa fa-search"></i></a>&nbsp;';
                                    }
                                    echo '<button id="delconfirmbtn" class="btn btn-primary text-light" data-toggle="modal" data-target="#user' . $x . 'modal"><i class="fa fa-plus"></i></button>';
                                    echo '</td>';
                                    $x++;
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                        $x = 0;
                        foreach (Page::$pageData->users as $user) { ?>
                            <div class="modal fade" id="user<?= $x ?>modal" tabindex="-1" role="dialog" aria-labelledby="user<?= $x ?>label" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="usermodal-title"></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/admin/users/pending" method="post">
                                                <div class="form-group">
                                                    <label for="usermodal-callsign">Callsign</label>
                                                    <input readonly type="text" value="<?= $user["callsign"] ?>" class="form-control" name="callsign">
                                                </div>
                                                <div class="form-group">
                                                    <label for="usermodal-name">Name</label>
                                                    <input readonly type="text" value="<?= $user["name"] ?>" class="form-control" name="name">
                                                </div>
                                                <div class="form-group">
                                                    <label for="usermodal-email">Email</label>
                                                    <input readonly type="text" value="<?= $user["email"] ?>" class="form-control" name="email">
                                                </div>
                                                <div class="form-group">
                                                    <label for="usermodal-ifc">IFC Username</label>
                                                    <?php $username = explode('/', $user['ifc'])[4]; ?>
                                                    <a href="<?= $user['ifc'] ?>" target="_blank"><input readonly type="text" style="cursor: pointer;" value="<?= $username ?>" class="form-control" name="ifc"></a>
                                                </div>
                                                <div class="form-group">
                                                    <label for="usermodal-joined">Grade</label>
                                                    <input readonly type="text" value="<?= $user["grade"] ?>" class="form-control" name="grade">
                                                </div>
                                                <div class="form-group">
                                                    <label for="usermodal-status">Violations to landings</label>
                                                    <input readonly type="text" value="<?= $user["viol"] ?>" class="form-control" name="viol">
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn bg-custom" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="user<?= $x ?>declinemodal" tabindex="-1" role="dialog" aria-labelledby="user<?= $x ?>label" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="declinemodal-title">Decline Application</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/admin/users/pending" method="post" id="declinemodal">
                                                <input hidden class="form-control" name="action" value="declineapplication" />
                                                <input hidden class="form-control" name="id" value="<?= $user['id'] ?>">
                                                <div class="form-group">
                                                    <label for="declinemodal-status">Reason for decline of application</label>
                                                    <input required type="text" class="form-control" name="declinereason">
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-danger" form="declinemodal" type="submit">Decline</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php $x++;
                        } ?>
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