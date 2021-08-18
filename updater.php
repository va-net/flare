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

if (!$user->hasPermission('opsmanage')) {
    die();
}

$RELEASES_URL = Updater::releasesUrl();
$DL_URL = Updater::downloadUrl();
$BRANCH = Updater::githubDefaultBranch();

$current = Updater::getVersion();

$auth = Updater::authentication();

// Get Releases
$opts = array(
    'http' => array(
        'method' => "GET",
        'header' => "User-Agent: va-net\r\n"
    )
);
if (!empty($auth)) {
    $opts['http']['header'] .= "Authorization: Basic " . base64_encode($auth) . "\r\n";
}
$context = stream_context_create($opts);
$releases = array_filter(Json::decode(file_get_contents($RELEASES_URL, false, $context)), function ($release) {
    return !$release['draft'];
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
$updateResponse = Json::decode(file_get_contents("{$DL_URL}/updates.json?ref=" . urlencode($BRANCH), false, $context));
$updateData = base64_decode($updateResponse["content"]);
// Check Release is Compatible
if ($updateData === FALSE) {
    echo "This Version of Flare does not support the Updater.";
    die();
}

// Process updates.json File
$updateData = Json::decode($updateData);
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
    $fileInfo = Json::decode(file_get_contents($DL_URL . "/" . $file . '?ref=' . urlencode($next["tag_name"]), false, $context));
    $fileData = base64_decode($fileInfo["content"]);
    if ($fileData === FALSE || file_put_contents(__DIR__ . '/' . $file, $fileData) === FALSE) {
        echo "Error Updating File " . $file;
        die();
    }
}
echo "Updated Files Successfully<br />";

// Update Version File
$vInfo = Json::decode(file_get_contents($DL_URL . "/version.json?ref=" . urlencode($next["tag_name"]), false, $context));
$vData = base64_decode($vInfo["content"]);
file_put_contents(__DIR__ . '/' . "version.json", $vData);
echo "Updated Version File<br />";

Events::trigger('site/updated', $nextUpdate);
Cache::clear();

echo "<br />Flare has been Updated to " . $nextUpdate["name"] . " (" . $nextUpdate["tag"] . ")";
