<?php

namespace Samu\Stdlib;

use ArrayAccess;

/**
 * Class for recursively iterating arrays that allows iterating into nonexistent
 * keys with read/write support.
 */
class TreeIterator implements ArrayAccess
{
    private $trace = [];
    private $root;
    private $value;
    private $default = null;

    public function __construct($root = null)
    {
        $this->setRoot($root);
    }

    public function setDefault($value)
    {
        $this->default = $value;
    }

    public function setRoot($root)
    {
        $this->root = $root;
        $this->setValue($root);
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Moves internal pointer to requested branch and returns itself
     *
     * @return $this
     */
    public function __get($key)
    {
        if (is_array($this->value)) {
            $next = array_key_exists($key, $this->value) ? $this->value[$key] : null;
        } else {
            $next = null;
        }

        $this->trace[] = $key;
        $this->setValue($next);
        return $this;
    }

    /**
     * Writes a value into the tree and creates missing branches if necessary
     */
    public function __set($key, $value)
    {
        $this->trace[] = $key;
        $this->value = $value;
        $this->commit();
    }

    /**
     * Return root of managed tree
     */
    public function root()
    {
        return $this->root;
    }

    /**
     * Cast current value to requested type. If points to null, will return
     * defined fallback value.
     *
     * NOTE: Follows PHP's standard casting rules.
     *
     * @param $type Variable type to cast to
     * @param $default Value to return in case of null
     *
     * @return mixed | null
     */
    public function value($type, $default = null)
    {
        $value = $this->value;
        $this->reset();

        if (is_null($value)) {
            return func_num_args() > 1 ? $default : $this->default;
        }

        switch ($type) {
            case 'array': return (array) $value;
            case 'int': return (int) $value;
            case 'string': return (string) $value;
            case 'bool': return (bool) $value;
            case null: return $value;
            default:
                $msg = sprintf('Trying to cast to invalid type "%s"', $type);
                trigger_error($msg, E_USER_WARNING);
                return $value;
        }
    }

    public function to($type, $default = null)
    {
        return $this->value($type, $default);
    }

    /**
     * Write a value into current branch.
     *
     * This function will insert a key into current branch. Position of internal
     * pointer is not altered. This allows multiple calls to be made into the
     * same branch.
     *
     * @param $key Key to be set
     * @param $value Value to write into $key
     * @return TreeIterator
     */
    public function set($key, $value)
    {
        $trace = $this->trace;
        $this->$key = $value;
        $this->trace = $trace;
        return $this;
    }

    /**
     * Return key from current branch without resetting internal pointer's position.
     *
     * @param $key Key to lookup
     * @param $type Type for return value
     * @param $default Fallback value in case of null
     * @return mixed | null
     */
    public function get($key, $type = null, $default = null)
    {
        $trace = $this->trace;
        $value = $this->$key->to($type, $default);
        $this->reset();
        return $value;
    }

    /**
     * Reset internal pointer to the root
     */
    public function reset()
    {
        $this->trace = [];
        $this->setValue($this->root);
    }

    /**
     * Write changes into the managed tree
     */
    protected function commit($reset = true)
    {
        $dir = & $this->root;
        $key = array_pop($this->trace);
        foreach ($this->trace as $foo) {
            if (!isset($dir[$foo]) || !is_array($dir[$foo])) {
                $dir[$foo] = [];
            }
            $dir = & $dir[$foo];
        }
        $dir[$key] = $this->value;

        if ($reset) {
            $this->reset();
        }
    }

    public function offsetExists($key)
    {
        if (!is_array($this->value)) {
            return false;
        }

        return array_key_exists($key, $this->value);
    }

    public function offsetGet($key)
    {
        return $this->$key;
    }

    public function offsetSet($key, $value)
    {
        $this->$key = $value;
    }

    public function offsetUnset($key)
    {
        if (is_array($this->value)) {
            unset($this->value[$key]);
        }
    }
}
