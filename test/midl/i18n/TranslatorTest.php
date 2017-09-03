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

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{

    /**
     *
     */
    private $adapter;

    /**
     *
     * @var Translator
     */
    private $translator;

    protected function setUp()
    {
        $this->adapter = $this->createMock(TranslateAdapterGettext::class);
        $this->adapter->method("loadTranslationData")->willReturn([]);
        
        $this->translator = new Translator($this->adapter);
    }

    public function testAdapter()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new TranslateAdapterGettext("not_existing");
    }

    public function testInitializationWithDomain()
    {
        $translator = new Translator($this->adapter, null, "midl");
        
        $this->assertEquals("midl", $translator->getDomain());
    }

    public function testTranslation()
    {
        $this->translator->addTranslationData(["Hello" => "Hola"]);
        
        $this->assertEquals("Hola", $this->translator->t("Hello"));
    }

    public function testPluralTranslation()
    {
        $this->translator->addTranslationData(["day" => ["día", "días"]]);
        
        $this->assertEquals("día", $this->translator->tn("day", "day", 1));
        $this->assertEquals("días", $this->translator->tn("day", "days", 2));
    }

    public function testSprintfTranslation()
    {
        $this->translator->addTranslationData(["Hello %s" => "Hola %s"]);
        
        $this->assertEquals("Hola World", $this->translator->tSpf("Hello %s", "World"));
    }

    /**
     * @dataProvider pluralTranslationDataProvider
     */
    public function testSprintfPluralTranslation($data)
    {
        $this->translator->addTranslationData($data);
        
        $singular = $this->translator->tSpfN('%1$d day in %2$s', '%1$d days in %2$s', 1, "Barcelona");
        $plural = $this->translator->tSpfN('%1$d day in %2$s', '%1$d days in %2$s', 5, "Barcelona");
        
        $this->assertEquals("un día en Barcelona", $singular);
        $this->assertEquals("5 días en Barcelona", $plural);
    }

    /**
     * @dataProvider pluralTranslationDataProvider
     */
    public function testPrintfSingularTranslation($data)
    {
        $this->translator->addTranslationData($data);
        
        $this->expectOutputString("un día en Barcelona");
        
        $this->translator->tPfN('%1$d day in %2$s', '%1$d days in %2$s', 1, "Barcelona");
    }

    /**
     * @dataProvider pluralTranslationDataProvider
     */
    public function testPrintfPluralTranslation($data)
    {
        $this->translator->addTranslationData($data);
        
        $this->expectOutputString("2 días en Barcelona");
        
        $this->translator->tPfN('%1$d day in %2$s', '%1$d days in %2$s', 2, "Barcelona");
    }

    public function testPrintfTranslation()
    {
        $this->translator->addTranslationData(["Hello %s" => "Hola %s"]);
        
        $this->expectOutputString("Hola World");
        
        $this->translator->tPf("Hello %s", "World");
    }

    public function testEchoTranslation()
    {
        $this->translator->addTranslationData(["Hello" => "Hola"]);
        
        $this->expectOutputString("Hola");
        
        $this->translator->e("Hello");
    }

    public function pluralTranslationDataProvider()
    {
        return [[['%1$d day in %2$s' => ['un día en %2$s', '%1$d días en %2$s']]]];
    }
}