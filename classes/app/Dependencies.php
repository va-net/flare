<?php

class Dependencies
{
    /**
     * @var array
     */
    private static $_classes = [];

    public static function register($className, $instance)
    {
        self::$_classes[$className] = $instance;
    }

    public static function &get($className)
    {
        if (isset(self::$_classes[$className])) {
            return self::$_classes[$className];
        }

        $inst = new $className();
        self::register($className, $inst);
        return $inst;
    }
}
