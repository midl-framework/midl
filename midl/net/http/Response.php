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

use midl\core\event\EventDispatcher;
use midl\core\event\FilterEvent;
use midl\core\exception\TerminateException;

/**
 *
 * @link https://tools.ietf.org/html/rfc2616#page-39
 */
class Response extends EventDispatcher
{

    /**
     *
     * @var string
     */
    const FILTER_REDIRECT_LOCATION = "filter_redirect_location";

    /**
     *
     * @var string
     */
    protected $version = "HTTP/1.1";

    /**
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     *
     * @var array
     */
    protected $reasonPhrases = [
        // Informational
        100 => "Continue", 
        101 => "Switching Protocols", 
        // Success
        200 => "OK", 
        201 => "Created", 
        202 => "Accepted", 
        203 => "Non-Authoritative Information", 
        204 => "No Content", 
        205 => "Reset Content", 
        206 => "Partial Content", 
        // Redirection
        300 => "Multiple Choices", 
        301 => "Moved Permanently", 
        302 => "Found", 
        303 => "See Other", 
        304 => "Not Modified", 
        305 => "Use Proxy", 
        307 => "Temporary Redirect", 
        // Client Error
        400 => "Bad Request", 
        401 => "Unauthorized", 
        402 => "Payment Required", 
        403 => "Forbidden", 
        404 => "Not Found", 
        405 => "Method Not Allowed", 
        406 => "Not Acceptable", 
        407 => "Proxy Authentication Required", 
        408 => "Request Time-out", 
        409 => "Conflict", 
        410 => "Gone", 
        411 => "Length Required", 
        412 => "Precondition Failed", 
        413 => "Request Entity Too Large", 
        414 => "Request-URI Too Large", 
        415 => "Unsupported Media Type", 
        416 => "Requested range not satisfiable", 
        417 => "Expectation Failed", 
        // Server Error
        500 => "Internal Server Error", 
        501 => "Not Implemented", 
        502 => "Bad Gateway", 
        503 => "Service Unavailable", 
        504 => "Gateway Time-out", 
        505 => "HTTP Version not supported"];

    /**
     *
     * @var HeaderFields
     */
    protected $headers;

    /**
     *
     * @var string
     */
    protected $content;

    /**
     */
    public function __construct()
    {
        $this->headers = new HeaderFields();
    }

    /**
     *
     * @param string $content
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     *
     * @return void
     */
    public function send()
    {
        $this->sendHeaders();
        
        // Message body
        if ($this->content !== null)
            echo $this->content;
    }

    /**
     *
     * @return void
     */
    public function sendHeaders()
    {
        if ($this->statusCode === null) {
            return;
        }
        
        // Status-Line
        header("{$this->version} {$this->statusCode} {$this->reasonPhrases[$this->statusCode]}");
        
        // Headers
        foreach ($this->headers as $fieldName => $fieldValue) {
            
            if (is_string($fieldValue))
                header("$fieldName: $fieldValue");
            
            elseif (is_array($fieldValue))
                foreach ($fieldValue as $value)
                    header("$fieldName: $value", false);
        }
        
        // Make sure to send headers only once
        $this->statusCode = null;
        $this->headers->clear();
    }

    /**
     * Sends 204 No Content header to the client
     *
     * @return void
     */
    public function sendNoContent()
    {
        $this->setStatusCode(204);
        $this->getHeaders()->add("Content-Length", 0);
        
        ob_end_clean();
        
        $this->send();
    }

    /**
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     *
     * @param string $version
     * @return void
     */
    public function setVersion($version)
    {
        if (is_string($version))
            $this->version = $version;
    }

    /**
     *
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     *
     * @param int $code
     * @return void
     */
    public function setStatusCode($code)
    {
        if (isset($this->reasonPhrases[$code]))
            $this->statusCode = (int)$code;
    }

    /**
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->statusCode ? $this->reasonPhrases[$this->statusCode] : "";
    }

    /**
     *
     * @return HeaderFields
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *
     * @param HeaderFields $headers
     * @return void
     */
    public function setHeaders(HeaderFields $headers)
    {
        $this->headers = $headers;
    }

    /**
     *
     * @return bool
     */
    public function isRedirect()
    {
        return $this->headers->has("Location");
    }

    /**
     * Redirects to specified location and terminates application
     *
     * @param string $location Path to redirect
     * @param int $code [optional] HTTP response code, defaults to 302
     * @param bool $terminate [optional] Whether to throw TerminateException or not, defaults to true
     * @return void
     * @throws TerminateException
     */
    public function redirect($location, $code = 302, $terminate = true)
    {
        if (!is_string($location))
            return;
        
        $event = new FilterEvent(static::FILTER_REDIRECT_LOCATION, $location);
        $this->dispatchEvent($event);
        
        $this->setStatusCode($code);
        $this->getHeaders()->add("Location", $event->value);
        
        if ($terminate)
            throw new TerminateException("Redirecting to '{$event->value}'");
    }
}