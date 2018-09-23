<?php

namespace Karamel\Kolerm;

use Karamel\Kolerm\Interfaces\IKolerm;
use Karamel\Kolerm\Traits\KolermDefineTrait;
use Karamel\Kolerm\Traits\KolermLimitTrait;

class Builder implements IKolerm
{

    use KolermLimitTrait;
    use KolermDefineTrait;


    protected $tableName;
    protected $primaryKey;
    protected $dates;
    protected $connection;
    protected $dateFormat;
    protected $conditions;

    private $model;
    private $orders;
    private $rowTakes;
    private $rowOffset;
    private $columns;

    public function __construct($connection, $model, $pk, $d, $df,$tb)
    {
        $this->model = $model;
        $this->connection = $connection;
        $this->primaryKey = $pk;
        $this->dates = $d;
        $this->dateFormat = $df;
        $this->tableName = $tb;
    }

    public function getColumns()
    {
        if ($this->columns !== null)
            return $this->columns;
        $columns = $this->connection->query('DESCRIBE ' . $this->tableName);
        $data = [];
        while ($field = $columns->fetch_object()) {
            $data[] = $field->Field;
        }
        $this->columns = $data;
        return $this->columns;
    }

    public function where()
    {
        $args = func_get_args();
        $columnName = $args[0];
        $values = ['>', '<', '>=', '<=', '!=', '=', '<>'];
        $this->defineEmptyConditions();

        if (in_array($args[1], $values)) {
            $where = '`' . $args[0] . '`' . $args[1] . '"' . $args[2] . '"';
        } else {
            $where = '`' . $args[0] . '`="' . $args[1] . '"';
        }
        $this->conditions[] = $where;
        return $this;
    }

    public function orderBy($columnName, $order = 'ASC')
    {
        $this->defineEmptyOrders();
        $this->validateOrderType($order);
        $this->orders[] = '`' . $columnName . '` ' . $order;
        return $this;
    }

    public function get()
    {
        //generate query
        $query = $this->generateQuery();

        $tmp = $this->connection->query($query);
        $result = [];
        $columns = $this->getColumns();
        while ($obj = $tmp->fetch_assoc()) {

            $object = new $this->model();

            foreach ($columns as $column) {
                $object->{$column} = $obj[$column];
            }

            $result[] = $object;
        }

        return $result;

    }

    public function first()
    {
        $this->take(1);
        return $this->get()[0];
    }

    public function find($number)
    {

        $data = $this->connection->query('SELECT ' . implode(",", $this->getColumns()) . ' FROM ' . $this->tableName . ' WHERE ' . $this->primaryKey . '="' . $number . '" LIMIT 1');
        if ($data->num_rows == 0)
            return null;
        $obj = new User();
        $data = $data->fetch_assoc();
        foreach ($data as $key => $value) {
            $obj->{$key} = $value;
        }
        return $obj;
    }

    public function delete($targetId = null)
    {
        if ($targetId !== null && $this->{$this->primaryKey} === null) {
            $this->connection->query('DELETE FROM ' . $this->tableName . ' WHERE `' . $this->primaryKey . '`="' . $targetId . '" LIMIT 1');
        } else {
            $this->connection->query('DELETE FROM ' . $this->tableName . ' WHERE `' . $this->primaryKey . '`="' . $this->{$this->primaryKey} . '" LIMIT 1');
        }
    }

    public function save()
    {
        $object_vars = get_object_vars($this);
        $is_this_exists_row = false;
        foreach ($object_vars as $key => $object_var) {
            if ($key == $this->primaryKey)
                $is_this_exists_row = true;
        }

        if ($is_this_exists_row == true) {
            if (!isset($object_vars[$this->dates['onUpdate']])) {
                $object_vars[$this->dates['onUpdate']] = (new DateTime())->format($this->dateFormat);
            }

            foreach ($object_vars as $key => $value) {
                if (!in_array($key, $this->columns))
                    continue;
                if ($key == $this->primaryKey)
                    continue;
                $object_sets[] = '`' . $key . '`="' . $value . '"';
            }

            $sql = 'UPDATE ' . $this->tableName . ' SET ' . implode(",", $object_sets) . ' WHERE ' . $this->primaryKey . '="' . $this->{$this->primaryKey} . '"';

            $query = $this->connection->query($sql);

        } else {

            if (!isset($object_vars[$this->dates['onCreate']])) {
                $object_vars[$this->dates['onCreate']] = (new DateTime())->format($this->dateFormat);
            }
            if (!isset($object_vars[$this->dates['onUpdate']])) {
                $object_vars[$this->dates['onUpdate']] = (new DateTime())->format($this->dateFormat);
            }

            $object_sets = [];

            foreach ($object_vars as $key => $value) {
                if (!in_array($key, $this->columns))
                    continue;
                $object_sets[] = '`' . $key . '`="' . $value . '"';
            }
            $sql = 'INSERT INTO ' . $this->tableName . ' SET ' . implode(",", $object_sets);

            $query = $this->connection->query($sql);
        }
    }

    public function validateOrderType($order)
    {
        if (!in_array($order, ['ASC', 'DESC']))
            throw new KolequentOrderByInvalidTypeException();
    }

    public function rand()
    {
        return $this->rand = ' ORDER BY RAND() ';
    }

    public function generateQuery()
    {
        $query = 'SELECT ' . implode(",", $this->getColumns()) . ' FROM ' . $this->tableName;
        if ($this->conditions !== null && count($this->conditions) > 0)
            $query .= ' WHERE ' . implode(" AND ", $this->conditions);

        if ($this->orders !== null && count($this->orders) > 0)
            $query .= ' order by ' . implode(",", $this->orders);

        // limit 200,350


        if ($this->rowOffset !== null) {
            $query .= ' LIMIT ' . $this->rowOffset . ' , ' . $this->rowTakes;
        } else {
            if ($this->rowTakes !== null && (int)$this->rowTakes > 0)
                $query .= ' LIMIT ' . $this->rowTakes;
        }

        return $query;
    }
}