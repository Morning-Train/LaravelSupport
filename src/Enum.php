<?php

namespace MorningTrain\Laravel\Support;

use Illuminate\Support\Str;

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

    public static function getValue(string $key, $default = null)
    {
        $key = "static::{$key}";

        return defined($key) ?
            constant($key) :
            $default;
    }

    public static function keys()
    {
        return array_keys(static::all());
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

    public static function options()
    {
        $keys = static::$by_keys ?
            static::keys() :
            static::values();

        return array_reduce($keys, function ($acc, $value) {
            $acc[$value] = static::translate($value);
            return $acc;
        }, []);
    }

    ////////////////////////////////
    /// Translations
    ////////////////////////////////

    static $namespace = 'enums';

    public static function basename()
    {
        $className = class_basename(static::class);

        return strtolower(Str::snake($className));
    }

    public static function namespace()
    {
        return static::$namespace . '.' . static::basename();
    }

    public static function translate($value)
    {
        $key     = static::namespace() . '.' . $value;
        $default = ucfirst(Str::studly($value));

        return ($trans = trans($key)) === $key ? $default : $trans;
    }

    ////////////////////////////////
    /// Exporting
    ////////////////////////////////

    static $export	= false;
    static $raw		= false;
    static $by_keys	= false;

    public static function export()
    {
        if (static::$export) {
            return static::$raw ?
                static::all() :
                static::options();
        }
    }

    /**
     * @deprecated
     */
    public static function label($value) {
        return static::translate($value);
    }

}
