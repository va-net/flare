<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

use RegRev\RegRev;

class AuthController extends Controller
{

    public function get()
    {
        if (!Config::isReady()) {
            $this->redirect('/install/install.php');
        }
        $user = new User;
        $data = new stdClass;
        $data->user = $user;
        if ($user->isLoggedIn()) {
            $this->redirect('/home');
        }
        $data->vanet_signin = !empty(Config::get('oauth/client_id')) && VANet::featureEnabled('airline-membership');
        $this->render('login', $data);
    }

    public function post()
    {
        $user = new User;
        $data = new stdClass;
        $data->user = $user;
        if ($user->isLoggedIn()) {
            $this->redirect('/home');
        }
        if (!Token::check(Input::get('token'))) $this->render('login', $data);

        $validate = new Validate();
        $validation = $validate->check($_POST, array(
            'email' => array(
                'required' => true
            ),
            'password' => array(
                'required' => true
            )
        ));

        if ($validation->passed()) {
            $remember = Input::get('remember') === 'on';

            if ($user->login(Input::get('email'), Input::get('password'), $remember)) {
                if ($user->data()->vanet_memberid == null && $user->data()->vanet_id != null && VANet::featureEnabled('airline-membership')) {
                    VANet::refreshMembership($user);
                }

                $this->redirect(Session::exists('login_redirect') ? Session::get('login_redirect') : '/home');
            } else {
                $user->logout();
                Session::flash('error', 'Login Failed. Your application may still be pending or it may have been denied. Please contact us for more details if you believe this is an error.');
                $this->render('login', $data);
            }
        } else {
            $data->errors = $validation->errors();
            $this->render('login', $data);
        }
    }

    public function apply_get()
    {
        $user = new User;
        $data = new stdClass;
        $data->user = $user;
        if ($user->isLoggedIn()) {
            $this->redirect('/home');
        }
        $data->callsign_format = Config::get('VA_CALLSIGN_FORMAT');
        $data->va_name = Config::get('va/name');

        $trimmedPattern = preg_replace("/\/[a-z]*$/", '', preg_replace("/^\//", '', $data->callsign_format));
        $filledCallsign = '';
        if (!empty($trimmedPattern)) {
            $callsigns = Callsign::all();
            if (count($callsigns) < 1) {
                $filledCallsign = RegRev::generate($trimmedPattern);
            } else {
                $filledCallsign = $callsigns[0];
                $i = 0;
                while (in_array($filledCallsign, $callsigns) && $i < 50) {
                    $filledCallsign = RegRev::generate($trimmedPattern);
                    $i++;
                }
                if (in_array($filledCallsign, $callsigns)) {
                    $filledCallsign = '';
                }
            }
        }

        $data->callsign = $filledCallsign;
        $data->vanet_signin = !empty(Config::get('oauth/client_id')) && VANet::featureEnabled('airline-membership');

        $this->render('apply', $data);
    }

    public function apply_post()
    {
        $csPattern = Config::get('VA_CALLSIGN_FORMAT');
        $validate = new Validate();
        $validate->check($_POST, array(
            'name' => array(
                'required' => true,
                'min' => 2,
                'max' => 50
            ),
            'ifc' => array(
                'required' => true
            ),
            'email' => array(
                'min' => 5,
                'max' => 50
            ),
            'callsign' => array(
                'required' => true,
                'max' => 120
            ),
            'violand' => array(
                'required' => true
            ),
            'grade' => array(
                'required' => true
            ),
            'password-repeat' => array(
                'matches' => 'password'
            )
        ));

        $assigned = Callsign::assigned(Input::get('callsign'), 0);
        if ($validate->passed() && Regex::match($csPattern, Input::get('callsign')) && !$assigned) {
            try {
                $user = new User;
                $uData = array(
                    'name' => Input::get('name'),
                    'email' => Input::get('email'),
                    'ifc' => Input::get('ifc'),
                    'password' => Hash::make(Input::get('password')),
                    'callsign' => Input::get('callsign'),
                    'grade' => Input::get('grade'),
                    'violand' => Input::get('violand'),
                    'notes' => Input::get('notes'),
                );
                if (Session::exists('pilot_apply')) {
                    foreach (Session::get('pilot_apply') as $key => $val) {
                        $uData[$key] = $val;
                    }
                    Session::delete('pilot_apply');
                }
                $user->create($uData);
            } catch (Exception $e) {
                die($e->getMessage());
            }
            Cache::delete('badge_recruitment');
            Session::flash('success', 'Your application has been submitted! You will be contacted by a staff member in the coming weeks regarding the status of your application.');
            $this->redirect('/');
        } elseif (!$validate->passed()) {
            Session::flash('error', $validate->errors()[0]);
        } elseif ($assigned) {
            Session::flash('error', 'That callsign is already taken');
        } else {
            Session::flash('error', 'Your Callsign is in an Invalid Format');
        }
        $this->apply_get();
    }

    public function apply_vanet_get()
    {
        $client_id = Config::get('oauth/client_id');
        if (empty($client_id) || !VANet::featureEnabled('airline-membership')) {
            $this->notFound();
        }

        $user = new User;
        $data = new stdClass;
        $data->user = $user;
        if ($user->isLoggedIn()) {
            $this->redirect('/home');
        }

        if (!Session::exists('pilot_apply')) {
            die();
        }

        $data->callsign_format = Config::get('VA_CALLSIGN_FORMAT');
        $trimmedPattern = preg_replace("/\/[a-z]*$/", '', preg_replace("/^\//", '', $data->callsign_format));
        $filledCallsign = '';
        if (!empty($trimmedPattern)) {
            $callsigns = Callsign::all();
            if (count($callsigns) < 1) {
                $filledCallsign = RegRev::generate($trimmedPattern);
            } else {
                $filledCallsign = $callsigns[0];
                $i = 0;
                while (in_array($filledCallsign, $callsigns) && $i < 50) {
                    $filledCallsign = RegRev::generate($trimmedPattern);
                    $i++;
                }
                if (in_array($filledCallsign, $callsigns)) {
                    $filledCallsign = '';
                }
            }
        }

        $data->callsign = $filledCallsign;
        $data->apply_data = Session::get('pilot_apply');

        $this->render('apply_vanet', $data);
    }

    public function logout()
    {
        $user = new User();
        $user->logout();

        $this->redirect('/');
    }

    public function get_profile()
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->awards = $user->getAwards();
        $this->render('profile', $data);
    }

    public function post_profile()
    {
        $user = new User;
        $csPattern = Config::get('VA_CALLSIGN_FORMAT');
        $trimmedPattern = preg_replace("/\/[a-z]*$/", '', preg_replace("/^\//", '', $csPattern));

        if (Callsign::assigned(Input::get('callsign'), $user->data()->id)) {
            Session::flash('error', 'Callsign is Already Taken!');
            $this->get_profile();
        } elseif (!Regex::match($csPattern, Input::get('callsign'))) {
            Session::flash('error', 'Callsign does not match the required format! Try <b>' . RegRev::generate($trimmedPattern) . '</b> instead.');
            $this->get_profile();
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
                $this->get_profile();
            }
            Session::flash('success', 'Profile updated successfully!');
            $this->get_profile();
        }
    }
}
