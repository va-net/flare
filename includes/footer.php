<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>
<p><small class="d-block mb-3 text-muted"><br />&copy; Lucas Rebato <?php echo date("Y"); ?>. All Rights Reserved.</small></p>
<script>
    $(document).ready(function() {
        $("#loader").fadeOut(500, function() {
            $("#home").fadeIn(400);
        });
    });
</script>

<?php if (Session::get('darkmode') === true): ?>
<script>
    $(document).ready(function() {
        $("body").addClass("bg-dark");
        $("body").addClass("text-light");
        $("#desktopMenu").removeClass("bg-light");
        $("#desktopMenu").addClass("bg-dark");
        $(".panel-link").addClass("panel-link-dark");
        $(".panel-link-dark").removeClass("panel-link");
        $("#desktopMenu").addClass("border");
        $("#desktopMenu").addClass("border-white");
        $("table").addClass("text-light");
        $(".card").addClass("bg-dark");
        $(".divider").addClass("divider-dark");
        $(".divider-dark").removeClass("divider");
        $("*").on("shown.bs.modal", function() {
            $(".modal-content").addClass("bg-dark");
            $("table").addClass("text-light");
        });
    });
</script>
<?php endif; ?>
