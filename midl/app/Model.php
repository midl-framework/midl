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

use midl\database\Database;

class Model
{

    /**
     *
     * @var Controller
     */
    protected $controller;

    /**
     *
     * @var Database
     */
    protected $db;
    
    /**
     * 
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->db = $controller->app->db;
    }
}
