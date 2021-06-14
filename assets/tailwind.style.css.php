<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once __DIR__ . '/../core/init.php';
header("Content-type: text/css; charset: UTF-8");

?>

:root {
    --va-theme-color: <?= Config::get('site/colour_main_hex') ?>;
    --va-text-color: <?= Config::get('TEXT_COLOUR') ?>;
}