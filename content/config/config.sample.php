<?php
use midl\net\Router;

/**
 * It is recommended to disable error reporting in production
 */
// error_reporting(0);

/**
 * It is recommended to set default timezone
 * otherwise every time you call date() function you might get E_WARNINGs
 *
 * @link http://php.net/manual/en/timezones.php
 */
if (!ini_get("date.timezone"))
    date_default_timezone_set("UTC");

/**
 * Secret key to be used for encryption, some random string, longer is better.
 * Before deploy to production change this value and once changed
 * please do not update it, otherwise it might break some functionality of application.
 *
 * @var string
 */
define("APP_KEY", "secret_key");

/**
 */
Router::add("", 'midlWebsite\app\controller\Home');
Router::add("home", 'midlWebsite\app\controller\Home');
