<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Updater
{
    /**
     * @return array
     */
    public static function getVersion()
    {
        return Json::decode(file_get_contents(__DIR__ . '/../version.json'));
    }

    /**
     * @return bool|array
     * @param bool $prerelease Whether to Check for Prerelease Versions
     */
    public static function checkUpdate($prerelease = false)
    {
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent: va-net\r\n"
            )
        );
        $context = stream_context_create($opts);
        $releases = Json::decode(file_get_contents("https://api.github.com/repos/va-net/flare/releases", false, $context));

        $next = null;
        $currentFound = false;
        $current = self::getVersion();

        foreach (array_reverse($releases) as $r) {
            if (!$currentFound && $r["tag_name"] == $current["tag"]) {
                $currentFound = true;
            } elseif ($currentFound && $next == null) {
                if ($prerelease) {
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

    /**
     * @return bool
     */
    public static function updateAvailable()
    {
        return Updater::checkUpdate(Config::get('CHECK_PRERELEASE') == 1) !== FALSE;
    }
}
