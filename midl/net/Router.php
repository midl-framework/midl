<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\net;

class Router
{

    /**
     * Segment => fully-qualified controller class name
     *
     * e.g.
     * "home" => 'sample\app\controller\Home'
     * "dashboard" => 'sample\app\controller\backend\Dashboard'
     *
     * @var array
     */
    protected static $routes = [];

    /**
     *
     * @param string $controllerSegment
     * @return string Fully-qualified controller class name, false on failure
     */
    public static function resolve($controllerSegment)
    {
        $controllerSegment = !$controllerSegment ? "" : $controllerSegment;
        
        if (!empty(static::$routes[$controllerSegment]))
            return static::$routes[$controllerSegment];
        
        return false;
    }

    /**
     *
     * @return void
     */
    public static function add($slug, $controller)
    {
        self::$routes[$slug] = $controller;
    }

    /**
     *
     * @return string
     */
    public static function get($slug)
    {
        return self::$routes[$slug];
    }
}
