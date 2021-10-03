<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once __DIR__ . '/core/init.php';

$user = new User();

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}

if (!$user->hasPermission('site')) {
    die();
}

$RELEASES_URL = Updater::releasesUrl();
$BRANCH = Updater::githubDefaultBranch();
$DL_URL = Updater::downloadUrl($BRANCH);

$current = Updater::getVersion();

$auth = Updater::authentication();

// Get Releases
$opts = array(
    'http' => array(
        'method' => "GET",
        'header' => "User-Agent: va-net\r\nCache-Control: no-cache\r\n"
    )
);
if (!empty($auth)) {
    $opts['http']['header'] .= "Authorization: Basic " . base64_encode($auth) . "\r\n";
}
$context = stream_context_create($opts);
$releases = array_filter(Json::decode(file_get_contents($RELEASES_URL, false, $context)), function ($release) {
    return !$release['draft'];
});
usort($releases, function ($x, $y) {
    if ($x['published_at'] == $y['published_at']) {
        return 0;
    }

    $a = new DateTime($x['published_at']);
    $b = new DateTime($y['published_at']);
    $diff = $a->diff($b);
    return $diff->invert ? -1 : 1;
});

// Find next applicable release
$currentFound = false;
$next = null;
foreach (array_reverse($releases) as $r) {
    if (!$currentFound && $r["tag_name"] == $current["tag"]) {
        $currentFound = true;
    } elseif ($currentFound && $next == null) {
        if (Config::get('CHECK_PRERELEASE') == 1) {
            $next = $r;
            $BRANCH = Updater::githubPrereleaseBranch();
            $DL_URL = Updater::downloadUrl($BRANCH);
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

if ($next["tag_name"] != $releases[0]["tag_name"]) {
    echo 'Notice: You are updating to a version of Flare that is not the most recent. 
    After this operation is complete, refresh the page then run the updater again.<br /><br />';
}

// Get the updates.json file
$updateData = Json::decode(file_get_contents("{$DL_URL}/updates.json", false, $context));
// Check Release is Compatible
if (empty($updateData)) {
    echo "This Version of Flare does not support the Updater.";
    die();
}

// Process updates.json File
$nextUpdate = null;
foreach ($updateData as $upd) {
    if ($upd["tag"] == $next["tag_name"]) {
        $nextUpdate = $upd;
    }
}

// Somethin' strange goin on?
if ($nextUpdate == null) {
    echo "There was an Error Updating Flare";
    die();
}

// Check it can be updated with the updater
if (!$nextUpdate["useUpdater"]) {
    echo "This version contains changes that cannot be installed with the updater. ";
    echo "Please update manually using the instructions available on <a href=\"{$next['html_url']}\" target=\"_blank\">GitHub</a>.";
    die();
}

$DL_URL = Updater::downloadUrl($next['tag_name']);

// Run DB Queries
if (count($nextUpdate["queries"]) != 0 && !($current["prerelease"] && !$next["prerelease"])) {
    $db = DB::getInstance();
    foreach ($nextUpdate["queries"] as $q) {
        if ($q == 'TODO') continue;
        $db->query($q);
        if ($db->error()) {
            echo "Error Running Query " . $q;
            die();
        }
    }
}
echo "Updated Database Successfully<br />";

// Delete Files to Delete
if (!($current["prerelease"] && !$next["prerelease"])) {
    foreach ($nextUpdate["deletedFiles"] as $delFile) {
        if (!file_exists($delFile)) continue;

        if (is_dir(__DIR__ . '/' . $delFile)) {
            if (!rrmdir(__DIR__ . '/' . $delFile)) {
                echo "There was an error deleting " . $delFile;
            }
        } else {
            if (!unlink(__DIR__ . '/' . $delFile)) {
                echo "There was an error deleting " . $delFile;
            }
        }
    }
    echo "Deleted Removed Files Successfully<br />";
}

// Add Directories
if (array_key_exists('newFolders', $nextUpdate)) {
    foreach ($nextUpdate['newFolders'] as $dir) {
        if (!file_exists(__DIR__ . '/' . $dir)) {
            if (mkdir(__DIR__ . '/' . $dir) === FALSE) {
                echo "Error Creating Directory " . $dir;
                die();
            }
        }
    }
}

// Update Files
foreach ($nextUpdate["files"] as $file) {
    $fileData = file_get_contents($DL_URL . '/' . $file, false, $context);
    if (empty($fileData) || file_put_contents(__DIR__ . '/' . $file, $fileData) === FALSE) {
        echo "Error Updating File " . $file;
        die();
    }
}
echo "Updated Files Successfully<br />";

// Update Version File
$vData = file_get_contents($DL_URL . "/version.json", false, $context);
file_put_contents(__DIR__ . '/' . "version.json", $vData);
echo "Updated Version File<br />";

Events::trigger('site/updated', $nextUpdate);
Cache::clear();

echo "<br />Flare has been Updated to " . $nextUpdate["name"] . " (" . $nextUpdate["tag"] . ")";
