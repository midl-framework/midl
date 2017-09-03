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

use PHPUnit\Framework\TestCase;

class IteratorTest extends TestCase
{

    public function testIterator()
    {
        $iterator = new Iterator(["item1", "key2" => "item2"]);
        
        $this->assertEquals("item1", $iterator->rewind());
        $this->assertEquals("item2", $iterator->next());
        $this->assertEquals("item2", $iterator->current());
        $this->assertEquals("key2", $iterator->key());
        $this->assertTrue($iterator->valid());
        $this->assertFalse($iterator->next());
        $this->assertFalse($iterator->valid());
    }

    public function testCountable()
    {
        $iterator = new Iterator([1, 2, 3]);
        
        $this->assertCount(3, $iterator);
        $this->assertEquals(3, count($iterator));
        $this->assertEquals(3, $iterator->count());
    }

    public function testArrayAccess()
    {
        $iterator = new Iterator();
        
        $iterator["key1"] = "item1";
        
        $this->assertContains("item1", $iterator);
        $this->assertTrue(isset($iterator["key1"]));
        
        unset($iterator["key1"]);
        
        $this->assertNotContains("item1", $iterator);
        $this->assertFalse(isset($iterator["key1"]));
    }
}
