<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>
<title><?= escape(Page::getTitle()) ?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
  foreach (Page::assets() as $name => $tags) {
    echo implode('', $tags);
  }
?>

<script>
  $(document).ready(function() {
    <?php if (Page::assetInUse('bootstrap')): ?>
      $('[data-toggle="tooltip"]').tooltip(); 
      $(".tooltip-toggle").tooltip();
    <?php endif; ?>
    
    $("nav .panel-link").addClass("nav-link");

    <?php if (Page::assetInUse('datatables')): ?>
      $(".datatable").dataTable({
        "paging": true,
        "ordering": true,
        "info": true,
        "pageLength": 10
      });
      $(".datatable-nosearch").dataTable({
        "paging": true,
        "ordering": true,
        "searching": false,
        "info": false,
        "pageLength": 10,
        "lengthChange": false,
      });
    <?php endif; ?>
    
    <?php if (Page::assetInUse('momentjs')): ?>
      $(".moment").each(function(item) {
        var m = moment($(this).text());
        $(this).text(m.format('LLL'));
      });
    <?php endif; ?>
  });
</script>
