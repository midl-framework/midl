<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\i18n;

use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{

    /**
     *
     * @var Locale
     */
    private $locale;

    protected function setUp()
    {
        $data = ["lang" => "en", "displayLang" => "English", "country" => "US", 
            "displayCountry" => "United States"];
        
        $this->locale = new Locale($data);
    }

    public function testGetLanguage()
    {
        $this->assertEquals("en", $this->locale->getLanguage());
    }

    public function testGetDisplayLanguage()
    {
        $this->assertEquals("English", $this->locale->getDisplayLanguage());
    }

    public function testGetCountry()
    {
        $this->assertEquals("US", $this->locale->getCountry());
    }

    public function testGetDisplayCountry()
    {
        $this->assertEquals("United States", $this->locale->getDisplayCountry());
    }

    public function testGetValue()
    {
        $this->assertEquals("en-US", $this->locale->getValue());
    }
}