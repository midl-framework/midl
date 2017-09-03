<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\net\http;

use PHPUnit\Framework\TestCase;

class HeaderFieldsTest extends TestCase
{

    public function testAddVariations()
    {
        $headers = new HeaderFields();
        
        $headers->add("Cache-Control", "no-cache");
        $headers->add("Cache-Control", "no-store", false);
        $headers->add("Cache-Control: must-revalidate", null, false);
        $headers->add([["Cache-Control", "max-age=3600"]], null, false);
        $headers->add([["Cache-Control" => "public"]], null, false);
        $headers->add(["Cache-Control" => "private"], null, false);
        
        $this->assertTrue($headers->has("Cache-Control"));
    }

    public function testAddSimple()
    {
        $headers = new HeaderFields();
        
        $headers->add("Content-Type", "text/html");
        
        $this->assertEquals(["Content-Type" => "text/html"], $headers->all());
        $this->assertEquals("text/html", $headers->get("Content-Type"));
        
        $headers->add("Content-Type", "text/plain");
        
        $this->assertEquals("text/plain", $headers->get("Content-Type"));
        
        $headers->add("Content-Type", "text/html", false);
        
        $this->assertEquals(["text/plain", "text/html"], $headers->get("Content-Type"));
    }

    public function testGetAndGetAll()
    {
        $headers = new HeaderFields();
        
        $headers->add("Cache-Control", "no-cache");
        $headers->add("Cache-Control", "no-store", false);
        $headers->add("Cache-Control: must-revalidate", null, false);
        $headers->add([["Cache-Control", "max-age=3600"]], null, false);
        $headers->add([["Cache-Control" => "public"]], null, false);
        $headers->add(["Cache-Control" => "private"], null, false);
        
        $this->assertCount(6, $headers->get("Cache-Control"));
        $this->assertCount(1, $headers->all());
    }

    public function testRemove()
    {
        $headers = new HeaderFields();
        
        $headers->add("Cache-Control", "no-cache");
        
        $headers->remove("Cache-Control");
        $this->assertCount(0, $headers->all());
    }

    public function testArrayAccess()
    {
        $headers = new HeaderFields();
        
        $headers["Accept"] = "text/html";
        
        $this->assertCount(1, $headers);
        $this->assertTrue($headers->has("Accept"));
        $this->assertEquals("text/html", $headers->get("Accept"));
        $this->assertEquals("text/html", $headers["Accept"]);
        
        $headers["Accept"] = "application/xhtml+xml";
        
        $this->assertEquals("application/xhtml+xml", $headers->get("Accept"));
        $this->assertEquals("application/xhtml+xml", $headers["Accept"]);
        
        $headers["Accept-Encoding"] = "  gzip, deflate, sdch, br  ";
        
        $this->assertEquals("gzip, deflate, sdch, br", $headers->get("Accept-Encoding"));
        
        $headers["Age"] = 7200;
        
        $this->assertEquals(7200, $headers->get("Age"));
        
        $headers["Accept-Language"] = "en-US";
        $headers->add("Accept-Language:   en;q=0.8 ", null, false);
        
        $this->assertEquals(["en-US", "en;q=0.8"], $headers->get("Accept-Language"));
        
        unset($headers["Accept-Language"]);
        
        $this->assertFalse($headers->has("Accept-Language"));
    }

    public function testForeachLoop()
    {
        $headers = new HeaderFields();
        $result = "";
        
        // Empty headers will not iterate
        foreach ($headers as $fieldName => $fieldValue)
            $result .= "{$fieldName}: {$fieldValue}, ";
        
        $headers["Accept"] = "text/html";
        $headers["Age"] = 7200;
        $headers["Accept-Language"] = "en-US";
        
        foreach ($headers as $fieldName => $fieldValue)
            $result .= "{$fieldName}: {$fieldValue}, ";
        
        $this->assertEquals("Accept: text/html, Age: 7200, Accept-Language: en-US, ", $result);
    }
}