<?php

namespace Karamel\Kolerm\Traits;

trait KolermDefineTrait
{
    public function defineEmptyConditions()
    {
        if (!is_array($this->conditions))
            $this->conditions = [];
    }

    public function defineEmptyOrders()
    {
        if (!is_array($this->orders))
            $this->orders = [];
    }
}