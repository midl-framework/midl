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

use midl\core\Iterator;

class Cookies extends Iterator
{

    /**
     *
     * @var string
     */
    protected $domain;

    /**
     *
     * @var string
     */
    protected $path;

    /**
     *
     * @param string $domain
     * @param string $path
     */
    public function __construct($domain = "", $path = "/")
    {
        $this->domain = $domain;
        $this->path = $path;
        
        $this->array = $_COOKIE;
    }

    /**
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     *
     * @param string $domain
     * @return string
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     *
     * @param string $path
     * @return string
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->offsetExists($name);
    }

    /**
     *
     * @param string $name
     * @return string|array
     */
    public function get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return bool
     */
    public function set($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        $path = $path === null ? $this->path : $path;
        $domain = $domain === null ? $this->domain : $domain;
        
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     *
     * @return void
     */
    public function remove($name)
    {
        unset($this->array[$name]);
        
        $this->set($name, false, 1);
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
