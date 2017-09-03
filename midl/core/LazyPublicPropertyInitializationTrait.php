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

/**
 * This is a utility trait to lazy initialize *public* property when referenced from object.
 * Please don't use this utility for protected or private properties.
 */
trait LazyPublicPropertyInitializationTrait
{

    /**
     * [propertyName => getterMethodName]
     *
     * @var array
     */
    private $properties;

    /**
     *
     * @var string
     */
    private $factoryMethod;

    /**
     * Initialize properties to be initialized when referenced from object.
     *
     * @param array $properties
     * @param string $factoryMethod
     * @return void
     */
    private function initLazyPublicPropertyInitialization(array $properties, $factoryMethod = null)
    {
        $this->properties = [];
        $this->factoryMethod = null;
        
        if ($properties) {
            if (is_string($factoryMethod) && method_exists($this, $factoryMethod))
                $this->factoryMethod = $factoryMethod;
            
            foreach ($properties as $property) {
                if (method_exists($this, $getterMethod = "get" . ucfirst($property))) {
                    $this->properties[$property] = $getterMethod;
                    unset($this->{$property});
                } elseif ($this->factoryMethod) {
                    unset($this->{$property});
                }
            }
        }
    }

    /**
     * Call property's getter function to initialize requested property if it is already registered.
     *
     * @param string $property
     * @return mixed
     */
    public function &__get($property)
    {
        if (isset($this->properties[$property]))
            return $this->{$this->properties[$property]}();
        
        else if ($this->factoryMethod)
            return $this->{$this->factoryMethod}($property);
        
        $nullProperty = null;
        
        return $nullProperty;
    }
}
