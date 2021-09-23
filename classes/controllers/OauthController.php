<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class OauthController extends Controller
{
    public function auth_redirect()
    {
        $client_id = Config::get('oauth/client_id');
        if (empty($client_id) || !VANet::featureEnabled('airline-membership')) {
            $this->notFound();
        }

        $scope = ['profile', 'register:memberships', 'write:flights', 'write:events'];
        $response_type = 'code';
        $redirect_uri = Analytics::url() . '/oauth/callback';
        $state = Token::generate();

        $this->redirect('https://vanet.app/oauth/authorize?scope=' . urlencode(implode(' ', $scope)) . '&response_type=' . urlencode($response_type) . '&client_id=' . urlencode($client_id) . '&redirect_uri=' . urlencode($redirect_uri) . '&state=' . urlencode($state));
    }

    public function auth_callback()
    {
        if (!empty(Input::get('error'))) {
            Session::flash('error', Input::get('error'));
            $this->redirect('/');
        }

        if (!Token::check(Input::get('state'))) {
            Session::flash('error', 'Invalid state');
            $this->redirect('/');
        }

        $data = VANet::tokenExchange(Input::get('code'), Analytics::url() . '/oauth/callback');
        if (!$data || isset($data['error'])) {
            Session::flash('error', 'Could not authenticate with VANet. Please try again later.');
            $this->redirect('/');
        }

        $accessToken = $data['access_token'];
        $refreshToken = $data['refresh_token'];
        $expiry = date('Y-m-d H:i:s', strtotime('+' . $data['expires_in'] . ' seconds'));

        $profile = Json::decode(HttpRequest::hacky('https://api.vanet.app/public/v1/oauth/userinfo', 'GET', '', [
            'Authorization: Bearer ' . $accessToken,
        ]));
        if (!$profile || isset($profile['error'])) {
            Session::flash('error', 'Failed to retrieve user profile');
            $this->redirect('/');
        }

        $user = new User;
        if ($user->isLoggedIn()) {
            try {
                $user->update([
                    'vanet_id' => $profile['sub'],
                    'vanet_accesstoken' => $accessToken,
                    'vanet_refreshtoken' => $refreshToken,
                    'vanet_expiry' => $expiry,
                    'ifuserid' => $profile['vanet_ifuid'],
                ]);
            } catch (Exception $e) {
                Session::flash('error', 'Failed to link your VANet account.');
                $this->redirect('/home');
            }

            Session::flash('success', 'Your VANet account has been linked.');
            $this->redirect('/home');
        }

        if ($user->vanetLogin($profile['sub'])) {
            $this->redirect('/home');
        } else {
            Session::flash('error', 'Login Failed. Your application may still be pending or it may have been denied. Please contact us for more details if you believe this is an error.');
            $this->redirect('/');
        }
    }
}
