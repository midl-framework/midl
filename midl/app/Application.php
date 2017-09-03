<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\app;

use midl\log\Logger;
use midl\net\http\Request;
use midl\net\http\Response;
use midl\net\URI;

class Application
{

    /**
     * Application root path
     *
     * @var string
     */
    protected $rootPath;

    /**
     *
     * @var Logger
     */
    protected $logger;

    /**
     *
     * @var Request
     */
    protected $request;

    /**
     *
     * @var Response
     */
    protected $response;

    /**
     *
     * @param string $rootPath Application root path
     * @throws \InvalidArgumentException
     */
    public function __construct($rootPath)
    {
        if (!is_dir($rootPath))
            throw new \InvalidArgumentException("Application root path '$rootPath' doesn't exists.");
        
        $this->rootPath = rtrim($rootPath, "/\\") . DIRECTORY_SEPARATOR;
    }

    /**
     * Bootstrap application, for every MIDL framework application all magics starts here.
     *
     * @param string $basePath Application base path,
     *        e.g.: /var/host/www/path/to/app/ ==> /path/to/app/
     * @param array|string $logOptions [optional] Log file path or options array
     * @return void
     */
    protected function run($basePath, $logOptions = null)
    {
        $this->logger = new Logger($logOptions);
        
        $this->request = new Request(new URI(null, $basePath));
        $this->response = new Response();
    }

    /**
     *
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
