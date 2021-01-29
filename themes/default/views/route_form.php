<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('Routes - ' . Page::$pageData->va_name);
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
                        <h3>Route Search</h3>
                        <form method="get" action="/routes/search">
                            <input hidden name="action" value="search" />
                            <div class="form-group">
                                <label for="dep">Departure ICAO</label>
                                <input type="text" class="form-control" name="dep" id="dep" placeholder="Leave Blank for Any" />
                            </div>
                            <div class="form-group">
                                <label for="arr">Arrival ICAO</label>
                                <input type="text" class="form-control" name="arr" id="arr" placeholder="Leave Blank for Any" />
                            </div>
                            <div class="form-group">
                                <label for="fltnum">Flight Number</label>
                                <input type="text" class="form-control" name="fltnum" id="fltnum" placeholder="Leave Blank for Any" />
                            </div>
                            <div class="form-group">
                                <label for="aircraft">Aircraft</label>
                                <select class="form-control" name="aircraft" id="aircraft">
                                    <option value="">Any Aircraft</option>
                                    <?php
                                    foreach ($aircraft as Page::$pageData->aircraft) {
                                        $notes = $ac->notes == null ? '' : ' - ' . $ac->notes;
                                        echo '<option value="' . $ac->id . '">' . $ac->name . ' (' . $ac->liveryname . ')' . $notes . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="duration">Flight Time</label>
                                <select class="form-control" name="duration" id="duration">
                                    <option value="">Any Flight Time</option>
                                    <option value="0">&lt; 1hr</option>
                                    <option value="1">1-2hrs</option>
                                    <option value="2">2-3hrs</option>
                                    <option value="3">3-4hrs</option>
                                    <option value="4">4-5hrs</option>
                                    <option value="5">5-6hrs</option>
                                    <option value="6">6-7hrs</option>
                                    <option value="7">7-8hrs</option>
                                    <option value="9">8-9hrs</option>
                                    <option value="9">9-10hrs</option>
                                    <option value="10">10hrs+</option>
                                </select>
                            </div>
                            <input type="submit" class="btn bg-custom" value="Search" />
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