<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>
<title><?= escape(Page::getTitle()) ?></title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="/assets/custom.css" />
<link rel="stylesheet" type="text/css" href="/assets/fontawesome.min.css" />
<link rel="stylesheet" type="text/css" href="/assets/style.css.php" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
        $(".tooltip-toggle").tooltip();
        $("nav .panel-link").addClass("nav-link");
        $(".datatable").dataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "pageLength": 10,
        });
        $(".datatable-nosearch").dataTable({
            "paging": true,
            "ordering": true,
            "searching": false,
            "info": false,
            "pageLength": 10,
            "lengthChange": false,
        });
        $(".moment").each(function(item) {
            var m = moment($(this).text());
            $(this).text(m.format('LLL'));
        });
    });
</script>