<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\net\http;

use midl\core\ArrayMidl;
use midl\core\Iterator;

/**
 * HeaderFields extends ArrayMidl so values can be added/changed as:
 * $headerFields["Content-Type"] = "text/html";
 * And can be removed as:
 * unset($headerFields["Content-Type"]);
 *
 * [
 * "field-name" => "field-value",
 * "field-name-with-multiple-values" => ["field-value1", "field-value2", ...],
 * ...]
 */
class HeaderFields extends ArrayMidl
{

    /**
     *
     * @param array $array [optional]
     */
    public function __construct(array $array = null)
    {
        parent::__construct();
        
        if ($array)
            $this->add($array);
    }

    /**
     *
     * @param string|array $name Possible values:
     *        "field-name",
     *        "field-name: field-value",
     *        ["field-name" => "field-value", ...],
     *        ["field-name: field-value", ...],
     *        [["field-name", "field-value"], ...],
     *        [["field-name" => "field-value"], ...],
     * @param string $value [optional] Header field value, defaults to null
     * @param bool $replace [optional] Whether replace previous same header field or add second one,
     *        defaults to true
     * @return void
     */
    public function add($name, $value = null, $replace = true)
    {
        if (is_string($name)) {
            if (is_scalar($value))
                // "field-name", "field-value"
                $this->addHeaderField($name, (string)$value, $replace);
            else {
                // "field-name: field-value", null
                $name = explode(":", $name, 2);
                
                if (isset($name[1]))
                    $this->addHeaderField($name[0], $name[1], $replace);
            }
        } elseif (is_array($name)) {
            foreach ($name as $key => $val) {
                
                if (!$val)
                    continue;
                
                if (is_string($key)) {
                    // ["field-name" => "field-value", ...], null
                    if (is_scalar($val))
                        $this->addHeaderField($key, (string)$val, $replace);
                } elseif (is_string($val)) {
                    // ["field-name: field-value", ...], null
                    $val = explode(":", $val, 2);
                    
                    if (isset($val[1]))
                        $this->addHeaderField($val[0], $val[1], $replace);
                } elseif (is_array($val)) {
                    // [["field-name", "field-value"], ...], null
                    if (@is_string($val[0]) && @is_scalar($val[1]))
                        $this->addHeaderField($val[0], (string)$val[1], $replace);
                    else {
                        // [["field-name" => "field-value"], ...], null
                        $fieldValue = reset($val);
                        $fieldName = key($val);
                        
                        if (is_string($fieldName) && is_scalar($fieldValue))
                            $this->addHeaderField($fieldName, (string)$fieldValue, $replace);
                    }
                }
            }
        }
    }

    /**
     *
     * @param string $name
     * @param string $value [optional]
     * @return void
     */
    public function remove($name, $value = null)
    {
        if (!isset($this->array[$name]))
            return;
        
        if (!$value || is_string($this->array[$name]))
            unset($this->array[$name]);
        
        else {
            $values = $this->array[$name];
            
            foreach ($values as $index => $val)
                if ($value == $val)
                    unset($this->array[$name][$index]);
        }
    }

    /**
     *
     * @param string $header
     * @return bool
     * @see \midl\core\Iterator::offsetExists()
     */
    public function has($header, $strict = false)
    {
        return $this->offsetExists($header);
    }

    /**
     *
     * @param string $header
     * @return mixed
     * @see \midl\core\Iterator::offsetGet()
     */
    public function get($header)
    {
        return $this->offsetGet($header);
    }

    /**
     *
     * @see Iterator::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null)
            $this->add($value);
            
            // $array["Content-Type"] = null;
        elseif ($value === null)
            unset($this->array[$offset]);
        else
            $this->add($offset, $value);
    }

    /**
     *
     * @param string $name
     * @param string $value
     * @param bool $value
     * @return void
     */
    protected function addHeaderField($name, $value, $replace)
    {
        $value = trim($value);
        
        if ($replace || !isset($this->array[$name]))
            $this->array[$name] = $value;
        
        else {
            if (is_string($this->array[$name]))
                $this->array[$name] = [$this->array[$name], $value];
            else
                $this->array[$name][] = $value;
        }
    }
}