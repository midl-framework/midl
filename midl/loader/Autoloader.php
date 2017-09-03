<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\loader;

/**
 * Auto loads framework files, this autoloader is developed
 * based on PSR-4 standards http://www.php-fig.org/psr/psr-4/
 *
 * @author Abdulhalim Kara
 */
class Autoloader
{

    /**
     * Namespace prefix and its base directory to search for requested classes.
     *
     * @var array
     */
    private $prefixes = [];

    /**
     * Constructor
     *
     * @param string $prefix [optional]
     * @param string $baseDir [optional]
     */
    public function __construct($prefix = null, $baseDir = null)
    {
        if ($prefix && $baseDir)
            $this->addNamespace($prefix, $baseDir);
        
        spl_autoload_register([$this, "load"]);
    }

    /**
     * Adds a base directory path for a namespace prefix
     *
     * @param string $prefix
     * @param string $baseDir
     */
    public function addNamespace($prefix, $baseDir)
    {
        $prefix = trim((string)$prefix, "\\");
        $baseDir = rtrim((string)$baseDir, "/\\") . DIRECTORY_SEPARATOR;
        
        if (!isset($this->prefixes[$prefix]))
            $this->prefixes[$prefix] = [];
        
        $this->prefixes[$prefix][] = $baseDir;
    }

    /**
     * Auto loads requested class by its fully qualified name
     *
     * @param string $class The fully-qualified class name, e.g. midl\database\Database
     * @return string|bool Mapped file path on success, false on failure
     */
    public function load($class)
    {
        $prefix = $class;
        
        while (($pos = strrpos($prefix, "\\")) !== false) {
            
            $prefix = substr($class, 0, $pos);
            $classPath = substr($class, $pos + 1);
            
            if (!empty($this->prefixes[$prefix])) {
                foreach ($this->prefixes[$prefix] as $baseDir) {
                    
                    $file = $baseDir . str_replace("\\", DIRECTORY_SEPARATOR, $classPath) . ".php";
                    
                    if (is_file($file)) {
                        require_once $file;
                        return $file;
                    }
                }
            }
            
            $prefix = rtrim($prefix, "\\");
        }
        
        return false;
    }
}