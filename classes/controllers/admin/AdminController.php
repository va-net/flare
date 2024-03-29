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
        $this->authenticate($user, true);
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
        $data->leaderboard = Stats::pilotLeaderboard(10, 'flighttime', 'DESC', $days);
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
        $scheme = isset($headers['X-Forwarded-Proto']) ? $headers['X-Forwarded-Proto'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http');

        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->version = Updater::getVersion();
        $data->check_pre = Config::get("CHECK_PRERELEASE");
        $data->update = Updater::checkUpdate($data->check_pre);
        $data->logo_url = Config::get('VA_LOGO_URL');
        $data->logo_url_dark = Config::get('VA_LOGO_URL_DARK');
        $data->callsign_format = Config::get('VA_CALLSIGN_FORMAT');
        $data->va_ident = Config::get('va/identifier');
        $data->force_server = Config::get('FORCE_SERVER');
        $data->color_main = Config::get('site/colour_main_hex');
        $data->text_color = Config::get('TEXT_COLOUR');
        $data->custom_css = Config::getCss();
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
                    || !Config::replace("VA_LOGO_URL_DARK", Input::get('valogo_dark'))
                    || !Config::replace("VA_CALLSIGN_FORMAT", Input::get('vaident'))
                    || !Config::replace("va/identifier", Input::get('vaabbrv'))
                    || !Config::replace("FORCE_SERVER", Input::get('forceserv'))
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
                $scheme = isset($headers['X-Forwarded-Proto']) ? $headers['X-Forwarded-Proto'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http');
                if ($scheme == 'http') {
                    Session::flash('error', 'You must use HTTPS to setup the application');
                    $this->redirect('/admin/settings?tab=interaction');
                }

                $app = VANet::registerApp();
                if (empty($app)) {
                    Session::flash('error', 'Error Registering App');
                    $this->settings();
                }

                Config::add('oauth/client_id', $app['clientId'], false);
                Config::add('oauth/client_secret', $app['clientSecret'], false);

                VANet::updateAppRedirects([OauthController::getUrl() . '/oauth/callback']);
                Session::flash('success', 'App Registered');
                $this->redirect('/admin/settings?tab=interaction');
                break;
            default:
                $this->settings();
        }
    }
}
