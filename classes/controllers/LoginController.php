<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class LoginController extends Controller
{

    public function get()
    {
        $user = new User;
        $data = new stdClass;
        $data->user = $user;
        if ($user->isLoggedIn()) {
            $this->redirect('/home');
        }
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
}
