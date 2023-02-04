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


        $data->installed = $GLOBALS['INSTALLED_PLUGINS'];
        $data->all = VANet::getPlugins();
        $data->active_dropdown = 'plugins';

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
            case 'updateplugin':
                $this->update();
            default:
                $this->get();
        }
    }

    private function install()
    {
        $plugin = VANet::pluginInstallDetails(Input::get('plugin'), !empty(Input::get('prerelease')));

        if ($plugin == null) {
            Session::flash('error', 'Plugin Not Found');
            $this->get();
        }

        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent: va-net\r\n"
            )
        );
        $context = stream_context_create($opts);

        foreach ($plugin['fileUrls'] as $name => $url) {
            $data = file_get_contents($url, false, $context);
            file_put_contents(__DIR__ . '/../../../' . $name, $data);
        }

        $db = DB::getInstance();
        foreach ($plugin['sqlQueries'] as $q) {
            $db->query($q);
        }

        $currentplugins = Json::decode(file_get_contents(__DIR__ . '/../../../plugins.json'));
        array_push($currentplugins, $plugin);
        file_put_contents(__DIR__ . '/../../../plugins.json', Json::encode($currentplugins, true));
        @$plugin['className']::init();
        if (method_exists($plugin['className'], 'installed')) @$plugin['className']::installed();

        Session::flash('success', 'Plugin Installed!');
        $this->redirect('/admin/plugins?tab=installed');
    }

    private function remove()
    {
        $plugins = Json::decode(file_get_contents(__DIR__ . '/../../../plugins.json'));
        $GLOBALS['plugin'] = null;
        foreach ($plugins as $p) {
            if ($GLOBALS['plugin'] == null && $p['pluginInfo']['id'] == Input::get('plugin')) {
                $GLOBALS['plugin'] = $p;
                break;
            }
        }

        $newplugins = array_filter($plugins, function ($p) {
            global $plugin;
            return $p['pluginInfo']['id'] != $plugin['pluginInfo']['id'];
        });
        file_put_contents(__DIR__ . '/../../../plugins.json', Json::encode($newplugins, true));

        foreach ($GLOBALS['plugin']['fileUrls'] as $file => $_) {
            $path = __DIR__ . '/../../../' . $file;
            if (!file_exists($path)) continue;

            if (unlink($path) === FALSE) {
                Session::flash('error', 'Failed to remove file - ' . $path);
                $this->get();
            }
        }

        Session::flash('success', 'Plugin Removed');
        $this->redirect('/admin/plugins?tab=installed');
    }

    private function update()
    {
        $installed = null;
        foreach ($GLOBALS['INSTALLED_PLUGINS'] as $p) {
            if ($p['pluginInfo']['id'] == Input::get('plugin')) {
                $installed = $p;
                break;
            }
        }

        if ($installed == null) {
            Session::flash('error', 'Failed to Update Plugin');
            $this->get();
        }

        $GLOBALS['installed'] = $installed;

        // Only update to prerelease if we are already on a prerelease version or the user requested it
        $prerelease = Regex::match("/^v\d+.\d+.\d+-[a-z]+.\d+$/", $installed['versionTag']) || !empty(Input::get('prerelease'));
        $plugin = VANet::pluginUpdateDetails(Input::get('plugin'), $prerelease);

        if ($plugin == null) {
            Session::flash('error', 'Plugin Not Found');
            $this->redirect('/admin/plugins?tab=installed');
        }

        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent: va-net\r\n"
            )
        );
        $context = stream_context_create($opts);

        foreach ($plugin['fileUrls'] as $name => $url) {
            $data = file_get_contents($url, false, $context);
            file_put_contents(__DIR__ . '/../../../' . $name, $data);
        }

        $db = DB::getInstance();
        foreach ($plugin['sqlQueries'] as $q) {
            $db->query($q);
        }

        $GLOBALS['INSTALLED_PLUGINS'] = array_filter($GLOBALS['INSTALLED_PLUGINS'], function ($p) {
            return $p['pluginInfo']['id'] != $GLOBALS['installed']['pluginInfo']['id'];
        });
        $GLOBALS['INSTALLED_PLUGINS'][] = $plugin;
        file_put_contents(__DIR__ . '/../../../plugins.json', Json::encode($GLOBALS['INSTALLED_PLUGINS'], true));
        @$plugin['className']::init();
        if (method_exists($plugin['className'], 'updated')) @$plugin['className']::updated();

        Session::flash('success', 'Plugin Updated!');
        $this->redirect('/admin/plugins?tab=installed');
    }
}
