<?php
namespace midl\core\event;

use midl\core\event\Event;

class FilterEvent extends Event
{

    /**
     *
     * @var string
     */
    const FILTER = "filter";

    /**
     * Value to be filtered
     * 
     * @var mixed
     */
    public $value;

    /**
     *
     * @param string $type
     * @param mixed $value
     */
    public function __construct($type, $value)
    {
        parent::__construct($type);
        
        $this->value = $value;
    }
}