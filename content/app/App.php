<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midlWebsite\app;

use midl\app\Application;
use midl\core\event\FilterEvent;
use midl\core\exception\NotFoundException;
use midl\core\exception\TerminateException;
use midl\database\Database;
use midl\i18n\I18n;
use midl\i18n\TranslateAdapterGettext;
use midl\i18n\Translator;
use midl\log\Logger;
use midl\net\http\Request;
use midl\net\http\Response;
use midl\net\http\Session;
use midl\net\Router;
use midl\net\URI;
use midl\package\Pactory;
use midlWebsite\app\controller\Controller;
use midlWebsite\app\controller\Error;

class App extends Application
{

    /**
     *
     * @var Request
     */
    public $request;

    /**
     *
     * @var Response
     */
    public $response;

    /**
     *
     * @var I18n
     */
    public $i18n;

    /**
     *
     * @var Database
     */
    public $db;

    /**
     *
     * @var Controller
     */
    public $controller;

    /**
     *
     * @var User
     */
    public $user;

    /**
     *
     * @var Session
     */
    public $session;

    /**
     *
     * @var array
     */
    public $uriSegments;

    /**
     *
     * @var string
     */
    protected $siteUrl;

    /**
     *
     * @param string $rootPath Application root path
     * @return void
     */
    public function __construct($rootPath)
    {
        try {
            parent::__construct($rootPath);
            self::run();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        
        $this->response->send();
    }

    /**
     *
     * @return void
     */
    protected function run()
    {
        $basePath = str_replace("//", "/", dirname(@$_SERVER["SCRIPT_NAME"]) . "/");
        $logPath = "{$this->rootPath}content/logs/" . date("Y-m") . ".log";
        $transPath = "{$this->rootPath}content/translations/";
        
        // Initialize Logger, Request and Response
        parent::run($basePath, $logPath);
        
        // Sets package base directory path to autoload packages
        Pactory::setBasePath("{$this->rootPath}content/package/");
        
        $this->i18n = new I18n();
        $locale = $this->i18n->getLocale()->getValue();
        $this->i18n->setTranslator(new Translator(new TranslateAdapterGettext($transPath), $locale));
        
        $this->response->addEventListener(Response::FILTER_REDIRECT_LOCATION, 
            [$this, "filterRedirectLocation"]);
        
        $this->db = Database::connectMySQL("root", "password");
        $this->session = new Session();
        $this->user = new User($this);
        
        $this->setLoggerFormat();
        
        $controllerClass = Router::resolve($this->getUriSegmentAt(0));
        
        if ($controllerClass && class_exists($controllerClass)) {
            $this->controller = new $controllerClass($this);
            $this->response->setContent($this->controller->getContent());
        } else
            throw new NotFoundException("Couldn't find controller.");
    }

    /**
     *
     * @return string
     */
    public function getSiteUrl()
    {
        if (!$this->siteUrl) {
            $lang = $this->i18n->getLocale()->getLanguage();
            
            if ($this->i18n->getDefaultLocale()->getLanguage() === $lang)
                $langSegment = "";
            else
                $langSegment = "$lang/";
            
            $this->siteUrl = $this->request->getUri()->getBaseUri() . $langSegment;
        }
        
        return $this->siteUrl;
    }

    /**
     *
     * @return array
     */
    public function getUriSegments()
    {
        static $segments = null;
        
        if ($segments === null) {
            $segments = $this->request->getUri()->getSegments();
            
            if (isset($segments[0]) && $segments[0] === $this->i18n->getLocale()->getLanguage())
                $segments = array_slice($segments, 1);
        }
        
        return $segments;
    }

    /**
     *
     * @return string
     */
    public function getUriSegmentAt($index)
    {
        $segments = $this->getUriSegments();
        
        if (isset($segments[$index]))
            return $segments[$index];
        
        return null;
    }

    /**
     *
     * @return void
     */
    public function filterRedirectLocation(FilterEvent $event)
    {
        if (preg_match("/^(https?:)?\/\//", $event->value))
            return;
        
        if (strpos($event->value, "/") === 0) {
            
            $lang = $this->i18n->getLocale()->getLanguage();
            
            if ($this->i18n->getDefaultLocale()->getLanguage() !== $lang)
                $event->value = "/$lang{$event->value}";
        }
        
        $event->value = $this->request->getUri()->getBasePath() . ltrim($event->value, "/");
    }

    /**
     *
     * @param \Exception $exception
     * @return void
     */
    protected function handleException(\Exception $exception)
    {
        if ($exception instanceof TerminateException)
            return;
        
        if (!$this->logger)
            exit("Failed to initialize Logger");
        
        if ($exception instanceof NotFoundException) {
            $httpStatusCode = 404;
            $logLevel = Logger::ERROR;
        } else {
            $httpStatusCode = 500;
            $logLevel = Logger::ALERT;
        }
        
        $this->logger->log($exception, $logLevel);
        
        if (!$this->response)
            exit("Failed to initialize Response");
        
        if (!$this->i18n)
            exit("Failed to initialize I18n");
        
        $this->response->setStatusCode($httpStatusCode);
        
        $errorController = new Error($this, $httpStatusCode);
        $this->response->setContent($errorController->getView()->getContent());
    }

    /**
     * Sets a custom log message formatter for this application
     *
     * @return void
     */
    protected function setLoggerFormat()
    {
        $format = "\n[{REMOTE_ADDR} - {TIME[d.m.Y H:i:s O]}] [{REQUEST_URI}, {USER_ID}] ";
        $format .= "[{LOG_LEVEL}] {MESSAGE}\n{BACKTRACE[5]]}";
        
        $uri = $this->request->getUri();
        $uri = $uri->getPath() . ($uri->getQuery() ? "?" . $uri->getQuery() : "");
        
        $formatter = $this->logger->getFormatter();
        $formatter->setFormat($format);
        $formatter->addVar("REQUEST_URI", $uri);
        $formatter->addVar("USER_ID", $this->user->id);
    }
}
