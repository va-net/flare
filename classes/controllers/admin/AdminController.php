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
        $data->pireps_90 = Stats::totalFlights(90);
        $data->hrs_90 = Time::secsToString(Stats::totalHours(90));
        $data->pilots_90 = Stats::pilotsApplied(90);
        $data->leaderboard = Stats::pilotLeaderboard(5, 'flighttime');

        $dates = daterange(date("Y-m-d", strtotime("-30 days")), date("Y-m-d"));
        $vals = array_map(function () {
            return 0;
        }, $dates);

        $allpireps = Pirep::fetchPast(30);
        $pirepsAssoc = array_combine($dates, $vals);
        foreach ($allpireps as $p) {
            $p['date'] = date_format(date_create($p['date']), "Y-m-d");
            $pirepsAssoc[$p['date']]++;
        }
        $data->pireps_chart_labels = array_keys($pirepsAssoc);
        $data->pireps_chart_data = array_values($pirepsAssoc);

        $allpilots = User::fetchPast(30);
        $pilotsAssoc = array_combine($dates, $vals);
        foreach ($allpilots as $p) {
            $p['joined'] = date_format(date_create($p['joined']), "Y-m-d");
            $pilotsAssoc[$p['joined']]++;
        }
        $data->pilots_chart_labels = array_keys($pilotsAssoc);
        $data->pilots_chart_data = array_values($pilotsAssoc);

        $this->render('admin/index', $data);
    }
}
