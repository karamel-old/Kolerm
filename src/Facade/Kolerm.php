<?php

namespace Karamel\Kolerm\Facade;
class Kolerm
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance !== null)
            return self::$instance;

        $classname = get_called_class();
        self::$instance = new $classname();
        return self::$instance;
    }
}