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

use midl\core\event\FilterEvent;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{

    /**
     *
     * @var string
     */
    private $logFilePath;

    protected function setUp()
    {
        $this->logFilePath = vfsStream::setup("root")->url() . "/" . date("Y-m") . ".log";
    }

    public function testNotExistingLogFileException()
    {
        $this->expectException(\RuntimeException::class);
        
        new Logger("not_existing/file");
    }

    public function testInvalidLogLevel()
    {
        $logger = new Logger();
        
        $this->expectException(\InvalidArgumentException::class);
        
        $logger->log("", -1);
    }

    public function testLog()
    {
        $logger = new Logger($this->logFilePath);
        
        $logger->log("Log message.", Logger::INFO);
        
        $this->assertContains("Log message.", file_get_contents($this->logFilePath));
    }

    public function testExceptionLog()
    {
        $logger = new Logger($this->logFilePath);
        
        $logger->log(new \RuntimeException("RuntimeException message"), Logger::EMERGENCY);
        
        $this->assertContains(__FUNCTION__, file_get_contents($this->logFilePath));
        $this->assertContains(__CLASS__, file_get_contents($this->logFilePath));
    }

    public function testCustomLogMessage()
    {
        $userId = 123456789;
        $requestUri = "/request/uri/";
        
        $logger = new Logger($this->logFilePath);
        $logger->getFormatter()->setFormat("{URI} {USER_ID} {MESSAGE}");
        $logger->getFormatter()->addVar("URI", $requestUri);
        $logger->getFormatter()->addVar("USER_ID", $userId);
        
        $logger->log("Testing custom message", Logger::INFO);
        
        $fileContent = file_get_contents($this->logFilePath);
        
        $this->assertContains((string)$userId, $fileContent);
        $this->assertContains($requestUri, $fileContent);
    }

    public function testLogEmergency()
    {
        $logger = new Logger($this->logFilePath);
        
        $logger->emergency("System is down!");
        
        $this->assertContains("System is down!", file_get_contents($this->logFilePath));
    }

    public function testPHPErrorHandlerLog()
    {
        $options = ["destination" => $this->logFilePath, "type" => LoggerOptions::TYPE_FILE];
        $options["errorLevel"] = E_ALL ^ E_NOTICE;
        
        new Logger($options);
        
        @trigger_error("Trigger user error");
        
        $this->assertContains("Trigger user error", file_get_contents($this->logFilePath));
    }
}
