<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once 'core/init.php';

if (Input::get('page') === 'usermanage'):
    Redirect::to('/admin/users.php');
elseif (Input::get('page') === 'staffmanage'):
    Redirect::to('/admin/staff.php');
elseif (Input::get('page') === 'site'):
    Redirect::to('/admin/site.php');
elseif (Input::get('page') === 'recruitment'):
    Redirect::to('/admin/recruitment.php');
elseif (Input::get('page') === 'pirepmanage'):
    Redirect::to('/admin/pireps.php');
elseif (Input::get('page') === 'multimanage'):
    Redirect::to('/admin/multipliers.php');
elseif (Input::get('page') === 'newsmanage'):
    Redirect::to('/admin/news.php');
elseif (Input::get('page') === 'events'):
    Redirect::to('/admin/events.php');
elseif (Input::get('page') === 'statsviewing'):
    Redirect::to('/admin/stats.php');
elseif (Input::get('page') === 'opsmanage'):
    Redirect::to('/admin/operations.php?section='.Input::get('section'));
elseif (Input::get('page') === 'codeshares'):
    Redirect::to('/admin/codeshares.php');
elseif (Input::get('page') === 'pluginmanage'):
    Redirect::to('/admin/plugins.php');
else:
    Redirect::to('/home.php');
    endif;
?>