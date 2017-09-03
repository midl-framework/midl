<?php
namespace midl\log;

class LoggerOptions
{

    /**
     * Log types
     * SYSTEM: send messages to PHP's system logger, this can be set in php.ini
     * EMAIL: send message to specified email
     * FILE: write message to file
     *
     * @var int
     */
    const TYPE_SYSTEM = 0;

    const TYPE_EMAIL = 1;

    const TYPE_FILE = 3;

    /**
     * It should be one of: TYPE_SYSTEM, TYPE_EMAIL or TYPE_FILE
     * Defaults to TYPE_SYSTEM
     *
     * @var int
     */
    public $type = self::TYPE_SYSTEM;

    /**
     * Depends on log type, might be file path, email address or null
     *
     * @var string
     */
    public $destination;

    /**
     * Admin email and error level to send email when logging.
     * For following example it will log the message and send email to the admin
     * for ALERT and EMERGENCY logs:
     *
     * ["address" => "admin@example.tld", "level" => Logger::ALERT | Logger::EMERGENCY]
     *
     * @var array
     */
    public $email;

    /**
     * PHP error level to catch and log the error
     *
     * @var int
     */
    protected $errorLevel;

    /**
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Example options array:
     * [
     * "destination" => "/abs/path/to/logs/file.log",
     * "type" => self::TYPE_FILE,
     * "errorLevel" => E_ALL ^ E_WARNING ^ E_NOTICE,
     * "email" => ["address" => "admin@example.tld", "level" => Logger::ALERT | Logger::EMERGENCY]
     * ]
     *
     * @param Logger $logger
     * @param array $options [optional]
     * @throws \RuntimeException When log folder is not writable
     */
    public function __construct(Logger $logger, array $options = [])
    {
        $this->logger = $logger;
        
        if (!empty($options["destination"])) {
            $this->destination = (string)$options["destination"];
            
            if (!empty($options["type"])) {
                
                if ($options["type"] == static::TYPE_EMAIL)
                    $this->type = (int)$options["type"];
                
                elseif ($options["type"] == static::TYPE_FILE) {
                    $this->type = (int)$options["type"];
                    
                    if (!is_writable(dirname($this->destination)))
                        throw new \RuntimeException("Log file '{$this->destination}' is not writable.");
                }
            }
        }
        
        if (!array_key_exists("errorLevel", $options))
            $options["errorLevel"] = E_ALL ^ E_NOTICE;
        
        if (!empty($options["errorLevel"])) {
            $this->errorLevel = (int)$options["errorLevel"];
            
            set_error_handler([$this, "errorHandler"], $this->errorLevel);
            register_shutdown_function([$this, "shutdownHandler"]);
        }
        
        if (!empty($options["email"]["address"]) && !empty($options["email"]["level"]))
            $this->email = $options["email"];
    }

    /**
     * Handle PHP errors
     *
     * @param int $code
     * @param string $error
     * @return bool This function always return false
     */
    public function errorHandler($code, $error)
    {
        if ($this->errorLevel & $code)
            $this->logger->error("[code: $code] Error handler: $error");
        
        return false;
    }

    /**
     * This function will be called on shutdown
     * Logs only if PHP shutdown becuase of an important error
     *
     * @return void
     */
    public function shutdownHandler()
    {
        $e = error_get_last();
        $errs = (E_ERROR + E_PARSE + E_COMPILE_WARNING + E_COMPILE_ERROR + E_CORE_WARNING + E_CORE_ERROR);
        
        if ($e["type"] & $errs) {
            $msg = "[file: {$e["file"]}, line: {$e["line"]}, code: {$e["type"]}] Shutdown error: {$e["message"]}";
            
            $this->logger->error($msg);
        }
    }
}