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

use midl\app\Application;
use midl\io\Assets;

abstract class Controller
{

    /**
     *
     * @var Application
     */
    protected $app;

    /**
     *
     * @var Model
     */
    protected $model;

    /**
     *
     * @var View
     */
    protected $view;

    /**
     *
     * @var Assets
     */
    protected $assets;

    /**
     *
     * @var string
     */
    protected $title = "";

    /**
     *
     * @param Application $app
     */
    protected function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     *
     * @return string|null
     */
    public function getContent()
    {
        if ($this->view)
            return $this->view->getContent();
        
        return null;
    }

    /**
     *
     * @return Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     *
     * @return Assets
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        if (is_string($title))
            $this->title = $title;
    }
}