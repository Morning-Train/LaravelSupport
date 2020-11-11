<?php

namespace MorningTrain\Laravel\Support\Traits;

use \ReflectionClass;

trait StaticCreate
{

    /**
     * @param array ...$arguments
     * @return static
     */
    public static function create()
    {
        return (new ReflectionClass(static::class))->newInstanceArgs(func_get_args());
    }

}
