<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';
$user = new User();

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}

if (Input::get('method') === 'codeshares' && $user->hasPermission('opsmanage')) {
    $all = VANet::getCodeshares();
    foreach ($all as $codeshare) {
        echo '<tr><td class="align-middle">';
        echo $codeshare["senderName"];
        echo '</td><td class="align-middle mobile-hidden">';
        echo $codeshare["message"];
        echo '</td><td class="align-middle">';
        echo count($codeshare["routes"]);
        echo '</td><td class="align-middle">';
        echo '<button value="' . $codeshare['id'] . '" form="importcodeshare" type="submit" class="btn bg-custom text-light" name="id"><i class="fa fa-file-download"></i></button>';
        echo '&nbsp;<button class="btn btn-danger deleteCodeshare" data-id="' . $codeshare["id"] . '"><i class="fa fa-trash"></i></button>';
    }
}
