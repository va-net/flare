<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class AirportController extends Controller
{
    public function get_airport($icao)
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;

        $data->airport = VANet::getAirport($icao);
        if ($data->airport == null) {
            $this->notFound('Airport Not Found');
        }

        $data->routes = Route::getByAirport($icao);
        $data->pireps = Pirep::getByAirport($icao);

        $this->render('airport', $data);
    }
}
