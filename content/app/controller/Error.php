<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midlWebsite\app\controller;

use midlWebsite\app\App;

class Error extends Controller
{

    /**
     *
     * @return string
     */
    public function __construct(App $app, $statusCode = 404)
    {
        parent::__construct($app);
        
        $this->view->setThemeFile("theme/error.php");
        $this->assets->addCSS("base");
        $this->assets->addJS("base");
        
        $this->setTitle($this->app->response->getReasonPhrase());
        
        if ($statusCode == 404)
            $this->notFoundAction();
        else
            $this->internalAction();
    }

    /**
     *
     * @return void
     */
    private function notFoundAction()
    {
        $this->view->setViewFile("error/404.php");
    }

    /**
     *
     * @return void
     */
    private function internalAction()
    {
        $this->view->setViewFile("error/500.php");
    }
}