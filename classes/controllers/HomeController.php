<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

use RegRev\RegRev;

class HomeController extends Controller
{
    public function get()
    {
        $user = new User;
        if (!$user->isLoggedIn()) {
            $this->redirect('/');
        }
        $data = new stdClass;
        $this->render('home', $data);
    }

    public function post()
    {
        if (Input::get('action') === 'editprofile') {
            $this->editprofile();
        } elseif (Input::get('action') === 'changepass') {
            $this->changepass();
        } elseif (Input::get('action') === 'delsensitive') {
            $this->delsensitive();
        } else {
            $this->get();
        }
    }

    private function editprofile()
    {
        $user = new User;
        $csPattern = Config::get('VA_CALLSIGN_FORMAT');
        $trimmedPattern = preg_replace("/\/[a-z]*$/", '', preg_replace("/^\//", '', $csPattern));

        if (Callsign::assigned(Input::get('callsign'), $user->data()->id)) {
            Session::flash('error', 'Callsign is Already Taken!');
            $this->get($user);
        } elseif (!Regex::match($csPattern, Input::get('callsign'))) {
            Session::flash('error', 'Callsign does not match the required format! Try <b>' . RegRev::generate($trimmedPattern) . '</b> instead.');
            Redirect::to('/home');
        } else {
            try {
                $user->update(array(
                    'name' => Input::get('name'),
                    'callsign' => Input::get('callsign'),
                    'email' => Input::get('email'),
                    'ifc' => Input::get('ifc')
                ));
            } catch (Exception $e) {
                Session::flash('error', $e->getMessage());
                $this->get();
            }
            Session::flash('success', 'Profile updated successfully!');
            $this->get();
        }
    }

    private function changepass()
    {
        $user = new User;
        if (!Hash::check(Input::get('oldpass'), $user->data()->password)) {
            Session::flash('error', 'Your Current Password was Incorrect!');
            $this->get();
        }

        try {
            $user->update(array(
                'password' => Hash::make(Input::get('newpass'))
            ));
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
            Redirect::to('home.php');
        }
        Session::flash('success', 'Password Changed Successfully!');
        $this->get();
    }

    private function delsensitive()
    {
        $user = new User;
        try {
            $user->update([
                'email' => '',
                'password' => '',
            ]);
            Session::flash('success', 'Your email and password have been removed from our servers. You now must use VANet to sign in.');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        } finally {
            $this->get();
        }
    }
}
