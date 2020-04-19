<?php


namespace Lobster;


/**
 * Class Type
 * @package Lobster
 */
final class Type
{
    public const array = 'array';
    public const object = 'object';
    public const int = 'int';
    public const bool = 'bool';
    public const string = 'string';
    public const resource = 'resource';
    public const callable = 'callable';
    public const float = 'float';
    public const null = 'null';

    private function __construct()
    {
    }

    /**
     * @param $var
     * @return string
     */
    public static function gettype($var, bool $objectAsClass = false) : string
    {

        if (is_array($var))
        {
            return self::array;
        }

        if (is_bool($var))
        {
            return self::bool;
        }

        if (is_int($var))
        {
            return self::int;
        }

        if (is_string($var))
        {
            return self::string;
        }

        if (is_null($var))
        {
            return self::null;
        }

        if (is_resource($var))
        {
            return self::resource;
        }

        if (is_callable($var))
        {
            return self::callable;
        }

        if (is_float($var))
        {
            return self::float;
        }

        $type = self::object;

        if ($objectAsClass)
        {
            return get_class($var);
        }

        return $type;
    }

    /**
     * @param $var
     * @return bool
     */
    public static function isClass($var, string $concrete = null) : bool
    {
        if (!(is_string($var) && class_exists($var)))
        {
            return false;
        }

        if ($concrete != null)
        {
            return strcasecmp($var, $concrete) != 0;
        }

        return true;
    }

    /**
     * @param $var
     * @param string|null $concrete
     * @return bool
     */
    public static function isInterface($var, string $concrete = null) : bool
    {
        if (!(is_string($var) && interface_exists($var)))
        {
            return false;
        }

        if ($concrete != null)
        {
            return strcasecmp($var, $concrete) != 0;
        }

        return true;
    }

    /**
     * @param $var
     * @param string $type
     * @return bool
     */
    public static function match($var, string $type): bool
    {
        $actual = self::gettype($var);

        if ($actual === self::object &&
            (self::isInterface($type) || self::isClass($type))
        )
        {
            return $var instanceof $type;
        }

        return $actual === $type;
    }

    /**
     * @param $var
     * @param string[] $types
     * @return bool
     */
    public static function matchMany($var, array $types) : bool
    {
        foreach ($types as $type)
        {
            if (self::match($var, (string) $type))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $argument
     * @param array $types
     * @param string $name
     * @param bool $nameAsMsg
     * @throws \InvalidArgumentException
     */
    public static function enforce($argument, array $types, string $name, bool $nameAsMsg = false) : void
    {
        if (self::matchMany($argument, $types))
        {
            return;
        }

        if (!$nameAsMsg)
        {
            $msg = 'Argument [' . $name . '] passed to ';

            $trace = end(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS	));

            if (array_key_exists('class', $trace))
            {
                $msg .= $trace['class'] . '::';
            }

            $msg .= $trace['function'];
            $msg .= ' must be';

            if (count($types) > 1)
            {
                $msg .= ' one of the types [';
                $glue = '';

                foreach ($types as $type)
                {
                    $msg .= $glue . $type;
                    $glue = '|';
                }

                $msg .= '], ';
            }

            else
            {
                $msg .= ' of the ' . $types[0] . ' type, ';
            }

            $msg .= Type::gettype($argument, true);
            $msg .= ' given.';
        }

        throw new \InvalidArgumentException($msg ?? $name);
    }
}
