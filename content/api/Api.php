<?php
namespace midlApi\app;

use midl\app\AuthUser;
use midl\app\Controller\Error;
use midl\app\Controller\User;
use midl\core\Database\Database;
use midl\core\Exception\Exception;
use midl\core\File\Image\Image;
use midl\core\I18n\Translator;
use midl\core\Loader\Loader;
use midl\core\Log\Logger;
use midl\core\Net\Route;
use midl\core\Net\Session;
use midl\core\Net\URL;

/**
 * Main application class,
 * this is the entry point to the application for each request
 *
 * @author Abdulhalim Kara
 */
class App
{

    /**
     * Application instance
     *
     * @var App
     */
    public static $instance;

    /**
     * Loader instance
     *
     * @var \midl\core\Loader\Loader
     */
    public static $loader;

    /**
     * Translator instance
     *
     * @var \midl\core\I18n\Translator
     */
    public static $translator;

    /**
     * URL instance
     *
     * @var \midl\core\Net\URL
     */
    public static $url;

    /**
     * Controller instance
     *
     * @var \midl\app\Controller\Controller
     */
    public static $controller;

    /**
     * Authenticated user instance
     *
     * @var \midl\app\AuthUser
     */
    public static $user;

    /**
     * Session instance
     *
     * @var \midl\core\Net\Session
     */
    public static $session;

    /**
     * DB instance
     *
     * @var \midl\core\Database\DB
     */
    public static $db;

    /**
     * Constructor
     *
     * @param \midl\core\Loader\Loader $loader
     */
    public function __construct(Loader $loader)
    {
        self::$instance = $this;
        self::$loader = $loader;
        
        Logger::init();
        
        register_shutdown_function(array($this, "end"));
    }

    /**
     * Run application
     *
     * @return void
     */
    public function run()
    {
        self::$translator = new Translator();
        self::$url = new URL();
        self::$db = new Database();
        self::$user = new AuthUser();
        self::$session = new Session();
        
        define("SITE_URL", ROOT_URL . self::$url->localeSlug . (self::$url->localeSlug ? "/" : ""));
        
        try {
            @include_once SYSTEM_DIR . "config/routes.php";
            
            $Controller = Route::get(self::$url->controllerSlug);
            
            if ($Controller && class_exists($Controller = "midl\\App\\Controller\\$Controller")) {
                self::$controller = new $Controller();
            } else {
                // a user page? example.tld / [username]
                self::$controller = new User();
            }
        } catch (Exception $e) {
            // Not found 404
            self::$controller = new Error();
        } catch (\Exception $e) {
            // internal server error 500
            self::$controller = new Error(Error::INTERNAL_ERROR);
        }
        
        if (self::$controller->standalone) {
            echo self::$controller->getBody();
            exit();
        }
        
        self::$controller->loadTheme();
    }

    /**
     * End application
     *
     * @return void
     */
    public function end()
    {
        Logger::shutdownHandler();
        
        self::$db->close();
    }

    /**
     * Get Image class instance
     *
     * @return \midl\core\File\Image\Image Image instance
     */
    public function image()
    {
        if (!$this->image)
            $this->image = new Image();
        
        return $this->image;
    }
}
