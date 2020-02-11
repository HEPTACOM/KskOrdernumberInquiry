<?php declare(strict_types=1);

namespace KskOrdernumberInquiry\Services;

use Closure;
use Exception;

class AccessEnforcer
{
    /**
     * Reads a private attribute of the given
     * instance. Cannot read private attributes
     * of any parent classes.
     *
     * @param mixed $instance
     * @param mixed $name
     *
     * @throws Exception
     */
    public function forceRead($instance, $name)
    {
        if (!is_object($instance)) {
            throw new Exception('\$instance must be an object.');
        }

        $spoof = Closure::bind(function ($instance) use ($name) {
            return $instance->{$name};
        }, null, $instance);

        return $spoof($instance);
    }

    /**
     * Writes a value to a specific private
     * attribute of the given instance.
     * Cannot override private attributes of
     * any parent classes.
     *
     * @param mixed $instance
     * @param mixed $name
     * @param mixed $value
     *
     * @throws Exception
     */
    public function forceWrite($instance, $name, $value)
    {
        if (!is_object($instance)) {
            throw new Exception('\$instance must be an object.');
        }

        $spoof = Closure::bind(function ($instance) use ($name, $value) {
            $instance->{$name} = $value;
        }, null, $instance);
        $spoof($instance);
    }

    /**
     * Calls a private method of the given
     * instance with the given parameters
     * and returns its return value.
     *
     * @param mixed $instance
     * @param mixed $name
     * @param array ...$arguments
     *
     * @throws Exception
     */
    public function forceCallMethod($instance, $name, ...$arguments)
    {
        if (!is_object($instance)) {
            throw new Exception('$instance must be an object.');
        }

        $spoof = Closure::bind(function ($instance) use ($name, $arguments) {
            return $instance->$name(...$arguments);
        }, null, $instance);

        return $spoof($instance);
    }
}
