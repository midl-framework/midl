<?php
namespace midl\app\Controller;

use midl\app\App;
use midl\core\Exception\Exception;

/**
 * Homepage controller
 *
 * @author Abdulhalim Kara
 */
class Home extends Controller
{
    // Constants
    const DEFAULT_ACTION = "home";
    
    // Public variables
    
    // Private variables
    /**
     * Requested action
     *
     * @var array
     */
    private $action;

    /**
     * Available actions for this controller
     * Action => [Method Name, Page Title] pairs
     *
     * @var array
     */
    private $actions = array(
        "home" => array("method" => "homeAction", "title" => "Welcome"));

    /**
     * List of view files
     *
     * @var array
     */
    protected $views = array("home" => "pages/home.php");

    /**
     * Constructor
     */
    public function __construct()
    {
        $actionSlug = App::$url->actionSlug ?: self::DEFAULT_ACTION;
        
        if (!array_key_exists($actionSlug, $this->actions))
            throw new Exception("Could not find action: '$actionSlug'");
        
        parent::__construct();
        
        $this->action = $this->actions[$actionSlug];
        $this->viewFile = $actionSlug;
        $this->{$this->action["method"]}();
    }

    /**
     * Home action
     *
     * @return void
     */
    private function homeAction()
    {}

    /**
     * Get page title
     *
     * @return string
     */
    public function getTitle()
    {
        return t__("Welcome");
    }
}