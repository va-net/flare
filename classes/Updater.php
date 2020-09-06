<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Updater 
{
    
    public static function getVersion() {
        $verinfo = Json::decode(file_get_contents(__DIR__.'/../version.json'));
        return array(
            "tag" => $verinfo["tag"],
            "name" => $verinfo["name"],
            "prerelease" => $verinfo["prerelease"],
        );
    }

    public static function getConfig($key = null) {
        $verinfo = Json::decode(file_get_contents(__DIR__.'/../version.json'));
        if ($key == null) {
            return $verinfo["config"];
        }

        return $verinfo["config"][$key];
    }

    public static function checkUpdate() {
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"User-Agent: va-net\r\n"
            )
        );
        $context = stream_context_create($opts);
        $releases = Json::decode(file_get_contents("https://api.github.com/repos/va-net/flare/releases", false, $context));

        $next = null;
        $currentFound = false;
        $current = self::getVersion();
        $config = self::getConfig();

        foreach (array_reverse($releases) as $r) {
            if (!$currentFound && $r["tag_name"] == $current["tag"]) {
                $currentFound = true;
            } elseif ($currentFound && $next == null) {
                if ($config["check_prerelease"]) {
                    $next = $r;
                    break;
                } elseif (!$r["prerelease"]) {
                    $next = $r;
                    break;
                }
            }
        }

        if ($next == null) {
            return false;
        }

        return array(
            "tag" => $next["tag_name"],
            "name" => $next["name"],
            "prerelease" => $next["prerelease"]
        );
    }

}