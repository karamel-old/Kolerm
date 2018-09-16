<?php
namespace Karamel\DB;
use Karamel\DB\Drivers\MySQL;

class DBFactory{
    public static function build()
    {
        $args = func_get_args();

        if($args[0] == 'mysql')
            return new MySQL(
                $args[1],
                $args[2],
                $args[3],
                $args[4],
                (isset($args[5]) ? $args[5] : 3306 ) ,
                (isset($args[6]) ? $args[6] : '' ),
                (isset($args[7]) ? $args[7] : 'utf8mb4' ) );
    }
}