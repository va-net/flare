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
$next = Updater::nextVersion();

if ($next == null) {
    echo "Already Up-to-Date";
    die();
}

// Get Tag Info
$nextTag = Updater::getTag($next);

// Get the updates.json file
$updateData = @file_get_contents("https://raw.githubusercontent.com/va-net/flare/".urlencode($nextTag["commit"]["sha"])."/updates.json");
// Check Release is Compatible
if ($updateData === FALSE) {
    echo "This Version of Flare does not support the Updater.";
    die();
}
// Process updates.json File
$updateData = Json::decode($updateData);
$nextUpdate = Updater::getUpdate($nextTag, $updateData);

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

// Update Files
foreach ($nextUpdate["files"] as $file) {
    $updRet = Updater::updateFile($file);
    if (!$updRet) {
        echo 'There was an Error Updating the File '.$file;
        die();
    }
}
echo "Updated Files Successfully<br />";

// Delete Files to Delete
foreach ($nextUpdate["deletedFiles"] as $delFile) {
    if (!Updater::deleteFile($delFile)) {
        echo 'There was an Error Deleting the File '.$file;
        die();
    }
}
echo "Deleted Removed Files Successfully<br />";

// Run DB Queries
if (count($nextUpdate["queries"]) != 0) {
    $db = DB::getInstance();
    foreach ($nextUpdate["queries"] as $q) {
        $db->query($q);
        if ($db->error()) {
            echo "Error Running the DB Query {$q}";
            die();
        }
    }
}
echo "Updated Database Successfully<br />";

echo "<br /><b>Flare has been Updated to ".$nextUpdate["name"]." (".$nextUpdate["tag"].")</b><br />";
echo '<a href="home.php">Go to Pilot Home</a>';