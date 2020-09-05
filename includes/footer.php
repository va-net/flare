<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>
<p><small class="d-block mb-3 text-muted"><br />&copy; <?php echo Config::get('va/name')." ".date("Y"); ?>. Flare Crew Center by Lucas Rebato and VANet.</small></p>
<script>
    $(document).ready(function() {
        $("#loader").fadeOut(500, function() {
            $("#home").fadeIn(400);
        });
    });
</script>
