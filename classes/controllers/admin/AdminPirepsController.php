<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class AdminPirepsController extends Controller
{
    public function get_index()
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

    public function post_index()
    {
        $user = new User;
        $this->authenticate($user, true, 'pirepmanage');
        switch (Input::get('action')) {
            case 'acceptpirep':
                $this->accept();
                $this->get_index();
            case 'declinepirep':
                $this->decline();
                $this->get_index();
            case 'editpirep':
                $this->update();
                $this->redirect('/admin/pireps?tab=all');
            case 'delpirep':
                $this->delete();
                $this->redirect('/admin/pireps?tab=all');
        }

        $this->get_index();
    }

    public function get_multis()
    {
        $user = new User;
        $this->authenticate($user, true, 'pirepmanage');
        $data = new stdClass;
        $data->user = $user;
        $data->va_name = Config::get('va/name');
        $data->is_gold = VANet::isGold();
        $data->multis = Pirep::fetchMultipliers();
        $this->render('admin/multipliers', $data);
    }

    public function post_multis()
    {
        $user = new User;
        $this->authenticate($user, true, 'pirepmanage');
        switch (Input::get('action')) {
            case 'deletemulti':
                $this->delete_multi();
            case 'addmulti':
                $this->create_multi();
            default:
                $this->get_multis();
        }
    }

    public function get_mutli_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'pirepmanage');
        $data = new stdClass;
        $data->user = $user;

        $this->render('admin/multipliers_new', $data);
    }

    public function post_mutli_new()
    {
        $user = new User;
        $this->authenticate($user, true, 'pirepmanage');

        $this->create_multi();
        $this->redirect('/admin/pireps/multipliers');
    }

    private function accept()
    {
        Pirep::accept(Input::get('accept'));
        Cache::delete('badge_pireps');
        Session::flash('success', 'PIREP Accepted Successfully!');
    }

    private function decline()
    {
        Pirep::decline(Input::get('decline'));
        Cache::delete('badge_pireps');
        Session::flash('success', 'PIREP Declined Successfully');
    }

    private function update()
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
        } else {
            Cache::delete('badge_pireps');
            Session::flash('success', 'PIREP Edited Successfully!');
        }
    }

    private function delete()
    {
        if (Pirep::delete(Input::get('id'))) {
            Session::flash('success', 'PIREP Deleted');
        } else {
            Session::flash('error', 'Failed to Delete PIREP');
        }
    }

    private function delete_multi()
    {
        Pirep::deleteMultiplier(Input::get('delete'));
        Session::flash('success', 'Multiplier Deleted Successfully!');
    }

    private function create_multi()
    {
        Pirep::addMultiplier([
            "code" => Pirep::generateMultiCode(),
            "name" => Input::get("name"),
            "multiplier" => Input::get("multi")
        ]);
        Session::flash('success', 'Multiplier Added Successfully!');
    }
}
