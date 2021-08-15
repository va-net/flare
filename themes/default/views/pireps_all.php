<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

Page::setTitle('All PIREPs - ' . Page::$pageData->va_name);
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <script type="application/json" id="all-pireps">
        <?= Json::encode(Page::$pageData->pireps) ?>
    </script>
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
                        <?php
                        if (Session::exists('error')) {
                            echo '<div class="text-center alert alert-danger">Error: ' . Session::flash('error') . '</div>';
                        }
                        if (Session::exists('success')) {
                            echo '<div class="text-center alert alert-success">' . Session::flash('success') . '</div>';
                        }
                        ?>
                        <h3>My Recent PIREPs</h3>
                        <p>Showing your 30 Most Recent PIREPs</p>
                        <br />
                        <table class="table table-striped datatable">
                            <thead class="bg-custom">
                                <tr>
                                    <th class="mobile-hidden">Flight Number</th>
                                    <th>Route</th>
                                    <th class="mobile-hidden">Date</th>
                                    <th class="mobile-hidden">Aircraft</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach (Page::$pageData->pireps as $pirep) {
                                    echo '<tr><td class="align-middle mobile-hidden">';
                                    echo $pirep["fnum"];
                                    echo '</td><td class="align-middle">';
                                    echo $pirep["departure"] . '-' . $pirep["arrival"];
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo date_format(date_create($pirep['date']), 'Y-m-d');
                                    echo '</td><td class="align-middle mobile-hidden">';
                                    echo $pirep["aircraft"];
                                    echo '</td><td class="align-middle">';
                                    echo $pirep["status"];
                                    echo '</td><td class="align-middle">';
                                    echo '<button class="btn text-light btn-primary editPirep" data-pirepid="' . $pirep['id'] . '"><i class="fa fa-edit"></i></button>';
                                    echo '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>

                        <div class="modal fade" id="pirepmodal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="pirepmodal-title"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="text-left modal-body row">
                                        <form action="/pireps" method="post" class="mb-4 col-xl mb-md-0">
                                            <h4>PIREP Details</h4>
                                            <input hidden name="action" value="editpirep">
                                            <input hidden name="id" id="pirepmodal-id" value="">
                                            <div class="form-group">
                                                <label for="pirepmodal-date">Date of Flight</label>
                                                <input required type="date" id="pirepmodal-date" class="form-control" name="date" value="">
                                            </div>
                                            <div class="form-group">
                                                <label for="pirepmodal-fnum">Flight Number</label>
                                                <input required type="text" class="form-control" name="fnum" id="pirepmodal-fnum" value="">
                                            </div>
                                            <div class="form-group">
                                                <label for="pirepmodal-dep">Departure</label>
                                                <input required maxlength="4" minlength="4" type="text" id="pirepmodal-dep" value="" class="form-control" name="dep">
                                            </div>
                                            <div class="form-group">
                                                <label for="pirepmodal-arr">Arrival</label>
                                                <input required maxlength="4" minlength="4" type="text" id="pirepmodal-arr" value="" class="form-control" name="arr">
                                            </div>
                                            <div class="form-group">
                                                <label for="pirepmodal-fuel">Fuel Used (kg)</label>
                                                <input required min="1" type="number" id="pirepmodal-fuel" value="" class="form-control" name="fuel">
                                            </div>
                                            <input type="submit" class="btn bg-custom" value="Save">
                                        </form>
                                        <div class="col-xl h-100">
                                            <h4>PIREP Comments</h4>
                                            <div id="pirepmodal-comments" class="mb-3">
                                                <div class="text-center">
                                                    <div class="spinner-border spinner-border-sm spinner-custom" style="height: 30px; width: 30px;"></div>
                                                </div>
                                            </div>
                                            <form id="pirepmodal-comments-form" class="form-inline d-flex">
                                                <div style="flex: 1 1 0%;" class="pr-1">
                                                    <label class="sr-only" for="pirepmodal-comments-form-input">Name</label>
                                                    <input type="text" class="mb-2 form-control mr-sm-2 w-100" id="pirepmodal-comments-form-input" placeholder="Add Comment...">
                                                </div>

                                                <button type="submit" class="mb-2 btn bg-custom" style="flex: none;">Send</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            $('.editPirep').click(function() {
                                var id = $(this).data('pirepid');
                                var all = JSON.parse($("#all-pireps").html());
                                var pirep = all.find((p) => p.id == id);

                                $("#pirepmodal-title").text(`Edit PIREP: ${pirep.departure}-${pirep.arrival} on ${pirep.date}`);
                                $("#pirepmodal-id").val(pirep.id);
                                $("#pirepmodal-date").val(pirep.date);
                                $("#pirepmodal-fnum").val(pirep.fnum);
                                $("#pirepmodal-dep").val(pirep.departure);
                                $("#pirepmodal-arr").val(pirep.arrival);
                                $("#pirepmodal-fuel").val(pirep.fuelused);
                                $("#pirepmodal").modal('show');

                                fetchComments(pirep.id);
                            });

                            $("#pirepmodal").on('hidden.bs.modal', function() {
                                $("#pirepmodal-comments").html(`
                                    <div class="text-center">
                                        <div class="spinner-border spinner-border-sm spinner-custom" style="height: 30px; width: 30px;"></div>
                                    </div>
                                `);
                            });

                            $("#pirepmodal-comments-form").submit(function(e) {
                                e.preventDefault();

                                var id = $("#editpirep-id").val();
                                if (!id) return;

                                var content = $("#editpirep-comments-form-input").val();
                                if (!content) return;

                                $.post(`/api.php/pireps/${encodeURIComponent(id)}/comments`, {
                                    content,
                                }, function(_, status) {
                                    if (status != 'success') {
                                        alert('Failed to add comment');
                                        return;
                                    }

                                    $("#editpirep-comments-form-input").val('');
                                    $("#editpirep-comments").html(`
                                                <div class="text-center">
                                                    <div class="spinner-border spinner-border-sm spinner-custom" style="height: 30px; width: 30px;"></div>
                                                </div>
                                            `);
                                    fetchComments(id);
                                });
                            });

                            function fetchComments(pirepid) {
                                $.getJSON(`/api.php/pireps/${encodeURIComponent(pirepid)}/comments`, function(result) {
                                    result = result.result;
                                    $("#pirepmodal-comments").html('');
                                    for (const c of result) {
                                        $("#pirepmodal-comments").append(`
                                            <div id="pirepmodal-comment-${c.id}" class="mb-1 d-flex">
                                                <span style="flex: 1 1 0%;">
                                                    <b>${c.userName}:</b> ${c.content}
                                                </span>
                                                <span class="text-sm text-muted" style="flex: none;">
                                                    <small>${new Date(`${c.dateposted}Z`).toLocaleString()}</small>
                                                </span>
                                            </div>
                                        `);
                                    }
                                });
                            }
                        </script>
                    </div>
                </div>
            </div>
            <footer class="text-center container-fluid">
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>

</html>