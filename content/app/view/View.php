<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midlWebsite\app\view;

use midl\i18n\Translator;
use midl\net\URI;
use midlWebsite\app\App;
use midlWebsite\app\controller\Controller;
use midlWebsite\app\User;

class View extends \midl\app\View
{

    /**
     *
     * @var App
     */
    protected $app;

    /**
     *
     * @var User
     */
    protected $user;

    /**
     *
     * @var URI
     */
    protected $uri;

    /**
     *
     * @var Translator
     */
    protected $translator;

    /**
     *
     * @var string
     */
    protected $themeFile = "theme/default.php";

    /**
     *
     * @see \midl\app\View::__construct()
     */
    public function __construct(Controller $controller, $rootPath)
    {
        parent::__construct($controller, $rootPath);
        
        // Some shortcuts which will be used frequently in views
        $this->app = $this->controller->app;
        $this->user = $this->app->user;
        $this->uri = $this->app->request->getUri();
        $this->translator = $this->app->i18n->getTranslator();
    }

    /**
     *
     * @return string
     */
    public function getCSSPath()
    {
        return $this->uri->getBasePath() . $this->controller->assets->getMinifiedCSSPath();
    }

    /**
     *
     * @return string
     */
    public function getCSSPaths()
    {
        $paths = "";
        $basePath = $this->uri->getBasePath();
        
        foreach ($this->controller->assets->getCSSPaths() as $cssFile)
            $paths .= '<link type="text/css" rel="stylesheet" href="' . $basePath . $cssFile . '" />';
        
        return $paths;
    }

    /**
     *
     * @return string
     */
    public function getJSPath()
    {
        return $this->uri->getBasePath() . $this->controller->assets->getMinifiedJSPath();
    }

    /**
     *
     * @return string
     */
    public function getJSPaths()
    {
        $paths = "";
        $basePath = $this->uri->getBasePath();
        
        foreach ($this->controller->assets->getJSPaths() as $jsFile)
            $paths .= '<script type="text/javascript" src="' . $basePath . $jsFile . '"></script>';
        
        return $paths;
    }

    /**
     *
     * @param string $img Image alias or path
     * @return string
     */
    public function getImgPath($img)
    {
        return $this->uri->getBasePath() . $this->controller->assets->getImgPath($img);
    }
}
