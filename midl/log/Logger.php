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

class Logger
{

    /**
     * Log levels
     *
     * @var int
     */
    const EMERGENCY = 128;

    const ALERT = 64;

    const CRITICAL = 32;

    const ERROR = 16;

    const WARNING = 8;

    const NOTICE = 4;

    const INFO = 2;

    const DEBUG = 1;

    /**
     *
     * @var array
     */
    protected $levels = [
        self::EMERGENCY => "EMERGENCY", 
        self::ALERT => "ALERT", 
        self::CRITICAL => "CRITICAL", 
        self::ERROR => "ERROR", 
        self::WARNING => "WARNING", 
        self::NOTICE => "NOTICE", 
        self::INFO => "INFO", 
        self::DEBUG => "DEBUG"];

    /**
     *
     * @var LoggerOptions
     */
    protected $options;

    /**
     *
     * @var LogMessageFormatter
     */
    protected $formatter;

    /**
     *
     * @param array|string $options [optional] Log file path or options array
     * @param LogMessageFormatter $formatter [optional]
     * @see LoggerOptions::__construct()
     */
    public function __construct($options = null, LogMessageFormatter $formatter = null)
    {
        if (is_string($options))
            $options = ["destination" => $options, "type" => LoggerOptions::TYPE_FILE];
        
        $this->options = new LoggerOptions($this, $options ?: []);
        
        if ($formatter)
            $this->formatter = $formatter;
        else {
            $format = "[{REMOTE_ADDR} - {TIME[d.m.Y H:i:s O]}] [{LOG_LEVEL}] {MESSAGE}\n{BACKTRACE[5]]}";
            $this->formatter = new LogMessageFormatter($format);
        }
    }

    /**
     *
     * @return LogMessageFormatter
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     *
     * @return void
     */
    public function setFormatter(LogMessageFormatter $formatter = null)
    {
        $this->formatter = $formatter;
    }

    /**
     *
     * @return LoggerOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     * @return void
     */
    public function setOptions(LoggerOptions $options)
    {
        $this->options = $options;
    }

    /**
     *
     * @param string|\Exception $message
     * @param int $level Log level, possible values Logger::ALERT, Logger::ERROR
     * @return void
     * @throws \InvalidArgumentException When invalid log level specified
     */
    public function log($message, $level)
    {
        if (!isset($this->levels[$level]))
            throw new \InvalidArgumentException("Invalid log level: $level");
        
        if ($this->formatter)
            $message = $this->formatter->format($message, ["LOG_LEVEL" => $this->levels[$level]]);
        
        @error_log($message . PHP_EOL, $this->options->type, $this->options->destination);
        
        if ($this->options->email && ($this->options->email["level"] & $level))
            @error_log($message, LoggerOptions::TYPE_EMAIL, $this->options->email["address"]);
    }

    /**
     *
     * @param string $message
     */
    public function emergency($message)
    {
        $this->log($message, static::EMERGENCY);
    }

    /**
     *
     * @param string $message
     */
    public function alert($message)
    {
        $this->log($message, static::ALERT);
    }

    /**
     *
     * @param string $message
     */
    public function critical($message)
    {
        $this->log($message, static::CRITICAL);
    }

    /**
     *
     * @param string $message
     */
    public function error($message)
    {
        $this->log($message, static::ERROR);
    }

    /**
     *
     * @param string $message
     */
    public function warning($message)
    {
        $this->log($message, static::WARNING);
    }

    /**
     *
     * @param string $message
     */
    public function notice($message)
    {
        $this->log($message, static::NOTICE);
    }

    /**
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->log($message, static::INFO);
    }

    /**
     *
     * @param string $message
     */
    public function debug($message)
    {
        $this->log($message, static::DEBUG);
    }
}