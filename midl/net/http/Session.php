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

use midl\core\Iterator;

class Session extends Iterator
{

    /**
     * Session key
     *
     * @var string
     */
    const SESS_KEY = "_appkey";

    /**
     * Constructor
     */
    public function __construct()
    {
        ini_set("session.use_strict_mode", "1");
        ini_set("session.cookie_httponly", "1");
        
        if (session_status() === PHP_SESSION_DISABLED)
            throw new \RuntimeException(sprintf("%s needs sessions to be enabled.", static::class));
        
        parent::__construct();
        
        $this->start();
        
        $this->array = &$_SESSION[static::SESS_KEY];
    }

    /**
     *
     * @return void
     */
    public function start()
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            throw new \RuntimeException("Session is already started.");
        
        session_name(self::SESS_KEY);
        session_start();
        
        $this->restore();
        
        if ($this->array["_start_time"] < time() - 300) {
            session_regenerate_id(true);
            $this->array["_start_time"] = time();
        }
    }

    /**
     * Ends and clean session data
     *
     * @return bool
     */
    public function end()
    {
        $this->array = [];
        
        return session_destroy();
    }

    /**
     * Ends, clean and starts a fresh session
     *
     * @return void
     */
    public function restart()
    {
        $this->end();
        $this->start();
    }

    /**
     *
     * @return void
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     *
     * @return void
     */
    protected function restore()
    {
        if (!isset($this->array) || !is_array($this->array))
            $this->array = [];
        
        if (!isset($this->array["_start_time"]))
            $this->array["_start_time"] = time();
    }
}
