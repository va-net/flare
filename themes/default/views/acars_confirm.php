<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
Page::setTitle('ACARS - ' . Page::$pageData->va_name);
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
        <div class="mt-4 text-center container-fluid" style="overflow: auto;">
            <div class="p-0 m-0 row">
                <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
                <div class="col-lg-9 main-content">
                    <div id="loader-wrapper">
                        <div id="loader" class="spinner-border spinner-border-sm spinner-custom"></div>
                    </div>
                    <div class="loaded">
                        <h3>ACARS</h3>
                        <p>
                            Nice! We've found you. If you've finished your flight and at the gate, go ahead and confirm the details below.
                            If not, reload the page once you're done.
                        </p>

                        <form action="/pireps/new" method="post">
                            <input hidden value="filepirep" name="action" />

                            <div class="form-group">
                                <label for="date">Flight Date</label>
                                <input readonly class="form-control" value="<?= date('Y-m-d') ?>" name="date" id="date" />
                            </div>

                            <div class="form-group">
                                <label for="ftime">Flight Time</label>
                                <input readonly value="<?= Time::secsToString(Page::$pageData->acars['result']["flightTime"]) ?>" class="form-control" name="ftime" id="ftime" />
                            </div>

                            <input hidden value="<?= Page::$pageData->aircraft->id ?>" name="aircraft" />
                            <div class="form-group">
                                <label for="aircraftname">Aircraft</label>
                                <input disabled value="<?= Page::$pageData->aircraft->name ?> - <?= Page::$pageData->aircraft->liveryname ?>" class="form-control" id="aircraftname" />
                            </div>

                            <?php if (Page::$pageData->acars['result']["departure"] != null) : ?>
                                <input hidden value="<?= Page::$pageData->acars['result']["departure"] ?>" name="dep" />
                            <?php else : ?>
                                <div class="form-group">
                                    <label for="dep">Departure</label>
                                    <input requried class="form-control" type="text" minlength="4" maxlength="4" name="dep" id="dep" placeholder="ICAO" />
                                </div>
                            <?php endif; ?>

                            <?php if (Page::$pageData->acars['result']["arrival"] != null) : ?>
                                <input hidden value="<?= Page::$pageData->acars['result']["arrival"] ?>" name="arr" />
                            <?php else : ?>
                                <div class="form-group">
                                    <label for="arr">Arrival</label>
                                    <input requried class="form-control" type="text" minlength="4" maxlength="4" name="arr" id="arr" placeholder="ICAO" />
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="fnum">Flight Number</label>
                                <input required type="text" class="form-control" name="fnum" />
                            </div>

                            <div class="form-group">
                                <label for="fuel">Fuel Used (kg)</label>
                                <input required type="number" class="form-control" name="fuel" />
                            </div>

                            <div class="form-group">
                                <label for="multi">Multiplier Number (if applicable)</label>
                                <input type="number" class="form-control" maxlength="6" minlength="6" id="multi" name="multi">
                            </div>

                            <input type="submit" class="btn bg-custom" value="File PIREP" />
                        </form>
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