<?php

namespace MorningTrain\Laravel\Support;

abstract class Enum
{
    public static function all()
    {
        $reflection = new \ReflectionClass(static::class);
        return $reflection->getConstants();
    }

    public static function random()
    {
        return static::all()[array_rand(static::all(), 1)];
    }

    public static function values()
    {
        return array_values(static::all());
    }

    public static function validate($value)
    {
        return in_array($value, static::all());
    }

    public static function default()
    {
        if (isset(static::$default)) {
            return static::$default;
        }

        $all = static::all();
        return array_shift($all);
    }
}