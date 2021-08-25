<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once 'core/init.php';

$user = new User();
$user->logout();

Session::flash('login', 'You have Logged Out Successfully!');
Redirect::to('index.php');