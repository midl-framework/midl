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
use midl\core\exception\NotFoundException;

class Home extends Controller
{

    /**
     *
     * @return string
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        
        $this->setTitle("Welcome");
        
        $action = $this->app->getUriSegmentAt(1);
        
        if (!$action)
            $this->indexAction();
        elseif ($action === "logo")
            $this->logoAction();
        else
            throw new NotFoundException("Couldn't find action '$action'");
    }

    /**
     *
     * @return void
     */
    private function indexAction()
    {
        $this->view->setViewFile("page/home.php");
    }

    /**
     *
     * @return void
     */
    private function logoAction()
    {
        $this->view->cleanContent();
        
        $this->app->response->getHeaders()->add("Content-Type", "image/png");
        $this->app->response->sendHeaders();
        
        readfile($this->app->getRootPath() . "assets/img/logo.png");
    }
}