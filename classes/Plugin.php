<?php

class Plugin {
    /**
     * @return null
     * @param string $label Menu Item Label
     * @param array $data Meny Item Data
     */
    public static function pilotMenu($label, $data) {
        $GLOBALS['pilot-menu'][$label] = $data;
    }

    /**
     * @return null
     * @param string $label Menu Item Label
     * @param array $data Meny Item Data
     */
    public static function adminMenu($label, $data) {
        $GLOBALS['admin-menu']['Plugins'][$label] = $data;
    }

    /**
     * @return null
     * @param string $label Menu Item Label
     * @param array $data Menu Item Data
     */
    public static function topMenu($label, $data)
    {
        $GLOBALS['top-menu'][$label] = $data;
    }
}