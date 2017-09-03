<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\package;

/**
 * Pac[kageFac]tory is a utility class to manage loading and initialization of packages
 */
class Pactory
{

    /**
     * Base directory path for packages
     *
     * @var array
     */
    protected static $singletons = [];

    /**
     * Base directory path for packages
     *
     * @var string
     */
    protected static $baseDir;

    /**
     * Gets base directory path for packages, defaults to directory of this file
     *
     * @return string
     */
    public static function getBaseDir()
    {
        if (!static::$baseDir)
            static::setBasePath(__DIR__ . DIRECTORY_SEPARATOR);
        
        return static::$baseDir;
    }

    /**
     * Sets base directory path for packages
     *
     * @param string $baseDir Absolute directory path for packages
     * @return void
     * @throws \RuntimeException
     */
    public static function setBasePath($baseDir)
    {
        if (!is_dir($baseDir))
            throw new \RuntimeException("Packages directory '$baseDir' doesn't exist.");
        
        static::$baseDir = rtrim($baseDir, "/\\") . DIRECTORY_SEPARATOR;
    }

    /**
     * Loads package file
     *
     * @param string $path Package relative path
     * @param bool $once [optional] Whether load package file once or not, defaults to true
     * @return mixed|bool Return value from include[_once], false on failure
     */
    public static function load($path, $once = true)
    {
        $path = static::getFullPath($path);
        
        if (!$path)
            return false;
        
        if ($once)
            return include_once $path;
        
        return include $path;
    }

    /**
     * Loads package file and creates only one instance of package for specified arguments
     *
     * @param string $path
     * @param string $className [optional]
     * @param array $args [optional]
     * @return mixed|null Package instance or null on failure
     * @throws \RuntimeException
     */
    public static function newSingleton($path, $className = null, array $args = null)
    {
        $fullPath = static::getFullPath($path);
        
        if (!$fullPath)
            throw new \RuntimeException("Package '$path' doesn't exist.");
        
        $key = $path . @json_encode($args);
        
        if (isset(static::$singletons[$key]))
            return static::$singletons[$key];
        
        $instance = static::initPackage($fullPath, $className, $args);
        
        if ($instance)
            static::$singletons[$key] = $instance;
        
        return $instance;
    }

    /**
     * Loads package file and creates an instance of package for each call.
     * If you want to have only one instance of package then use newSingleton() method.
     *
     * @param string $path
     * @param string $className [optional]
     * @param array $args [optional]
     * @return mixed|null Package instance or null on failure
     * @see Pactory::newSingleton()
     */
    public static function newPackage($path, $className = null, array $args = null)
    {
        $path = static::getFullPath($path);
        
        if (!$path)
            return null;
        
        return static::initPackage($path, $className, $args);
    }

    /**
     * Loads and initialize package class
     *
     * @param string $path
     * @param string $className [optional]
     * @param array $args [optional]
     * @return mixed|null Package instance or null on failure
     * @throws \RuntimeException
     */
    protected static function initPackage($path, $className = null, array $args = null)
    {
        require_once $path;
        
        if (!is_string($className))
            $className = pathinfo($path, PATHINFO_FILENAME);
        
        if (!$className || !class_exists($className))
            throw new \RuntimeException("Package class '$className' doesn't exist.");
        
        $rc = new \ReflectionClass($className);
        
        if ($args !== null)
            return $rc->newInstanceArgs($args);
        
        return $rc->newInstance();
    }

    /**
     * Gets package's full path
     *
     * @return string|bool Package's full path on success, false if package doesn't exist
     */
    protected static function getFullPath($path)
    {
        $path = static::getBaseDir() . ltrim($path, "/\\");
        
        if (!is_file($path))
            return false;
        
        return $path;
    }
}
