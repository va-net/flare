<?php 

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Api {

    /**
     * @return object|bool
     * @param string $key Key to Match
     */
    public static function processKey($key) 
    {
        $db = DB::getInstance();
        $sql = "SELECT * FROM pilots WHERE MD5(CONCAT(callsign, password))=? AND status=1";
        $res = $db->query($sql, [$key]);
        if (count($res->results()) == 0) {
            return false;
        }

        return $res->results()[0];
    }

    /**
     * @return object|bool
     * @param string $data Basic Auth Data
     */
    public static function processBasic($data)
    {
        $db = DB::getInstance();
        $data = explode(':', base64_decode($data), 2);
        $sql = "SELECT * FROM pilots WHERE email=? AND status=1";
        $res = $db->query($sql, [$data[0]])->results();
        foreach ($res as $pilot) {
            if (Hash::check($data[1], $pilot->password)) {
                return $pilot;
            }
        }

        return false;
    }

}