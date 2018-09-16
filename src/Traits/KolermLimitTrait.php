<?php
namespace Karamel\Kolerm\Traits;
trait KolermLimitTrait{
    public function take($number)
    {
        $this->rowTakes = $number;
        return $this;
    }

    public function offset($number)
    {
        $this->rowOffset = $number;
        return $this;
    }
}