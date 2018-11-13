<?php

namespace Karamel\DB\Facade;

use Karamel\DB\DBFactory;

class DB
{
    private static $instance;

    public static function getInstace()
    {
        if (self::$instance !== null)
            return self::$instance;

        self::$instance = DBFactory::build();
        return self::$instance;
    }

    public static function query($query)
    {
        return self::getInstace()->query($query);
    }

}