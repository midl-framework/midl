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
namespace midl\net;

/**
 * http_URL = "http:" "//" host [ ":" port ] [ abs_path [ "?" query ]]
 *
 * @link https://tools.ietf.org/html/rfc2616#page-18
 */
class URI
{

    /**
     * Request path, examples: "http", "https"
     *
     * @var string
     */
    protected $scheme;

    /**
     * Host string, example: "example.tld"
     *
     * @var string
     */
    protected $host;

    /**
     *
     * @var int
     */
    protected $port;

    /**
     * Relative path, examples:
     * example.tld/app/base/dir/path/to/page/?p=val ==> "/path/to/page/"
     * example.tld/app/base/dir/path/to/page/index.php ==> "/path/to/page/index.php"
     * where application is located at: /app/base/dir/
     *
     * @var string
     */
    protected $path;

    /**
     * Decoded request query string, example:
     * example.tld/app/base/dir/page/?p1=a%20b&p2=c+d ==> "p1=a b&p2=c d"
     *
     * @var string
     */
    protected $query = "";

    /**
     * Each piece of path is a segment, example:
     * example.tld/base/dir/path/to/page/index.php ==> ["path", "to", "page", "index.php"]
     *
     * @var array
     */
    protected $segments = [];

    /**
     * Base path for application, example:
     * example.tld/path/to/app/controller/action/ ==> "/path/to/app/"
     *
     * @var string
     */
    protected $basePath = "/";

    /**
     *
     * @param string $uri [optional]
     * @param string $basePath [optional]
     */
    function __construct($uri = null, $basePath = null)
    {
        if ($basePath && is_string($basePath)) {
            $basePath = "/" . trim($basePath, "./\\") . "/";
            
            $this->basePath = str_replace("//", "/", $basePath);
        }
        
        if ($uri === null)
            $this->parseFromGlobals();
        
        elseif (is_string($uri))
            $this->parseUri($uri);
    }

    /**
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     *
     * @param bool $withPort [optional] Defaults to false
     * @return string
     */
    public function getHost($withPort = false)
    {
        return $this->host . ($withPort && $this->port ? ":{$this->port}" : "");
    }

    /**
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     *
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * Gets application base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Gets full URI
     *
     * @param bool $protocolRelative [optional] Whether to prepend scheme or not
     * @param bool $withQuery [optional] Whether to append query string or not
     * @return string
     */
    public function getUri($protocolRelative = false, $withQuery = true)
    {
        $scheme = $protocolRelative ? "" : "{$this->scheme}:";
        $basePath = rtrim($this->basePath, "/");
        $query = $withQuery && $this->query ? "?{$this->query}" : "";
        
        return "{$scheme}//{$this->host}{$basePath}{$this->path}{$query}";
    }

    /**
     * Gets application base URI, e.g.
     * http://example.tld/base/path/to/app/
     *
     * @param bool $protocolRelative [optional] Whether to prepend scheme or not
     * @return string
     */
    public function getBaseUri($protocolRelative = false)
    {
        $scheme = $protocolRelative ? "" : "{$this->scheme}:";
        
        return "{$scheme}//{$this->host}{$this->basePath}";
    }

    /**
     * Parses URI
     *
     * @param string $uri
     * @return void
     */
    protected function parseUri($uri)
    {
        if (!$uri)
            return;
        
        $uri = parse_url($uri);
        
        $this->scheme = @$uri["scheme"];
        $this->host = @$uri["host"];
        $this->port = @$uri["port"];
        $this->query = urldecode(@$uri["query"]);
        
        $this->path = "/" . substr(rawurldecode(@$uri["path"]), strlen($this->basePath));
        
        if (trim($this->path, "/") !== "")
            $this->segments = explode("/", trim($this->path, "/"));
    }

    /**
     * Parses URI from PHP globals
     *
     * @return void
     */
    protected function parseFromGlobals()
    {
        if (!($this->scheme = @$_SERVER["REQUEST_SCHEME"]))
            $this->scheme = @$_SERVER["HTTPS"] && @$_SERVER["HTTPS"] !== "off" ? "https" : "http";
        
        $host = parse_url(@$_SERVER["HTTP_HOST"]);
        $this->host = @$host["path"];
        $this->port = @$host["port"];
        
        $path = @$_SERVER["REQUEST_URI"];
        
        if (($qmPos = strpos($path, "?")) !== false) {
            
            // Using urldecode instead of rawurldecode will convert + chars to whitespaces
            $this->query = urldecode(substr($path, $qmPos + 1));
            
            $path = substr($path, 0, $qmPos);
        }
        
        $this->path = "/" . substr(rawurldecode($path), strlen($this->basePath));
        
        if (trim($this->path, "/") !== "")
            $this->segments = explode("/", trim($this->path, "/"));
    }
}
