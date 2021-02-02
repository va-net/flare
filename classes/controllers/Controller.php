<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Controller
{
    /**
     * @param string $pagename
     * @param stdClass $data
     */
    protected function render($pagename, $data)
    {
        $theme = Config::get('ACTIVE_THEME');
        if (empty($theme)) $theme = 'default';

        if (!isset($data->user)) $data->user = new User;

        $phppath = __DIR__ . "/../../themes/{$theme}/views/{$pagename}.php";
        if (file_exists($phppath)) {
            Page::$pageData = $data;
            include $phppath;
            die();
        } else {
            $this->notFound();
        }
    }

    /**
     * @param string $url
     */
    protected function redirect($url)
    {
        Redirect::to($url);
        die();
    }

    /**
     * @param string $content
     */
    protected function notFound($content = '<h1>Not Found</h1>')
    {
        http_response_code(404);
        echo $content;
        die();
    }

    /**
     * @param User $user
     * @param string $permission
     */
    protected function authenticate($user, $admin = false, $permission = null)
    {
        if (!$user->isLoggedIn() || ($admin && !$user->hasPermission('admin')) || !$user->hasPermission($permission)) {
            $this->redirect('/');
        }
    }
}
