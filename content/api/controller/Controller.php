<?php
namespace midl\app\Controller;

use midl\app\App;
use midl\core\File\StaticFiles;

/**
 * Abstract base controller class
 * All controller class must extend this class.
 *
 * @author Abdulhalim Kara
 */
abstract class Controller
{
    // Constants
    
    // Public variables
    /**
     * View file path
     *
     * @var string
     */
    public $viewFile;

    /**
     * HTML content that will be rendered between head tags
     *
     * @var string
     */
    public $headHTML;

    /**
     * Script content, generally JavaScript
     *
     * @var string
     */
    public $scriptContent;

    /**
     * Comma separated script modules name
     *
     * @var string
     */
    public $jsModules = "base";

    /**
     * Comma separated css modules name
     *
     * @var string
     */
    public $cssModules = "base";

    /**
     * Message type to be displayed on the top of form,
     * possible values ["error", "warning", "success", "info"]
     *
     * @var string
     */
    public $formMessageType;

    /**
     * Message to be displayed on the top of form
     *
     * @var string
     */
    public $formMessage;

    /**
     * Whether or not this view has standalone rendering
     * If it is true then application bootsrap will skip loading theme structure
     *
     * @var bool
     */
    public $standalone;
    
    // Private variables
    /**
     * Theme index file name
     *
     * @var string
     */
    protected $theme = "default";

    /**
     * List of view files for current controller
     * This variable must be redefined in each controller class
     *
     * @var array
     */
    protected $views;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->viewFile = strtolower(substr(strrchr(get_class($this), "\\"), 1));
    }
}
