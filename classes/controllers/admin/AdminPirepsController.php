<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class AdminPirepsController extends Controller
{
    public function get()
    {
        $user = new User;
        $this->authenticate($user, true, 'pirepmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->pending = Pirep::fetchPending();
        $data->all = Pirep::fetchAll();
        $this->render('admin/pireps', $data);
    }

    public function post()
    {
        $user = new User;
        $this->authenticate($user, true, 'pirepmanage');
        switch (Input::get('action')) {
            case 'acceptpirep':
                $this->accept();
            case 'declinepirep':
                $this->decline();
            case 'editpirep':
                $this->edit();
            default:
                $this->get();
        }
    }

    private function accept()
    {
        Pirep::accept(Input::get('accept'));
        Cache::delete('badge_pireps');
        Session::flash('success', 'PIREP Accepted Successfully!');
        $this->redirect('/admin/pireps');
    }

    private function decline()
    {
        Pirep::decline(Input::get('decline'));
        Cache::delete('badge_pireps');
        Session::flash('success', 'PIREP Declined Successfully');
        $this->redirect('/admin/pireps');
    }

    private function edit()
    {
        $data = [
            'flightnum' => Input::get('fnum'),
            'departure' => Input::get('dep'),
            'arrival' => Input::get('arr'),
            'date' => Input::get('date'),
            'flighttime' => Time::strToSecs(Input::get('ftime')),
            'aircraftid' => Input::get('aircraft'),
            'status' => Input::get('status'),
        ];
        if (!Pirep::update(Input::get('id'), $data)) {
            Session::flash('error', 'There was an Error Editing the PIREP');
            $this->redirect('/admin/pireps?tab=all');
        } else {
            Cache::delete('badge_pireps');
            Session::flash('success', 'PIREP Edited Successfully!');
            $this->redirect('/admin/pireps?tab=all');
        }
    }
}
