<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

if (!$user->hasPermission('opsmanage')) {
    die();
}

$current = Updater::getVersion();
$config = Updater::getConfig();

// Get Releases
$opts = array(
    'http'=>array(
        'method'=>"GET",
        'header'=>"User-Agent: va-net\r\n"
    )
);
$context = stream_context_create($opts);
$releases = Json::decode(file_get_contents("https://api.github.com/repos/va-net/flare/releases", false, $context));

// Find next applicable release
$currentFound = false;
$next = null;
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
    echo "Already Up-to-Date";
    die();
}

// Get Tag Info
$tags = Json::decode(file_get_contents("https://api.github.com/repos/va-net/flare/tags", false, $context));
$nextTag = null;
foreach ($tags as $t) {
    if ($t["name"] == $next["tag_name"] && $nextTag == null) {
        $nextTag = $t;
        break;
    }
}

// Get the updates.json file
