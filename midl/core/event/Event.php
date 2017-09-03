<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\core\event;

class Event
{

    /**
     * Some common event types
     * 
     * @var string
     */
    const ADD = "add";

    const CANCEL = "cancel";

    const CHANGE = "change";

    const CLOSE = "close";

    const END = "end";

    const INIT = "init";

    const REMOVE = "remove";

    const START = "start";

    /**
     *
     * @var object [readonly]
     */
    public $currentTarget;

    /**
     *
     * @var object [readonly]
     */
    public $target;

    /**
     *
     * @var string [readonly]
     */
    public $type;

    /**
     *
     * @param string $type
     * @throws \InvalidArgumentException
     */
    public function __construct($type)
    {
        if (!is_string($type) || !$type)
            throw new \InvalidArgumentException("Event type must be a valid string");
        
        $this->type = $type;
    }
}