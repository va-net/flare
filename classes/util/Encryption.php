<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Encryption
{
    /**
     * @param string $data
     * @param string $key
     * @return string
     */
    public static function encrypt($data, $key)
    {
        $method = "AES-256-CBC";
        $key = hash('sha256', $data, true);
        $iv = openssl_random_pseudo_bytes(16);

        $ciphertext = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
        $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

        return base64_encode($iv . $hash . $ciphertext);
    }

    /**
     * @param string $data
     * @param string $key
     * @return string
     */
    public static function decrypt($ivHashCiphertext, $key)
    {
        $method = "AES-256-CBC";
        $ivHashCiphertext = base64_decode($ivHashCiphertext);
        $iv = substr($ivHashCiphertext, 0, 16);
        $hash = substr($ivHashCiphertext, 16, 32);
        $ciphertext = substr($ivHashCiphertext, 48);
        $key = hash('sha256', $key, true);

        if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;

        return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    }
}
