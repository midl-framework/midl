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

use midl\io\Assets;
use midl\package\Pactory;
use midlWebsite\app\App;
use midlWebsite\app\view\View;

abstract class Controller extends \midl\app\Controller
{

    /**
     *
     * @var App
     */
    public $app;

    /**
     *
     * @var Assets
     */
    public $assets;

    /**
     *
     * @param App $app
     */
    protected function __construct(App $app)
    {
        parent::__construct($app);
        
        // TODO delete Pactory
        $minifier = Pactory::newSingleton("midl/io/AssetMinifr.php", 'midl\io\AssetMinifr');
        $assetVars = ['LANG' => $app->i18n->getLocale()->getLanguage()];
        $this->assets = new Assets($app->getRootPath(), "content/config/assets.php", $assetVars, $minifier);

        $this->assets->addCSS("base");
        $this->assets->addJS("base");
        
        $this->view = new View($this, $app->getRootPath() . "content/app/www/");
    }
}
