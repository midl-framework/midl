<?php
use midl\loader\Autoloader;

require_once __DIR__ . "/Autoloader.php";

return new Autoloader("midl", dirname(__DIR__));
