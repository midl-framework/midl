<?php
use midl\loader\Autoloader;
use midlWebsite\app\App;

define("APP_ROOT_DIR", __DIR__ . DIRECTORY_SEPARATOR);

/**
 *
 * @var Autoloader $loader
 */
$loader = require_once APP_ROOT_DIR . "midl/loader/Autoload.php";
$loader->addNamespace("midlWebsite", APP_ROOT_DIR . "content");

require_once APP_ROOT_DIR . "vendor/autoload.php";
include_once APP_ROOT_DIR . "content/config/config.php";

new App(APP_ROOT_DIR);
