<?php

namespace Karamel\DB\Drivers;

use Karamel\DB\Exceptions\ConnectionRefusedException;
use Karamel\DB\Interfaces\IDB;
use mysqli;

class MySQL implements IDB
{
    private $host;
    private $user;
    private $name;
    private $pass;
    private $port;
    private $prefix;
    private $result;
    private $collation;
    private $connection;

    public function __construct($host, $user, $pass, $name, $port = 3306, $prefix = '', $collation = 'uft8mb4')
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->name = $name;
        $this->port = $port;
        $this->prefix = $prefix;
        $this->collation = $collation;
    }

    public function connection()
    {
        if ($this->connection !== null)
            return $this->connection;

        $connection = new mysqli($this->host, $this->user, $this->pass, $this->name, $this->port);
        if ($connection->connect_errno !== 0)
            throw new ConnectionRefusedException($connection->connect_error, $connection->connect_errno);


        $connection->set_charset($this->collation);
        $this->connection = $connection;
        return $this->connection;
    }

    public function query($query)
    {

        $this->result = $this->connection()->query($query);
        return $this;
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
