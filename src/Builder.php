<?php

namespace Karamel\Kolerm;

use Karamel\DB\Facade\DB;
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
    protected $dateFormat;
    protected $conditions;

    private $model;
    private $orders;
    private $rowTakes;
    private $rowOffset;
    private $columns;
    private $join;

    public function __construct($model, $pk, $d, $df, $tb)
    {
        $this->model = $model;
        $this->primaryKey = $pk;
        $this->dates = $d;
        $this->dateFormat = $df;
        $this->tableName = $tb;
    }

    public function getColumns($complete = false)
    {
        if ($this->columns !== null && isset($this->columns[(int)$complete]))
            return $this->columns[(int)$complete];
        $columns = DB::getInstace()->query('DESCRIBE ' . $this->tableName);
        $data = [];
        while ($field = $columns->fetch_object()) {
            $data[] = $complete ? '`' . $this->tableName . '`.`' . $field->Field . '`' : $field->Field;
        }

        $this->columns[(int)$complete] = $data;
        return $this->columns[(int)$complete];
    }

    private function pAW($arg, $type = 'column')
    {
        if (strpos($arg, "`") > -1 && $type == 'column')
            return $arg;
        elseif (strpos($arg, "`") > -1 && $type == 'value')
            return $arg;
        elseif (strpos($arg, "`") == -1 && $type == 'column')
            return '`' . $arg . '`';
        elseif (strpos($arg, "`") == -1 && $type == 'value')
            return '"' . $arg . '"';
        else
            return '"' . $arg . '"';
    }

    public function where()
    {
        $args = func_get_args();
        $columnName = $args[0];
        $values = ['>', '<', '>=', '<=', '!=', '=', '<>'];
        $this->defineEmptyConditions();

        if (in_array($args[1], $values)) {
            $where = $this->pAW($args[0]) . $args[1] . $this->pAW($args[1], 'value');
        } else {
            $where = $this->pAW($args[0]) . '=' . $this->pAW($args[1], 'value');
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

        $tmp = DB::getInstace()->query($query);
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

    public function join($tableName)
    {
        $this->join = $tableName;
        return $this;
    }

    public function first()
    {
        $this->take(1);
        return $this->get()[0];
    }

    public function find($number)
    {
        $query = 'SELECT ' . implode(",", $this->getColumns()) . ' FROM ' . $this->tableName . ' WHERE ' . $this->primaryKey . '="' . $number . '" LIMIT 1';
        $data = DB::getInstace()->query($query);

        if ($data->num_rows == 0)
            return null;

        $obj = new $this->model();

        $data = $data->fetch_assoc();
        foreach ($data as $key => $value) {
            $obj->{$key} = $value;
        }
        return $obj;
    }

    public function delete()
    {

        if ($this->model->{$this->primaryKey} != null) {
            $query = 'DELETE FROM ' . $this->tableName . ' WHERE `' . $this->primaryKey . '`="' . $this->model->{$this->primaryKey} . '" LIMIT 1';
        } else {
            $query = 'DELETE FROM ' . $this->tableName . ' ';
            $query .= $this->generateConditionQuerySegment();
        }

        DB::getInstace()->query($query);
    }

    public function save()
    {

        $object_vars = get_object_vars($this->model);

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
                if (!in_array($key, $this->getColumns()))
                    continue;
                if ($key == $this->primaryKey)
                    continue;
                $object_sets[] = '`' . $key . '`="' . $value . '"';
            }

            $sql = 'UPDATE ' . $this->tableName . ' SET ' . implode(",", $object_sets) . ' WHERE ' . $this->primaryKey . '="' . $this->{$this->primaryKey} . '"';

            $query = DB::getInstace()->query($sql);

        } else {

            if (!isset($object_vars[$this->dates['onCreate']])) {
                $object_vars[$this->dates['onCreate']] = (new \DateTime())->format($this->dateFormat);
            }
            if (!isset($object_vars[$this->dates['onUpdate']])) {
                $object_vars[$this->dates['onUpdate']] = (new \DateTime())->format($this->dateFormat);
            }

            $object_sets = [];

            foreach ($object_vars as $key => $value) {
                if (!in_array($key, $this->getColumns()))
                    continue;
                $object_sets[] = '`' . $key . '`="' . $value . '"';
            }
            $sql = 'INSERT INTO ' . $this->tableName . ' SET ' . implode(",", $object_sets);
            $query = DB::getInstace()->query($sql);
            $this->model->id = DB::getInstace()->inserted_Id();
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
        $query = 'SELECT ' . implode(",", $this->getColumns(true)) . ' FROM `' . $this->tableName . '`';
        if ($this->join !== null)
            $query .= ',`' . $this->join . '`';

        $query .= $this->generateConditionQuerySegment();

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

    /**
     * @param $query
     * @return string
     */
    private function generateConditionQuerySegment()
    {
        if ($this->conditions !== null && count($this->conditions) > 0)
            return ' WHERE ' . implode(" AND ", $this->conditions);
        return '';
    }
}