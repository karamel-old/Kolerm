<?php

namespace Karamel\DB\Facade;

use Karamel\DB\DBFactory;

class DB
{
    private static $type;
    private static $host;
    private static $user;
    private static $name;
    private static $pass;
    private static $port;
    private static $prefix;
    private static $collation;
    private static $instance;


    public static function setConfig($type, $host, $user, $name, $pass, $port, $prefix, $collation)
    {
        self::$type = $type;
        self::$host = $host;
        self::$user = $user;
        self::$name = $name;
        self::$pass = $pass;
        self::$port = $port;
        self::$prefix = $prefix;
        self::$collation = $collation;
    }

    public static function getInstace()
    {
        if (self::$instance !== null)
            return self::$instance;

        self::$instance = DBFactory::build(
            self::$type,
            self::$host,
            self::$user,
            self::$name,
            self::$pass,
            self::$port,
            self::$prefix,
            self::$collation
        );
        return self::$instance;
    }

    public static function query($query)
    {
        return self::getInstace()->query($query);
    }

}