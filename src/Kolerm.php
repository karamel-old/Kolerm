<?php

namespace Karamel\Kolerm;

class Kolerm
{


    protected $tableName;
    protected $primaryKey;
    protected $dates;
    protected $connection;
    protected $dateFormat;
    protected $query;
    private $switches;
    protected static $instance;

    public function __construct()
    {
        $this->tableName = sanetizeString(get_called_class());
        $this->primaryKey = 'id';
        $this->dates = [
            'onCreate' => 'created_at',
            'onUpdate' => 'updated_at'
        ];
        $this->connection = \Karamel\DB\Facade\DB::getInstace();
        $this->dateFormat = 'Y-m-d H:i:s';
        $this->switches = [];
    }

    public function newQuery()
    {
        if ($this->query !== null) {
            return $this->query;
        } else {
            $this->query = new Builder(
                $this->connection,
                get_called_class(),
                $this->primaryKey,
                $this->dates,
                $this->dateFormat
            );
            return $this->query;
        }
    }


    public static function __callStatic($name, $arguments)
    {
        return (new static)->newQuery()->$name(...$arguments);

    }

    public function __call($name, $arguments)
    {
        return $this->newQuery()->$name(...$arguments);
    }

}