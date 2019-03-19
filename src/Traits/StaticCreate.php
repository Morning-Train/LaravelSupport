<?php

namespace MorningTrain\Laravel\Support\Traits;

use \ReflectionClass;

trait StaticCreate
{

    /**
     * @param array ...$arguments
     * @return static
     */
    public static function create(...$arguments)
    {
        return (new ReflectionClass(static::class))->newInstanceArgs($arguments);
    }

}