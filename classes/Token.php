<?php 

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Token {

    /**
     * @return string
     */
    public static function generate() {
        return Session::create('token', Hash::make(uniqid()));
    }

    /**
     * @return bool
     * @param string $token Token to Check
     */
    public static function check($token) {
        $tokenName = 'token';

        if (Session::exists($tokenName) && $token === Session::get($tokenName)) {
            Session::delete($tokenName);
            return true;
        }

        return false;
    }

}