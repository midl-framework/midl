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

class ArrayMidlTest extends TestCase
{

    public function testSort()
    {
        $array = new ArrayMidl([1 => "a1", 2 => "a01", 0 => "A1", 3 => "A01"]);
        $array->sort();
        
        $this->assertEquals([3 => "A01", 2 => "a01", 0 => "A1", 1 => "a1"], $array->all());
        
        $array = new ArrayMidl([1 => "c", 2 => "b", 0 => "a", 3 => "a"]);
        $array->sort();

        $this->assertEquals([0 => "a", 3 => "a", 2 => "b", 1 => "c"], $array->all());
        
        $array = new ArrayMidl([4, 1, 5, 3, 2]);
        $array->sort();

        $this->assertEquals([1, 2, 3, 4, 5], $array->all());
    }

    public function testHas()
    {
        $array = new ArrayMidl([1 => "item", 2]);
        
        $this->assertTrue($array->has("item"));
        $this->assertTrue($array->has("2"));
        $this->assertFalse($array->has("2", true));
    }

    public function testPushPopRemove()
    {
        $array = new ArrayMidl();
        
        $this->assertEquals(1, $array->push("item"));
        $this->assertEquals("item", $array->pop());
        
        $array[] = "item1";
        $array[] = "item2";
        $array[] = "item3";
        $array[] = "item1";
        $array->remove("item1");
        
        $this->assertEquals([1 => "item2", 2 => "item3"], $array->all());
    }
}
