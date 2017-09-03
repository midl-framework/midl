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

use midl\net\URI;

/**
 *
 * @link https://tools.ietf.org/html/rfc2616#page-35
 */
class Request
{

    /**
     *
     * @var string
     */
    const METHOD_OPTIONS = "OPTIONS";

    const METHOD_GET = "GET";

    const METHOD_HEAD = "HEAD";

    const METHOD_POST = "POST";

    const METHOD_PUT = "PUT";

    const METHOD_DELETE = "DELETE";

    const METHOD_TRACE = "TRACE";

    const METHOD_CONNECT = "CONNECT";

    /**
     *
     * @var string
     */
    protected $method;

    /**
     *
     * @var URI
     */
    protected $uri;

    /**
     *
     * @var string
     */
    protected $version = "HTTP/1.1";

    /**
     *
     * @var HeaderFields
     */
    protected $headers;

    /**
     *
     * @var Cookies
     */
    protected $cookies;

    /**
     *
     * @var string
     */
    protected $content;

    /**
     * Constructor
     */
    function __construct(URI $uri)
    {
        $this->uri = $uri;
        $this->cookies = new Cookies($uri->getHost(), $uri->getBasePath());
        $this->headers = new HeaderFields();
    }

    /**
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     *
     * @param string $method
     * @return void
     */
    public function setMethod($method)
    {
        if (defined("static::METHOD_$method"))
            $this->method = $method;
    }

    /**
     *
     * @return URI
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     *
     * @return void
     */
    public function setUri(URI $uri)
    {
        $this->uri = $uri;
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
     * @return Cookies
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     *
     * @param Cookies $cookies
     * @return void
     */
    public function setCookies(Cookies $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     *
     * @return string
     */
    public function getContent()
    {
        if ($this->content === null)
            $this->content = @file_get_contents("php://input") ?: "";
        
        return $this->content;
    }

    /**
     *
     * @param string $content
     * @return void
     */
    public function setContent($content)
    {
        if (is_string($content))
            $this->content = $content;
    }

    /**
     * This might not work for every client requests, client must set
     * HTTP_X_REQUESTED_WITH=XmlHttpRequest header before making request.
     *
     * @return string
     */
    public function isXmlHttpRequest()
    {
        if ($this->headers) {
            foreach ($this->headers as $header)
                if ($header[0] === "HTTP_X_REQUESTED_WITH" && $header[1] === "XmlHttpRequest")
                    return true;
        }
        
        return false;
    }
}
