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

class ArrayMidl extends Iterator
{

    /**
     * By default it sorts values naturally, case-insensitive and maintains index association
     * in ascending order, except if array has sequential integer keys starting at 0
     * then it doesn't maintain index association, e.g.
     * ["b", "c", "a"] will be sorted as ["a", "b", "c"] instead of [2 => "a", 0 => "b", 1 => "c"]
     *
     * @param bool $byValues [optional] Whether sort array by key or value, defaults to true
     * @param int $flags [optional] Sorting flags to change sorting behavior,
     *        defaults to SORT_NATURAL|SORT_FLAG_CASE|SORT_ASC,
     *        to reverse ordering you can use SORT_DESC,
     *        if you don't want natural sorting you can set the flags to SORT_REGULAR
     * @param callable $compFunc [optional] Comparison function to sort array
     * @return bool
     * @see sort()
     */
    public function sort($byValues = true, $flags = null, $compFunc = null)
    {
        if (is_callable($compFunc)) {
            if ($byValues)
                return uasort($this->array, $compFunc);
            
            return uksort($this->array, $compFunc);
        } else {
            if ($flags === null)
                $flags = SORT_NATURAL | SORT_FLAG_CASE | SORT_ASC;
            
            if ($byValues) {
                if (array_values($this->array) === $this->array)
                    return sort($this->array, $flags);
                else
                    return asort($this->array, $flags);
            }
            
            return ksort($this->array, $flags);
        }
    }

    /**
     *
     * @param mixed $object
     * @param bool $strict [optional] Defaults to false
     * @return bool
     */
    public function has($object, $strict = false)
    {
        return in_array($object, $this->array, $strict);
    }

    /**
     *
     * @param mixed $value
     * @return int New array count
     */
    public function push($value)
    {
        $this->array[] = $value;
        
        return $this->count();
    }

    /**
     *
     * @return mixed Last element in the array or null if there is no element in the array
     */
    public function pop()
    {
        return array_pop($this->array);
    }

    /**
     *
     * @param mixed $value
     * @param bool $strict [optional] Defaults to false
     */
    public function remove($value, $strict = false)
    {
        if ($this->array) {
            $array = $this->array;
            
            foreach ($this->array as $key => $val)
                if ((!$strict && $value == $val) || ($strict && $value === $val))
                    unset($array[$key]);
            
            $this->array = $array;
        }
    }

    /**
     *
     * @return array
     */
    public function all()
    {
        return $this->array;
    }

    /**
     *
     * @return void
     */
    public function clear()
    {
        $this->array = [];
    }
}
