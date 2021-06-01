<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Plugin
{
    /**
     * @return void
     * @param string $label Menu Item Label
     * @param array $data Meny Item Data
     */
    public static function pilotMenu($label, $data)
    {
        if (!isset($data["needsGold"])) $data["needsGold"] = false;
        if (!isset($data["badgeid"])) $data["badgeid"] = null;
        $GLOBALS['pilot-menu'][$label] = $data;
    }

    /**
     * @return void
     * @param string $label Menu Item Label
     * @param array $data Meny Item Data
     */
    public static function adminMenu($label, $data, $category = 'Plugins')
    {
        if (!isset($data["needsGold"])) $data["needsGold"] = false;
        if (!isset($data["badgeid"])) $data["badgeid"] = null;
        $GLOBALS['admin-menu'][$category][$label] = $data;
    }

    /**
     * @return void
     * @param string $label Menu Item Label
     * @param array $data Menu Item Data
     */
    public static function topMenu($label, $data)
    {
        $GLOBALS['top-menu'][$label] = $data;
    }

    /**
     * @return void
     * @param string $id Badge ID
     * @param callback $action Badge Action
     */
    public static function registerBadge($id, $action)
    {
        $b = Page::$badges;
        $b[$id] = $action;
        Page::$badges = $b;
    }
}
