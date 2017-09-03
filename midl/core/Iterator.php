<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\core;

class Iterator implements Iterable
{

    /**
     *
     * @var array
     */
    protected $array;

    /**
     */
    public function __construct(array $array = null)
    {
        if ($array !== null)
            $this->array = $array;
        else
            $this->array = [];
    }

    /**
     *
     * @see \Iterator::rewind()
     */
    public function rewind()
    {
        return reset($this->array);
    }

    /**
     *
     * @see \Iterator::current()
     */
    public function current()
    {
        return current($this->array);
    }

    /**
     *
     * @see \Iterator::key()
     */
    public function key()
    {
        return key($this->array);
    }

    /**
     *
     * @see \Iterator::next()
     */
    public function next()
    {
        return next($this->array);
    }

    /**
     *
     * @see \Iterator::valid()
     */
    public function valid()
    {
        return key($this->array) !== null;
    }

    /**
     *
     * @see Countable::count()
     */
    public function count()
    {
        return count($this->array);
    }

    /**
     *
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null)
            $this->array[] = $value;
        else
            $this->array[$offset] = $value;
    }

    /**
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }

    /**
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    /**
     *
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }
}