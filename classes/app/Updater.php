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
        return Json::decode(file_get_contents(__DIR__ . '/../../version.json'));
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
        $auth = self::authentication();
        if (!empty($auth)) {
            $opts['http']['header'] .= "Authorization: Basic " . base64_encode($auth) . "\r\n";
        }
        $context = stream_context_create($opts);
        $releases = Json::decode(file_get_contents("https://api.github.com/repos/va-net/flare/releases", false, $context));

        if ($releases == null) {
            return false;
        }

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
        return self::checkUpdate(Config::get('CHECK_PRERELEASE') == 1) !== FALSE;
    }

    /**
     * @return string
     */
    public static function releasesUrl()
    {
        $conf = Config::get('updater/releases_url');
        if (!empty($conf)) return $conf;

        return 'https://api.github.com/repos/va-net/flare/releases';
    }

    /**
     * @return string
     * @param string $ref
     */
    public static function downloadUrl($ref)
    {
        $conf = Config::get('updater/raw_url');
        if (!empty($conf)) return $conf;

        return 'https://raw.githubusercontent.com/va-net/flare/' . urlencode($ref);
    }

    /**
     * @return string
     */
    public static function githubDefaultBranch()
    {
        $conf = Config::get('updater/github_branch');
        if (!empty($conf)) return $conf;

        return 'master';
    }

    /**
     * @return string
     */
    public static function githubPrereleaseBranch()
    {
        $conf = Config::get('updater/github_branch_prerelease');
        if (!empty($conf)) return $conf;

        return 'beta';
    }

    /**
     * @return string|null
     */
    public static function authentication()
    {
        $conf = Config::get('updater/authentication');
        if (!empty($conf)) return $conf;

        return null;
    }
}
