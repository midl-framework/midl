<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 *           
 */
namespace midl\i18n;

use PHPUnit\Framework\TestCase;

class I18nTest extends TestCase
{

    /**
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    protected function setUp()
    {
        $this->adapter = $this->createMock(TranslateAdapterGettext::class);
        $this->adapter->method("loadTranslationData")->willReturn([]);
    }

    public function testInitializationWithAvailableLocales()
    {
        $availableLocales = [["lang" => "es", "country" => "ES"]];
        
        $i18n = new I18n($availableLocales);

        $actualLocales = [];
        
        foreach ($i18n->getAvailableLocales() as $locale)
            $actualLocales[] = ["lang" => $locale->getLanguage(), "country" => $locale->getCountry()];
        
        $this->assertEquals($availableLocales, $actualLocales);
        $this->assertEquals("es", $i18n->getDefaultLocale()->getLanguage());
        $this->assertEquals("es", $i18n->getLocale()->getLanguage());
        $this->assertEquals("es-ES", $i18n->getLocale()->getValue());
    }

    public function testInitializationWithTranslator()
    {
        $availableLocales = [["lang" => "es", "country" => "ES"]];
        
        $translator = new Translator($this->adapter, "es-ES", "text-domain");
        $i18n = new I18n($availableLocales, $translator);
        
        $this->assertEquals("text-domain", $i18n->getTranslator()->getDomain());
    }

    public function testDefaultLocale()
    {
        $availableLocales = [["lang" => "es", "country" => "ES"]];
        $availableLocales[] = ["lang" => "en", "country" => "GB", "default" => true];
        
        $i18n = new I18n($availableLocales);
        
        $this->assertEquals("en", $i18n->getDefaultLocale()->getLanguage());
        $this->assertEquals("en-GB", $i18n->getDefaultLocale()->getValue());
    }

    public function testInvalidLocaleData()
    {
        $availableLocales = [["no-lang" => "es", "country" => "ES", "default" => true]];
        $availableLocales[] = ["lang" => "en", "country" => "GB"];
        
        $i18n = new I18n($availableLocales);
        
        $this->assertNotEquals("es-ES", $i18n->getDefaultLocale()->getValue());
    }

    public function testChangeLocaleByLanguage()
    {
        $availableLocales = [["lang" => "es", "country" => "ES"]];
        $availableLocales[] = ["lang" => "en", "country" => "GB", "default" => true];
        
        $i18n = new I18n($availableLocales);
        $i18n->changeLocaleBy("es");
        
        $this->assertEquals("en-GB", $i18n->getDefaultLocale()->getValue());
        $this->assertEquals("es-ES", $i18n->getLocale()->getValue());
    }

    public function testChangeLocaleByCountry()
    {
        $availableLocales = [["lang" => "es", "country" => "ES"]];
        $availableLocales[] = ["lang" => "en", "country" => "GB", "default" => true];
        
        $i18n = new I18n($availableLocales);
        
        $this->assertEquals("en-GB", $i18n->getLocale()->getValue());
        $i18n->changeLocaleBy(null, "ES");
        $this->assertEquals("es-ES", $i18n->getLocale()->getValue());
    }

    public function testChangeLocaleByLanguageAndCountry()
    {
        $availableLocales = [["lang" => "es", "country" => "ES"]];
        $availableLocales[] = ["lang" => "en", "country" => "GB", "default" => true];
        
        $i18n = new I18n($availableLocales);
        
        $this->assertEquals("en-GB", $i18n->getLocale()->getValue());
        $i18n->changeLocaleBy("es", "ES");
        $this->assertEquals("es-ES", $i18n->getLocale()->getValue());
    }
}
