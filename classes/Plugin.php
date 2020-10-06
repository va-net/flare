<?php

class Plugin {
    /**
     * @return null
     * @param string $label Menu Item Label
     * @param array $data Meny Item Data
     */
    public static function pilotMenu($label, $data) {
        $data["needsGold"] = false;
        $GLOBALS['pilot-menu'][$label] = $data;
    }

    /**
     * @return null
     * @param string $label Menu Item Label
     * @param array $data Meny Item Data
     */
    public static function adminMenu($label, $data) {
        $data["needsGold"] = false;
        $GLOBALS['admin-menu']['Plugins'][$label] = $data;
    }
}