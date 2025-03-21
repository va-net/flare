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

        $scope = ['read:profile', 'register:memberships', 'write:flights', 'write:events'];
        $response_type = 'code';
        $redirect_uri = $this->getUrl() . '/oauth/callback';
        $state = Token::generate();

        $this->redirect('https://vanet.app/oauth/authorize?scope=' . urlencode(implode(' ', $scope)) . '&response_type=' . urlencode($response_type) . '&client_id=' . urlencode($client_id) . '&redirect_uri=' . urlencode($redirect_uri) . '&state=' . urlencode($state));
    }

    public static function getUrl()
    {
        $headers = getallheaders();
        $scheme = isset($headers['X-Forwarded-Proto']) ? $headers['X-Forwarded-Proto'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http');
        return sprintf(
            "%s://%s",
            $scheme,
            $_SERVER['SERVER_NAME']
        );
    }

    public function auth_callback()
    {
        $client_id = Config::get('oauth/client_id');
        if (empty($client_id) || !VANet::featureEnabled('airline-membership')) {
            $this->notFound();
        }

        if (!empty(Input::get('error'))) {
            Session::flash('error', Input::get('error'));
            $this->redirect('/');
        }

        if (!Token::check(Input::get('state'))) {
            Session::flash('error', 'Invalid state');
            $this->redirect('/');
        }

        $data = VANet::tokenExchange(Input::get('code'), $this->getUrl() . '/oauth/callback');
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
        $key = Config::get('vanet/api_key');
        if ($user->isLoggedIn()) {
            try {
                $user->update([
                    'vanet_id' => $profile['sub'],
                    'vanet_accesstoken' => Encryption::encrypt($accessToken, $key),
                    'vanet_refreshtoken' => Encryption::encrypt($refreshToken, $key),
                    'vanet_expiry' => $expiry,
                    'ifuserid' => $profile['vanet_ifuid'],
                ]);
                if (!$user->data()->vanet_memberid) VANet::refreshMembership($user);
            } catch (Exception $e) {
                Session::flash('error', 'Failed to link your VANet account.');
                $this->redirect(Session::exists('login_redirect') ? Session::get('login_redirect') : '/home');
            }

            Session::flash('success', 'Your VANet account has been linked.');
            $this->redirect(Session::exists('login_redirect') ? Session::get('login_redirect') : '/home');
        }

        if ($user->vanetLogin($profile['sub'])) {
            if (!$user->data()->vanet_memberid) VANet::refreshMembership($user);
            $this->redirect(Session::exists('login_redirect') ? Session::get('login_redirect') : '/home');
        } elseif ($profile['vanet_admin']) {
            $id = User::nextId();
            $user->create([
                'id' => $id,
                'callsign' => '',
                'name' => $profile['name'],
                'ifc' => '',
                'ifuserid' => $profile['vanet_ifuid'],
                'email' => '',
                'password' => '',
                'status' => 1,
                'vanet_id' => $profile['sub'],
                'vanet_accesstoken' => Encryption::encrypt($accessToken, $key),
                'vanet_refreshtoken' => Encryption::encrypt($refreshToken, $key),
                'vanet_expiry' => $expiry,
            ]);
            Permissions::giveAll($id);
            $user->vanetLogin($profile['sub']);
            $this->redirect(Session::exists('login_redirect') ? Session::get('login_redirect') : '/home');
        } else {
            Session::create('pilot_apply', [
                'vanet_id' => $profile['sub'],
                'vanet_accesstoken' => Encryption::encrypt($accessToken, $key),
                'vanet_refreshtoken' => Encryption::encrypt($refreshToken, $key),
                'vanet_expiry' => $expiry,
                'ifuserid' => $profile['vanet_ifuid'],
                'name' => $profile['name'],
                'email' => '',
                'password' => '',
            ]);
            $this->redirect('/apply/vanet');
        }
    }
}
