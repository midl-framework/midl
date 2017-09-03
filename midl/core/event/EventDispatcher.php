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

class EventDispatcher
{

    /**
     *
     * @var array
     */
    private $eventListeners = [];

    /**
     */
    public function __construct()
    {}

    /**
     *
     * @param string $type
     * @param callable $listener
     * @param int $priority
     * @return void
     * @throws \InvalidArgumentException
     */
    public function addEventListener($type, callable $listener, $priority = 0)
    {
        if (!is_string($type) || !$type)
            throw new \InvalidArgumentException("Type must be a valid string");
        
        if (!is_int($priority))
            throw new \InvalidArgumentException("Priority must be a signed integer");
        
        $listenerId = $this->getEventListenerId($listener);
        
        if (isset($this->eventListeners[$type][$priority][$listenerId]))
            return;
        
        $this->removeEventListener($type, $listener);
        
        if (!isset($this->eventListeners[$type]))
            $this->eventListeners[$type] = [$priority => []];
        
        elseif (!isset($this->eventListeners[$type][$priority]))
            $this->eventListeners[$type][$priority] = [];
        
        $this->eventListeners[$type][$priority][$listenerId] = ["callable" => $listener];
        
        ksort($this->eventListeners[$type]);
    }

    /**
     *
     * @param string $type
     * @return bool
     */
    public function hasEventListener($type)
    {
        return is_string($type) && isset($this->eventListeners[$type]);
    }

    /**
     *
     * @param string $type
     * @param callable $listener [optional]
     * @return void
     */
    public function removeEventListener($type, callable $listener = null)
    {
        if ($listener === null)
            unset($this->eventListeners[$type]);
        
        elseif ($this->hasEventListener($type) && count($this->eventListeners[$type]) > 0) {
            
            $listenerId = $this->getEventListenerId($listener);
            
            foreach ($this->eventListeners[$type] as $priority => $listeners) {
                if (isset($this->eventListeners[$type][$priority][$listenerId])) {
                    unset($this->eventListeners[$type][$priority][$listenerId]);
                    
                    if (count($this->eventListeners[$type][$priority]) === 0) {
                        unset($this->eventListeners[$type][$priority]);
                        
                        if (count($this->eventListeners[$type]) === 0)
                            unset($this->eventListeners[$type]);
                    }
                    return;
                }
            }
        }
    }

    /**
     *
     * @param Event $event
     * @return bool
     */
    public function dispatchEvent(Event $event)
    {
        if (!is_string($event->type) || !$event->type)
            return false;
        
        $event->currentTarget = $this;
        $event->target = $this;
        
        if (isset($this->eventListeners[$event->type]) && count($this->eventListeners[$event->type]) > 0) {
            reset($this->eventListeners[$event->type]);
            
            foreach ($this->eventListeners[$event->type] as $priority => $listeners) {
                reset($this->eventListeners[$event->type][$priority]);
                
                foreach ($listeners as $listener)
                    call_user_func($listener["callable"], $event);
            }
        } else
            return false;
        
        return true;
    }

    /**
     *
     * @param callable $listener
     * @return string
     */
    private function getEventListenerId(callable $listener)
    {
        if (is_string($listener))
            return $listener;
        
        if (is_object($listener))
            $listener = [spl_object_hash($listener), ""];
        
        elseif (is_object($listener[0]))
            $listener[0] = spl_object_hash($listener[0]);
        
        return $listener[0] . "-" . $listener[1];
    }
}