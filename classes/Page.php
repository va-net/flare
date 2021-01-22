<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Page
{

    private static $_title;
    private static $_assets = [
        'bootstrap' => [
            '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">',
            '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>',
            '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>',
            '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>',
        ],
        'custom-css' => ['<link rel="stylesheet" type="text/css" href="/assets/custom.css">'],
        'flare-css' => ['<link rel="stylesheet" type="text/css" href="/assets/style.css.php">'],
        'font-awesome' => ['<script src="https://kit.fontawesome.com/a076d05399.js"></script>'],
        'chartjs' => ['<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>'],
        'momentjs' => ['<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>'],
        'datatables' => [
            '<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>',
            '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.css"/>',
        ],
    ];
    private static $_usedAssets = [];
    private static $_assetsMode = 0;

    public static $badges = [
        "codeshares" => "VANet::getCodeshareCount",
        "recruitment" => "User::pendingCount",
        "pireps" => "Pirep::pendingCount",
        "settings" => "Updater::updateAvailable",
    ];

    /**
     * @return void
     * @param string $title Title
     */
    public static function setTitle($title)
    {
        self::$_title = $title;
        if (self::$_usedAssets == [] && self::$_assetsMode != 1) {
            self::$_usedAssets = self::$_assets;
        }
    }

    /**
     * @return string
     */
    public static function getTitle()
    {
        return self::$_title;
        if (self::$_usedAssets == [] && self::$_assetsMode != 1) {
            self::$_usedAssets = self::$_assets;
        }
    }

    /**
     * @return void
     * @param int $mode 0 = Exclude to Remove, 1 = Include to Use
     */
    public static function assetsMode($mode)
    {
        if ($mode == 0) {
            self::$_usedAssets = self::$_assets;
        } elseif ($mode == 1) {
            self::$_usedAssets = [];
        } else {
            throw new Exception("Invalid Assets Mode");
        }

        self::$_assetsMode = $mode;
    }

    /**
     * @return void
     * @param string $asset Key of Asset to Include
     */
    public static function includeAsset($asset)
    {
        if (self::$_assetsMode != 1) return;

        if (!array_key_exists($asset, self::$_assets)) {
            throw new Exception("Asset '{$asset}' does not exist");
        }

        array_push(self::$_usedAssets, self::$_assets[$asset]);
    }

    /**
     * @return void
     * @param string $asset Key of Asset to Exclude
     */
    public static function excludeAsset($asset)
    {
        if (self::$_assetsMode != 0) return;

        if (!array_key_exists($asset, self::$_assets)) {
            throw new Exception("Asset '{$asset}' does not exist");
        }

        if (!array_key_exists($asset, self::$_usedAssets)) return;

        unset(self::$_usedAssets[$asset]);
    }

    /**
     * @return void
     * @param string $key Asset Key
     * @param array $tags Asset HTML Tags
     */
    public static function registerAsset($key, $tags)
    {
        self::$_assets[$key] = $tags;
    }

    /**
     * @return array
     */
    public static function assets()
    {
        return self::$_assets;
    }

    /**
     * @return bool
     * @param string $asset Asset Key or HTML Tag
     */
    public static function assetInUse($asset)
    {
        return array_key_exists($asset, self::$_usedAssets) || in_array($asset, array_values(self::$_usedAssets));
    }

    /**
     * @return void
     * @param array $value New Value
     */
    public static function setBadges($value)
    {
        self::$badges = $value;
    }
}
