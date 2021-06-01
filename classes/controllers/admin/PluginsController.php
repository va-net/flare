<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class PluginsController extends Controller
{
    public function get()
    {
        $user = new User;
        $this->authenticate($user, true, 'site');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->pending = Pirep::fetchPending();

        $url = "https://raw.githubusercontent.com/va-net/flare-plugins/v2/plugins.tsv";
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent: va-net\r\n"
            )
        );
        $context = stream_context_create($opts);
        $plugins = file_get_contents($url, false, $context);
        preg_match_all('/\n.*/m', $plugins, $lines);
        $data->all = array_map(function ($l) {
            $segments = explode("\t", $l);
            $segments = array_map(function ($x) {
                return trim($x);
            }, $segments);
            return [
                "name" => $segments[0],
                "slug" => $segments[1],
                "author" => $segments[2],
                "version" => $segments[3],
                "update-date" => $segments[4],
                "tags" => explode(',', $segments[5]),
                "description" => $segments[6],
            ];
        }, $lines[0]);
        $data->installed = Json::decode(file_get_contents(__DIR__ . '/../../../plugins.json'));

        $this->render('admin/plugins', $data);
    }

    public function post()
    {
        $user = new User;
        $this->authenticate($user, true, 'site');
        switch (Input::get('action')) {
            case 'installplugin':
                $this->install();
            case 'removeplugin':
                $this->remove();
            default:
                $this->get();
        }
    }

    private function install()
    {
        $slash = "/";
        if (strpos(strtolower(php_uname('s')), "window") !== FALSE) {
            $slash = "\\";
        }

        $GH_BRANCH = "v2";

        $url = "https://raw.githubusercontent.com/va-net/flare-plugins/{$GH_BRANCH}/plugins.tsv";
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent: va-net\r\n"
            )
        );
        $context = stream_context_create($opts);
        $plugins = file_get_contents($url, false, $context);
        $pluginbasic = null;
        preg_match_all('/\n.*/m', $plugins, $lines);
        foreach ($lines[0] as $l) {
            $l = trim($l);
            $l = explode("\t", $l);
            if ($pluginbasic == null && $l[1] == Input::get('plugin')) {
                $pluginbasic = array(
                    "name" => $l[0],
                    "slug" => $l[1],
                    "author" => $l[2],
                    "version" => $l[3],
                    "update-date" => $l[4],
                    "tags" => explode(",", $l[5])
                );
                break;
            }
        }

        $pluginbasic["slug"] = strtolower($pluginbasic["slug"]);

        $version = Updater::getVersion();
        $pluginadv = Json::decode(file_get_contents("https://raw.githubusercontent.com/va-net/flare-plugins/{$GH_BRANCH}/" . $pluginbasic["slug"] . "/plugin.json", false, $context));

        foreach ($pluginadv["installation"]["files"] as $f) {
            $f = str_replace("/", $slash, $f);
            if (file_exists(__DIR__ . '/../../..' . $slash . $f)) {
                if (unlink(__DIR__ . '/../../..' . $slash . $f) !== TRUE) {
                    Session::flash('error', 'File "' . $f . '" already exists, failed to delete it.');
                    $this->redirect('/admin/plugins');
                }

                Logger::log('File "' . __DIR__ . $slash . $f . '" was deleted while installing plugin ' . $pluginbasic["name"]);
            }
        }
        foreach ($pluginadv["installation"]["files"] as $f) {
            $data = file_get_contents("https://raw.githubusercontent.com/va-net/flare-plugins/{$GH_BRANCH}/" . $pluginbasic["slug"] . "/" . $f, false, $context);
            $f = str_replace("/", $slash, $f);
            file_put_contents(__DIR__ . '/../../..' . $slash . $f, $data);
        }

        $db = DB::getInstance();
        foreach ($pluginadv["installation"]["queries"] as $q) {
            $db->query($q);
        }

        $currentplugins = Json::decode(file_get_contents(__DIR__ . '/../../../plugins.json'));
        array_push($currentplugins, $pluginadv);
        file_put_contents('./plugins.json', Json::encode($currentplugins, true));

        Session::flash('success', 'Plugin Installed!');
        $this->redirect('/admin/plugins?tab=installed');
    }

    private function remove()
    {
        $slash = "/";
        if (strpos(strtolower(php_uname('s')), "window") !== FALSE) {
            $slash = "\\";
        }

        $plugins = Json::decode(file_get_contents(__DIR__ . '/../../../plugins.json'));
        $theplugin = null;
        foreach ($plugins as $p) {
            if ($theplugin == null && $p["name"] == Input::get('plugin')) {
                $theplugin = $p;
                $newplugins = [];
                foreach ($plugins as $pp) {
                    if ($pp == $theplugin) continue;

                    $newplugins[] = $pp;
                }
                file_put_contents(__DIR__ . '/../../../plugins.json', Json::encode($newplugins, true));
                break;
            }
        }

        foreach ($theplugin["installation"]["files"] as $file) {
            $file = str_replace("/", $slash, $file);
            $path = __DIR__ . '/../../..' . $slash . $file;
            if (unlink($path) === FALSE) {
                Session::flash('error', 'Failed to remove file - ' . $path);
                $this->redirect('/admin/plugins');
            }
        }

        Session::flash('success', 'Plugin Removed');
        $this->redirect('/admin/plugins?tab=installed');
    }
}
