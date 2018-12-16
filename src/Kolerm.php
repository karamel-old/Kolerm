<?php

namespace Karamel\Kolerm;

class Kolerm
{


    protected $tableName;
    protected $primaryKey;
    protected $dates;
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

        $this->dateFormat = 'Y-m-d H:i:s';
        $this->switches = [];
    }

    public function newQuery()
    {
        if ($this->query !== null) {
            return $this->query;
        } else {
            $this->query = new Builder(
                $this,
                $this->primaryKey,
                $this->dates,
                $this->dateFormat,
                $this->tableName
            );
            return $this->query;
        }
    }

    public function hasMany($classname, $foreignKey, $sourceKey = 'id')
    {
        $class = new $classname();
        $builder = new Builder($class, $class->primaryKey, $class->dates, $class->dateFormat, $class->tableName);
        return $builder->where($foreignKey, $this->{$sourceKey});
    }

    public function belongsTo($classname, $foreignKey, $sourceKey = 'id')
    {
        $class = new $classname();
        $builder = new Builder($class, $class->primaryKey, $class->dates, $class->dateFormat, $class->tableName);
        return $builder->where($sourceKey, $this->{$foreignKey})->first();
    }

    public function belongsToMany($classname, $pivot_table, $sourceKey, $foreignKey)
    {

        $class = new $classname;
        $builder = new Builder($class, $class->primaryKey, $class->dates, $class->dateFormat, $class->tableName);

        return $builder->join($pivot_table)
            ->where('`' . $pivot_table . '`.`' . $sourceKey . '`', $this->{$this->primaryKey})
            ->where('`' . $pivot_table . '`.`' . $foreignKey . '`', '`' . $class->tableName . '`.`' . $class->primaryKey . '`');


    }

    public static function __callStatic($name, $arguments)
    {
        return (new static)->newQuery()->$name(...$arguments);

    }

    public function __call($name, $arguments)
    {
        return $this->newQuery()->$name(...$arguments);
    }

    public function __sleep()
    {
        return $this->newQuery()->getColumns();
    }

    public function __get($name)
    {
        if (method_exists($this, $name))
            return $this->$name();
        throw new \Exception("Attribute not found");

    }
}