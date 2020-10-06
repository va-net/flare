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

$current = Updater::getVersion();

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
        if ($current["prerelease"]) {
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
$updateData = @file_get_contents("https://raw.githubusercontent.com/va-net/flare/".urlencode($nextTag["commit"]["sha"])."/updates.json");
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

// Add Directories
if (array_key_exists('newFolders', $nextUpdate)) {
    foreach ($nextUpdate['newFolders'] as $dir) {
        $dir = str_replace("/", $slash, $dir);
        if (!file_exists(__DIR__.$slash.$dir)) {
            if (mkdir(__DIR__.$slash.$dir) === FALSE) {
                echo "Error Creating Directory ".$dir;
                die();
            }
        }
    }
}

// Update Files
foreach ($nextUpdate["files"] as $file) {
    $fileData = file_get_contents("https://raw.githubusercontent.com/va-net/flare/".urlencode($nextTag["commit"]["sha"])."/".urlencode($file));
    $file = str_replace("/", $slash, $file); 
    if ($fileData === FALSE || file_put_contents(__DIR__.$slash.$file, $fileData) === FALSE) {
        echo "Error Updating File ".$file;
        die();
    }
}
echo "Updated Files Successfully<br />";

// Delete Files to Delete
foreach ($nextUpdate["deletedFiles"] as $delFile) {
    if (!unlink(__DIR__.$slash.$delFile)) {
        echo "There was an error deleting ".$delFile;
    }
}
echo "Deleted Removed Files Successfully<br />";

// Run DB Queries
if (count($nextUpdate["queries"]) != 0) {
    $db = DB::getInstance();
    foreach ($nextUpdate["queries"] as $q) {
        $db->query($q);
        if ($db->error()) {
            echo "Error Running Query ".$q;
            die();
        }
    }
}

Events::trigger('site/updated', $nextUpdate);

echo "Updated Database Successfully<br />";

echo "<br />Flare has been Updated to ".$nextUpdate["name"]." (".$nextUpdate["tag"].")";