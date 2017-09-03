<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\net;

use PHPUnit\Framework\TestCase;

class URITest extends TestCase
{

    /**
     *
     * @var Locale
     */
    private $serverCopy;

    protected function setUp()
    {
        $this->serverCopy = $_SERVER;
    }

    protected function tearDown()
    {
        $_SERVER = $this->serverCopy;
    }

    public function testURIFromPHPGlobals()
    {
        $_SERVER["SCRIPT_NAME"] = "/base/path/controller/action/index.php";
        $_SERVER["REQUEST_SCHEME"] = "https";
        $_SERVER["HTTP_HOST"] = "example.tld";
        $_SERVER["REQUEST_URI"] = "/base/path/controller/action/?param=a+b&p=c%20d";
        
        $uri = new URI(null, "base/path");
        $uri2 = new URI(null, "//base//path////");
        
        $this->assertEquals("https", $uri->getScheme());
        $this->assertEquals("example.tld", $uri->getHost());
        $this->assertEquals("/controller/action/", $uri->getPath());
        $this->assertEquals("param=a b&p=c d", $uri->getQuery());
        $this->assertEquals("/base/path/", $uri2->getBasePath());
        $this->assertEquals("/base/path/", $uri->getBasePath());
        $this->assertEquals(["controller", "action"], $uri->getSegments());
        $this->assertEquals("https://example.tld/base/path/controller/action/?param=a b&p=c d", $uri->getUri());
        $this->assertEquals("//example.tld/base/path/controller/action/?param=a b&p=c d", $uri->getUri(true));
    }

    public function testURI()
    {
        $uri = new URI("https://example.tld:8080/base/path/controller/action/?param=a+b&p=c%20d", "base/path");
        
        $this->assertEquals("https", $uri->getScheme());
        $this->assertEquals("example.tld", $uri->getHost());
        $this->assertEquals(8080, $uri->getPort());
        $this->assertEquals("/controller/action/", $uri->getPath());
        $this->assertEquals("param=a b&p=c d", $uri->getQuery());
        $this->assertEquals("/base/path/", $uri->getBasePath());
        $this->assertEquals(["controller", "action"], $uri->getSegments());
        $this->assertEquals("https://example.tld/base/path/controller/action/?param=a b&p=c d", $uri->getUri());
        $this->assertEquals("//example.tld/base/path/controller/action/?param=a b&p=c d", $uri->getUri(true));
    }
}