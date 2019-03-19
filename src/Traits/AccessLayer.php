<?php

namespace MorningTrain\Laravel\Support\Traits;

trait AccessLayer
{

    public function __call($name, $arguments)
    {
        $method = substr($name, 0, 3);
        $property = lcfirst(substr($name, 3));

        if (strlen($property) > 0 && property_exists($this, $property)) {
            if ($method === 'get' && empty($arguments)) {
                return $this->$property;
            }

            if ($method === 'set' && count($arguments) === 1) {
                $this->$property = $arguments[0];
                return $this;
            }
        }
    }

}