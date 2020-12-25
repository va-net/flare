<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once './core/init.php';

$user = new User();

if (!$user->isLoggedIn()) {
    Redirect::to('index.php');
}

if (!$user->hasPermission('opsmanage')) {
    die();
}

$RELEASES_URL = "https://api.github.com/repos/va-net/flare/releases";
$TAGS_URL = "https://api.github.com/repos/va-net/flare/tags";
$RAW_URL = "https://raw.githubusercontent.com/va-net/flare/";
$BRANCH = "master";

$current = Updater::getVersion();

// Get Releases
$opts = array(
    'http' => array(
        'method' => "GET",
        'header' => "User-Agent: va-net\r\n"
    )
);
$context = stream_context_create($opts);
$releases = Json::decode(file_get_contents($RELEASES_URL, false, $context));

// Find next applicable release
$currentFound = false;
$next = null;
foreach (array_reverse($releases) as $r) {
    if (!$currentFound && $r["tag_name"] == $current["tag"]) {
        $currentFound = true;
    } elseif ($currentFound && $next == null) {
        if (Config::get('CHECK_PRERELEASE') == 1) {
            $next = $r;
            $BRANCH = "beta";
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

// Get Tag Info
$tags = Json::decode(file_get_contents($TAGS_URL, false, $context));
$nextTag = null;
foreach ($tags as $t) {
    if ($t["name"] == $next["tag_name"] && $nextTag == null) {
        $nextTag = $t;
        break;
    }
}

// Get the updates.json file
$updateData = @file_get_contents($RAW_URL . $BRANCH . "/updates.json");
// Check Release is Compatible
if ($updateData === FALSE) {
    echo "This Version of Flare does not support the Updater.";
    die();
}

// Process updates.json File
$updateData = Json::decode($updateData);
$nextUpdate = null;
foreach ($updateData as $upd) {
    if ($upd["tag"] == $nextTag["name"]) {
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

$slash = "/";
if (strpos(strtolower(php_uname('s')), "window") !== FALSE) {
    $slash = "\\";
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

// Add Directories
if (array_key_exists('newFolders', $nextUpdate)) {
    foreach ($nextUpdate['newFolders'] as $dir) {
        $dir = str_replace("/", $slash, $dir);
        if (!file_exists(__DIR__ . $slash . $dir)) {
            if (mkdir(__DIR__ . $slash . $dir) === FALSE) {
                echo "Error Creating Directory " . $dir;
                die();
            }
        }
    }
}

// Update Files
foreach ($nextUpdate["files"] as $file) {
    $fileData = file_get_contents($RAW_URL . urlencode($nextTag["commit"]["sha"]) . "/" . urlencode($file));
    $file = str_replace("/", $slash, $file);
    if ($fileData === FALSE || file_put_contents(__DIR__ . $slash . $file, $fileData) === FALSE) {
        echo "Error Updating File " . $file;
        die();
    }
}
echo "Updated Files Successfully<br />";

// Delete Files to Delete
if (!($current["prerelease"] && !$next["prerelease"])) {
    foreach ($nextUpdate["deletedFiles"] as $delFile) {
        if (!unlink(__DIR__ . $slash . $delFile)) {
            echo "There was an error deleting " . $delFile;
        }
    }
    echo "Deleted Removed Files Successfully<br />";
}

// Update Version File
$vData = file_get_contents($RAW_URL . urlencode($nextTag["commit"]["sha"]) . "/version.json");
file_put_contents(__DIR__ . $slash . "version.json", $vData);
echo "Updated Version File<br />";

Events::trigger('site/updated', $nextUpdate);

echo "<br />Flare has been Updated to " . $nextUpdate["name"] . " (" . $nextUpdate["tag"] . ")";
