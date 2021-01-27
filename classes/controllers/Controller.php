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
        $htmlpath = __DIR__ . "/../../themes/{$theme}/views/{$pagename}.html";
        if (file_exists($phppath)) {
            Page::$pageData = $data;
            include $phppath;
            die();
        } elseif (file_exists($htmlpath)) {
            echo file_get_contents($htmlpath);
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
}
