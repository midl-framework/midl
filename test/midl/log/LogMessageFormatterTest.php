<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\log;

use PHPUnit\Framework\TestCase;

class LogMessageFormatterTest extends TestCase
{

    public function testDefault()
    {
        $formatter = new LogMessageFormatter();
        
        $message = "Default message";
        
        $this->assertEquals($message, $formatter->format($message));
    }

    public function testCustomMessageFormat()
    {
        $format = "[{REMOTE_ADDR} - {TIME[d.m.Y H:i:s O]}] {LOG_LEVEL} {MESSAGE}\n{BACKTRACE[5]]}";
        
        $formatter = new LogMessageFormatter($format);
        
        $formattedMsg = $formatter->format("Custom message", ["LOG_LEVEL" => "INFO"]);
        
        $this->assertContains("INFO", $formattedMsg);
        $this->assertContains(__CLASS__, $formattedMsg);
        $this->assertContains(__FUNCTION__, $formattedMsg);
    }

    public function testTimeInMessage()
    {
        $format = "Time: {TIME[d.m.Y H:i]} {LOG_LEVEL} {MESSAGE}";
        
        $formatter = new LogMessageFormatter($format);
        
        $formattedMsg = $formatter->format("Custom message", ["LOG_LEVEL" => "DEBUG"]);
        
        $this->assertContains("Time: " . date("d.m.Y H:i"), $formattedMsg);
    }

    public function testExceptionLog()
    {
        $format = "[{TIME[d.m.Y H:i:s O]}} {LOG_LEVEL} {MESSAGE}\n{BACKTRACE}";
        
        $formatter = new LogMessageFormatter($format);
        $formatter->addVar("LOG_LEVEL", "EXCEPTION");
        
        $exception = new \Exception("Exception message");
        $formattedMsg = $formatter->format($exception);
        
        $this->assertContains("EXCEPTION", $formattedMsg);
        $this->assertContains("Exception message", $formattedMsg);
    }
}
