<?php

namespace Karamel\Kolerm\Interfaces;
interface IKolerm
{
    public function where();

    public function orderBy($columnName, $order = 'ASC');

    public function get();

    public function first();

    public function find($number);

    public function delete();

    public function save();

    public function validateOrderType($order);

    public function take($number);

    public function offset($number);
}