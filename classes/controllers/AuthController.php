<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class AuthController extends Controller
{

    public function get()
    {
        $user = new User;
        $data = new stdClass;
        $data->user = $user;
        if ($user->isLoggedIn()) {
            $this->redirect('/home');
        }
        $data->va_name = Config::get('va/name');
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
                $this->redirect('/home');
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
                'required' => true,
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
            'password' => array(
                'required' => true,
                'min' => 6
            ),
            'password-repeat' => array(
                'required' => true,
                'min' => 6,
                'matches' => 'password'
            )
        ));

        $assigned = Callsign::assigned(Input::get('callsign'), 0);
        if ($validate->passed() && Regex::match($csPattern, Input::get('callsign')) && !$assigned) {
            try {
                $user = new User;
                $user->create(array(
                    'name' => Input::get('name'),
                    'email' => Input::get('email'),
                    'ifc' => Input::get('ifc'),
                    'password' => Hash::make(Input::get('password')),
                    'callsign' => Input::get('callsign'),
                    'grade' => Input::get('grade'),
                    'violand' => Input::get('violand'),
                    'notes' => Input::get('notes'),
                ));
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

    public function logout()
    {
        $user = new User();
        $user->logout();

        $this->redirect('/');
    }
}
