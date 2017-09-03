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

use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{

    public function testAddEventListenerTypeException()
    {
        $eventDispatcher = new EventDispatcher();
        
        $this->expectException(\InvalidArgumentException::class);
        
        $eventDispatcher->addEventListener(null, array($this, "handler"));
    }

    public function testAddEventListenerWithSubsquentCallForSameListener()
    {
        $eventDispatcher = new EventDispatcher();
        
        $eventDispatcher->addEventListener(Event::ADD, array($this, "handler"), 2);
        $eventDispatcher->addEventListener(Event::ADD, array($this, "handler"), 1);
        $eventDispatcher->addEventListener(Event::ADD, array($this, "handler"));
        
        $this->expectOutputString(Event::ADD);
        
        $eventDispatcher->dispatchEvent(new Event(Event::ADD));
    }

    public function testPriority()
    {
        $eventDispatcher = new EventDispatcher();
        
        $handler2 = function($event) {
            echo $event->type . "2";
        };
        
        $eventDispatcher->addEventListener(Event::ADD, array($this, "handler"), 2);
        $eventDispatcher->addEventListener(Event::ADD, $handler2, 1);
        
        $this->expectOutputString(Event::ADD . "2" . Event::ADD);
        
        $eventDispatcher->dispatchEvent(new Event(Event::ADD));
    }

    public function testDispatchEvent()
    {
        $eventDispatcher = new EventDispatcher();
        
        $eventDispatcher->addEventListener(Event::ADD, array($this, "handler"), 2);
        $eventDispatcher->addEventListener(Event::REMOVE, array($this, "handler"), 1);
        
        $this->expectOutputString(Event::ADD . Event::REMOVE);
        
        $eventDispatcher->dispatchEvent(new Event(Event::ADD));
        $eventDispatcher->dispatchEvent(new Event(Event::REMOVE));
    }

    public function testHasEventListener()
    {
        $eventDispatcher = new EventDispatcher();
        
        $eventDispatcher->addEventListener(Event::ADD, array($this, "handler"), 2);
        
        $this->assertTrue($eventDispatcher->hasEventListener(Event::ADD));
        $this->assertFalse($eventDispatcher->hasEventListener(Event::REMOVE));
    }

    public function testRemoveEventListener()
    {
        $eventDispatcher = new EventDispatcher();
        
        $eventDispatcher->addEventListener(Event::ADD, array($this, "handler"), 1);
        $eventDispatcher->removeEventListener(Event::ADD, array($this, "handler"));
        
        $this->assertFalse($eventDispatcher->hasEventListener(Event::ADD));
    }

    public function handler(Event $event)
    {
        echo $event->type;
    }
}
