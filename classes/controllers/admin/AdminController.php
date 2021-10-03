<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->va_color = Config::get('site/colour_main_hex');
        $data->is_gold = VANet::isGold();

        $days = 90;
        if (!empty(Input::get('days')) && is_numeric(Input::get('days'))) {
            $days = intval(Input::get('days'));
        }
        $data->days = $days;

        $data->pireps = Stats::totalFlights($days);
        $data->hrs = Time::secsToString(Stats::totalHours($days));
        $data->pilots = Stats::pilotsApplied($days);
        $data->leaderboard = Stats::pilotLeaderboard(5, 'flighttime');
        $data->active_dropdown = 'site-management';

        $dates = daterange(date("Y-m-d", strtotime("-{$days} days")), date("Y-m-d"));
        $vals = array_map(function () {
            return 0;
        }, $dates);

        $allpireps = Pirep::fetchPast($days);
        $pirepsAssoc = array_combine($dates, $vals);
        foreach ($allpireps as $p) {
            $p['date'] = date_format(date_create($p['date']), "Y-m-d");
            $pirepsAssoc[$p['date']]++;
        }
        $data->pireps_chart_labels = array_keys($pirepsAssoc);
        $data->pireps_chart_data = array_values($pirepsAssoc);

        $allpilots = User::fetchPast($days);
        $pilotsAssoc = array_combine($dates, $vals);
        foreach ($allpilots as $p) {
            $p['joined'] = date_format(date_create($p['joined']), "Y-m-d");
            $pilotsAssoc[$p['joined']]++;
        }
        $data->pilots_chart_labels = array_keys($pilotsAssoc);
        $data->pilots_chart_data = array_values($pilotsAssoc);

        $this->render('admin/index', $data);
    }

    public function settings()
    {
        $user = new User;
        $this->authenticate($user, true, 'site');

        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->version = Updater::getVersion();
        $data->check_pre = Config::get("CHECK_PRERELEASE");
        $data->update = Updater::checkUpdate($data->check_pre);
        $data->logo_url = Config::get('VA_LOGO_URL');
        $data->callsign_format = Config::get('VA_CALLSIGN_FORMAT');
        $data->va_ident = Config::get('va/identifier');
        $data->force_server = Config::get('FORCE_SERVER');
        $data->color_main = Config::get('site/colour_main_hex');
        $data->text_color = Config::get('TEXT_COLOUR');
        $data->analytics_enabled = !empty(Config::get('INSTANCE_ID'));
        $data->custom_css = Config::getCss();
        $data->migrate_config = !file_exists(__DIR__ . '/../../../core/config.new.php');
        $data->setup_app = (empty(Config::get('oauth/client_id')) || empty(Config::get('oauth/client_secret'))) && VANet::featureEnabled('airline-membership') && file_exists(__DIR__ . '/../../../core/config.new.php');
        $data->themes = array_filter(scandir(__DIR__ . '/../../../themes'), function ($x) {
            return strpos($x, '.') !== 0;
        });
        $data->active_theme = Config::get('ACTIVE_THEME');
        if (empty($data->active_theme)) {
            $data->active_theme = 'default';
        }

        $data->active_dropdown = 'site-management';
        $this->render('admin/settings', $data);
    }

    public function settings_post()
    {
        $user = new User;
        $this->authenticate($user, true, 'site');
        switch (Input::get('action')) {
            case 'vasettingsupdate':
                $pre = true;
                if (!Updater::getVersion()['prerelease']) {
                    $pre = Config::replace("CHECK_PRERELEASE", Input::get('checkpre'));
                }

                if (
                    !Config::replace('name', Input::get('vaname'))
                    || !Config::replace('identifier', Input::get('vaabbrv'))
                    || !Config::replace("FORCE_SERVER", Input::get('forceserv'))
                    || !$pre
                    || !Config::replace("VA_CALLSIGN_FORMAT", Input::get('vaident'))
                    || !Config::replace("VA_LOGO_URL", Input::get('valogo'))
                ) {
                    Session::flash('error', 'Error Updating Settings');
                } else {
                    Session::flash('success', 'Settings Updated');
                }
                $this->redirect('/admin/settings');
                break;
            case 'setdesign':
                if (
                    !Config::replaceColour(trim(Input::get('hexcol'), "#"), trim(Input::get('textcol'), "#"))
                    || !Config::replaceCss(Input::get('customcss'))
                    || !Config::replace('ACTIVE_THEME', Input::get('theme'))
                ) {
                    Session::flash('error', 'Error Updating Design');
                } else {
                    Session::flash('success', 'Design Updated. You may need to reload the page or clear your cache in order for it to show.');
                }
                $this->redirect('/admin/settings?tab=design');
                break;
            case 'interactionupdate':
                $oldAnalytics = !empty(Config::get('INSTANCE_ID'));
                if ($oldAnalytics && Input::get('analytics') == 0) {
                    Analytics::unregister();
                } elseif (!$oldAnalytics && Input::get('analytics') == 1) {
                    Analytics::register();
                }

                Session::flash('success', 'Settings Updated');
                $this->redirect('/admin/settings?tab=interaction');
                break;
            case 'clearlogs':
                if (Input::get('period') == '*') {
                    Logger::clearAll();
                } else {
                    Logger::clearOld(Input::get('period'));
                }

                Session::flash('success', 'Logs Cleared');
                $this->redirect('/admin/settings?tab=maintenance');
            case 'clearcache':
                Cache::clear();
                Session::flash('success', 'Cache Cleared');
                $this->redirect('/admin/settings?tab=maintenance');
                break;
            case 'setupapp':
                $app = VANet::registerApp();
                if (empty($app)) {
                    Session::flash('error', 'Error Registering App');
                    $this->settings();
                }

                Config::add('oauth/client_id', $app['clientId'], false);
                Config::add('oauth/client_secret', $app['clientSecret'], false);

                VANet::updateAppRedirects([Analytics::url() . '/oauth/callback']);
                Session::flash('success', 'App Registered');
                $this->redirect('/admin/settings?tab=interaction');
            default:
                $this->settings();
        }
    }
}
