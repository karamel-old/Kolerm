<?php

namespace Karamel\DB\Drivers;

use Karamel\DB\Exceptions\ConnectionRefusedException;
use Karamel\DB\Exceptions\QueryErrorException;
use Karamel\DB\Interfaces\IDB;
use mysqli;

class MySQL implements IDB
{

    private $result;

    private $connection;

    public function __construct()
    {

    }

    public function connection()
    {
        if ($this->connection !== null)
            return $this->connection;


        $connection = new mysqli( KM_DB_HOST,
            KM_DB_USER,
            KM_DB_PASS,
            KM_DB_NAME,
            KM_DB_PORT,
            KM_DB_PREFIX);
        if ($connection->connect_errno !== 0)
            throw new ConnectionRefusedException($connection->connect_error, $connection->connect_errno);


        $connection->set_charset(KM_DB_COLLATION);
        $this->connection = $connection;
        return $this->connection;
    }

    public function query($query)
    {

        $this->result = $this->connection()->query($query);
        if($this->result == false)
            throw new QueryErrorException("Query : ".$query ." \nError:".$this->connection()->error);
        return $this->result;
    }

    public function fetch_object()
    {
        return $this->result->fetch_object();
    }

    public function fetch_assoc()
    {
        return $this->result->fetch_assoc();
    }

    public function num_rows()
    {
        return $this->result->num_rows;
    }

    public function inserted_Id()
    {
        return $this->connection()->insert_id;
    }
}
