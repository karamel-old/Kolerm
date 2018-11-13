<?php
namespace Karamel\DB;
use Karamel\DB\Drivers\MySQL;

class DBFactory{
    public static function build()
    {

        if(KM_DB_TYPE == 'mysql')
            return new MySQL();
    }
}