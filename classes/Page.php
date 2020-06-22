<?php

class Page
{

    private static $_title;
    private static $_active;

    public static function setTitle($title)
    {

        self::$_title = $title;

    }

    public static function getTitle()
    {

        return self::$_title;

    }

    public static function setSidebarActive($active)
    {

        self::$_active = $active;

    }

    public static function getSidebarActive($name)
    {

        if (self::$_active == $name) {
            return 'active';
        }
        return '';
        
    }

}